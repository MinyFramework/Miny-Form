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

        $attributes = $this->getOption('attributes');
        $attributes->add('name', $this->getOption('name'));
        $attributes->add('id', $this->getOption('name'));

        $this->setModelValue(
            $this->getDefaultData()
        );
    }

    private function getDefaultData()
    {
        if (isset($this->options['data'])) {
            return $this->options['data'];
        }

        return $this->options['empty_data'];
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
            'attributes'       => new AttributeSet(),
            'label_attributes' => new AttributeSet(),
            'empty_data'       => null,
            'required'         => false,
            'disabled'         => false,
            'label'            => null,
            'data'             => null
        );
    }

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

    public function label($label = null, AttributeSet $attributes = null)
    {
        if (!$attributes) {
            $attributes = new AttributeSet();
        }
        $attributes->addMultiple($this->getOption('label_attributes'));

        $idAttribute = $this->getOption('attributes')->get('id');

        $attributes->add('id', 'label_' . $idAttribute);
        $attributes->add('for', $idAttribute);

        $label = $label ? : $this->getOption('label');

        return "<label{$attributes}>{$label}</label>";
    }

    public function widget(AttributeSet $attributes = null)
    {
        if (!$attributes) {
            $attributes = new AttributeSet();
        }
        $attributes->addMultiple($this->getOption('attributes'));

        if ($this->getOption('required')) {
            $attributes->add('required', 'required');
        }
        if ($this->getOption('disabled')) {
            $attributes->add('disabled', 'disabled');
        }

        $this->form->markRendered($this->getOption('name'));

        return $this->render($attributes);
    }

    abstract protected function render(AttributeSet $attributes);

    public function row(AttributeSet $attributes = null)
    {
        if (!$attributes) {
            $attributes = new AttributeSet();
        }
        $label = $attributes->remove('label');

        $labelAttributes = $attributes->remove('label_attributes') ? : array();
        $errorAttributes = $attributes->remove('error_attributes') ? : array();
        $rowAttributes   = AttributeSet::getAttributeString(
            $attributes->remove('row_attributes') ? : array()
        );

        $label  = $this->label($label, new AttributeSet($labelAttributes));
        $error  = $this->error(new AttributeSet($errorAttributes));
        $widget = $this->widget($attributes);

        return "<div{$rowAttributes}>{$label}{$error}{$widget}</div>";
    }

    public function error(AttributeSet $attributes = null)
    {
        if ($this->getErrors() === null) {
            return '';
        }

        if (!$attributes) {
            $attributes = new AttributeSet();
        }

        $output = "<ul{$attributes}>";
        foreach ($this->getErrors() as $error) {
            $output .= "<li>{$error}</li>";
        }

        return $output . '</ul>';
    }
}
