<?php

namespace ILIAS\Plugin\AntragoGradeOverview\Model;

class ImportHistory
{
    /**
     * @var int
     */
    protected $userId;
    /**
     * @var int
     */
    protected $date;
    /**
     * @var int
     */
    protected $nDatasets;

    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return ImportHistory
     */
    public function setUserId(int $userId) : ImportHistory
    {
        $this->userId = $userId;
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
     * @return ImportHistory
     */
    public function setDate(int $date) : ImportHistory
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return int
     */
    public function getNDatasets() : int
    {
        return $this->nDatasets;
    }

    /**
     * @param int $nDatasets
     * @return ImportHistory
     */
    public function setNDatasets(int $nDatasets) : ImportHistory
    {
        $this->nDatasets = $nDatasets;
        return $this;
    }
}