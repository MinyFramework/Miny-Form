<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

class ResetButton extends Button
{
    protected function getDefaultOptions()
    {
        $options = parent::getDefaultOptions();

        $options['attributes']->add('type', 'reset');

        return $options;
    }
}
