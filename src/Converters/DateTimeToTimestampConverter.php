<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Converters;

use Modules\Form\AbstractConverter;

class DateTimeToTimestampConverter extends AbstractConverter
{

    public function convert($value)
    {
        if (!$value instanceof \DateTime) {
            throw new \InvalidArgumentException('A DateTime object is expected');
        }

        return $value->getTimestamp();
    }

    public function convertBack($value)
    {
        return \DateTime::createFromFormat('U', $value);
    }
}
