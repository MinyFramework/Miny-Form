<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

class SubmitButton extends Button
{
    protected function getDefaultOptions()
    {
        $options = parent::getDefaultOptions();

        $options['validate_for']       = null;
        $options['attributes']['type'] = 'submit';

        return $options;
    }

    public function toModelValue($viewValue)
    {
        $this->form->setCurrentValidationScenario($this->getOption('validate_for'));

        return true;
    }

    public function clicked()
    {
        return $this->getModelValue() === true;
    }
}
