<?php

declare(strict_types=1);

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
        $this->setFormAction($this->ctrl->getFormActionByClass(
            ilAntragoGradeOverviewConfigGUI::class,
            "generalSettings"
        ));

        $showMainMenuItemInput = new ilCheckboxInputGUI($this->plugin->txt("showMainMenuItem"), "showMainMenuItem");
        $showMainMenuItemInput->setRequired(true);
        $showMainMenuItemInput->setChecked($this->plugin->settings->get("showMainMenuItem", false));
        $showMainMenuItemInput->setInfo($this->plugin->txt("showMainMenuItem_info"));

        $this->setShowTopButtons(true);
        $this->addCommandButton("saveGeneralSettings", $this->lng->txt("save"));
        $this->addItem($showMainMenuItemInput);
    }
}
