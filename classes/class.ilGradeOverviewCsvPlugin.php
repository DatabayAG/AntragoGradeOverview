<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilGradeOverviewCsv
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilGradeOverviewCsvPlugin extends ilUserInterfaceHookPlugin
{
    /** @var string */
    const CTYPE = "Services";
    /** @var string */
    const CNAME = "UIComponent";
    /** @var string */
    const SLOT_ID = "uihk";
    /** @var string */
    const PNAME = "GradeOverviewCsv";
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
     * @var ilGradeOverviewCsvPlugin|null
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
     * @return ilGradeOverviewCsvPlugin
     */
    public static function getInstance() : ilGradeOverviewCsvPlugin
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
