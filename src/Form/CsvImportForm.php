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

use ilPropertyFormGUI;
use ilAntragoGradeOverviewPlugin;
use ilAntragoGradeOverviewConfigGUI;
use ilFileInputGUI;

class CsvImportForm extends ilPropertyFormGUI
{
    /**
     * @var ilAntragoGradeOverviewPlugin
     */
    protected $plugin;

    public function __construct()
    {
        parent::__construct();
        $this->plugin = ilAntragoGradeOverviewPlugin::getInstance();

        $this->setTitle($this->plugin->txt("fileImport"));

        $csvFileUploadInput = new ilFileInputGUI($this->plugin->txt("fileImport"), "csvFileImport");
        $csvFileUploadInput->setSuffixes(["csv"]);
        $csvFileUploadInput->setRequired(true);

        $this->addItem($csvFileUploadInput);

        $this->setFormAction($this->ctrl->getFormActionByClass(
            ilAntragoGradeOverviewConfigGUI::class,
            "gradesCsvImport"
        ));
        $this->addCommandButton("saveGradesCsvImport", $this->lng->txt("save"));
    }
}
