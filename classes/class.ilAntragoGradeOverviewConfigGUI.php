<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilAntragoGradeOverviewConfigGUI
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilAntragoGradeOverviewConfigGUI extends ilPluginConfigGUI
{
    protected const AGOP_SETTINGS_TAB = "agop_settings_tab";
    protected const AGOP_GENERAL_SUBTAB = "agop_general_subTab";
    protected const AGOP_CSV_IMPORT_SUBTAB = "agop_csv_import_subTab";
    /**
     * @var ilAntragoGradeOverviewPlugin
     */
    protected $plugin;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var Container
     */
    protected $dic;
    /**
     * @var ilTemplate
     */
    protected $mainTpl;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilCtrl
     */
    private $ctrl;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->lng = $this->dic->language();
        $this->ctrl = $this->dic->ctrl();
        $this->mainTpl = $this->dic->ui()->mainTemplate();
        $this->tabs = $this->dic->tabs();

        //Todo: Make sure config can only be accessed when the plugin is activated (no update required)
        $this->plugin = ilAntragoGradeOverviewPlugin::getInstance();
    }

    /**
     * Show the plugin settings form
     */
    public function generalSettings()
    {
        $this->tabs->activateSubTab(self::AGOP_GENERAL_SUBTAB);
        $this->mainTpl->setContent("Test");
    }

    public function gradesCsvImport()
    {
        $this->tabs->activateSubTab(self::AGOP_CSV_IMPORT_SUBTAB);
        $this->mainTpl->setContent("55");
    }

    /**
     * Calls the function for a received command
     * @param $cmd
     * @throws Exception
     */
    public function performCommand($cmd)
    {
        $this->injectTabs();

        $cmd = $cmd === "configure" ? $this->getDefaultCommand() : $cmd;

        if (method_exists($this, $cmd)) {
            $this->{$cmd}();
        } else {
            ilUtil::sendFailure(sprintf($this->plugin->txt("cmdNotFound"), $cmd));
            $this->{$this->getDefaultCommand()}();
        }
    }

    protected function injectTabs()
    {
        $this->tabs->addTab(
            ilAntragoGradeOverviewConfigGUI::AGOP_SETTINGS_TAB,
            $this->lng->txt("settings"),
            ""
        );

        $this->tabs->setForcePresentationOfSingleTab(true);
        $this->tabs->addSubTab(
            ilAntragoGradeOverviewConfigGUI::AGOP_GENERAL_SUBTAB,
            $this->lng->txt("general_settings"),
            $this->ctrl->getLinkTargetByClass(ilAntragoGradeOverviewConfigGUI::class, "generalSettings")
        );

        $this->tabs->addSubTab(
            ilAntragoGradeOverviewConfigGUI::AGOP_CSV_IMPORT_SUBTAB,
            $this->plugin->txt("grades_csv_import"),
            $this->ctrl->getLinkTargetByClass(ilAntragoGradeOverviewConfigGUI::class, "gradesCsvImport")
        );

        $this->tabs->activateTab(self::AGOP_SETTINGS_TAB);
    }

    /**
     * Returns the default command
     * @return string
     */
    protected function getDefaultCommand() : string
    {
        return "generalSettings";
    }
}
