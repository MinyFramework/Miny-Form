<?php

/**
 * This file is part of the Miny framework.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version accepted by the author in accordance with section
 * 14 of the GNU General Public License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   Miny/Modules/Form
 * @copyright 2012 Dániel Buga <daniel@bugadani.hu>
 * @license   http://www.gnu.org/licenses/gpl.txt
 *            GNU General Public License
 * @version   1.0-dev
 */

namespace Modules\Form;

use Miny\Validator\ConstraintViolationList;

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