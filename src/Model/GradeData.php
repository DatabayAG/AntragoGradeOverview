<?php

declare(strict_types=1);

namespace ILIAS\Plugin\AntragoGradeOverview\Model;

use DateTime;
use ReflectionClass;
use ILIAS\Plugin\AntragoGradeOverview\Exception\ValueConvertException;
use Exception;
use ILIAS\Plugin\AntragoGradeOverview\Polyfill\StrContains;

class GradeData
{
    /**
     * @var int|null
     * @dbCol id
     */
    private $id;
    /**
     * @var int
     * @csvCol  TLN_FP_IDNR
     * @dbCol   fp_id_nr
     */
    private $fpIdNr;
    /**
     * @var int
     * @csvCol TLN_TLN_ID
     * @dbCol  tln_id
     */
    private $tlnId;
    /**
     * @var string
     * @csvCol TLN_NAME_LANG
     * @dbCol  tln_name_long
     */
    private $tlnNameLong;
    /**
     * @var string
     * @csvCol PON01_SEMESTER
     * @dbCol  semester
     */
    private $semester;
    /**
     * @var string
     * @csvCol PON01_SEM_ORT
     * @dbCol  semester_location
     */
    private $semesterLocation;
    /**
     * @var DateTime
     * @csvCol PON01_ABSOLVIERTAM
     * @dbCol  date
     */
    private $date;
    /**
     * @var string
     * @csvCol PON01_NAME_LANG
     * @dbCol  subject_name
     */
    private $subjectName;

    /**
     * @var string
     * @csvCol PON01_DOZENT
     * @dbCol  tutor
     */
    private $tutor;

    /**
     * @var float
     * @csvCol PON01_ERG_NOTE
     * @dbCol  grade
     */
    private $grade;

    /**
     * @var float
     * @csvCol PON01_ERG_PUNKTE
     * @dbCol  ects_pkt_tn
     */
    private $ectsPktTn;

    /**
     * @var bool
     * @csvCol PON01_BESTANDEN
     * @dbCol  passed
     */
    private $passed;

    /**
     * @var string
     * @csvCol PON01_ERROR_TEXT
     * @dbCol  error_text
     */
    private $errorText;

    /**
     * @var int
     * @csvCol PON01_ANZAHL_WDH
     * @dbCol  number_of_repeats
     */
    private $numberOfRepeats;

    /**
     * @var DateTime
     * @dbCol created_at
     */
    private $createdAt;

    /**
     * @var DateTime
     * @dbCol modified_at
     */
    private $modifiedAt;

    /**
     * @var string
     * @dbCol firstName
     */
    private $firstName;

    /**
     * @var string
     * @dbCol lastName
     */
    private $lastName;
    /**
     * @var \ilLogger
     */
    private $logger;

    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->root();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return GradeData
     */
    public function setId(?int $id): GradeData
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getFpIdNr(): int
    {
        return $this->fpIdNr;
    }

    /**
     * @param int $fpIdNr
     * @return GradeData
     */
    public function setFpIdNr(int $fpIdNr): GradeData
    {
        $this->fpIdNr = $fpIdNr;
        return $this;
    }

    /**
     * @return int
     */
    public function getTlnId(): int
    {
        return $this->tlnId;
    }

    /**
     * @param int $tlnId
     * @return GradeData
     */
    public function setTlnId(int $tlnId): GradeData
    {
        $this->tlnId = $tlnId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTlnNameLong(): string
    {
        return $this->tlnNameLong;
    }

    /**
     * @param string $tlnNameLong
     * @return GradeData
     */
    public function setTlnNameLong(string $tlnNameLong): GradeData
    {
        $this->tlnNameLong = $tlnNameLong;
        return $this;
    }

    /**
     * @return string
     */
    public function getSemester(): string
    {
        return $this->semester;
    }

    /**
     * @param string $semester
     * @return GradeData
     */
    public function setSemester(string $semester): GradeData
    {
        $this->semester = $semester;
        return $this;
    }

    /**
     * @return string
     */
    public function getSemesterLocation(): string
    {
        return $this->semesterLocation;
    }

    /**
     * @param string $semesterLocation
     * @return GradeData
     */
    public function setSemesterLocation(string $semesterLocation): GradeData
    {
        $this->semesterLocation = $semesterLocation;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     * @return GradeData
     */
    public function setDate(DateTime $date): GradeData
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubjectName(): string
    {
        return $this->subjectName;
    }

    /**
     * @param string $subjectName
     * @return GradeData
     */
    public function setSubjectName(string $subjectName): GradeData
    {
        $this->subjectName = $subjectName;
        return $this;
    }

    /**
     * @return string
     */
    public function getTutor(): string
    {
        return $this->tutor;
    }

    /**
     * @param string $tutor
     * @return GradeData
     */
    public function setTutor(string $tutor): GradeData
    {
        $this->tutor = $tutor;
        return $this;
    }

    /**
     * @return float
     */
    public function getGrade(): float
    {
        return $this->grade;
    }

    /**
     * @param float $grade
     * @return GradeData
     */
    public function setGrade(float $grade): GradeData
    {
        $this->grade = $grade;
        return $this;
    }

    /**
     * @return float
     */
    public function getEctsPktTn(): float
    {
        return $this->ectsPktTn;
    }

    /**
     * @param float $ectsPktTn
     * @return GradeData
     */
    public function setEctsPktTn(float $ectsPktTn): GradeData
    {
        $this->ectsPktTn = $ectsPktTn;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPassed(): bool
    {
        return $this->passed;
    }

    /**
     * @param bool $passed
     * @return GradeData
     */
    public function setPassed(bool $passed): GradeData
    {
        $this->passed = $passed;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrorText(): string
    {
        return $this->errorText;
    }

    /**
     * @param string $errorText
     * @return GradeData
     */
    public function setErrorText(string $errorText): GradeData
    {
        $this->errorText = $errorText;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfRepeats(): int
    {
        return $this->numberOfRepeats;
    }

    /**
     * @param int $numberOfRepeats
     * @return GradeData
     */
    public function setNumberOfRepeats(int $numberOfRepeats): GradeData
    {
        $this->numberOfRepeats = $numberOfRepeats;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return GradeData
     */
    public function setCreatedAt(DateTime $createdAt): GradeData
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getModifiedAt(): DateTime
    {
        return $this->modifiedAt;
    }

    /**
     * @param DateTime $modifiedAt
     * @return GradeData
     */
    public function setModifiedAt(DateTime $modifiedAt): GradeData
    {
        $this->modifiedAt = $modifiedAt;
        return $this;
    }

    /**
     * @param array<string, string> $data
     * @param                       $annotationString
     * @return GradeData
     * @throws ValueConvertException
     */
    public function setDataByAnnotation(array $data, $annotationString): GradeData
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
            preg_match("/$annotationString .+\n/", $comment, $match);
            if (count($match) === 0) {
                continue;
            }
            $annotation = str_replace([$annotationString, " ", "\n"], "", $match[0]);

            preg_match("/@var .+\n/", $comment, $match);
            $type = str_replace(["@var", " ", "\n"], "", $match[0]);

            $mapping[$annotation] = [
                "function" => $setOrGet . ucfirst($property->getName()),
                "type" => $type
            ];
        }
        return $mapping;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return GradeData
     */
    public function setFirstName(string $firstName): GradeData
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return GradeData
     */
    public function setLastName(string $lastName): GradeData
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
