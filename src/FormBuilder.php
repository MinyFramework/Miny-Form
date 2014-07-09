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

        $this->form = new Form($object, $formService->getSession(), $formService->getValidator());
    }

    public function add($property, $type, array $options = array())
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
