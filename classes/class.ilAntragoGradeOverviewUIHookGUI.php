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
use ILIAS\Plugin\AntragoGradeOverview\AntragoGradeOverview;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilAntragoGradeOverviewUIHookGUI
 * @author            Marvin Beym <mbeym@databay.de>
 * @ilCtrl_isCalledBy ilAntragoGradeOverviewUIHookGUI: ilUIPluginRouterGUI
 */
class ilAntragoGradeOverviewUIHookGUI extends ilUIHookPluginGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilAntragoGradeOverviewPlugin
     */
    protected $plugin;
    /**
     * @var Container
     */
    protected $dic;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->ctrl = $this->dic->ctrl();
        $this->lng = $this->dic->language();
        $this->plugin = ilAntragoGradeOverviewPlugin::getInstance();
    }

    public function modifyGUI($a_comp, $a_part, $a_par = []): void
    {
        if ($a_part === "tabs") {
            $correctTabs = false;
            /**
             * @var ilTabsGUI $tabs
             */
            $tabs = $a_par["tabs"];
            foreach ($tabs->target as $target) {
                switch ($target["text"]) {
                    case $this->lng->txt("learning_progress"):
                    case $this->lng->txt("obj_bdga"):
                    case $this->lng->txt("obj_cert"):
                        $correctTabs = true;
                        break;
                    default:
                        return;
                }
            }

            if (!$correctTabs) {
                return;
            }

            $this->dic->tabs()->addTab(
                AntragoGradeOverview::AGOP_GRADES_TAB,
                $this->plugin->txt("grades"),
                $this->ctrl->getLinkTargetByClass([ilUIPluginRouterGUI::class, self::class], "showGradesOverview")
            );
        }
    }

    /**
     * @throws Exception
     */
    public function executeCommand(): void
    {
        (new AntragoGradeOverview($this->dic))->performCommand($this->ctrl->getCmd());
    }

    /**
     * Returns the array used to replace the html content
     * @param string $mode
     * @param string $html
     * @return string[]
     */
    protected function uiHookResponse(string $mode = ilUIHookPluginGUI::KEEP, string $html = ""): array
    {
        return ['mode' => $mode, 'html' => $html];
    }
}
