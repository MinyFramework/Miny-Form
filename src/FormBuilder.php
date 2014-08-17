<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

class FormBuilder
{
    /**
     * @var Form
     */
    private $form;

    /**
     * @var FormService
     */
    private $formService;

    public function __construct($object, FormService $formService)
    {
        $this->formService = $formService;

        $this->form = new Form($object,
            $formService->getCsrfTokenProvider(),
            $formService->getValidator()
        );
    }

    public function get($option, $scenario = null)
    {
        return $this->form->getOption($option, $scenario);
    }

    public function set($option, $value, $scenario = null)
    {
        $this->form->setOption($option, $value, $scenario);

        return $this;
    }

    public function add($property, $type, array $options = [])
    {
        $element = $this->formService->createElement($this->form, $type, $options);

        $this->form->add($property, $element);

        return $this;
    }

    public function getForm()
    {
        $this->form->initialize();

        return $this->form;
    }
}
