<?php

namespace Modules\Form;

use Miny\HTTP\Request;
use Miny\HTTP\Session;
use Modules\Validator\ErrorList;
use Modules\Validator\ValidatorService;

class Form implements \IteratorAggregate
{
    /**
     * @var object
     */
    private $object;

    /**
     * @var ValidatorService
     */
    private $validator;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ErrorList
     */
    private $validationErrors;

    /**
     * @var array
     */
    private $elements = array();

    public function __construct($object, Session $session, ValidatorService $validator)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('$object is not an object.');
        }
        $this->object    = $object;
        $this->validator = $validator;
        $this->session   = $session;
    }

    /**
     * @param Request      $request
     * @param string|array $scenario
     *
     * @return bool Whether the request was valid
     */
    public function handle(Request $request, $scenario = null)
    {
        //fill $this->object

        if (!$this->validator->validate($this->object, $scenario)) {
            $this->validationErrors = $this->validator->getErrors();

            return false;
        }

        $this->validationErrors = null;

        return true;
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }
}
