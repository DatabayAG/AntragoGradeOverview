<?php

declare(strict_types=1);

namespace ILIAS\Plugin\AntragoGradeOverview\Table;

use ilTable2GUI;
use ilAntragoGradeOverviewPlugin;
use ilTextInputGUI;
use ilDateTimeInputGUI;
use ilAntragoGradeOverviewConfigGUI;
use ILIAS\Plugin\AntragoGradeOverview\Model\ImportHistory;
use DateTime;
use ilObjUser;
use ILIAS\DI\Container;
use Exception;

class ImportHistoryTable extends ilTable2GUI
{
    /**
     * @var Container
     */
    protected $dic;
    /**
     * @var ilAntragoGradeOverviewPlugin
     */
    protected $plugin;

    /**
     * ImportHistoryTable constructor.
     * @param                 $a_parent_obj
     */
    public function __construct($a_parent_obj)
    {
        parent::__construct($a_parent_obj, "gradesCsvImport", "");
        global $DIC;
        $this->dic = $DIC;
        $this->plugin = ilAntragoGradeOverviewPlugin::getInstance();

        $this->setFormAction($this->ctrl->getFormActionByClass(ilAntragoGradeOverviewConfigGUI::class));
        $this->setRowTemplate($this->plugin->templatesFolder("table/tpl.import_history_table_row.html"));

        $this->setTitle($this->plugin->txt("import_history"));
        $this->addColumn($this->lng->txt("name"), "name");
        $this->addColumn($this->lng->txt("firstname"), "firstname");
        $this->addColumn($this->lng->txt("date"), "date");
        $this->addColumn($this->plugin->txt("number_of_datasets"), "number_of_datasets");

        $this->setDefaultOrderField("date");
        $this->setDefaultOrderDirection("desc");
        $this->setEnableHeader(true);

        $this->initFilter();
    }

    /**
     * Sets up the table filtering
     */
    public function initFilter()
    {
        $nameFilterInput = new ilTextInputGUI($this->lng->txt("name"), "name");
        $dateFilterInput = new ilDateTimeInputGUI($this->lng->txt("date"), "date");

        $this->setFilterCommand("applyFilter");
        $this->setResetCommand("resetFilter");
        $this->addFilterItem($nameFilterInput);
        $this->addFilterItem($dateFilterInput);

        $nameFilterInput->readFromSession();
        $dateFilterInput->readFromSession();

        parent::initFilter();
    }

    /**
     * Builds the table data so the objects can be displayed in an ilias table
     * @param ImportHistory[] $importHistories
     * @throws Exception
     */
    public function buildTableData(array $importHistories) : array
    {
        /**
         * @var ilDateTimeInputGUI $dateFilterInput
         */
        $nameFilterValue = $this->getFilterValue($this->getFilterItemByPostVar("name"));
        $dateFilterInput = $this->getFilterItemByPostVar("date");

        $dateFilter = $dateFilterInput->getDate();

        $dateFilterSet = $dateFilter != null;
        if ($dateFilterSet) {
            $dateFilterValue = $this->getFilterValue($dateFilterInput);
        }

        $nameFilteredImportHistories = [];

        if ($nameFilterValue) {
            foreach ($importHistories as $importHistory) {
                if ($nameFilterValue === $importHistory->getLastName()) {
                    $nameFilteredImportHistories[] = $importHistory;
                }
            }
        } else {
            $nameFilteredImportHistories = $importHistories;
        }

        $filteredImportHistories = [];

        if ($dateFilterSet) {
            foreach ($nameFilteredImportHistories as $importHistory) {
                $filterDate = new DateTime($dateFilterValue);
                if ($filterDate == $importHistory->getDate()) {
                    $filteredImportHistories[] = $importHistory;
                }
            }
        } else {
            $filteredImportHistories = $nameFilteredImportHistories;
        }

        $tableData = [];

        foreach ($filteredImportHistories as $importHistory) {
            $tableData[] = [
                "name" => $importHistory->getLastName(),
                "firstname" => $importHistory->getFirstName(),
                "date" => $importHistory->getDate()->format("d.m.Y"),
                "number_of_datasets" => $importHistory->getDatasets()
            ];
        }
        return $tableData;
    }
}
