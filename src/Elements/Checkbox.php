<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

class Checkbox extends Input
{
    public function __construct($name, $label, $options = array())
    {
        parent::__construct($name, $label, $options, 'checkbox');
    }

    public function render(array $options = array())
    {
        if ($this->hasValue()) {
            $options['checked'] = 'checked';
            $this->value = null;
        }
        return parent::render($options);
    }

}
