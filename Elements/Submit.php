<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

class Submit extends Button
{
    public function __construct($name, $label, array $options = array())
    {
        parent::__construct($name, $label, $options, 'submit');
    }

}