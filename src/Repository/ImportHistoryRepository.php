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
use ILIAS\Plugin\AntragoGradeOverview\Model\ImportHistory;
use DateTime;
use Exception;

class ImportHistoryRepository
{
    private static ?self $instance = null;
    protected ilDBInterface $db;
    /**
     * @var string
     */
    protected const TABLE_NAME = "ui_uihk_agop_history";

    public function __construct(ilDBInterface $db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            global $DIC;
            $this->db = $DIC->database();
        }
    }

    public static function getInstance(?ilDBInterface $db = null): self
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self($db);
    }

    /**
     * @return ImportHistory[]
     * @throws Exception
     */
    public function readAll(): array
    {
        $query = "SELECT " . self::TABLE_NAME . ".*, usr_data.firstname, usr_data.lastname FROM " . self::TABLE_NAME . " LEFT JOIN usr_data ON ui_uihk_agop_history.user_id = usr_data.usr_id";
        $result = $this->db->query($query);
        $importHistories = [];

        foreach ($this->db->fetchAll($result) as $data) {
            $importHistories[] = (new ImportHistory())
                ->setId((int) $data["id"])
                ->setUserId((int) $data["user_id"])
                ->setDatasetsAdded((int) $data["datasets_added"])
                ->setDatasetsChanged((int) $data["datasets_changed"])
                ->setDatasetsUnchanged((int) $data["datasets_unchanged"])
                ->setDate(new DateTime((string) $data["date"]))
                ->setLastName((string) $data["lastname"])
                ->setFirstName((string) $data["firstname"]);
        }
        return $importHistories;
    }

    public function create(ImportHistory $importHistory): bool
    {
        $affected_rows = (int) $this->db->manipulateF(
            "INSERT INTO " . self::TABLE_NAME . " (id, user_id, date, datasets_added, datasets_changed, datasets_unchanged) VALUES " .
            "(%s, %s, %s, %s, %s, %s)",
            ["integer", "integer", "timestamp", "integer", "integer", "integer"],
            [
                $this->db->nextId(self::TABLE_NAME),
                $importHistory->getUserId(),
                $importHistory->getDate()->format("Y-m-d H:i:s"),
                $importHistory->getDatasetsAdded(),
                $importHistory->getDatasetsChanged(),
                $importHistory->getDatasetsUnchanged(),
            ]
        );
        return $affected_rows === 1;
    }
}
