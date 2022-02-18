<?php

declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\AntragoGradeOverview\Exception;

use Exception;
use ilAntragoGradeOverviewPlugin;

/**
 * Class ValueConvertException
 * @package ILIAS\Plugin\AntragoGradeOverview\Exception
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ValueConvertException extends Exception
{
    public function __construct()
    {
        parent::__construct(ilAntragoGradeOverviewPlugin::getInstance()->txt("value_convert_exception"));
    }
}
