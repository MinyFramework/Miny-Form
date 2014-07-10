<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

/**
 * AttributeSet represents a set of attributes on an HTML element.
 */
class AttributeSet
{
    /**
     * Returns the HTML string representation of the given attributes.
     *
     * @param array $attributes The attributes in (attributeName => value) form
     *
     * @return string
     */
    public static function getAttributeString(array $attributes)
    {
        $attributeString = '';
        foreach ($attributes as $name => $value) {
            $attributeString .= " {$name}=\"{$value}\"";
        }

        return $attributeString;
    }

    private $attributes;

    public function __construct(array $attributes = array())
    {
        $this->attributes = $attributes;
    }

    public function add($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    public function append($attribute, $value)
    {
        if (!$this->has($attribute)) {
            throw new \OutOfBoundsException("Attribute {$attribute} is not set.");
        }
        $this->attributes[$attribute] .= $value;
    }

    public function addMultiple($attributes)
    {
        if ($attributes instanceof AttributeSet) {
            $attributes = $attributes->attributes;
        }
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    public function replace($attribute, $value)
    {
        if (isset($this->attributes[$attribute])) {
            return;
        }
        $this->add($attribute, $value);
    }

    public function has($attribute)
    {
        return isset($this->attributes[$attribute]);
    }

    public function get($attribute)
    {
        if (!$this->has($attribute)) {
            throw new \OutOfBoundsException("Attribute {$attribute} is not set.");
        }

        return $this->attributes[$attribute];
    }

    public function __toString()
    {
        return self::getAttributeString($this->attributes);
    }
}
