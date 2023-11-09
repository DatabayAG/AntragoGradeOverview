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

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;
use ILIAS\Plugin\AntragoGradeOverview\Provider\MainMenu;
use ILIAS\Plugin\AntragoGradeOverview\Utils\UiUtil;

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

    protected ilCtrl $ctrl;
    public ilSetting $settings;
    protected Container $dic;
    private UiUtil $uiUtil;
    private static ?self $instance = null;

    public function __construct(ilDBInterface $db, ilComponentRepositoryWrite $component_repository, string $id)
    {
        global $DIC;
        parent::__construct($db, $component_repository, $id);
        $this->dic = $DIC;
        $this->ctrl = $this->dic->ctrl();
        $this->settings = new ilSetting(self::class);
        $this->uiUtil = new UiUtil($this->dic);
    }

    public function getPluginName(): string
    {
        return self::PNAME;
    }

    public function assetsFolder(string $file = ''): string
    {
        return $this->getDirectory() . '/assets/$file';
    }

    public function cssFolder(string $file = ''): string
    {
        return $this->assetsFolder('css/$file');
    }

    public function jsFolder(string $file = ''): string
    {
        return $this->assetsFolder('js/$file');
    }

    public function imagesFolder(string $file = ''): string
    {
        return $this->assetsFolder('images/$file');
    }

    public function templatesFolder(string $file = ''): string
    {
        return $this->assetsFolder('templates/$file');
    }

    protected function beforeUninstall(): bool
    {
        //*
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

    public static function getInstance(): self
    {
        if (self::$instance) {
            return self::$instance;
        }

        global $DIC;

        /** @var ilComponentFactory $componentFactory */
        $componentFactory = $DIC['component.factory'];
        self::$instance = $componentFactory->getPlugin('agop');
        return self::$instance;
    }

    public function hasAccessToLearningAchievements(): bool
    {
        return (new ilAchievements())->isAnyActive();
    }

    public function promoteGlobalScreenProvider(): AbstractStaticPluginMainMenuProvider
    {
        return new MainMenu($this->dic, $this);
    }

    public function redirectToHome(): void
    {
        $this->dic->ctrl()->redirectByClass('ilDashboardGUI', 'show');
    }

    public function isUserAdmin(?int $userId, ?int $roleId): bool
    {
        if ($userId === null) {
            $userId = $this->dic->user->getId();
        }

        if ($roleId === null) {
            if (defined('SYSTEM_ROLE_ID')) {
                $roleId = (int) SYSTEM_ROLE_ID;
            } else {
                $roleId = 2;
            }
        }

        $roleIds = [];

        foreach ($this->dic->rbac()->review()->assignedGlobalRoles($userId) as $id) {
            $roleIds[] = (int) $id;
        }

        return in_array($roleId, $roleIds, true);
    }

    public function denyConfigIfPluginNotActive(): void
    {
        if (!$this->isActive()) {
            $this->uiUtil->sendFailure($this->txt('general.plugin.notActivated'), true);
            $this->dic->ctrl()->redirectByClass(ilObjComponentSettingsGUI::class, 'view');
        }
    }
}
