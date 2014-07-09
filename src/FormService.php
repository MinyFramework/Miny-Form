<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use Miny\HTTP\Session;
use Modules\Validator\ValidatorService;

class FormService
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var ValidatorService
     */
    private $validator;

    private $elements = array(
        'text' => 'Modules\\Form\\Elements\\Text'
    );

    public function __construct(Session $session, ValidatorService $validator)
    {
        $this->session   = $session;
        $this->validator = $validator;
    }

    public function registerElement($name, $class)
    {
        $this->elements[$name] = $class;
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return AbstractFormElement
     * @throws \OutOfBoundsException
     */
    public function createElement($name, array $options = array())
    {
        if (!isset($this->elements[$name])) {
            throw new \OutOfBoundsException("Element {$name} does not exist.");
        }

        $class = new $this->elements[$name];

        return $class($options);
    }

    /**
     * @param $object
     *
     * @return Form
     *
     * @throws \UnexpectedValueException
     */
    public function getForm($object)
    {
        if (!$object instanceof FormBuilderInterface) {
            $class = get_class($object);
            throw new \UnexpectedValueException("{$class} does not implement FormBuilderInterface.");
        }
        $form = $object->getForm($this->getFormBuilder($object));
        if($form instanceof FormBuilder) {
            return $form->getForm();
        }
        if (!$form instanceof Form) {
            $class = get_class($object);
            throw new \UnexpectedValueException("{$class} does not implement FormBuilderInterface properly.");
        }

        return $form;
    }

    /**
     * @param object $object
     *
     * @return FormBuilder
     */
    public function getFormBuilder($object)
    {
        return new FormBuilder($object, $this);
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return ValidatorService
     */
    public function getValidator()
    {
        return $this->validator;
    }
}
