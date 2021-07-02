<?php

namespace ILIAS\Plugin\AntragoGradeOverview\Repository;

use ilDBInterface;
use ILIAS\Plugin\AntragoGradeOverview\Model\GradeData;
use DateTime;
use Exception;

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
     * Returns all rows from the database table
     * @param string $matriculation
     * @return GradeData[]
     * @throws Exception
     */
    public function readAll(string $matriculation) : array
    {
        $result = $this->db->queryF(
            "SELECT * FROM " . self::TABLE_NAME . " WHERE matrikel = %s",
            ["text"],
            [$matriculation]
        );

        $gradesData = [];

        foreach ($this->db->fetchAll($result) as $data) {
            $gradesData[] = (new GradeData())
                ->setId((int) $data["id"])
                ->setNoteId((int) $data["note_id"])
                ->setMatrikel((string) $data["matrikel"])
                ->setStg((string) $data["stg"])
                ->setSubjectNumber((string) $data["subject_number"])
                ->setSubjectShortName((string) $data["subject_short_name"])
                ->setSubjectName((string) $data["subject_name"])
                ->setSemester((int) $data["semester"])
                ->setInstructorName((string) $data["instructor_name"])
                ->setType((string) $data["type"])
                ->setDate(new DateTime((string) $data["date"]))
                ->setGrade((float) $data["grade"])
                ->setEvaluation((float) $data["evaluation"])
                ->setAverageEvaluation((float) $data["average_evaluation"])
                ->setCredits((float) $data["credits"])
                ->setSeatNumber((int) $data["seat_number"])
                ->setStatus((string) $data["status"])
                ->setSubjectAuthorization((bool) $data["subject_authorization"])
                ->setRemark((string) $data["remark"])
                ->setCreatedAt(new DateTime((string) $data["created_at"]))
                ->setModifiedAt(new DateTime((string) $data["modified_at"]));
        }

        return $gradesData;
    }

    /**
     * Creates a new row in the database table.
     * @param GradeData $gradeData
     * @return bool
     */
    public function create(GradeData $gradeData) : bool
    {
        $affected_rows = (int) $this->db->manipulateF(
            "INSERT INTO " . self::TABLE_NAME .
            " (id, note_id, matrikel, stg, subject_number, subject_short_name, subject_name, semester, instructor_name, type, date, grade, evaluation, average_evaluation, credits, seat_number, status, subject_authorization, remark, created_at, modified_at) VALUES " .
            "(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
            ["integer",
             "integer",
             "text",
             "text",
             "text",
             "text",
             "text",
             "integer",
             "text",
             "text",
             "date",
             "float",
             "float",
             "float",
             "float",
             "integer",
             "text",
             "integer",
             "text",
             "date",
             "date"
            ],
            [
                $this->db->nextId(self::TABLE_NAME),
                $gradeData->getNoteId(),
                $gradeData->getMatrikel(),
                $gradeData->getStg(),
                $gradeData->getSubjectNumber(),
                $gradeData->getSubjectShortName(),
                $gradeData->getSubjectName(),
                $gradeData->getSemester(),
                $gradeData->getInstructorName(),
                $gradeData->getType(),
                $gradeData->getDate()->format("Y-m-d"),
                $gradeData->getGrade(),
                $gradeData->getEvaluation(),
                $gradeData->getAverageEvaluation(),
                $gradeData->getCredits(),
                $gradeData->getSeatNumber(),
                $gradeData->getStatus(),
                $gradeData->isSubjectAuthorization(),
                $gradeData->getRemark(),
                $gradeData->getCreatedAt()->format("Y-m-d"),
                $gradeData->getModifiedAt()->format("Y-m-d")
            ]
        );
        return $affected_rows === 1;
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
     * @noinspection SqlWithoutWhere
     */
    public function deleteAll()
    {
        $this->db->manipulate("DELETE FROM " . self::TABLE_NAME);
    }
}
