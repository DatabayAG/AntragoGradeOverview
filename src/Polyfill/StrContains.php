<?php

declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Plugin\AntragoGradeOverview\Polyfill;

/**
 * Class StrContains
 * @author Marvin Beym <mbeym@databay.de>
 */
class StrContains
{
    public function contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}
