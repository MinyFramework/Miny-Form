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

use ArrayIterator;
use IteratorAggregate;
use Modules\Validator\ConstraintViolationList;
use UnexpectedValueException;

class FormErrorList implements IteratorAggregate
{
    private $errors = array();

    public function addList(array $errors)
    {
        foreach ($errors as $field => $list) {

            if (!$list instanceof ConstraintViolationList) {
                throw new UnexpectedValueException('Array values must be instances of ConstraintViolationList');
            }

            if (!isset($this->errors[$field])) {
                $this->errors[$field] = $list;
            } else {
                $this->errors[$field]->addViolationList($list);
            }
        }
    }

    public function getFieldErrors($field)
    {
        if (isset($this->errors[$field])) {
            return $this->errors[$field];
        }
    }

    public function fieldHasErrors($field)
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->errors);
    }

    public function getList()
    {
        return $this->errors;
    }

}