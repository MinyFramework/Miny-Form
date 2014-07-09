<?php

namespace Modules\Form;

use Miny\HTTP\Request;
use Miny\HTTP\Session;
use Modules\Validator\ValidatorService;

class Form
{
    public function __construct($object, Session $session, ValidatorService $validator)
    {
        $this->object    = $object;
        $this->validator = $validator;
        $this->session   = $session;
    }

    public function handle(Request $request)
    {
    }

    public function valid()
    {
    }
}
