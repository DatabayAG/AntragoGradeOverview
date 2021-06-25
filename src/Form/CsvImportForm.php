<?php

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

        $this->setFormAction($this->ctrl->getFormActionByClass(ilAntragoGradeOverviewConfigGUI::class,
            "gradesCsvImport"));
        $this->addCommandButton("save_gradesCsvImport", $this->lng->txt("save"));
    }
}