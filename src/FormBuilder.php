<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use Modules\Form\Elements\Hidden;
use Modules\Form\Elements\Reset;
use Modules\Form\Elements\Submit;
use Modules\Translation\Translation;

class FormBuilder
{
    /**
     * @var FormDescriptor
     */
    private $descriptor;

    /**
     * @var iFormErrorRenderer
     */
    private $errorRenderer;

    /**
     * @var bool
     */
    private $errorsRendered = false;

    /**
     * @var Translation
     */
    private $translation;

    /**
     * @param FormDescriptor     $form
     * @param iFormErrorRenderer $renderer
     * @param Translation        $translation
     */
    public function __construct(
        FormDescriptor $form = null,
        iFormErrorRenderer $renderer = null,
        Translation $translation = null
    ) {

        $form     = $form ? : new FormDescriptor;
        $renderer = $renderer ? : new FormErrorRenderer($translation);

        $this->descriptor    = $form;
        $this->errorRenderer = $renderer;

        if ($translation === null) {
            return;
        }
        $this->translation   = $translation;
        $translatable_fields = array('placeholder', 'label');
        foreach ($form->getFields() as $element) {
            foreach ($translatable_fields as $field) {
                if (isset($element->$field)) {
                    $element->$field = $this->translate($element->$field);
                }
            }
        }
    }

    protected function translate($string)
    {
        if (isset($this->translation)) {
            return $this->translation->get($string);
        }

        return $string;
    }

    public function addField(FormElement $field)
    {
        $this->descriptor->addField($field);
    }

    /**
     * @return FormElement[]
     */
    public function getFields()
    {
        return $this->descriptor->getFields();
    }

    public function getHTMLArgList(array $args)
    {
        $arglist = '';
        foreach ($args as $name => $value) {
            $arglist .= ' ' . $name . '="' . $value . '"';
        }

        return $arglist;
    }

    public function generate(array $options = array(), $errors_first = true, $reset = false)
    {
        $form = $this->begin($options);
        if ($errors_first) {
            $form .= $this->errors();
        }
        foreach (array_keys($this->descriptor->getFields()) as $key) {
            $form .= $this->label($key);
            $form .= $this->render($key);
        }
        if ($reset) {
            $reset_label = isset($options['reset']) ? $options['reset'] : null;
            $form .= $this->reset($reset_label);
        }
        $submit_label = isset($options['submit']) ? $options['submit'] : null;
        $form .= $this->submit($submit_label);
        $form .= $this->end();

        return $form;
    }

    public function partial()
    {
        $form = '';
        foreach (func_get_args() as $key) {
            $form .= $this->label($key);
            $form .= $this->render($key);
        }

        return $form;
    }

    public function submit($label = null, $id = 'submit', array $options = array())
    {
        if ($label !== null) {
            $label = $this->translate($label);
        }
        $submit = new Submit($id, $label);

        return $submit->render($options);
    }

    public function reset($label = null, $id = 'reset', array $options = array())
    {
        if ($label !== null) {
            $label = $this->translate($label);
        }
        $reset = new Reset($id, $label);

        return $reset->render($options);
    }

    public function begin(array $options = array())
    {
        $method = isset($options['method']) ? $options['method'] : $this->descriptor->getOption(
            'method'
        );

        $options['method'] = ($method == 'GET') ? 'GET' : 'POST';
        $form              = sprintf('<form%s>', $this->getHTMLArgList($options));
        if ($method != $options['method']) {
            $method_field = new Hidden('_method', array('value' => $method));
            $form .= $method_field->render();
        }

        if ($this->descriptor->getOption('csrf') && $this->descriptor->token) {
            $token_field = new Hidden('token', array('value' => $this->descriptor->token));
            if ($this->descriptor->hasOption('name')) {
                $form_name         = $this->descriptor->getOption('name');
                $token_field->name = $form_name . '[token]';
            }

            $form .= $token_field->render();
        }

        return $form;
    }

    public function end()
    {
        return '</form>';
    }

    public function errors()
    {
        $this->errorsRendered = true;

        return $this->errorRenderer->renderList($this->descriptor);
    }

    public function label($field, $options = array())
    {
        return $this->descriptor->getField($field)->renderLabel($options);
    }

    public function render($field, array $options = array())
    {
        $element = $this->descriptor->getField($field);
        if ($this->descriptor->hasOption('name')) {

            $form_name = $this->descriptor->getOption('name');

            if (($pos = strpos($element->name, '[')) !== false) {
                $name          = substr($element->name, 0, $pos);
                $extra         = substr($element->name, $pos);
                $element->name = $form_name . '[' . $name . ']' . $extra;
            } else {
                $element->name = $form_name . '[' . $element->name . ']';
            }
        }
        if (isset($this->descriptor->$field)) {
            $element->value = $this->descriptor->$field;
        }
        if ($this->descriptor->hasErrors() && !$this->errorsRendered) {
            $form_errors = $this->descriptor->getErrors();
            if ($form_errors->fieldHasErrors($field)) {
                $errors = $form_errors->getFieldErrors($field);

                return $this->errorRenderer->render($element, $options, $errors);
            }
        }

        return $element->render($options);
    }

}
