<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

abstract class AbstractFormElement
{
    /**
     * @var array
     */
    private $options;
    private $viewValue;
    private $modelValue;
    protected $form;

    public function __construct(Form $form, array $options)
    {
        $this->form    = $form;
        $this->options = array_merge($this->getDefaultOptions(), $options);
    }

    public function initialize()
    {
        if ($this->getOption('label') === null) {
            $this->setOption('label', ucwords($this->getOption('name')));
        }
        $attributes       = $this->getOption('attributes');
        $attributes['id'] = $attributes['name'] = $this->getOption('name');
        $this->setOption('attributes', $attributes);
        $defaultData = $this->getOption('data');
        if ($defaultData === null) {
            $defaultData = $this->getOption('empty_data');
        }
        $this->setModelValue($defaultData);
    }

    public function getOption($key)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new \OutOfBoundsException("Option {$key} is not set.");
        }

        return $this->options[$key];
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    protected function getDefaultOptions()
    {
        return array(
            'attributes'       => array(),
            'label_attributes' => array(),
            'empty_data'       => null,
            'required'         => false,
            'disabled'         => false,
            'label'            => null,
            'data'             => null
        );
    }

    public function attributes(array $attributes)
    {
        $attributeList = '';
        foreach ($attributes as $name => $value) {
            $attributeList .= " {$name}=\"{$value}\"";
        }

        return $attributeList;
    }

    public function label(array $attributes = array())
    {
        $attributes        = array_merge($this->options['label_attributes'], $attributes);
        $defaultAttributes = $this->getOption('attributes');

        $attributes['id']  = 'label_' . $defaultAttributes['id'];
        $attributes['for'] = $defaultAttributes['id'];

        return "<label{$this->attributes($attributes)}>{$this->getOption('label')}</label>";
    }

    public function widget(array $attributes = array())
    {
        $attributes = array_merge($this->options['attributes'], $attributes);
        if ($this->getOption('required')) {
            $attributes['required'] = 'required';
        }
        if ($this->getOption('disabled')) {
            $attributes['disabled'] = 'disabled';
        }

        return $this->render($attributes);
    }

    abstract protected function render(array $attributes);

    public function setViewValue($value)
    {
        $this->viewValue  = $value;
        $this->modelValue = $this->toModelValue($value);
    }

    public function setModelValue($value)
    {
        if (!$this->getOption('disabled')) {
            $this->modelValue = $value;
            $this->viewValue  = $this->toViewValue($value);
        }
    }

    public function getViewValue()
    {
        return $this->viewValue;
    }

    public function getModelValue()
    {
        return $this->modelValue;
    }

    protected function toModelValue($value)
    {
        return $value;
    }

    protected function toViewValue($value)
    {
        return $value;
    }

    public function getErrors()
    {
        if ($this->form->isValid() || !$this->form->isSubmitted()) {
            return null;
        }

        return $this->form
            ->getValidationErrors()
            ->get($this->getOption('name'));
    }
}
