<?php

namespace ILIAS\Plugin\AntragoGradeOverview\Model;

class GradeData
{
    /**
     * @var int
     */
    protected $noteId;
    /**
     * @var string
     */
    protected $matrikel;
    /**
     * @var string
     */
    protected $stg;
    /**
     * @var string
     */
    protected $subjectNumber;
    /**
     * @var string
     */
    protected $subjectShortName;
    /**
     * @var string
     */
    protected $subjectName;
    /**
     * @var int
     */
    protected $semester;
    /**
     * @var string
     */
    protected $instructorName;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var int
     */
    protected $date;
    /**
     * @var float
     */
    protected $grade;
    /**
     * @var float
     */
    protected $evaluation;
    /**
     * @var float
     */
    protected $averageEvaluation;
    /**
     * @var float
     */
    protected $credits;
    /**
     * @var int
     */
    protected $seatNumber;
    /**
     * @var string
     */
    protected $status;
    /**
     * @var bool
     */
    protected $subjectAuthorization;
    /**
     * @var string
     */
    protected $remark;
    /**
     * @var int
     */
    protected $createdAt;
    /**
     * @var int
     */
    protected $modifiedAt;

    /**
     * @return int
     */
    public function getNoteId() : int
    {
        return $this->noteId;
    }

    /**
     * @param int $noteId
     * @return GradeData
     */
    public function setNoteId(int $noteId) : GradeData
    {
        $this->noteId = $noteId;
        return $this;
    }

    /**
     * @return string
     */
    public function getMatrikel() : string
    {
        return $this->matrikel;
    }

    /**
     * @param string $matrikel
     * @return GradeData
     */
    public function setMatrikel(string $matrikel) : GradeData
    {
        $this->matrikel = $matrikel;
        return $this;
    }

    /**
     * @return string
     */
    public function getStg() : string
    {
        return $this->stg;
    }

    /**
     * @param string $stg
     * @return GradeData
     */
    public function setStg(string $stg) : GradeData
    {
        $this->stg = $stg;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubjectNumber() : string
    {
        return $this->subjectNumber;
    }

    /**
     * @param string $subjectNumber
     * @return GradeData
     */
    public function setSubjectNumber(string $subjectNumber) : GradeData
    {
        $this->subjectNumber = $subjectNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubjectShortName() : string
    {
        return $this->subjectShortName;
    }

    /**
     * @param string $subjectShortName
     * @return GradeData
     */
    public function setSubjectShortName(string $subjectShortName) : GradeData
    {
        $this->subjectShortName = $subjectShortName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubjectName() : string
    {
        return $this->subjectName;
    }

    /**
     * @param string $subjectName
     * @return GradeData
     */
    public function setSubjectName(string $subjectName) : GradeData
    {
        $this->subjectName = $subjectName;
        return $this;
    }

    /**
     * @return int
     */
    public function getSemester() : int
    {
        return $this->semester;
    }

    /**
     * @param int $semester
     * @return GradeData
     */
    public function setSemester(int $semester) : GradeData
    {
        $this->semester = $semester;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstructorName() : string
    {
        return $this->instructorName;
    }

    /**
     * @param string $instructorName
     * @return GradeData
     */
    public function setInstructorName(string $instructorName) : GradeData
    {
        $this->instructorName = $instructorName;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return GradeData
     */
    public function setType(string $type) : GradeData
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getDate() : int
    {
        return $this->date;
    }

    /**
     * @param int $date
     * @return GradeData
     */
    public function setDate(int $date) : GradeData
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return float
     */
    public function getGrade() : float
    {
        return $this->grade;
    }

    /**
     * @param float $grade
     * @return GradeData
     */
    public function setGrade(float $grade) : GradeData
    {
        $this->grade = $grade;
        return $this;
    }

    /**
     * @return float
     */
    public function getEvaluation() : float
    {
        return $this->evaluation;
    }

    /**
     * @param float $evaluation
     * @return GradeData
     */
    public function setEvaluation(float $evaluation) : GradeData
    {
        $this->evaluation = $evaluation;
        return $this;
    }

    /**
     * @return float
     */
    public function getAverageEvaluation() : float
    {
        return $this->averageEvaluation;
    }

    /**
     * @param float $averageEvaluation
     * @return GradeData
     */
    public function setAverageEvaluation(float $averageEvaluation) : GradeData
    {
        $this->averageEvaluation = $averageEvaluation;
        return $this;
    }

    /**
     * @return float
     */
    public function getCredits() : float
    {
        return $this->credits;
    }

    /**
     * @param float $credits
     * @return GradeData
     */
    public function setCredits(float $credits) : GradeData
    {
        $this->credits = $credits;
        return $this;
    }

    /**
     * @return int
     */
    public function getSeatNumber() : int
    {
        return $this->seatNumber;
    }

    /**
     * @param int $seatNumber
     * @return GradeData
     */
    public function setSeatNumber(int $seatNumber) : GradeData
    {
        $this->seatNumber = $seatNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus() : string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return GradeData
     */
    public function setStatus(string $status) : GradeData
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSubjectAuthorization() : bool
    {
        return $this->subjectAuthorization;
    }

    /**
     * @param bool $subjectAuthorization
     * @return GradeData
     */
    public function setSubjectAuthorization(bool $subjectAuthorization) : GradeData
    {
        $this->subjectAuthorization = $subjectAuthorization;
        return $this;
    }

    /**
     * @return string
     */
    public function getRemark() : string
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     * @return GradeData
     */
    public function setRemark(string $remark) : GradeData
    {
        $this->remark = $remark;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreatedAt() : int
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     * @return GradeData
     */
    public function setCreatedAt(int $createdAt) : GradeData
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getModifiedAt() : int
    {
        return $this->modifiedAt;
    }

    /**
     * @param int $modifiedAt
     * @return GradeData
     */
    public function setModifiedAt(int $modifiedAt) : GradeData
    {
        $this->modifiedAt = $modifiedAt;
        return $this;
    }
}