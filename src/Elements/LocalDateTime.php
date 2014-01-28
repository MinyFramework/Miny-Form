<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

class LocalDateTime extends Input
{
    public function __construct($name, $label, $options = array())
    {
        parent::__construct($name, $label, $options);
        $this->type = 'datetime-local';
    }

}