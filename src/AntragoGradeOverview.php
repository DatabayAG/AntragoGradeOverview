<?php

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
use Twig\Environment;
use ilCtrl;
use ILIAS\Plugin\AntragoGradeOverview\Repository\GradeDataRepository;
use ilObjUser;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Plugin\AntragoGradeOverview\Model\GradeData;
use ilSetting;

class AntragoGradeOverview
{
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
     * @var Environment
     */
    protected $twig;
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

    public function showGradesOverview()
    {
        $this->drawHeader();
        $this->dic->tabs()->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTargetByClass([ilPersonalDesktopGUI::class, ilAchievementsGUI::class])
        );
        $this->dic->tabs()->addTab(self::AGOP_GRADES_TAB, $this->plugin->txt("grades"),
            $this->ctrl->getLinkTargetByClass([ilUIPluginRouterGUI::class, ilAntragoGradeOverviewUIHookGUI::class],
                "showGradesOverview"));

        if ($this->plugin->isAtLeastIlias6()) {
            $this->mainTpl->loadStandardTemplate();
        } else {
            $this->mainTpl->getStandardTemplate();
        }


        $gradesOverviewHtml = $this->buildGradesOverview($this->gradeDataRepo->readAll($this->user->getId()));

        $this->mainTpl->setContent($gradesOverviewHtml);

        if ($this->plugin->isAtLeastIlias6()) {
            $this->dic->ui()->mainTemplate()->printToStdOut();
        } else {
            $this->mainTpl->show();
        }
    }

    /**
     * @param GradeData[] $gradesData
     */
    protected function buildGradesOverview(array $gradesData) : string
    {
        $entries = [];
        $gradePassedThreshold = (float) $this->settings->get("gradePassedThreshold", 4.5);
        foreach ($gradesData as $gradeData) {
            $item = $this->factory->item()->standard($gradeData->getSubjectName())
                ->withProperties([
                    $this->plugin->txt("instructor") => $gradeData->getInstructorName(),
                    $this->lng->txt("date") => $gradeData->getDate()->format("d.m.Y"),
                    $this->plugin->txt("grade") => number_format($gradeData->getGrade(), 1, ",", "."),
                    $this->plugin->txt("rating_points") => $gradeData->getEvaluation(),
                    $this->lng->txt("status") => $this->buildStatus($gradeData->getGrade() < $gradePassedThreshold),
                ]);
            $entries[] = $this->factory->item()->group("", [$item]);
        }

        $list = $this->factory->panel()->listing()->standard(
            sprintf($this->plugin->txt("gradesOverviewOfUser"), $this->user->getFirstname(), $this->user->getLastname()
        ), $entries);
        return $this->renderer->render($list);
    }

    /**
     * @param bool $passed
     * @return string
     */
    protected function buildStatus(bool $passed = true) : string
    {
        if($passed) {
            return $this->plugin->txt("passed") . " " . $this->buildImageIcon(ilUtil::getImagePath("icon_ok.svg"), "");
        } else {
            return $this->plugin->txt("failed") . " " . $this->buildImageIcon(ilUtil::getImagePath("icon_not_ok.svg"), "");
        }
    }

    /**
     * @param $src
     * @param $alt
     * @return string
     */
    protected function buildImageIcon($src, $alt)
    {
        return "<img border=\"0\" align=\"middle\" src=\"" . $src . "\" alt=\"" . $alt . "\" />";
    }

    protected function drawHeader() : void
    {
        $this->mainTpl->setTitle("Test");
        $this->mainTpl->setTitle($this->lng->txt("pd_achievements"));
        $this->mainTpl->setTitleIcon(ilUtil::getImagePath("icon_lhist.svg"));
    }

    /**
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