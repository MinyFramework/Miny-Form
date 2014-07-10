<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

use Modules\Form\AttributeSet;

class SubmitButton extends Button
{
    protected function getDefaultOptions()
    {
        $options = parent::getDefaultOptions();

        $options['validate_for'] = null;
        $options['widget']       = 'button';

        return $options;
    }

    protected function render(AttributeSet $attributes)
    {
        $widget = $this->getOption('widget');
        switch ($widget) {
            case 'button':
                $attributes->add('type', 'submit');

                return parent::render($attributes);
            case 'image':
                $attributes->add('type', 'image');
                $attributes->add('src', $this->getOption('label'));

                return sprintf('<input%s />', $attributes);

            default:
                throw new \InvalidArgumentException("Invalid submit button widget: {$widget}");
        }
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
