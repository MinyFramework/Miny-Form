<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Converters;

use Modules\Form\AbstractConverter;

class DateTimeToArrayConverter extends AbstractConverter
{
    private $keys;
    private static $formats = array(
        'year'   => 'Y',
        'month'  => 'm',
        'day'    => 'd',
        'hour'   => 'H',
        'minute' => 'i',
        'second' => 's',
    );

    public function __construct(array $keys)
    {
        $this->keys = $keys;
    }

    public function convert($value)
    {
        if (!$value instanceof \DateTime) {
            throw new \InvalidArgumentException('A DateTime object is expected');
        }

        $return = array();
        foreach ($this->keys as $key) {
            $return[$key] = $value->format(self::$formats[$key]);
        }

        return $return;
    }

    public function convertBack($value)
    {
        $string = '';
        $format = '';
        foreach ($this->keys as $key) {
            if (!isset($value[$key])) {
                throw new \InvalidArgumentException("Invalid date array. Key {$key} is not present.");
            }
            $string .= $value[$key] . ' ';
            $format .= self::$formats[$key] . ' ';
        }

        return \DateTime::createFromFormat($format, $string);
    }
}
