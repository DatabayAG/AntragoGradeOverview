<?php

declare(strict_types=1);

namespace ILIAS\Plugin\AntragoGradeOverview\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;
use ilAntragoGradeOverviewPlugin;
use ilUIPluginRouterGUI;
use ilAntragoGradeOverviewUIHookGUI;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

class MainMenu extends AbstractStaticPluginMainMenuProvider
{
    /**
     * @var ilAntragoGradeOverviewPlugin
     */
    protected $plugin;

    /**
     * Handles the main menu item
     * @return array
     */
    public function getStaticTopItems() : array
    {
        $showMainMenuItem = (bool) $this->plugin->settings->get("showMainMenuItem", false);

        $mainMenuItem = $this->mainmenu
            ->topLinkItem($this->if->identifier("agop_mainmenu_item"))
            ->withPosition(999)
            ->withTitle($this->plugin->txt("grades"))
            ->withAction($this->dic->ctrl()->getLinkTargetByClass([
                ilUIPluginRouterGUI::class,
                ilAntragoGradeOverviewUIHookGUI::class
            ], "showGradesOverview"))
            ->withVisibilityCallable(
                function () use ($showMainMenuItem) {
                    return $showMainMenuItem &&
                        !$this->dic->user()->isAnonymous() &&
                        $this->plugin->hasAccessToLearningAchievements();
                }
            );

        return [$mainMenuItem];
    }

    /**
     * Adds a sub item to the achievements main menu entry when ilias version is at least ilias 6
     * @return array
     */
    public function getStaticSubItems() : array
    {
        if (!$this->plugin->isAtLeastIlias6()) {
            return [];
        }
        $achievementsGrades = $this->mainmenu
            ->link($this->if->identifier("agop_achievements_grades_subItem"))
            ->withPosition(999)
            ->withTitle($this->plugin->txt("grades"))
            ->withParent(StandardTopItemsProvider::getInstance()->getAchievementsIdentification())
            ->withAction($this->dic->ctrl()->getLinkTargetByClass([
                ilUIPluginRouterGUI::class,
                ilAntragoGradeOverviewUIHookGUI::class
            ], "showGradesOverview"))
            ->withVisibilityCallable(
                function () {
                    return !$this->dic->user()->isAnonymous() &&
                        $this->plugin->hasAccessToLearningAchievements();
                }
            );
        return [$achievementsGrades];
    }
}
