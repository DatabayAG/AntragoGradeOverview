<?php

namespace ILIAS\Plugin\AntragoGradeOverview\Repository;

use ilDBInterface;
use ILIAS\Plugin\AntragoGradeOverview\Model\GradeData;

class GradeDataRepository
{
    /**
     * @var GradeDataRepository|null
     */
    private static $instance = null;
    /**
     * @var ilDBInterface
     */
    protected $db;
    /**
     * @var string
     */
    protected const TABLE_NAME = "ui_uihk_agop_grades";

    /**
     * GradeDataRepository constructor.
     * @param ilDBInterface|null $db
     */
    public function __construct(ilDBInterface $db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            global $DIC;
            $this->db = $DIC->database();
        }
    }

    /**
     * Returns the instance of the repository to prevent recreation of the whole object.
     * @param ilDBInterface|null $db
     * @return static
     */
    public static function getInstance(ilDBInterface $db = null) : self
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self($db);
    }

    /**
     * Creates a new row in the database table.
     * @param GradeData $gradeData
     * @return bool
     */
    public function create(GradeData $gradeData) : bool
    {
        $affected_rows = $this->db->manipulateF(
            "INSERT INTO " . self::TABLE_NAME .
            " (id, user_id, note_id, matrikel, stg, subject_number, subject_short_name, subject_name, semester, instructor_name, type, date, grade, evaluation, average_evaluation, credits, seat_number, status, subject_authorization, remark, created_at, modified_at) VALUES " .
            "(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
            ["integer", "integer", "integer", "text", "text", "text", "text", "text", "integer", "text", "text", "timestamp", "float", "float", "float", "float", "integer", "text", "integer", "text", "timestamp", "timestamp"],
            [
                $this->db->nextId(self::TABLE_NAME),
                $gradeData->getUserId(),
                $gradeData->getNoteId(),
                $gradeData->getMatrikel(),
                $gradeData->getStg(),
                $gradeData->getSubjectNumber(),
                $gradeData->getSubjectShortName(),
                $gradeData->getSubjectName(),
                $gradeData->getSemester(),
                $gradeData->getInstructorName(),
                $gradeData->getType(),
                $gradeData->getDate()->format("Y-m-d H:i:s"),
                $gradeData->getGrade(),
                $gradeData->getEvaluation(),
                $gradeData->getAverageEvaluation(),
                $gradeData->getCredits(),
                $gradeData->getSeatNumber(),
                $gradeData->getStatus(),
                $gradeData->isSubjectAuthorization(),
                $gradeData->getRemark(),
                $gradeData->getCreatedAt()->format("Y-m-d H:i:s"),
                $gradeData->getModifiedAt()->format("Y-m-d H:i:s")
            ]
        );
        return $affected_rows == 1;
    }

    /**
     * Creates multiple new rows in the database table
     * @param GradeData[] $gradesData
     */
    public function import(array $gradesData) : bool
    {
        $this->deleteAll();

        $affectedRows = 0;
        foreach ($gradesData as $gradeData) {
            $affectedRows += $this->create($gradeData);
        }

        return $affectedRows === count($gradesData);
    }

    /**
     * Deletes all rows in the database table
     */
    public function deleteAll() {
        $this->db->manipulate("DELETE FROM " . self::TABLE_NAME);
    }
}