<?php

declare(strict_types=1);

namespace ILIAS\Plugin\AntragoGradeOverview\Repository;

use ilDBInterface;
use ILIAS\Plugin\AntragoGradeOverview\Model\GradeData;
use Exception;
use ilPDOStatement;
use DateTime;
use ILIAS\Plugin\AntragoGradeOverview\Exception\ValueConvertException;

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
     * TODO: Implement or remove/replace
     * Returns all rows from the database table filtered and sorted
     * @param string $matriculation
     * @param string $dateOrder
     * @param string $subjectOrder
     * @return GradeData[]
     * @throws Exception
     */
    public function readAllByMatriculation(string $matriculation, string $dateOrder, string $subjectOrder) : array
    {
        /*
        $dateOrder = strtoupper($dateOrder);
        $subjectOrder = strtoupper($subjectOrder);

        $result = $this->db->query(
            "SELECT * FROM " . self::TABLE_NAME . " WHERE matrikel LIKE " . $this->db->quote("%" . $matriculation,
                "text") . "ORDER BY date {$dateOrder}, subject_name {$subjectOrder}"
        );

        return $this->mapResult($result);
        */
    }

    /**
     * Returns all rows from the database table
     * @return GradeData[]
     * @throws ValueConvertException
     */
    public function readAll() : array
    {
        $result = $this->db->query(
            "SELECT * FROM " . self::TABLE_NAME
        );

        return $this->mapResult($result);
    }

    /**
     * @param ilPDOStatement $result
     * @return GradeData[]
     * @throws ValueConvertException
     */
    private function mapResult(ilPDOStatement $result) : array
    {
        $gradesData = [];

        foreach ($this->db->fetchAll($result) as $data) {
            $gradesData[] = (new GradeData())
                ->setDataByAnnotation($data, "@dbCol");
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
            " (id, fp_id_nr, tln_id, tln_name_long, semester, semester_location, date, subject_name, dozent, grade, ects_pkt_tn, passed, error_text, number_of_repeats, created_at, modified_at) VALUES " .
            "(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
            [
                "integer",
                "integer",
                "integer",
                "text",
                "text",
                "text",
                "timestamp",
                "text",
                "text",
                "float",
                "float",
                "integer",
                "text",
                "integer",
                "timestamp",
                "timestamp",
            ],
            [
                $this->db->nextId(self::TABLE_NAME),
                $gradeData->getFpIdNr(),
                $gradeData->getTlnId(),
                $gradeData->getTlnNameLong(),
                $gradeData->getSemester(),
                $gradeData->getSemesterLocation(),
                $gradeData->getDate() ? $gradeData->getDate()->format("Y-m-d H:i:s") : null,
                $gradeData->getSubjectName(),
                $gradeData->getDozent(),
                $gradeData->getGrade(),
                $gradeData->getEctsPktTn(),
                $gradeData->isPassed(),
                $gradeData->getErrorText(),
                $gradeData->getNumberOfRepeats(),
                (new DateTime("now"))->format("Y-m-d H:i:s"),
                (new DateTime("now"))->format("Y-m-d H:i:s")
            ]
        );
        return $affected_rows === 1;
    }

    public function update(GradeData $gradeData) : bool
    {
        $affected_rows = (int) $this->db->manipulateF(
            "UPDATE " . self::TABLE_NAME .
            " SET fp_id_nr=%s, tln_id=%s, tln_name_long=%s, semester=%s, semester_location=%s, date=%s, subject_name=%s, dozent=%s, grade=%s, ects_pkt_tn=%s, passed=%s, error_text=%s, number_of_repeats=%s, modified_at=%s WHERE id=%s",
            [
                "integer",
                "integer",
                "text",
                "text",
                "text",
                "timestamp",
                "text",
                "text",
                "float",
                "float",
                "integer",
                "text",
                "integer",
                "timestamp",
                "integer",
            ],
            [
                $gradeData->getFpIdNr(),
                $gradeData->getTlnId(),
                $gradeData->getTlnNameLong(),
                $gradeData->getSemester(),
                $gradeData->getSemesterLocation(),
                $gradeData->getDate() ? $gradeData->getDate()->format("Y-m-d H:i:s") : null,
                $gradeData->getSubjectName(),
                $gradeData->getDozent(),
                $gradeData->getGrade(),
                $gradeData->getEctsPktTn(),
                $gradeData->isPassed(),
                $gradeData->getErrorText(),
                $gradeData->getNumberOfRepeats(),
                (new DateTime("now"))->format("Y-m-d H:i:s"),
                $gradeData->getId()
            ]);

        return $affected_rows === 1;
    }

    /**
     * Creates multiple new rows in the database table
     * @param array{'new': GradeData[], 'changed': GradeData[], 'unchanged': GradeData[]} $mappedDatasets
     */
    public function import(array $mappedDatasets) : bool
    {
        $newDatasets = $mappedDatasets["new"];
        $changedDatasets = $mappedDatasets["changed"];

        $affectedRows = 0;
        foreach ($newDatasets as $newDataset) {
            $affectedRows += $this->create($newDataset);
        }

        foreach ($changedDatasets as $changedDataset) {
            $affectedRows += $this->update($changedDataset);
        }

        return $affectedRows === count($newDatasets) + count($changedDatasets);
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
