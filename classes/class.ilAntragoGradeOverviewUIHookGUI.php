<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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
