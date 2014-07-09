<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

use Modules\Form\AbstractFormElement;

class Textarea extends AbstractFormElement
{
    protected function render(array $attributes)
    {
        return sprintf(
            '<textarea%s>%s</textarea>',
            $this->attributes($attributes),
            $this->getViewValue()
        );
    }
}
