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

class Textarea extends AbstractFormElement
{
    protected function render(AttributeSet $attributes)
    {
        return sprintf(
            '<textarea%s>%s</textarea>',
            $attributes,
            $this->getViewValue()
        );
    }
}
