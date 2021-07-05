<?php

declare(strict_types=1);

namespace ILIAS\Plugin\AntragoGradeOverview;

use Exception;
use ilAntragoGradeOverviewPlugin;
use ILIAS\DI\Container;
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

class AntragoGradeOverview
{
    public const AGOP_DEFAULT_SORTING = "desc";
    public const AGOP_USER_PREF_SORTING_KEY_DATE = "agop_sortation_date";
    public const AGOP_USER_PREF_SORTING_KEY_SUBJECT = "agop_sortation_subject";
    public const AGOP_GRADES_TAB = "agop_grades_tab";
    /**
     * @var ilSetting
     */
    protected $settings;
    /**
     * @var Renderer
     */
    protected $renderer;
    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var ilObjUser
     */
    protected $user;
    /**
     * @var GradeDataRepository
     */
    protected $gradeDataRepo;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilTemplate
     */
    protected $mainTpl;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ServerRequestInterface
     */
    protected $request;
    /**
     * @var Container
     */
    protected $dic;
    /**
     * @var ilAntragoGradeOverviewPlugin
     */
    protected $plugin;

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
    }

    /**
     * Handles saving of the grades overview sorting
     */
    public function gradesOverviewSorting()
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
    public function showGradesOverview()
    {
        if (!$this->plugin->hasAccessToLearningAchievements()) {
            ilUtil::sendFailure($this->plugin->txt("achievementsNotActive"), true);
            $this->plugin->redirectToHome();
        }

        $this->drawHeader();

        if ($this->plugin->isAtLeastIlias6()) {
            $this->mainTpl->loadStandardTemplate();
        } else {
            $this->dic->tabs()->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTargetByClass([
                    $this->plugin->isAtLeastIlias6() ? ilDashboardGUI::class : ilPersonalDesktopGUI::class,
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
            $this->mainTpl->getStandardTemplate();
        }

        $this->buildSorting();
        $selectedSorting = $this->getUserGradesSortingPref();
        $gradesData = $this->gradeDataRepo->readAll(
            $this->user->getMatriculation(),
            $selectedSorting["date"],
            $selectedSorting["subject"]
        );

        $gradesOverviewHtml = $this->buildGradesOverview($gradesData);

        $this->mainTpl->setContent($sortingHtml . $gradesOverviewHtml);

        if ($this->plugin->isAtLeastIlias6()) {
            $this->dic->ui()->mainTemplate()->printToStdOut();
        } else {
            $this->mainTpl->show();
        }
    }

    /**
     * Returns the saved user sorting preference
     * returns either date or subject
     * @return array
     */
    protected function getUserGradesSortingPref() : array
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

    /**
     * Builds the sorting
     * @return void
     */
    protected function buildSorting()
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
     * Builds the grades overview html using ilias list items
     * @param GradeData[] $gradesData
     */
    protected function buildGradesOverview(array $gradesData) : string
    {
        $entries = [];
        $gradePassedThreshold = (float) $this->settings->get("gradePassedThreshold", 4.5);
        if (count($gradesData) === 0) {
            $noEntriesItem = $this->factory->item()->standard("")->withLeadText($this->plugin->txt("noGradesAvailable"));
            $entries[] = $this->factory->item()->group("", [$noEntriesItem]);
        }
        foreach ($gradesData as $gradeData) {
            $item = $this->factory
                ->item()
                ->standard(htmlspecialchars($gradeData->getSubjectName()))
                ->withProperties([
                    $this->plugin->txt("instructor") => $gradeData->getInstructorName(),
                    $this->lng->txt("date") => $gradeData->getDate()->format("d.m.Y"),
                    $this->plugin->txt("grade") => number_format(
                        $gradeData->getGrade(),
                        1,
                        ",",
                        "."
                    ),
                    $this->plugin->txt("rating_points") => $gradeData->getEvaluation(),
                    $this->lng->txt("status") => $this->buildStatus($gradeData->getGrade() < $gradePassedThreshold),
                ]);
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

    /**
     * Builds the status displayed for grades with a
     * green (passed) or red (failed) icon
     * @param bool $passed
     * @return string
     */
    protected function buildStatus(bool $passed = true) : string
    {
        if ($passed) {
            return $this->plugin->txt("passed") . " " . $this->buildImageIcon(ilUtil::getImagePath("icon_ok.svg"), "");
        }

        return $this->plugin->txt("failed") . " " . $this->buildImageIcon(
                ilUtil::getImagePath("icon_not_ok.svg"),
                ""
            );
    }

    /**
     * Builds an image icon html string
     * @param $src
     * @param $alt
     * @return string
     */
    protected function buildImageIcon($src, $alt) : string
    {
        return "<img border=\"0\" align=\"middle\" src=\"" . $src . "\" alt=\"" . $alt . "\" />";
    }

    /**
     * Draws the header of the achievements page
     */
    protected function drawHeader() : void
    {
        if ($this->plugin->isAtLeastIlias6()) {
            $this->mainTpl->setTitle($this->plugin->txt("grades"));
        } else {
            $this->mainTpl->setTitle($this->lng->txt("pd_achievements"));
        }
        $this->mainTpl->setTitleIcon(ilUtil::getImagePath("icon_lhist.svg"));
    }

    /**
     * Performs the commands called in the ui hook gui of the plugin
     * @param string $cmd
     * @throws Exception
     */
    public function performCommand(string $cmd)
    {
        if (method_exists($this, $cmd)) {
            $this->{$cmd}();
        } else {
            throw new Exception(sprintf($this->plugin->txt("cmdNotFound"), $cmd));
        }
    }
}
