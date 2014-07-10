<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use Minty\Compiler\TemplateFunction;
use Minty\Extension;

class FormExtension extends Extension
{
    /**
     * @var FormService
     */
    private $formService;

    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    public function getExtensionName()
    {
        return 'miny/form';
    }

    public function getFunctions()
    {
        $safe = array('is_safe' => 'html');

        return array(
            new TemplateFunction('button', array($this, 'button'), $safe),
            new TemplateFunction('form', array($this, 'form'), $safe),
            new TemplateFunction('form_begin', array($this, 'begin'), $safe),
            new TemplateFunction('form_end', array($this, 'end'), $safe),
            new TemplateFunction('form_row', array($this, 'row'), $safe),
            new TemplateFunction('form_label', array($this, 'label'), $safe),
            new TemplateFunction('form_widget', array($this, 'widget'), $safe),
            new TemplateFunction('form_error', array($this, 'error'), $safe),
            new TemplateFunction('form_errors', array($this, 'errors'), $safe),
        );
    }

    public function form(Form $form, array $attributes = array(), $scenario = null)
    {
        $output = $this->begin($form, $attributes, $scenario);
        foreach ($form as $element) {
            $output .= $this->row($element);
        }

        return $output . $this->end($form);
    }

    public function begin(Form $form, array $attributes = array(), $scenario = null)
    {
        return $form->begin($attributes, $scenario);
    }

    public function end(Form $form)
    {
        return $form->end();
    }

    public function error(AbstractFormElement $element, array $attributes = array())
    {
        if ($element->getErrors() === null) {
            return '';
        }
        $attributeList = \Minty\Extensions\template_function_attributes($attributes);

        $output = "<ul{$attributeList}>";
        foreach ($element->getErrors() as $error) {
            $output .= "<li>{$error}</li>";
        }

        return $output . '</ul>';
    }

    public function errors(Form $form, array $attributes = array())
    {
        if ($form->isValid()) {
            return '';
        }
        $attributeList = \Minty\Extensions\template_function_attributes($attributes);

        $output = "<ul{$attributeList}>";
        foreach ($form as $element) {
            $output .= "<li>{$element->getOption('label')}: {$this->error($element)}</li>";
        }

        return $output . '</ul>';
    }

    public function label(AbstractFormElement $element, array $attributes = array())
    {
        return $element->label($attributes);
    }

    public function widget(AbstractFormElement $element, array $attributes = array())
    {
        return $element->widget($attributes);
    }

    public function row(AbstractFormElement $element, array $attributes = array())
    {
        $attributeList = \Minty\Extensions\template_function_attributes($attributes);

        return "<div{$attributeList}>{$this->label($element)}" .
        "{$this->error($element)}{$this->widget($element)}</div>";
    }

    public function button($url, $method, array $attributes = array())
    {
        if (isset($attributes['form'])) {
            $formAttributes = $attributes['form'];
            unset($attributes['form']);
        } else {
            $formAttributes = array();
        }

        $options = array();
        foreach (array('widget', 'label') as $copied) {
            if (isset($attributes[$copied])) {
                $options[$copied] = $attributes[$copied];
                unset($attributes[$copied]);
            }
        }

        $options['attributes'] = $attributes;

        $form = $this->formService->getFormBuilder(array())
            ->set('action', $url)
            ->set('method', $method)
            ->add('submit', 'submit', $options)
            ->getForm();

        return $this->form($form, $formAttributes);
    }
}
