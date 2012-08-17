<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use Modules\Validator\ConstraintViolationList;

class FormErrorRenderer implements iFormErrorRenderer
{
    private function renderFieldErrors(ConstraintViolationList $list)
    {
        $string = '<ul class="field_errors">';
        foreach ($list as $error) {
            $string .= sprintf('<li>%s</li>', (string) $error);
        }
        $string .= '</ul>';
        return $string;
    }

    public function render(FormElement $element, array $element_options, ConstraintViolationList $errors)
    {
        $string = $element->render($element_options);
        $string .= $this->renderFieldErrors($errors);
        return $string;
    }

    public function renderList(FormDescriptor $form)
    {
        if (!$form->hasErrors()) {
            return;
        }
        $string = '<ul class="form_errors">';
        foreach ($form->getErrors() as $field => $list) {
            $string .= '<li class="error_field">';
            if ($form->hasField($field)) {
                $string .= $form->getField($field)->label;
            }
            $string .= $this->renderFieldErrors($list);
            $string .= '</li>';
        }
        $string .= '</ul>';
        return $string;
    }

}