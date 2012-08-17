<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use ArrayAccess;
use InvalidArgumentException;
use Modules\Validator\Descriptor;
use Modules\Validator\iValidable;
use OutOfBoundsException;
use Traversable;

class FormDescriptor implements iValidable
{
    protected $fields = array();
    protected $options = array(
        'csrf'   => true,
        'method' => 'POST'
    );
    public $token;
    private $errors;

    public function __construct($data = array())
    {
        if (!is_array($data) && !$data instanceof ArrayAccess && !$data instanceof Traversable) {
            throw new InvalidArgumentException('Data should be an array or array-like object.');
        }

        if ($this->hasOption('name') && !empty($data)) {
            $data = $data[$this->getOption('name')];
        }
        $this->fields = $this->fields();
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getValidationInfo(Descriptor $class)
    {

    }

    public function fields()
    {
        return array();
    }

    public function getOption($key)
    {
        if (!isset($this->options[$key])) {
            throw new OutOfBoundsException('Option not set: ' . $key);
        }
        return $this->options[$key];
    }

    public function hasOption($key)
    {
        return isset($this->options[$key]);
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function toArray()
    {
        return array_intersect_key(get_object_vars($this), $this->fields);
    }

    public function addField(FormElement $field)
    {
        $this->fields[$field->name] = $field;
    }

    public function getField($key)
    {
        if (!isset($this->fields[$key])) {
            throw new OutOfBoundsException('Field not set: ' . $key);
        }
        return $this->fields[$key];
    }

    public function hasField($key)
    {
        return isset($this->fields[$key]);
    }

    public function addErrors(array $errors)
    {
        if (is_null($this->errors)) {
            $this->errors = new FormErrorList;
        }
        $this->errors->addList($errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return !is_null($this->errors);
    }

}