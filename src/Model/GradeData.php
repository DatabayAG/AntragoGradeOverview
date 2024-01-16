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

namespace ILIAS\Plugin\AntragoGradeOverview\Model;

use DateTime;
use ilLogger;
use ReflectionClass;
use ILIAS\Plugin\AntragoGradeOverview\Exception\ValueConvertException;
use Exception;
use ILIAS\Plugin\AntragoGradeOverview\Polyfill\StrContains;

class GradeData
{
    /**
     * @dbCol id
     */
    private ?int $id = null;
    /**
     * @csvCol  TLN_FP_IDNR
     * @dbCol   fp_id_nr
     */
    private int $fpIdNr;
    /**
     * @csvCol TLN_TLN_ID
     * @dbCol  tln_id
     */
    private int $tlnId;
    /**
     * @csvCol TLN_NAME_LANG
     * @dbCol  tln_name_long
     */
    private string $tlnNameLong;
    /**
     * @csvCol PON01_SEMESTER
     * @dbCol  semester
     */
    private string $semester;
    /**
     * @csvCol PON01_SEM_ORT
     * @dbCol  semester_location
     */
    private string $semesterLocation;
    /**
     * @csvCol PON01_ABSOLVIERTAM
     * @dbCol  date
     */
    private DateTime $date;
    /**
     * @csvCol PON01_NAME_LANG
     * @dbCol  subject_name
     */
    private string $subjectName;

    /**
     * @csvCol PON01_DOZENT
     * @dbCol  tutor
     */
    private string $tutor;

    /**
     * @csvCol PON01_ERG_NOTE
     * @dbCol  grade
     */
    private float $grade;

    /**
     * @csvCol PON01_ERG_PUNKTE
     * @dbCol  ects_pkt_tn
     */
    private float $ectsPktTn;

    /**
     * @csvCol PON01_BESTANDEN
     * @dbCol  passed
     */
    private bool $passed;

    /**
     * @csvCol PON01_ERROR_TEXT
     * @dbCol  error_text
     */
    private string $errorText;

    /**
     * @csvCol PON01_ANZAHL_WDH
     * @dbCol  number_of_repeats
     */
    private int $numberOfRepeats;

    /**
     * @dbCol created_at
     */
    private DateTime $createdAt;

    /**
     * @dbCol modified_at
     */
    private DateTime $modifiedAt;

    /**
     * @dbCol firstName
     */
    private string $firstName;

    /**
     * @dbCol lastName
     */
    private string $lastName;
    private ilLogger $logger;

    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->root();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getFpIdNr(): int
    {
        return $this->fpIdNr;
    }

    public function setFpIdNr(int $fpIdNr): self
    {
        $this->fpIdNr = $fpIdNr;
        return $this;
    }

    public function getTlnId(): int
    {
        return $this->tlnId;
    }

    public function setTlnId(int $tlnId): self
    {
        $this->tlnId = $tlnId;
        return $this;
    }

    public function getTlnNameLong(): string
    {
        return $this->tlnNameLong;
    }

    public function setTlnNameLong(string $tlnNameLong): self
    {
        $this->tlnNameLong = $tlnNameLong;
        return $this;
    }

    public function getSemester(): string
    {
        return $this->semester;
    }

    public function setSemester(string $semester): self
    {
        $this->semester = $semester;
        return $this;
    }

    public function getSemesterLocation(): string
    {
        return $this->semesterLocation;
    }

