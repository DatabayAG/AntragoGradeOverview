<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilAntragoGradeOverviewUIHookGUI
 * @author            Marvin Beym <mbeym@databay.de>
 * @ilCtrl_isCalledBy ilAntragoGradeOverviewUIHookGUI: ilUIPluginRouterGUI
 */
class ilAntragoGradeOverviewUIHookGUI extends ilUIHookPluginGUI
{
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

        $this->plugin = ilAntragoGradeOverviewPlugin::getInstance();
    }

    /**
     * Returns the array used to replace the html content
     * @param string $mode
     * @param string $html
     * @return string[]
     */
    protected function uiHookResponse(string $mode = ilUIHookPluginGUI::KEEP, string $html = "") : array
    {
        return ['mode' => $mode, 'html' => $html];
    }
}
