<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
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