    public function setSemesterLocation(string $semesterLocation): self
    {
        $this->semesterLocation = $semesterLocation;
        return $this;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getSubjectName(): string
    {
        return $this->subjectName;
    }

    public function setSubjectName(string $subjectName): self
    {
        $this->subjectName = $subjectName;
        return $this;
    }

    public function getTutor(): string
    {
        return $this->tutor;
    }

    public function setTutor(string $tutor): self
    {
        $this->tutor = $tutor;
        return $this;
    }

    public function getGrade(): float
    {
        return $this->grade;
    }

    public function setGrade(float $grade): self
    {
        $this->grade = $grade;
        return $this;
    }

    public function getEctsPktTn(): float
    {
        return $this->ectsPktTn;
    }

    public function setEctsPktTn(float $ectsPktTn): self
    {
        $this->ectsPktTn = $ectsPktTn;
        return $this;
    }

    public function isPassed(): bool
    {
        return $this->passed;
    }

    public function setPassed(bool $passed): self
    {
        $this->passed = $passed;
        return $this;
    }

    public function getErrorText(): string
    {
        return $this->errorText;
    }

    public function setErrorText(string $errorText): self
    {
        $this->errorText = $errorText;
        return $this;
    }

    public function getNumberOfRepeats(): int
    {
        return $this->numberOfRepeats;
    }

    public function setNumberOfRepeats(int $numberOfRepeats): self
    {
        $this->numberOfRepeats = $numberOfRepeats;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getModifiedAt(): DateTime
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(DateTime $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;
        return $this;
    }

    /**
     * @param array<string, string> $data
     * @throws ValueConvertException
     */
    public function setDataByAnnotation(array $data, string $annotationString): self
    {
        foreach ($data as $col => $value) {
            $mapping = $this->getFunctions($annotationString, "set");
            $mapped = $mapping[$col];
            if (!$mapped) {
                continue;
            }

            $value = $this->convertValue($mapped["type"], $value);

            $this->{$mapped["function"]}($value);
        }
        return $this;
    }

    /**
     * @throws ValueConvertException
     */
    private function convertValue(string $type, $value)
    {
        $strContains = new StrContains();
        $type = str_replace(["|null", "null|"], "", $type);
        if ($strContains->contains($type, "DateTime")) {
            if ($value === "" || $value === null) {
                $value = null;
            } else {
                try {
                    $value = new DateTime($value);
                } catch (Exception $ex) {
                    throw new ValueConvertException();
                }
            }
        } elseif ($strContains->contains($type, "float")) {
            $value = str_replace(",", ".", $value);
            try {
                settype($value, $type);
            } catch (Exception $ex) {
                throw new ValueConvertException();
            }
        } elseif (!settype($value, $type)) {
            throw new ValueConvertException();
        }
        return $value;
    }

    private function getFunctions(string $annotationString, string $setOrGet): array
    {
        $mapping = [];
        $reflect = new ReflectionClass($this);
        $properties = $reflect->getProperties();
        foreach ($properties as $property) {
            $match = [];
            $comment = $property->getDocComment();
            preg_match("/$annotationString .+\n/", $comment ?: "", $match);
            if (count($match) === 0) {
                continue;
            }
            $annotation = str_replace([$annotationString, " ", "\n"], "", $match[0]);

            if (preg_match("/@var .+\n/", $comment, $match)) {
                $type = str_replace(["@var", " ", "\n"], "", $match[0]);
            } else {
                $type = $property->getType()->getName();
            }


            $mapping[$annotation] = [
                "function" => $setOrGet . ucfirst($property->getName()),
                "type" => $type
            ];
        }
        return $mapping;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function compare(GradeData $comparer): bool
    {
        $reflect1 = new ReflectionClass($this);
        $reflect2 = new ReflectionClass($comparer);

        $props = $reflect1->getProperties();

        $same = true;

        foreach ($props as $prop1) {
            try {
                $prop2 = $reflect2->getProperty($prop1->getName());
            } catch (Exception $ex) {
                $this->logger->error("Exception occurred while trying to compare datasets. Assuming different and continuing.");
                return false;
            }

            $prop1->setAccessible(true);
            $prop2->setAccessible(true);

            $value1 = $prop1->getValue($this);
            $value2 = $prop2->getValue($comparer);

            switch ($prop1->getName()) {
                case "id":
                case "modifiedAt":
                case "createdAt":
                case "firstName":
                case "lastName":
                    continue 2;
                case "date":
                    /**
                     * @var DateTime $value1
                     * @var DateTime $value2
                     */
                    $same = $value1 == $value2 ? $same : false;
                    break;
                default:
                    $same = $value1 === $value2 ? $same : false;
                    continue 2;
            }
        }
        return $same;
    }
}
