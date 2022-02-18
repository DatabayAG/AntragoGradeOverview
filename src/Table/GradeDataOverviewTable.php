<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\AntragoGradeOverview\Table;

use ilTable2GUI;
use ilAntragoGradeOverviewPlugin;
use ILIAS\Plugin\AntragoGradeOverview\Model\GradeData;
use Exception;
use DateTime;
use ilAntragoGradeOverviewConfigGUI;
use ilUtil;
use ilAdvancedSelectionListGUI;
use ILIAS\DI\Container;

/**
 * Class GradeDataOverviewTable
 * @package ILIAS\Plugin\AntragoGradeOverview\Table
 * @author  Marvin Beym <mbeym@databay.de>
 */
class GradeDataOverviewTable extends ilTable2GUI
{
    /**
     * @var ilAntragoGradeOverviewPlugin
     */
    private $plugin;
    /**
     * @var Container
     */
    private $dic;

    public function __construct($a_parent_obj)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->plugin = ilAntragoGradeOverviewPlugin::getInstance();

        $this->setId("gradeDataOverviewTable");
        $this->setTitle($this->plugin->txt("grade_data_overview"));

        $this->setExternalSorting(false);
        $this->setExternalSegmentation(false);

        $this->setDefaultOrderField("date");
        $this->setDefaultOrderDirection("desc");
        $this->setEnableHeader(true);
        parent::__construct($a_parent_obj, "gradeDataOverview");

        $this->setFormAction($this->ctrl->getFormActionByClass(ilAntragoGradeOverviewConfigGUI::class));
        $this->setRowTemplate($this->plugin->templatesFolder("table/tpl.grade_data_overview_table_row.html"));

        $this->addColumn('', '', "1%", true);
        $this->addColumns([
            $this->lng->txt("firstname") => "firstName",
            $this->lng->txt("lastname") => "lastName",
            $this->lng->txt("matriculation") => "fpIdNr",
            $this->plugin->txt("study_program") => "semester",
            $this->plugin->txt("exam_performance") => "subjectName",
            $this->plugin->txt("examiner") => "examiner",
            $this->lng->txt("date") => "date",
            $this->plugin->txt("grade") => "grade",
            $this->plugin->txt("rating") => "rating",
            $this->plugin->txt("status") => "status",
            $this->plugin->txt("importDate") => "createdDate",
            $this->plugin->txt("lastChangedDate") => "modifiedDate"
        ]);

        $this->addColumn($this->lng->txt('actions'), '', "1%");
        $this->setSelectAllCheckbox('id');
        $this->addMultiCommand(
            "deleteSelectedGradesData",
            $this->lng->txt('delete')
        );

        $this->setSelectAllCheckbox('id');
        $this->initFilter();
    }

    private function addColumns(array $columns) : void
    {
        foreach ($columns as $text => $sortField) {
            $this->addColumn($text, $sortField);
        }
    }

    /**
     * @throws Exception
     */
    protected function fillRow($a_set) : void
    {
        $date = new DateTime($a_set["date"]);
        $a_set["date"] = $date->format("d.m.Y H:i:s");
        parent::fillRow($a_set);
    }

    public function numericOrdering($a_field) : bool
    {
        if ($a_field === "date") {
            return true;
        }
        return parent::numericOrdering($a_field);
    }

    /**
     * @param GradeData[] $gradesData
     */
    public function buildTableData(array $gradesData) : array
    {
        $tableData = [];

        foreach ($gradesData as $gradeData) {
            $userData = $this->getUserDataByMatriculation($gradeData->getFpIdNr());
            $action = new ilAdvancedSelectionListGUI();
            $action->setListTitle($this->lng->txt("actions"));
            $this->ctrl->setParameter($this->parent_obj, "id", $gradeData->getId());
            $action->addItem(
                $this->lng->txt("delete"),
                'deleteGradeData',
                $this->ctrl->getLinkTarget(
                    $this->parent_obj,
                    "deleteGradeData"
                )
            );

            $tableData[] = [
                "checkbox" => ilUtil::formCheckbox(false, "id[]", $gradeData->getId()),
                "firstName" => $userData["firstName"],
                "lastName" => $userData["lastName"],
                "fpIdNr" => $gradeData->getFpIdNr(),
                "semester" => $gradeData->getSemester(),
                "subjectName" => $gradeData->getSubjectName(),
                "examiner" => $gradeData->getDozent(),
                "date" => $gradeData->getDate() ? $gradeData->getDate()->format("d.m.Y") : "",
                "grade" => $gradeData->getGrade(),
                "rating" => $gradeData->getEctsPktTn(),
                "status" => $this->plugin->txt($gradeData->isPassed() ? "passed" : "failed"),
                "createdDate" => $gradeData->getCreatedAt()->format("d.m.Y H:i"),
                "modifiedDate" => $gradeData->getModifiedAt()->format("d.m.Y H:i"),
                "actions" => $action->getHTML()
            ];
        }
        return $tableData;
    }

    /**
     * Sets up the table filtering
     */
    public function initFilter() : void
    {
        $this->setFilterCommand("applyFilter");
        $this->setResetCommand("resetFilter");
        /*$this->addFilterItem($nameFilterInput);
        $this->addFilterItem($dateFilterInput);

        $nameFilterInput->readFromSession();
        $dateFilterInput->readFromSession();
*/
        parent::initFilter();
    }

    private function getUserDataByMatriculation(int $fpIdNr)
    {
        $db = $this->dic->database();

        $result = $db->queryF(
            "SELECT firstname AS firstName, lastname AS lastName FROM usr_data WHERE matriculation = %s",
            ["integer"],
            [$fpIdNr]
        );

        $data = $db->fetchAssoc($result);

        return $data ?? ["firstName" => "", "lastName" => "",];
    }
}
