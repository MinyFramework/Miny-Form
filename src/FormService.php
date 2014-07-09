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

    /**
     * @var string[]
     */
    private $elements = array(
        'button'   => 'Modules\\Form\\Elements\\Button',
        'checkbox' => 'Modules\\Form\\Elements\\CheckBox',
        'choice'   => 'Modules\\Form\\Elements\\Choice',
        'date'     => 'Modules\\Form\\Elements\\Date',
        'password' => 'Modules\\Form\\Elements\\Password',
        'reset'    => 'Modules\\Form\\Elements\\ResetButton',
        'submit'   => 'Modules\\Form\\Elements\\SubmitButton',
        'text'     => 'Modules\\Form\\Elements\\Text',
        'textarea' => 'Modules\\Form\\Elements\\Textarea',
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
     * @param Form $form
     * @param string $name
     * @param array  $options
     *
     * @throws \OutOfBoundsException
     * @return AbstractFormElement
     */
    public function createElement(Form $form, $name, array $options = array())
    {
        if (!isset($this->elements[$name])) {
            throw new \OutOfBoundsException("Element {$name} does not exist.");
        }

        $class = $this->elements[$name];

        return new $class($form, $options);
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
        if ($form instanceof FormBuilder) {
            return $form->getForm();
        }
        if (!$form instanceof Form) {
            $class = get_class($object);
            throw new \UnexpectedValueException("{$class} does not implement FormBuilderInterface properly.");
        }

        return $form;
    }

    /**
     * @param object|array $object
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
