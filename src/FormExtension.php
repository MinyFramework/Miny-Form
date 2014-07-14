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
        $ns = '\\' . __NAMESPACE__;

        return array(
            new TemplateFunction('button', array($this, 'button'), $safe),
            new TemplateFunction('form', $ns . '\\extension_function_form', $safe),
            new TemplateFunction('form_begin', $ns . '\\extension_function_begin', $safe),
            new TemplateFunction('form_end', $ns . '\\extension_function_end', $safe),
            new TemplateFunction('form_row', $ns . '\\extension_function_row', $safe),
            new TemplateFunction('form_label', $ns . '\\extension_function_label', $safe),
            new TemplateFunction('form_widget', $ns . '\\extension_function_widget', $safe),
            new TemplateFunction('form_error', $ns . '\\extension_function_error', $safe),
            new TemplateFunction('form_errors', $ns . '\\extension_function_errors', $safe),
        );
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

        $options['attributes'] = new AttributeSet($attributes);
        if (isset($options['label_attributes'])) {
            $options['attributes'] = new AttributeSet($options['label_attributes']);
        }

        $form = $this->formService->getFormBuilder(array())
            ->set('action', $url)
            ->set('method', $method)
            ->add('submit', 'submit', $options)
            ->getForm();

        return extension_function_form($form, $formAttributes);
    }
}

function extension_function_form(Form $form, array $attributes = array(), $scenario = null)
{
    return $form->begin($attributes, $scenario) . $form->end();
}

function extension_function_begin(Form $form, array $attributes = array(), $scenario = null)
{
    return $form->begin($attributes, $scenario);
}

function extension_function_end(Form $form)
{
    return $form->end();
}

function extension_function_error(AbstractFormElement $element, array $attributes = array())
{
    return $element->error(new AttributeSet($attributes));
}

function extension_function_errors(Form $form, array $attributes = array())
{
    if ($form->isValid()) {
        return '';
    }
    $attributeList = AttributeSet::getAttributeString($attributes);

    $output = "<ul{$attributeList}>";
    foreach ($form as $element) {
        $output .= "<li>{$element->getOption('label')}: {$this->error($element)}</li>";
    }

    return $output . '</ul>';
}

function extension_function_label(AbstractFormElement $element, array $attributes = array())
{
    return $element->label(new AttributeSet($attributes));
}

function extension_function_widget(AbstractFormElement $element, array $attributes = array())
{
    return $element->widget(new AttributeSet($attributes));
}

function extension_function_row(AbstractFormElement $element, array $attributes = array())
{
    return $element->row(new AttributeSet($attributes));
}
