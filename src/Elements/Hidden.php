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

class Hidden extends AbstractFormElement
{
    protected function render(AttributeSet $attributes)
    {
        $viewValue = $this->getViewValue();
        if ($viewValue !== null) {
            $attributes->add('value', $viewValue);
        }

        return sprintf('<input type="hidden"%s />', $attributes);
    }
}
