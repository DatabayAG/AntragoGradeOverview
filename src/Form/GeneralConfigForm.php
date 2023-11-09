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

namespace ILIAS\Plugin\AntragoGradeOverview\Form;

use ilAntragoGradeOverviewPlugin;
use ILIAS\DI\Container;
use ilPropertyFormGUI;
use ilAntragoGradeOverviewConfigGUI;
use ilCheckboxInputGUI;

class GeneralConfigForm extends ilPropertyFormGUI
{
    protected ilAntragoGradeOverviewPlugin $plugin;
    protected Container $dic;

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
        $showMainMenuItemInput->setChecked((bool) $this->plugin->settings->get("showMainMenuItem"));
        $showMainMenuItemInput->setInfo($this->plugin->txt("showMainMenuItem_info"));

        $this->setShowTopButtons(true);
        $this->addCommandButton("saveGeneralSettings", $this->lng->txt("save"));
        $this->addItem($showMainMenuItemInput);
    }
}
