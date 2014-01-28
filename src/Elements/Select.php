<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

use Modules\Form\FormElement;

class Select extends FormElement
{
    protected $choices = array();
    protected $multiple = false;

    public function __construct($name, $label, array $choices, array $options = array())
    {
        if (isset($options['multiple'])) {
            $options['multiple'] = 'multiple';
            $name .= '[]';
            $this->multiple = true;
        }
        $this->choices = $choices;
        parent::__construct($name, $label, $options);
    }

    public function render(array $args = array())
    {
        $args = $args + $this->options;
        return sprintf('<select%s>%s</select>', $this->getHTMLArgList($args), $this->renderChoices());
    }

    protected function renderChoices()
    {
        $options = '';
        $data = $this->value;
        if (is_null($data) && $this->multiple) {
            $data = array();
        }
        foreach ($this->choices as $key => $text) {
            if ($this->multiple) {
                $selected = in_array($key, $data) ? ' selected="selected"' : '';
            } else {
                $selected = $data == $key ? ' selected="selected"' : '';
            }
            $options .= sprintf('<option value="%s"%s>%s</option>', $key, $selected, $text);
        }
        return $options;
    }

}