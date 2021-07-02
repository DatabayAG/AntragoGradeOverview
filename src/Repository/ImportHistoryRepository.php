<?php

namespace ILIAS\Plugin\AntragoGradeOverview\Repository;

use ilDBInterface;
use ILIAS\Plugin\AntragoGradeOverview\Model\ImportHistory;
use DateTime;
use Exception;

class ImportHistoryRepository
{
    /**
     * @var ImportHistoryRepository|null
     */
    private static $instance = null;
    /**
     * @var ilDBInterface
     */
    protected $db;
    /**
     * @var string
     */
    protected const TABLE_NAME = "ui_uihk_agop_history";

    /**
     * ImportHistoryRepository constructor.
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
     * @return ImportHistory[]
     * @throws Exception
     */
    public function readAll() : array
    {
        $result = $this->db->query("SELECT * FROM " . self::TABLE_NAME);

        $importHistories = [];

        foreach ($this->db->fetchAll($result) as $data) {
            $importHistories[] = (new ImportHistory())
                ->setId((int) $data["id"])
                ->setUserId((int) $data["user_id"])
                ->setDatasets((int) $data["datasets"])
                ->setDate(new DateTime((string) $data["date"]));
        }
        return $importHistories;
    }

    /**
     * Creates a new row in the database table.
     * @param ImportHistory $importHistory
     * @return bool
     */
    public function create(ImportHistory $importHistory) : bool
    {
        $affected_rows = $this->db->manipulateF(
            "INSERT INTO " . self::TABLE_NAME . " (id, user_id, date, datasets) VALUES " .
            "(%s, %s, %s, %s)",
            ["integer", "integer", "date", "integer"],
            [
                $this->db->nextId(self::TABLE_NAME),
                $importHistory->getUserId(),
                $importHistory->getDate()->format("Y-m-d H:i:s"),
                $importHistory->getDatasets(),
            ]
        );
        return $affected_rows == 1;
    }
}
