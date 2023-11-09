<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Plugin\AntragoGradeOverview;

use Exception;
use ilAntragoGradeOverviewPlugin;
use ilGlobalPageTemplate;
use ILIAS\DI\Container;
use ILIAS\Plugin\AntragoGradeOverview\Utils\UiUtil;
use ilTemplate;
use ilLanguage;
use Psr\Http\Message\ServerRequestInterface;
use ilUtil;
use ilUIPluginRouterGUI;
use ilAntragoGradeOverviewUIHookGUI;
use ilAchievementsGUI;
use ilPersonalDesktopGUI;
use ilCtrl;
use ILIAS\Plugin\AntragoGradeOverview\Repository\GradeDataRepository;
use ilObjUser;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Plugin\AntragoGradeOverview\Model\GradeData;
use ilSetting;
use ilDashboardGUI;
use ILIAS\Plugin\AntragoGradeOverview\Exception\ValueConvertException;

class AntragoGradeOverview
{
    public const AGOP_DEFAULT_SORTING = "desc";
    public const AGOP_USER_PREF_SORTING_KEY_DATE = "agop_sortation_date";
    public const AGOP_USER_PREF_SORTING_KEY_SUBJECT = "agop_sortation_subject";
    public const AGOP_GRADES_TAB = "agop_grades_tab";

    protected ilSetting $settings;
    protected Renderer $renderer;
    protected Factory $factory;
    protected ilObjUser $user;
    protected GradeDataRepository $gradeDataRepo;
    protected ilCtrl $ctrl;
    protected ilGlobalPageTemplate $mainTpl;
    protected ilLanguage $lng;
    protected ServerRequestInterface $request;
    protected Container $dic;
    protected ilAntragoGradeOverviewPlugin $plugin;
    private UiUtil $uiUtil;

    public function __construct(Container $dic)
    {
        $this->dic = $dic;
        $this->request = $dic->http()->request();
        $this->ctrl = $this->dic->ctrl();
        $this->lng = $dic->language();
        $this->lng->loadLanguageModule("pd");
        $this->lng->loadLanguageModule("esc");

        $this->user = $dic->user();
        $this->mainTpl = $dic->ui()->mainTemplate();
        $this->plugin = ilAntragoGradeOverviewPlugin::getInstance();
        $this->factory = $dic->ui()->factory();
        $this->renderer = $dic->ui()->renderer();
        $this->settings = new ilSetting(ilAntragoGradeOverviewPlugin::class);
        $this->gradeDataRepo = GradeDataRepository::getInstance();
        $this->uiUtil = new UiUtil($this->dic);
    }

    public function gradesOverviewSorting(): void
    {
        $query = $this->request->getQueryParams();

        if (isset($query["date_sorting"])) {
            $dateSorting = $query["date_sorting"];
            if (!in_array($dateSorting, ["asc", "desc"])) {
                $dateSorting = self::AGOP_DEFAULT_SORTING;
            }
            $this->user->writePref(self::AGOP_USER_PREF_SORTING_KEY_DATE, $dateSorting);
        }

        if (isset($query["subject_sorting"])) {
            $subjectSorting = $query["subject_sorting"];
            if (!in_array($subjectSorting, ["asc", "desc"])) {
                $subjectSorting = self::AGOP_DEFAULT_SORTING;
            }
            $this->user->writePref(self::AGOP_USER_PREF_SORTING_KEY_SUBJECT, $subjectSorting);
        }

        $this->ctrl->redirectByClass(
            [ilUIPluginRouterGUI::class, ilAntragoGradeOverviewUIHookGUI::class],
            "showGradesOverview"
        );
    }

    /**
     * @throws Exception
     */
    public function showGradesOverview(): void
    {
        if (!$this->plugin->hasAccessToLearningAchievements()) {
            $this->uiUtil->sendFailure($this->plugin->txt("achievementsNotActive"), true);
            $this->plugin->redirectToHome();
        }

        $this->drawHeader();
        $this->mainTpl->loadStandardTemplate();

        $this->dic->tabs()->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTargetByClass([
                ilDashboardGUI::class,
                ilAchievementsGUI::class
            ])
        );

        $this->dic->tabs()->addTab(
            self::AGOP_GRADES_TAB,
            $this->plugin->txt("grades"),
            $this->ctrl->getLinkTargetByClass(
                [ilUIPluginRouterGUI::class, ilAntragoGradeOverviewUIHookGUI::class],
                "showGradesOverview"
            )
        );
        $this->dic->tabs()->activateTab(self::AGOP_GRADES_TAB);

        $this->buildSorting();
        $selectedSorting = $this->getUserGradesSortingPref();
        try {
            $gradesData = $this->gradeDataRepo->readAllByMatriculation(
                $this->user->getMatriculation(),
                $selectedSorting["date"],
                $selectedSorting["subject"]
            );
        } catch (ValueConvertException $ex) {
            $this->uiUtil->sendFailure($ex->getMessage(), true);
            $gradesData = [];
        }

        $gradesOverviewHtml = $this->buildGradesOverview($gradesData);

