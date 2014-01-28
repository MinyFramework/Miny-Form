<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

class Button extends Input
{
    public function __construct($name, $label, array $options = array(), $type = 'button')
    {
        parent::__construct($name, $label, $options, $type);
    }

    public function renderLabel(array $options = array())
    {

    }

    public function render(array $options = array())
    {
        $options = $options + $this->options;
        if (!is_null($this->label)) {
            $this->value = $this->label;
        }
        return parent::render($options);
    }

}