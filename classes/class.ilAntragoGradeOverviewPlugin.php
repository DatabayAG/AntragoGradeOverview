<?php declare(strict_types=1);

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
    const CTYPE = "Services";
    /** @var string */
    const CNAME = "UIComponent";
    /** @var string */
    const SLOT_ID = "uihk";
    /** @var string */
    const PNAME = "AntragoGradeOverview";
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
     * Finds a user object by the matriculation
     * @param string $matriculation
     * @return ilObjUser|null
     */
    public function findUserByMatriculation(string $matriculation) : ?ilObjUser
    {
        $result = $this->dic->database()->queryF(
            "SELECT usr_id FROM usr_data WHERE matriculation = %s",
            ["text"],
            [$matriculation]
        );
        $row = $result->fetch();

        return isset($row["usr_id"]) ? new ilObjUser($row["usr_id"]) : null;
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

    /**
     * Returns if the user has access to learning achievements
     * @return bool
     */
    public function hasAccessToLearningAchievements() : bool
    {
        $achievements = new ilAchievements();

        return $achievements->isAnyActive();
    }

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
    public function redirectToHome()
    {
        if ($this->isAtLeastIlias6()) {
            $this->ctrl->redirectByClass("ilDashboardGUI", "show");
        } else {
            $this->ctrl->redirectByClass("ilPersonalDesktopGUI");
        }
    }

    public function isAtLeastIlias6() : bool
    {
        return version_compare(ILIAS_VERSION_NUMERIC, "6.0.0", ">=");
    }
}
