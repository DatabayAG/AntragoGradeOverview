<?php

namespace ILIAS\Plugin\AntragoGradeOverview\Form;

use ilAntragoGradeOverviewPlugin;
use ILIAS\DI\Container;
use ilPropertyFormGUI;
use ilNumberInputGUI;
use ilAntragoGradeOverviewConfigGUI;
use ilCheckboxInputGUI;

class GeneralConfigForm extends ilPropertyFormGUI
{
    /**
     * @var ilAntragoGradeOverviewPlugin
     */
    protected $plugin;
    /**
     * @var Container
     */
    protected $dic;

    public function __construct()
    {
        parent::__construct();
        global $DIC;
        $this->dic = $DIC;
        $this->plugin = ilAntragoGradeOverviewPlugin::getInstance();

        $this->setTitle($this->lng->txt("general_settings"));
        $this->setFormAction($this->ctrl->getFormActionByClass(ilAntragoGradeOverviewConfigGUI::class,
            "generalSettings"));

        $gradePassedThresholdInput = new ilNumberInputGUI($this->plugin->txt("gradePassedThreshold"),
            "gradePassedThreshold");
        $gradePassedThresholdInput->setInfo($this->plugin->txt("gradePassedThreshold_info"));
        $gradePassedThresholdInput->setRequired(true);
        $gradePassedThresholdInput->setMinValue(1, true);
        $gradePassedThresholdInput->setMaxValue(6, true);
        $gradePassedThresholdInput->setDecimals(1);
        $gradePassedThresholdInput->setValue($this->plugin->settings->get("gradePassedThreshold", 4.5));

        $showMainMenuItemInput = new ilCheckboxInputGUI($this->plugin->txt("showMainMenuItem"), "showMainMenuItem");
        $showMainMenuItemInput->setRequired(true);
        $showMainMenuItemInput->setChecked($this->plugin->settings->get("showMainMenuItem", false));
        $showMainMenuItemInput->setInfo($this->plugin->txt("showMainMenuItem_info"));

        $this->setShowTopButtons(true);
        $this->addCommandButton("save_generalSettings", $this->lng->txt("save"));
        $this->addItem($gradePassedThresholdInput);
        $this->addItem($showMainMenuItemInput);
    }
}