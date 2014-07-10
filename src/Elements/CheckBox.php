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

class CheckBox extends AbstractFormElement
{
    protected function getDefaultOptions()
    {
        $default = array(
            'empty_data' => false
        );

        return array_merge(parent::getDefaultOptions(), $default);
    }

    public function toModelValue($value)
    {
        return (bool)$value;
    }

    public function checked()
    {
        return $this->getModelValue();
    }

    protected function render(AttributeSet $attributes)
    {
        $viewValue = $this->getViewValue();
        if ($viewValue) {
            $attributes->add('checked', 'checked');
        }

        return sprintf('<input type="checkbox"%s />', $attributes);
    }
}
