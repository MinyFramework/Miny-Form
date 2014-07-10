<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

interface FormBuilderInterface
{
    /**
     * @param FormBuilder $formBuilder
     */
    public function getForm(FormBuilder $formBuilder);
}
