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
     *
     * @return Form|FormBuilder
     */
    public function getForm(FormBuilder $formBuilder);
}
