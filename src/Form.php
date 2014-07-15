<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use Miny\HTTP\ParameterContainer;
use Miny\HTTP\Request;
use Modules\Form\Elements\Hidden;
use Modules\Validator\ErrorList;
use Modules\Validator\ValidatorService;

class Form implements \IteratorAggregate
{
    /**
     * @var object
     */
    private $data;

    /**
     * @var ValidatorService
     */
    private $validator;

    /**
     * @var CsrfTokenProvider
     */
    private $tokenProvider;

    /**
     * @var ErrorList|bool
     */
    private $validationErrors = false;

    /**
     * @var AbstractFormElement[]
     */
    private $fields = array();

    /**
     * @var string
     */
    private $currentScenario = 'default';

    /**
     * @var array
     */
    private $options = array();
    private $currentValidationScenario;
    private $notRenderedFields = array();
    private $onFailureCallback;
    private $onSuccessCallback;

    public function __construct(
        $data,
        CsrfTokenProvider $tokenProvider,
        ValidatorService $validator
    ) {
        $this->data               = $data;
        $this->validator          = $validator;
        $this->tokenProvider      = $tokenProvider;
        $this->options['default'] = $this->getDefaultOptionsArray();
    }

    public function setCurrentScenario($scenario)
    {
        $this->currentScenario = $scenario ? : 'default';
    }

    public function setOptions(array $options, $scenario = null)
    {
        $scenario = $scenario ? : $this->currentScenario;

        $this->options[$scenario] = array_merge(
            $this->getDefaultOptionsArray(),
            $options
        );
    }

    public function setOption($key, $value, $scenario = null)
    {
        $scenario = $scenario ? : $this->currentScenario;
        if (!isset($this->options[$scenario])) {
            $this->options[$scenario] = $this->getDefaultOptionsArray();
        }
        $this->options[$scenario][$key] = $value;
    }

    private function getDefaultOptionsArray()
    {
        return array(
            'action'          => '?',
            'method'          => 'POST',
            'csrf_protection' => true,
            'csrf_field'      => '_token',
            'validate_for'    => null
        );
    }

    public function hasOption($key, $scenario = null)
    {
        $scenario = $scenario ? : $this->currentScenario;

        return isset($this->options[$scenario][$key]);
    }

    public function getOption($key, $scenario = null)
    {
        $scenario = $scenario ? : $this->currentScenario;
        if (!array_key_exists($key, $this->options[$scenario])) {
            throw new \OutOfBoundsException("Key {$key} is not set for scenario '{$scenario}'");
        }

        return $this->options[$scenario][$key];
    }

    public function add($property, AbstractFormElement $element)
    {
        $element->setOption('name', $property);
        $this->fields[$property] = $element;
    }

    /**
     * @param Request      $request
     * @param string|array $scenario
     *
     * @return bool Whether the request was valid
     */
    public function handle(Request $request, $scenario = null)
    {
        $this->currentValidationScenario = null;

        if ($scenario !== null) {
            $this->currentScenario = $scenario;
        }
        $method = strtoupper($this->getOption('method', $this->currentScenario));
        if ($request->getMethod() !== $method) {
            return false;
        }
        if ($method === 'GET') {
            $container = $request->get();
        } else {
            $container = $request->post();
        }
        if (!$this->csrfTokenPresent($container, $this->currentScenario)) {
            $this->failure();

            return false;
        }

        //fill $this->object
        foreach ($this->fields as $property => $element) {
            $element->initialize();
            if ($container->has($property)) {
                $element->setViewValue($container->get($property));
            }
            $this->setProperty($property, $element->getModelValue());
        }

        return $this->validateRequest($this->currentScenario);
    }

    /**
     * @param $scenario
     *
     * @return bool
     */
    private function validateRequest($scenario)
    {
        //null if not set by user - use configuration
        if ($this->currentValidationScenario === null) {
            if ($this->hasOption('validate_for', $scenario)) {
                $this->currentValidationScenario = $this->getOption('validate_for', $scenario);
            } else {
                $this->currentValidationScenario = $scenario;
            }
        }

        //false if validation is disabled
        if ($this->currentValidationScenario !== false) {
            if (!$this->validator->validate($this->data, $this->currentValidationScenario)) {
                $this->validationErrors = $this->validator->getErrors();
                $this->failure();

                return false;
            }
        }

        $this->validationErrors = null;
        $this->success();

        return true;
    }

