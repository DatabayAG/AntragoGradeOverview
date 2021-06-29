<?php

namespace ILIAS\Plugin\AntragoGradeOverview\Repository;

use ilDBInterface;

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
    protected const TABLE_NAME = "ui_uihk_agop_history";

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
}