        $this->mainTpl->setContent($gradesOverviewHtml);
        $this->mainTpl->printToStdOut();
    }

    protected function getUserGradesSortingPref(): array
    {
        $subjectPref = $this->user->getPref(self::AGOP_USER_PREF_SORTING_KEY_SUBJECT);
        $datePref = $this->user->getPref(self::AGOP_USER_PREF_SORTING_KEY_DATE);

        if (!$subjectPref || !in_array($subjectPref, ["asc", "desc"])) {
            $subjectPref = self::AGOP_DEFAULT_SORTING;
            $this->user->writePref(self::AGOP_USER_PREF_SORTING_KEY_SUBJECT, $subjectPref);
        }

        if (!$datePref || !in_array($datePref, ["asc", "desc"])) {
            $datePref = self::AGOP_DEFAULT_SORTING;
            $this->user->writePref(self::AGOP_USER_PREF_SORTING_KEY_DATE, $datePref);
        }

        return [
            "subject" => $subjectPref,
            "date" => $datePref
        ];
    }

    protected function buildSorting(): void
    {
        $selectedSorting = $this->getUserGradesSortingPref();

        $subjectSortingLabel = sprintf(
            $this->plugin->txt("sortingBy"),
            $this->plugin->txt("subject"),
            $this->lng->txt($selectedSorting["subject"] === "asc" ? "sorting_asc" : "sorting_desc")
        );

        $dateSortingLabel = sprintf(
            $this->plugin->txt("sortingBy"),
            $this->lng->txt("date"),
            $this->lng->txt($selectedSorting["date"] === "asc" ? "sorting_asc" : "sorting_desc")
        );

        $dateSortingComponent = $this->factory->viewControl()->sortation([
            "asc" => $this->lng->txt("sorting_asc"),
            "desc" => $this->lng->txt("sorting_desc")
        ])->withLabel($dateSortingLabel)
                                              ->withTargetURL(
                                                  $this->ctrl->getLinkTargetByClass([
                                                      ilUIPluginRouterGUI::class,
                                                      ilAntragoGradeOverviewUIHookGUI::class
                                                  ], "gradesOverviewSorting"),
                                                  "date_sorting"
                                              );

        $subjectSortingComponent = $this->factory->viewControl()->sortation([
            "asc" => $this->lng->txt("sorting_asc"),
            "desc" => $this->lng->txt("sorting_desc")
        ])->withLabel($subjectSortingLabel)
                                                 ->withTargetURL(
                                                     $this->ctrl->getLinkTargetByClass([
                                                         ilUIPluginRouterGUI::class,
                                                         ilAntragoGradeOverviewUIHookGUI::class
                                                     ], "gradesOverviewSorting"),
                                                     "subject_sorting"
                                                 );

        $this->dic->toolbar()->addComponent($dateSortingComponent);
        $this->dic->toolbar()->addComponent($subjectSortingComponent);
    }

    /**
     * @param GradeData[] $gradesData
     */
    protected function buildGradesOverview(array $gradesData): string
    {
        $entries = [];
        if (count($gradesData) === 0) {
            $noEntriesItem = $this->factory->item()->standard("")->withLeadText($this->plugin->txt("noGradesAvailable"));
            $entries[] = $this->factory->item()->group("", [$noEntriesItem]);
        }
        foreach ($gradesData as $gradeData) {
            $properties = [
                $this->plugin->txt("examiner") => $gradeData->getTutor(),
                $this->lng->txt("date") => $gradeData->getDate()->format("d.m.Y"),
                $this->plugin->txt("grade") => number_format(
                    $gradeData->getGrade(),
                    1,
                    ",",
                    "."
                ),
                $this->lng->txt("status") => $this->buildStatus($gradeData->isPassed()),
            ];
            if ($gradeData->getEctsPktTn() != "") {
                $properties[$this->plugin->txt("rating_points")] = $gradeData->getEctsPktTn();
            }
            if ($gradeData->getNumberOfRepeats() >= 1) {
                $properties[$this->plugin->txt("retryNumber")] = $gradeData->getNumberOfRepeats();
            }

            $item = $this->factory
                ->item()
                ->standard(
                    htmlspecialchars(
                        $gradeData->getSemester()
                        . " ({$gradeData->getSemesterLocation()}):"
                        . " " . $gradeData->getSubjectName()
                    )
                )
                ->withProperties($properties);

            $entries[] = $this->factory->item()->group("", [$item]);
        }

        $list = $this->factory
            ->panel()
            ->listing()
            ->standard(
                sprintf(
                    $this->plugin->txt("gradesOverviewOfUser"),
                    $this->user->getFirstname(),
                    $this->user->getLastname()
                ),
                $entries
            );
        return $this->renderer->render($list);
    }

    protected function buildStatus(bool $passed = true): string
    {
        if ($passed) {
            return $this->plugin->txt("passed") . " " . $this->buildImageIcon(ilUtil::getImagePath("icon_ok.svg"), "");
        }

        return $this->plugin->txt("failed") . " " . $this->buildImageIcon(
            ilUtil::getImagePath("icon_not_ok.svg"),
            ""
        );
    }

    protected function buildImageIcon(string $src, string $alt): string
    {
        return "<img border=\"0\" align=\"middle\" src=\"" . $src . "\" alt=\"" . $alt . "\" />";
    }

    protected function drawHeader(): void
    {
        $this->mainTpl->setTitle($this->plugin->txt("grades"));
        $this->mainTpl->setTitleIcon(ilUtil::getImagePath("icon_lhist.svg"));
    }

    /**
     * @throws Exception
     */
    public function performCommand(string $cmd): void
    {
        if (method_exists($this, $cmd)) {
            $this->{$cmd}();
        } else {
            throw new Exception(sprintf($this->plugin->txt("cmdNotFound"), $cmd));
        }
    }
}
