<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

use Modules\Form\FormElement;

class Textarea extends FormElement
{
    public function render(array $options = array())
    {
        $options = $options + $this->options;
        return sprintf('<textarea%s>%s</textarea>', $this->getHTMLArgList($options), $this->value);
    }

}