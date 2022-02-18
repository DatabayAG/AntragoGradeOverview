<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;
use ILIAS\Plugin\AntragoGradeOverview\Provider\MainMenu;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilAntragoGradeOverview
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilAntragoGradeOverviewPlugin extends ilUserInterfaceHookPlugin
{
    /** @var string */
    public const CTYPE = "Services";
    /** @var string */
    public const CNAME = "UIComponent";
    /** @var string */
    public const SLOT_ID = "uihk";
    /** @var string */
    public const PNAME = "AntragoGradeOverview";
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilSetting
     */
    public $settings;
    /**
     * @var Container
     */
    protected $dic;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->ctrl = $this->dic->ctrl();
        $this->settings = new ilSetting(self::class);
        parent::__construct();
    }

    /**
     * @var ilAntragoGradeOverviewPlugin|null
     */
    private static $instance;

    /**
     * @inheritdoc
     */
    public function getPluginName() : string
    {
        return self::PNAME;
    }

    public function assetsFolder(string $file = "") : string
    {
        return $this->getDirectory() . "/assets/$file";
    }

    public function cssFolder(string $file = "") : string
    {
        return $this->assetsFolder() . "css/$file";
    }

    public function templatesFolder(string $file = "") : string
    {
        return $this->assetsFolder() . "templates/$file";
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
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    public static function getInstance() : ilAntragoGradeOverviewPlugin
    {
        return self::$instance ?? (self::$instance = ilPluginAdmin::getPluginObject(
                self::CTYPE,
                self::CNAME,
                self::SLOT_ID,
                self::PNAME
            ));
    }

    /**
     * Returns if the user has access to learning achievements
     * @return bool
     */
    public function hasAccessToLearningAchievements() : bool
    {
        $achievements = new ilAchievements();

        return $achievements->isAnyActive();
    }

    /**
     * Adds the main menu provider
     * @return AbstractStaticPluginMainMenuProvider
     */
    public function promoteGlobalScreenProvider() : AbstractStaticPluginMainMenuProvider
    {
        return new MainMenu($this->dic, $this);
    }

    /**
     * Redirects the user back to the home page
     * Takes ilias version into account
     * Ilias 5.x gets redirected to the personal desktop
     * Ilias >=6.x gets redirected to the dashboard
     */
    public function redirectToHome() : void
    {
        if ($this->isAtLeastIlias6()) {
            $this->ctrl->redirectByClass(ilDashboardGUI::class, "show");
        } else {
            $this->ctrl->redirectByClass(ilPersonalDesktopGUI::class);
        }
    }

    /**
     * Checks if the current ilias version is at least ilias 6
     * @return bool
     */
    public function isAtLeastIlias6() : bool
    {
        return version_compare(ILIAS_VERSION_NUMERIC, "6.0", ">=");
    }

    /**
     * Checks if the current ilias version is at least ilias 7
     * @return bool
     */
    public function isAtLeastIlias7() : bool
    {
        return version_compare(ILIAS_VERSION_NUMERIC, "7.0", ">=");
    }
}
