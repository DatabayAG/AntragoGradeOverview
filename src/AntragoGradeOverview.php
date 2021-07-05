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
use ilTemplateException;

class AntragoGradeOverview
{
    public const AGOP_DEFAULT_GRADES_SORTING = "date";
    public const AGOP_USER_PREF_SORTING_KEY = "agop_sortation";
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

        $selectedSorting = self::AGOP_USER_PREF_SORTING_KEY;
        if ($query["sorting"] && in_array($query["sorting"], ["date", "subject"])) {
            $selectedSorting = $query["sorting"];
        }

        $this->user->writePref(self::AGOP_USER_PREF_SORTING_KEY, $selectedSorting);
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

        $sortingHtml = $this->buildSorting();
        $selectedSorting = $this->getUserGradesSortingPref();
        $gradesData = $this->gradeDataRepo->readAll($this->user->getMatriculation());
        usort($gradesData, function (GradeData $a, GradeData $b) use ($selectedSorting) : int {
            /**
             * @var GradeData $a
             * @var GradeData $b
             */
            if ($selectedSorting === "date") {
                return $b->getDate() <=> $a->getDate();
            } elseif ($selectedSorting === "subject") {
                return strcasecmp($a->getSubjectName(), $b->getSubjectName());
            } else {
                return 1;
            }
        });

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
        $subjectPref = $this->user->getPref(self::AGOP_USER_PREF_SORTING_KEY . "_subject");
        $datePref = $this->user->getPref(self::AGOP_USER_PREF_SORTING_KEY . "_date");

        if (!$subjectPref || !in_array($subjectPref, ["asc", "desc"])) {
            $this->user->writePref(self::AGOP_USER_PREF_SORTING_KEY . "_subject", $subjectPref);
        }

        if (!$datePref || !in_array($datePref, ["asc", "desc"])) {
            $this->user->writePref(self::AGOP_USER_PREF_SORTING_KEY . "_date", $datePref);
        }

        return [
            "subject" => $subjectPref,
            "date" => $datePref
        ];
    }

    /**
     * Builds the sorting html string
     * @return string
     * @throws ilTemplateException
     */
    protected function buildSorting() : string
    {
        $selectedSorting = $this->getUserGradesSortingPref();

        $sortingElements = [];

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

        $sortingElements[] = $this->factory->viewControl()->sortation([
            "asc" => $this->lng->txt("sorting_asc"),
            "desc" => $this->lng->txt("sorting_desc")
        ])->withLabel($dateSortingLabel)
                                           ->withTargetURL(
                                               $this->ctrl->getLinkTargetByClass([
                                                   ilUIPluginRouterGUI::class,
                                                   ilAntragoGradeOverviewUIHookGUI::class
                                               ], "gradesOverviewSorting"),
                                               "sorting"
                                           );

        $sortingElements[] = $this->factory->viewControl()->sortation([
            "asc" => $this->lng->txt("sorting_asc"),
            "desc" => $this->lng->txt("sorting_desc")
        ])->withLabel($subjectSortingLabel)
          ->withTargetURL(
              $this->ctrl->getLinkTargetByClass([
                  ilUIPluginRouterGUI::class,
                  ilAntragoGradeOverviewUIHookGUI::class
              ], "gradesOverviewSorting"),
              "sorting"
          );

        $this->mainTpl->addCss($this->plugin->cssFolder("grade_overview.css"));

        $sortationTemplate = new ilTemplate($this->plugin->templatesFolder("tpl.grade_overview_sortation.html"), true, true);
        $sortationTemplate->setVariable("SORTER", $this->renderer->render($sortingElements));

        return $sortationTemplate->get();
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
