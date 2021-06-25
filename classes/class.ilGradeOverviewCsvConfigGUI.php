<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilGradeOverviewCsvConfigGUI
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilGradeOverviewCsvConfigGUI extends ilPluginConfigGUI
{

    /**
     * @var Container
     */
    private $dic;
    /**
     * @var ilTemplate
     */
    private $mainTpl;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->mainTpl = $this->dic->ui()->mainTemplate();
    }

    /**
     * Show the plugin settings form
     */
    public function showGradeOverviewConfig()
    {
        $this->mainTpl->setContent("Test");
    }

    /**
     * Calls the function for a received command
     * @param $cmd
     * @throws Exception
     */
    public function performCommand($cmd)
    {
        switch ($cmd) {
            case "showGradeOverviewConfig":
                if (method_exists($this, $cmd)) {
                    $this->{$cmd}();
                } else {
                    $this->{$this->getDefaultCommand()}();
                }
                break;
            default:
                $this->{$this->getDefaultCommand()}();
        }
    }

    /**
     * Returns the default command
     * @return string
     */
    protected function getDefaultCommand() : string
    {
        return "showGradeOverviewConfig";
    }
}
