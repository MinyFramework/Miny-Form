<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use OutOfBoundsException;

abstract class FormElement
{
    private $options;
    private $value;
    private $label;

    public function __construct($name, $label, array $options = array())
    {
        if (isset($options['value'])) {
            $this->value = $options['value'];
            unset($options['value']);
        }
        $options['name'] = $name;
        $this->options   = $options;
        $this->label     = $label;

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->options['name'];
        }
    }

    public function __isset($key)
    {
        return in_array($key, array('label', 'value')) || isset($this->options[$key]);
    }

    public function __set($key, $value)
    {
        if (in_array($key, array('label', 'value'))) {
            $this->$key = $value;
        } else {
            $this->options[$key] = $value;
        }
    }

    public function __get($key)
    {
        if (in_array($key, array('value', 'label', 'options'))) {
            return $this->$key;
        }
        if (!isset($this->options[$key])) {
            throw new OutOfBoundsException('Option not set: ' . $key);
        }

        return $this->options[$key];
    }

    public function getHTMLArgList(array $args)
    {
        $arglist = '';
        foreach ($args as $name => $value) {
            $arglist .= ' ' . $name . '="' . $value . '"';
        }

        return $arglist;
    }

    public function hasValue()
    {
        return isset($this->value);
    }

    public function renderLabel(array $options = array())
    {
        if (!isset($this->label)) {
            return null;
        }

        $options['for'] = $this->options['id'];
        $options        = $this->getHTMLArgList($options);

        return sprintf('<label%s>%s</label>', $options, $this->label);
    }

    public abstract function render(array $options = array());
}
