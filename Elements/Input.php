<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

use Modules\Form\FormElement;

class Input extends FormElement
{
    public function __construct($name, $label, $options = array(), $type = NULL)
    {
        if ($type) {
            $options['type'] = $type;
        }
        parent::__construct($name, $label, $options);
    }

    public function render(array $options = array())
    {
        $options = $options + $this->options;
        if ($this->hasValue()) {
            $options['value'] = $this->value;
        }
        return sprintf('<input%s />', $this->getHTMLArgList($options));
    }

}