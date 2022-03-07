<?php

declare(strict_types=1);
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
use ilTextInputGUI;
use ilDateTimeInputGUI;
use ILIAS\Plugin\AntragoGradeOverview\Polyfill\StrContains;

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
            $this->lng->txt("lastname") => "lastName",
            $this->lng->txt("firstname") => "firstName",
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

        foreach ($this->filterData($gradesData) as $gradeData) {
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
                "lastName" => $gradeData->getLastName(),
                "firstName" => $gradeData->getFirstName(),
                "fpIdNr" => $gradeData->getFpIdNr(),
                "semester" => $gradeData->getSemester(),
                "subjectName" => $gradeData->getSubjectName(),
                "examiner" => $gradeData->getTutor(),
                "date" => $gradeData->getDate()->format("d.m.Y"),
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
     * @param GradeData[] $gradesData
     * @return GradeData[]
     */
    private function filterData(array $gradesData) : array
    {
        $strContains = new StrContains();

        $filterValues = [];

        $filterValues["firstName"] = $this->getFilterValue($this->getFilterItemByPostVar("firstName"));
        $filterValues["lastName"] = $this->getFilterValue($this->getFilterItemByPostVar("lastName"));
        $filterValues["fpIdNr"] = $this->getFilterValue($this->getFilterItemByPostVar("fpIdNr"));
        $filterValues["subjectName"] = $this->getFilterValue($this->getFilterItemByPostVar("subjectName"));
        $filterValues["semester"] = $this->getFilterValue($this->getFilterItemByPostVar("semester"));
        $filterValues["examiner"] = $this->getFilterValue($this->getFilterItemByPostVar("examiner"));

        $dateFilterInput = $this->getFilterItemByPostVar("date");

        $dateFilter = $dateFilterInput->getDate();

        $dateFilterSet = $dateFilter !== null;
        if ($dateFilterSet) {
            $filterValues["date"] = $this->getFilterValue($dateFilterInput);
        }


        if ($filterValues["firstName"]) {
            $filteredData = [];
            foreach ($gradesData as $gradeData) {
                if ($strContains->contains($gradeData->getFirstName(), $filterValues["firstName"])) {
                    $filteredData[] = $gradeData;
                }
            }
            $gradesData = $filteredData;
        }

        if ($filterValues["lastName"]) {
            $filteredData = [];
            foreach ($gradesData as $gradeData) {
                if ($strContains->contains($gradeData->getLastName(), $filterValues["lastName"])) {
                    $filteredData[] = $gradeData;
                }
            }
            $gradesData = $filteredData;
        }

        if ($filterValues["date"]) {
            $filteredData = [];
            foreach ($gradesData as $gradeData) {
                try {
                    $filterDate = new DateTime($filterValues["date"]);
                    if ($filterDate == $gradeData->getDate()) {
                        $filteredData[] = $gradeData;
                    }
                } catch (Exception $ex) {
                }
            }
            $gradesData = $filteredData;
        }

        if ($filterValues["fpIdNr"]) {
            $filteredData = [];
            foreach ($gradesData as $gradeData) {
                if ($strContains->contains((string) $gradeData->getFpIdNr(), $filterValues["fpIdNr"])) {
                    $filteredData[] = $gradeData;
                }
            }
            $gradesData = $filteredData;
        }

        if ($filterValues["subjectName"]) {
            $filteredData = [];
            foreach ($gradesData as $gradeData) {
                if ($strContains->contains($gradeData->getSubjectName(), $filterValues["subjectName"])) {
                    $filteredData[] = $gradeData;
                }
            }
            $gradesData = $filteredData;
        }

        if ($filterValues["semester"]) {
            $filteredData = [];
            foreach ($gradesData as $gradeData) {
                if ($strContains->contains($gradeData->getSemester(), $filterValues["semester"])) {
                    $filteredData[] = $gradeData;
                }
            }
            $gradesData = $filteredData;
        }

        if ($filterValues["examiner"]) {
            $filteredData = [];
            foreach ($gradesData as $gradeData) {
                if ($strContains->contains($gradeData->getTutor(), $filterValues["examiner"])) {
                    $filteredData[] = $gradeData;
                }
            }
            $gradesData = $filteredData;
        }

        return $gradesData;
    }

    /**
     * Sets up the table filtering
     */
    public function initFilter() : void
    {
        $firstNameInput = new ilTextInputGUI($this->lng->txt("firstname"), "firstName");
        $lastNameInput = new ilTextInputGUI($this->lng->txt("lastname"), "lastName");
        $dateInput = new ilDateTimeInputGUI($this->lng->txt("date"), "date");
        $fpIdNrInput = new ilTextInputGUI($this->lng->txt("matriculation"), "fpIdNr");
        $subjectNameInput = new ilTextInputGUI($this->plugin->txt("exam_performance"), "subjectName");
        $semesterInput = new ilTextInputGUI($this->plugin->txt("study_program"), "semester");
        $examinerInput = new ilTextInputGUI($this->plugin->txt("examiner"), "examiner");

        $this->setFilterCommand("applyFilterGradeDataOverviewTable");
        $this->setResetCommand("resetFilterGradeDataOverviewTable");
        $this->addFilterItem($firstNameInput);
        $this->addFilterItem($lastNameInput);
        $this->addFilterItem($fpIdNrInput);
        $this->addFilterItem($dateInput);
        $this->addFilterItem($subjectNameInput);
        $this->addFilterItem($semesterInput);
        $this->addFilterItem($examinerInput);

        $firstNameInput->readFromSession();
        $lastNameInput->readFromSession();
        $dateInput->readFromSession();
        $fpIdNrInput->readFromSession();
        $subjectNameInput->readFromSession();
        $semesterInput->readFromSession();
        $examinerInput->readFromSession();

        parent::initFilter();
    }
}
