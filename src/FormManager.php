<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use InvalidArgumentException;
use Miny\HTTP\Session;
use UnexpectedValueException;

class FormManager
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var FormValidator
     */
    private $formValidator;

    public function __construct(Session $session, FormValidator $validator)
    {
        $this->session       = $session;
        $this->formValidator = $validator;
    }

    public function createForm($class, $name)
    {
        $class = $this->getFullyQualifiedName($class);
        if (isset($this->session->$name) && $this->session->$name instanceof $class) {
            return $this->session->$name;
        }

        return $this->instantiateForm($class);
    }

    public function getValidatedForm($class, array $data)
    {
        $class = $this->getFullyQualifiedName($class);

        $form = $this->instantiateForm($class, $data);

        $this->formValidator->validateForm($form);

        return $form;
    }

    public function createFormBuilder($class, $name)
    {
        return new FormBuilder($this->createForm($class, $name));
    }

    public function storeForm(FormDescriptor $form, $name, $ttl = 1)
    {
        $this->session->flash($name, $form, $ttl);
    }

    private function getFullyQualifiedName($class)
    {
        if (class_exists($class)) {
            return $class;
        }
        $class = '\\Application\\Forms\\' . $class;

        if (class_exists($class)) {
            return $class;
        }

        throw new InvalidArgumentException('Class ' . $class . ' does not exist.');
    }

    /**
     * @param string $class
     *
     * @param array $data
     *
     * @throws UnexpectedValueException
     * @return FormDescriptor
     */
    private function instantiateForm($class, array $data = array())
    {
        /** @var $form FormDescriptor */
        $form = new $class($data);

        if (!$form instanceof FormDescriptor) {
            $pattern = 'Class %s is not an instance of FormDescriptor';
            throw new UnexpectedValueException(sprintf($pattern, $class));
        }
        if ($form->getOption('csrf')) {
            $form->token = $this->session['token'];
        }

        return $form;
    }
}
