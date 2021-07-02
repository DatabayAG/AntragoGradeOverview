<?php

declare(strict_types=1);

namespace ILIAS\Plugin\AntragoGradeOverview\Model;

use DateTime;

class ImportHistory
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var int
     */
    protected $userId;
    /**
     * @var DateTime
     */
    protected $date;
    /**
     * @var int
     */
    protected $datasets;
    /**
     * @var string
     */
    protected $firstName;
    /**
     * @var string
     */
    protected $lastName;

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ImportHistory
     */
    public function setId(int $id) : ImportHistory
    {
        $this->id = $id;
        return $this;
    }

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
     * @return DateTime
     */
    public function getDate() : DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     * @return ImportHistory
     */
    public function setDate(DateTime $date) : ImportHistory
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return int
     */
    public function getDatasets() : int
    {
        return $this->datasets;
    }

    /**
     * @param int $datasets
     * @return ImportHistory
     */
    public function setDatasets(int $datasets) : ImportHistory
    {
        $this->datasets = $datasets;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName() : string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return ImportHistory
     */
    public function setFirstName(string $firstName) : ImportHistory
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName() : string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return ImportHistory
     */
    public function setLastName(string $lastName) : ImportHistory
    {
        $this->lastName = $lastName;
        return $this;
    }
}
