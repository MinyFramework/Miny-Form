<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

use Modules\Form\AbstractFormElement;

class Password extends AbstractFormElement
{
    protected function render(array $attributes)
    {
        return sprintf('<input type="password"%s />', $this->attributes($attributes));
    }
}