    /**
     * @param $property
     * @param $value
     */
    private function setProperty($property, $value)
    {
        if (is_array($this->data)) {
            $this->data[$property] = $value;
        } elseif (property_exists($this->data, $property)) {
            $this->data->$property = $value;
        } else {
            $setter = 'set' . ucfirst($property);
            if (method_exists($this->data, $setter)) {
                $this->data->$setter($value);
            }
        }
    }

    /**
     * @param $property
     *
     * @return mixed
     *
     * @throws \LogicException
     */
    private function getProperty($property)
    {
        if (is_array($this->data)) {
            return isset($this->data[$property]) ? $this->data[$property] : null;
        } elseif (property_exists($this->data, $property)) {
            return $this->data->$property;
        } else {
            $name = ucfirst($property);
            foreach (array('get', 'has', 'is') as $prefix) {
                $getter = $prefix . $name;
                if (method_exists($this->data, $getter)) {
                    return $this->data->$getter();
                }
            }
        }

        return null;
    }

    public function isValid()
    {
        return $this->validationErrors === null;
    }

    public function isSubmitted()
    {
        return $this->validationErrors !== false;
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    public function getFormData()
    {
        return $this->data;
    }

    public function get($property)
    {
        if (!isset($this->fields[$property])) {
            throw new \OutOfBoundsException("Form element {$property} is not set");
        }

        return $this->fields[$property];
    }

    /**
     * @param mixed $currentValidationScenario
     */
    public function setCurrentValidationScenario($currentValidationScenario)
    {
        $this->currentValidationScenario = $currentValidationScenario;
    }

    private function csrfTokenPresent(ParameterContainer $container, $scenario)
    {
        if (!$this->getOption('csrf_protection', $scenario)) {
            return true;
        }

        $fieldName = $this->getOption('csrf_field', $scenario);
        if (!$container->has($fieldName)) {
            return false;
        }

        return $this->tokenProvider->matchToken($container->get($fieldName));
    }

    public function initialize()
    {
        foreach ($this->fields as $element) {
            $element->initialize();
        }
        $this->synchronize();
    }

    private function synchronize()
    {
        //pass values to elements
        foreach ($this->fields as $property => $element) {
            $value = $this->getProperty($property);
            if ($value !== null) {
                $element->setModelValue($value);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        $this->synchronize();

        return new \ArrayIterator($this->fields);
    }

    public function begin(array $attributes = array(), $scenario = null)
    {
        if ($scenario !== null) {
            $this->currentScenario = $scenario;
        }
        $method = $this->getOption('method');
        $output = $this->getFormOpeningTag(
            $this->getFormAttributes($attributes, $method)
        );

        $this->addMethodEmulationField($method);
        $this->addCsrfTokenField();

        $this->notRenderedFields = array_flip(array_keys($this->fields));

        return $output;
    }

    private function getFormAttributes(array $attributes, $method)
    {
        $attributes = new AttributeSet($attributes);
        $attributes->add('action', $this->getOption('action'));
        $attributes->add('method', $method !== 'GET' ? 'POST' : $method);

        if ($this->getOption('validate_for') === false) {
            $attributes->add('novalidate', 'novalidate');
        }

        return $attributes;
    }

    private function addCsrfTokenField()
    {
        if ($this->getOption('csrf_protection', $this->currentScenario)) {
            $csrfField = new Hidden($this, array());

            $this->add($this->getOption('csrf_field'), $csrfField);
            $csrfField->initialize();
            $csrfField->setModelValue($this->tokenProvider->generateToken());
        }
    }

    private function addMethodEmulationField($method)
    {
        if ($method !== 'GET' && $method !== 'POST') {
            $methodField = new Hidden($this, array());

            $this->add('_method', $methodField);
            $methodField->initialize();
            $methodField->setModelValue($method);
        }
    }

    private function getFormOpeningTag(AttributeSet $attributes)
    {
        return "<form{$attributes}>";
    }

    public function end()
    {
        $output = '';
        foreach ($this->notRenderedFields as $field => $key) {
            $output .= $this->get($field)->row();
        }

        return $output . '</form>';
    }

    public function markRendered($field)
    {
        unset($this->notRenderedFields[$field]);
    }

    public function onSuccess($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('$callback needs to be callable');
        }
        $this->onSuccessCallback = $callback;
    }

    public function onFailure($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('$callback needs to be callable');
        }
        $this->onFailureCallback = $callback;
    }

    private function success()
    {
        if (isset($this->onSuccessCallback)) {
            call_user_func($this->onSuccessCallback, $this);
        }
    }

    private function failure()
    {
        if (isset($this->onFailureCallback)) {
            call_user_func($this->onFailureCallback, $this);
        }
    }
}
