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

class FormBuilder
{
    private $descriptor;
    private $error_renderer;
    private $errors_rendered = false;

    public function __construct(FormDescriptor $form = NULL, iFormErrorRenderer $renderer = NULL)
    {
        if (is_null($form)) {
            $form = new FormDescriptor;
        }
        if (is_null($renderer)) {
            $renderer = new FormErrorRenderer;
        }
        $this->descriptor = $form;
        $this->error_renderer = $renderer;
    }

    public function addField(FormElement $field)
    {
        $this->descriptor->addField($field);
    }

    public function getHTMLArgList(array $args)
    {
        $arglist = '';
        foreach ($args as $name => $value) {
            $arglist .= sprintf(' %s="%s"', $name, $value);
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
            $form .= $this->reset();
        }
        $form .= $this->submit();
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

    public function submit($label = NULL, $id = 'submit', array $options = array())
    {
        $submit = new Submit($id, $label);
        return $submit->render($options);
    }

    public function reset($label = NULL, $id = 'reset', array $options = array())
    {
        $submit = new Reset($id, $label);
        return $submit->render($options);
    }

    public function begin(array $options = array())
    {
        $method = isset($options['method']) ? $options['method'] : $this->descriptor->getOption('method');
        $options['method'] = ($method == 'GET') ? 'GET' : 'POST';
        $form = sprintf('<form%s>', $this->getHTMLArgList($options));
        if ($method != $options['method']) {
            $method_field = new Hidden('_method', array('value' => $method));
            $form .= $method_field->render();
        }

        if ($this->descriptor->getOption('csrf')) {
            $token_field = new Hidden('token', array('value' => $this->descriptor->token));
            if ($this->descriptor->hasOption('name')) {
                $form_name = $this->descriptor->getOption('name');
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
        $this->errors_rendered = true;
        return $this->error_renderer->renderList($this->descriptor);
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
                $name = substr($element->name, 0, $pos);
                $extra = substr($element->name, $pos);
                $element->name = $form_name . '[' . $name . ']' . $extra;
            } else {
                $element->name = $form_name . '[' . $element->name . ']';
            }
        }
        if (isset($this->descriptor->$field)) {
            $element->value = $this->descriptor->$field;
        }
        if ($this->descriptor->hasErrors() && !$this->errors_rendered) {
            $form_errors = $this->descriptor->getErrors();
            if ($form_errors->fieldHasErrors($field)) {
                $errors = $form_errors->getFieldErrors($field);
                return $this->error_renderer->render($element, $options, $errors);
            }
        }
        return $element->render($options);
    }

}