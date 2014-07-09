<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

use Modules\Form\AbstractFormElement;

class Button extends AbstractFormElement
{
    protected function render(array $attributes)
    {
        return sprintf(
            '<button%s />%s</button>',
            $this->attributes($attributes),
            $this->getOption('label')
        );
    }
}
