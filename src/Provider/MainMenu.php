<?php

namespace ILIAS\Plugin\AntragoGradeOverview\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;
use ilAntragoGradeOverviewPlugin;
use ilUIPluginRouterGUI;
use ilAntragoGradeOverviewUIHookGUI;

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

    public function getStaticSubItems() : array
    {
        return [];
    }
}