<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Converters;

use Modules\Form\AbstractConverter;

class DateTimeToStringConverter extends AbstractConverter
{
    private $format;

    public function __construct($format)
    {
        $this->format = $format;
    }

    public function convert($value)
    {
        if (!$value instanceof \DateTime) {
            throw new \InvalidArgumentException('A DateTime object is expected');
        }

        return $value->format($this->format);
    }

    public function convertBack($value)
    {
        return \DateTime::createFromFormat($this->format, $value);
    }
}
