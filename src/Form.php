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
use Miny\HTTP\Session;
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
     * @var Session
     */
    private $session;

    /**
     * @var ErrorList|bool
     */
    private $validationErrors = false;

    /**
     * @var AbstractFormElement[]
     */
    private $elements = array();

    /**
     * @var string
     */
    private $currentScenario = 'default';

    /**
     * @var array
     */
    private $options = array();
    private $currentValidationScenario;

    public function __construct($data, Session $session, ValidatorService $validator)
    {
        $this->data               = $data;
        $this->validator          = $validator;
        $this->session            = $session;
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
        $this->elements[$property] = $element;
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

        $scenario = $scenario ? : $this->currentScenario;
        $method   = strtoupper($this->getOption('method', $scenario));
        if ($request->getMethod() !== $method) {
            return false;
        }
        if ($method === 'GET') {
            $container = $request->get();
        } else {
            $container = $request->post();
        }
        if (!$this->csrfTokenPresent($container, $scenario)) {
            return false;
        }

        //fill $this->object
        foreach ($this->elements as $property => $element) {
            if ($container->has($property)) {
                $element->setViewValue($container->get($property));
            }
            $this->setProperty($property, $element->getModelValue());
        }

        return $this->validateRequest($scenario);
    }

    /**
     * @param $scenario
     *
     * @return bool
     */
    private function validateRequest($scenario)
    {
        if ($this->currentValidationScenario === null) {
            if ($this->hasOption('validate_for', $scenario)) {
                $this->currentValidationScenario = $this->getOption('validate_for', $scenario);
            } else {
                $this->currentValidationScenario = $scenario;
            }
        }

        if ($this->currentValidationScenario !== false) {
            if (!$this->validator->validate($this->data, $this->currentValidationScenario)) {
                $this->validationErrors = $this->validator->getErrors();

                return false;
            }
        }

        $this->validationErrors = null;

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
        if (!isset($this->elements[$property])) {
            throw new \OutOfBoundsException("Form element {$property} is not set");
        }

        return $this->elements[$property];
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

        if (!isset($this->session->csrf_token)) {
            return false;
        }

        $fieldName = $this->getOption('csrf_field', $scenario);
        if (!$container->has($fieldName)) {
            return false;
        }

        return $container->get($fieldName) === $this->session->csrf_token;
    }

    /**
     * @return AbstractFormElement
     */
    public function initialize()
    {
        foreach ($this->elements as $element) {
            $element->initialize();
        }
        $this->synchronize();
    }

    private function synchronize()
    {
        //pass values to elements
        foreach ($this->elements as $property => $element) {
            $value = $this->getProperty($property);
            if ($value !== null) {
                $element->setModelValue($value);
            }
        }
    }

    public function begin(array $attributes = array(), $scenario = null)
    {
        $this->setCurrentScenario($scenario);
        $method     = $this->getOption('method');
        $attributes = $this->getFormAttributes($attributes, $method);
        $output     = $this->getFormOpeningTag($attributes, $method);
        if ($this->getOption('csrf_protection', $this->currentScenario)) {
            if (!isset($this->session->has_csrf_token)) {
                $this->session->flash('has_csrf_token', true, 0);
                $this->session->csrf_token = sha1(mt_rand() . microtime());
            }
            $output .= sprintf(
                '<input type="hidden" name="%s" value="%s">',
                $this->getOption('csrf_field'),
                $this->session->csrf_token
            );
        }

        return $output;
    }

    private function getFormAttributes(array $attributes, $method)
    {
        if ($method !== 'GET' && $method !== 'POST') {
            $methodAttribute = 'POST';
        } else {
            $methodAttribute = $method;
        }
        $attributes = new AttributeSet($attributes);
        $attributes->addMultiple(
        array(
                'action' => $this->getOption('action'),
                'method' => $methodAttribute
            )
        );
        if ($this->getOption('validate_for') === false) {
            $attributes->add('novalidate', 'novalidate');
        }

        return $attributes;
    }

    private function getFormOpeningTag(AttributeSet $attributes, $method)
    {
        $output = "<form{$attributes}>";
        if ($method !== 'GET' && $method !== 'POST') {
            $output .= '<input type="hidden" name="_method" value="' . $method . '" />';
        }

        return $output;
    }

    public function end()
    {
        return '</form>';
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        $this->synchronize();

        return new \ArrayIterator($this->elements);
    }
}
