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

namespace ILIAS\Plugin\AntragoGradeOverview\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticPluginMainMenuProvider;
use ilAntragoGradeOverviewPlugin;
use ilUIPluginRouterGUI;
use ilAntragoGradeOverviewUIHookGUI;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

class MainMenu extends AbstractStaticPluginMainMenuProvider
{
    protected ilAntragoGradeOverviewPlugin $plugin;

    public function getStaticTopItems(): array
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
    public function getStaticSubItems(): array
    {
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
