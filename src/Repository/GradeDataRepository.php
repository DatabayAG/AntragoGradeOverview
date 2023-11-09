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

namespace ILIAS\Plugin\AntragoGradeOverview\Repository;

use ilDBInterface;
use ILIAS\Plugin\AntragoGradeOverview\Model\GradeData;
use Exception;
use ilPDOStatement;
use DateTime;
use ILIAS\Plugin\AntragoGradeOverview\Exception\ValueConvertException;
use ILIAS\Plugin\AntragoGradeOverview\Model\Datasets;

class GradeDataRepository
{
    /**
     * @var GradeDataRepository|null
     */
    private static $instance;
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
    public static function getInstance(ilDBInterface $db = null): self
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
    public function readAllByMatriculation(string $matriculation, string $dateOrder, string $subjectOrder): array
    {
        $dateOrder = strtoupper($dateOrder);
        $subjectOrder = strtoupper($subjectOrder);

        $result = $this->db->queryF(
            "SELECT * FROM " . self::TABLE_NAME . " WHERE fp_id_nr = %s ORDER BY date $dateOrder, subject_name $subjectOrder",
            ["text"],
            [$matriculation]
        );

        return $this->mapResult($result);
    }

    /**
     * Returns all rows from the database table
     * @return GradeData[]
     * @throws ValueConvertException
     */
    public function readAll(array $ids = []): array
    {
        if (count($ids) > 0) {
            $idsString = "";

            foreach ($ids as $index => $id) {
                if ($index === count($ids) - 1) {
                    $idsString .= $id;
                } else {
                    $idsString .= "$id,";
                }
            }

            $result = $this->db->query(
                "SELECT grades.*, user.firstname AS firstName, user.lastname AS lastName FROM " . self::TABLE_NAME . " AS grades LEFT JOIN usr_data AS user ON user.matriculation = grades.fp_id_nr WHERE grades.id IN ($idsString)"
            );
        } else {
            $result = $this->db->query(
                "SELECT grades.*, user.firstname AS firstName, user.lastname AS lastName FROM " . self::TABLE_NAME . " AS grades LEFT JOIN usr_data AS user ON user.matriculation = grades.fp_id_nr"
            );
        }

        return $this->mapResult($result);
    }

    /**
     * @param ilPDOStatement $result
     * @return GradeData[]
     * @throws ValueConvertException
     */
    private function mapResult(ilPDOStatement $result): array
    {
        $gradesData = [];

        foreach ($this->db->fetchAll($result) as $data) {
            $gradesData[] = (new GradeData())
                ->setDataByAnnotation($data, "@dbCol");
        }

        return $gradesData;
    }

    public function create(GradeData $gradeData): bool
    {
        $affected_rows = (int) $this->db->manipulateF(
            "INSERT INTO " . self::TABLE_NAME .
            " (id, fp_id_nr, tln_id, tln_name_long, semester, semester_location, date, subject_name, tutor, grade, ects_pkt_tn, passed, error_text, number_of_repeats, created_at, modified_at) VALUES " .
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
                $gradeData->getDate()->format("Y-m-d H:i:s"),
                $gradeData->getSubjectName(),
                $gradeData->getTutor(),
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

    public function update(GradeData $gradeData): bool
    {
        $affected_rows = (int) $this->db->manipulateF(
            "UPDATE " . self::TABLE_NAME .
            " SET fp_id_nr=%s, tln_id=%s, tln_name_long=%s, semester=%s, semester_location=%s, date=%s, subject_name=%s, tutor=%s, grade=%s, ects_pkt_tn=%s, passed=%s, error_text=%s, number_of_repeats=%s, modified_at=%s WHERE id=%s",
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
                $gradeData->getDate()->format("Y-m-d H:i:s"),
                $gradeData->getSubjectName(),
                $gradeData->getTutor(),
                $gradeData->getGrade(),
                $gradeData->getEctsPktTn(),
                $gradeData->isPassed(),
                $gradeData->getErrorText(),
                $gradeData->getNumberOfRepeats(),
                (new DateTime("now"))->format("Y-m-d H:i:s"),
                $gradeData->getId()
            ]
        );

        return $affected_rows === 1;
    }

    public function import(Datasets $datasets): bool
    {
        $affectedRows = 0;
        foreach ($datasets->getNew() as $new) {
            $affectedRows += $this->create($new);
        }

        foreach ($datasets->getChanged() as $changed) {
            $affectedRows += $this->update($changed);
        }

        return $affectedRows === count($datasets->getNew()) + count($datasets->getChanged());
    }

    public function delete(int $id): bool
    {
        $affected_rows = (int) $this->db->manipulateF(
            "DELETE FROM " . self::TABLE_NAME . " WHERE id=%s",
            ["integer"],
            [$id]
        );
        return $affected_rows === 1;
    }
}
