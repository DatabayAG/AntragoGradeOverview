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
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use ilCtrl;
use Twig_Error_Runtime;
use Twig_Error_Loader;
use Twig_Error_Syntax;
use ILIAS\Plugin\AntragoGradeOverview\Repository\GradeDataRepository;

class AntragoGradeOverview
{
    public const AGOP_GRADES_TAB = "agop_grades_tab";
    /**
     * @var \ilObjUser
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
        $this->user = $dic->user();
        $this->mainTpl = $dic->ui()->mainTemplate();
        $this->plugin = ilAntragoGradeOverviewPlugin::getInstance();

        $this->gradeDataRepo = GradeDataRepository::getInstance();

        $twigLoader = new FilesystemLoader($this->plugin->templatesFolder());
        $this->twig = new Environment($twigLoader);

    }

    /**
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     * @throws Exception
     */
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

        $gradesData = $this->gradeDataRepo->readAll($this->user->getId());

        $this->mainTpl->setContent($this->twig->render("tpl.grades_data.html.twig", ["gradesData" => $gradesData]));

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