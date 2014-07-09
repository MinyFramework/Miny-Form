<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

use Modules\Form\AbstractFormElement;

class SubmitButton extends AbstractFormElement
{
    protected function render(array $options)
    {

    }

    public function toModelValue($viewValue)
    {
        return true;
    }

    public function clicked()
    {
        return $this->getModelValue();
    }
}
