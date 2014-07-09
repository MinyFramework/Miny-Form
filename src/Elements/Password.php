<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

use Modules\Form\AbstractFormElement;

class Text extends AbstractFormElement
{
    protected function render(array $attributes)
    {
        $viewValue = $this->getViewValue();
        if ($viewValue !== null) {
            $attributes['value'] = $viewValue;
        }

        return sprintf('<input type="text"%s />', $this->attributes($attributes));
    }
}
