<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

use Modules\Form\AbstractFormElement;
use Modules\Form\AttributeSet;

class Button extends AbstractFormElement
{
    public function label($label = null, AttributeSet $attributes = null)
    {
    }

    protected function render(AttributeSet $attributes)
    {
        return "<button{$attributes}>{$this->getOption('label')}</button>";
    }
}
