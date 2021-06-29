<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilAntragoGradeOverview
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilAntragoGradeOverviewPlugin extends ilUserInterfaceHookPlugin
{
    /** @var string */
    const CTYPE = "Services";
    /** @var string */
    const CNAME = "UIComponent";
    /** @var string */
    const SLOT_ID = "uihk";
    /** @var string */
    const PNAME = "AntragoGradeOverview";
    /**
     * @var Container
     */
    protected $dic;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;

        parent::__construct();
    }

    /**
     * @var ilAntragoGradeOverviewPlugin|null
     */
    private static $instance = null;

    /**
     * @inheritdoc
     */
    public function getPluginName() : string
    {
        return self::PNAME;
    }

    public function assetsFolder(string $file = "") : string
    {
        return $this->getDirectory() . "/assets/{$file}";
    }

    public function cssFolder(string $file = "") : string
    {
        return $this->assetsFolder() . "css/{$file}";
    }

    public function templatesFolder(string $file = "") : string
    {
        return $this->assetsFolder() . "templates/{$file}";
    }

    public function jsFolder(string $file = "") : string
    {
        return $this->assetsFolder() . "js/{$file}";
    }

    /**
     * Runs before uninstalling plugin.
     * Deletes database tables
     * Deletes settings
     * @return bool
     */
    protected function beforeUninstall() : bool
    {
        $settings = new ilSetting(self::class);
        $settings->deleteAll();
        global $DIC;
        $db = $DIC->database();
        if ($db->tableExists("ui_uihk_agop_history")) {
            $db->dropTable("ui_uihk_agop_history");
        }
        if ($db->tableExists("ui_uihk_agop_grades")) {
            $db->dropTable("ui_uihk_agop_grades");
        }
        return parent::beforeUninstall();
    }

    /**
     * @return ilAntragoGradeOverviewPlugin
     */
    public static function getInstance() : ilAntragoGradeOverviewPlugin
    {
        if (null === self::$instance) {
            return self::$instance = ilPluginAdmin::getPluginObject(
                self::CTYPE,
                self::CNAME,
                self::SLOT_ID,
                self::PNAME
            );
        }

        return self::$instance;
    }
}
