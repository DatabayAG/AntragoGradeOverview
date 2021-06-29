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

class AntragoGradeOverview
{
    public const AGOP_GRADES_TAB = "agop_grades_tab";
    /**
     * @var \ilCtrl
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

        $this->mainTpl = $dic->ui()->mainTemplate();
        $this->plugin = ilAntragoGradeOverviewPlugin::getInstance();
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

        $this->mainTpl->setContent("TEMPORARY");

        if ($this->plugin->isAtLeastIlias6()) {
            $this->dic->ui()->mainTemplate()->printToStdOut();
        } else {
            $this->mainTpl->show();
        }
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