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
    private $error_renderer;

    /**
     * @var bool
     */
    private $errors_rendered = false;

    /**
     * @var Translation
     */
    private $translation;

    /**
     * @param FormDescriptor $form
     * @param iFormErrorRenderer $renderer
     * @param Translation $translation
     */
    public function __construct(FormDescriptor $form = NULL, iFormErrorRenderer $renderer = NULL,
                                Translation $translation = NULL)
    {
        if (is_null($form)) {
            $form = new FormDescriptor;
        }
        if (is_null($renderer)) {
            $renderer = new FormErrorRenderer($translation);
        }
        $this->descriptor = $form;
        $this->error_renderer = $renderer;
        $this->translation = $translation;

        if ($translation !== NULL) {
            $translatable_fields = array('placeholder', 'label');
            foreach ($form->getFields() as $element) {
                foreach ($translatable_fields as $field) {
                    if (isset($element->$field)) {
                        $element->$field = $this->translate($element->$field);
                    }
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
            $reset_label = isset($options['reset']) ? $options['reset'] : NULL;
            $form .= $this->reset($reset_label);
        }
        $submit_label = isset($options['submit']) ? $options['submit'] : NULL;
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

    public function submit($label = NULL, $id = 'submit', array $options = array())
    {
        if (!is_null($label)) {
            $label = $this->translate($label);
        }
        $submit = new Submit($id, $label);
        return $submit->render($options);
    }

    public function reset($label = NULL, $id = 'reset', array $options = array())
    {
        if (!is_null($label)) {
            $label = $this->translate($label);
        }
        $reset = new Reset($id, $label);
        return $reset->render($options);
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

        if ($this->descriptor->getOption('csrf') && $this->descriptor->token) {
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
