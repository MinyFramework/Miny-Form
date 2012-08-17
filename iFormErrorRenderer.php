<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use Modules\Validator\ConstraintViolationList;

interface iFormErrorRenderer
{
    public function renderList(FormDescriptor $form);
    public function render(FormElement $element, array $element_options,
                           ConstraintViolationList $errors);
}