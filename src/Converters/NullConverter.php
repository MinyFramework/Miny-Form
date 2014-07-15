<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Converters;

use Modules\Form\AbstractConverter;

class NullConverter extends AbstractConverter
{

    public function convert($value)
    {
        return $value;
    }

    public function convertBack($value)
    {
        return $value;
    }
}
