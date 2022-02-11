<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\AntragoGradeOverview\Exception;


use Exception;
use Throwable;

/**
 * Class FileImportException
 * @author Marvin Beym <mbeym@databay.de>
 */
class FileImportException extends Exception
{
    public function __construct($lngKey)
    {
        parent::__construct($lngKey);
    }
}