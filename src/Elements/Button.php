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
    protected function getDefaultOptions()
    {
        $options = parent::getDefaultOptions();

        $options['validate_for'] = null;

        return $options;
    }

    protected function render(array $attributes)
    {
        return sprintf(
            '<button type="submit"%s />%s</button>',
            $this->attributes($attributes),
            $this->getOption('label')
        );
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
