<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

class Image extends Button
{
    public function __construct($name, $src, array $options = array())
    {
        $options['src'] = $src;
        parent::__construct($name, NULL, $options, 'image');
    }

}