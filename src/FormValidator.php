<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use Modules\Validator\Constraints\Equals;
use Modules\Validator\Descriptor;
use Modules\Validator\iValidable;
use Modules\Validator\Validator;

class FormValidator extends Validator
{
    private $token;

    public function setCSRFToken($token)
    {
        $this->token = $token;
    }

    protected function loadConstraints(iValidable $form)
    {
        $class = new Descriptor;
        $form->getValidationInfo($class);

        if ($form->getOption('csrf') && !is_null($this->token)) {
            $class->addPropertyConstraint('token', new Equals($this->token));
        }

        return $class;
    }

    public function validateForm(FormDescriptor $form, $scenario = NULL)
    {
        $result = parent::validate($form, $scenario);
        if ($result === true) {
            return true;
        }

        $form->addErrors($result);
        return false;
    }

}