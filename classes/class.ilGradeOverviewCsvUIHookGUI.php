<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilGradeOverviewCsvUIHookGUI
 * @author            Marvin Beym <mbeym@databay.de>
 * @ilCtrl_isCalledBy ilGradeOverviewCsvUIHookGUI: ilUIPluginRouterGUI
 */
class ilGradeOverviewCsvUIHookGUI extends ilUIHookPluginGUI
{
    /**
     * @var ilGradeOverviewCsvPlugin
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
        $this->plugin = ilGradeOverviewCsvPlugin::getInstance();
    }
}
