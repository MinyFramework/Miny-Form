<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

use Modules\Form\AbstractFormElement;
use Modules\Form\AttributeSet;

class Choice extends AbstractFormElement
{
    private $checkedCallback;

    protected function getDefaultOptions()
    {
        $default = array(
            'choices'   => null,
            'source'    => null,
            'multiple'  => false,
            'expanded'  => false,
            'preferred' => null,
            'separator' => '--------'
        );

        return array_merge(parent::getDefaultOptions(), $default);
    }

    protected function toViewValue($value)
    {
        if ($this->getOption('multiple')) {
            $value = (array)$value;
        }

        return $value;
    }

    protected function toModelValue($value)
    {
        $choices = $this->getOption('choices');
        if ($this->getOption('multiple')) {
            $value = array_filter(
                $value,
                function ($value) use ($choices) {
                    return isset($choices[$value]);
                }
            );
        } elseif (!isset($choices[$value])) {
            return null;
        }

        return $value;
    }

    public function initialize()
    {
        if (!is_array($this->getOption('choices'))) {
            $source = $this->getOption('source');
            if ($source === null) {
                throw new \LogicException('No item source has been set.');
            }
            $choices = call_user_func($source);
            if (!is_array($choices)) {
                throw new \LogicException('No valid item source has been set.');
            }
            $this->setOption('choices', $choices);
        }
        if ($this->getOption('multiple')) {
            $this->setOption('empty_data', array());
            $this->checkedCallback = function ($needle, $haystack) {
                return in_array($needle, $haystack);
            };
        } else {
            $this->checkedCallback = function ($needle, $haystack) {
                return $needle === $haystack;
            };
        }
        parent::initialize();
    }

    public function widget(AttributeSet $attributes = null)
    {
        if (isset($attributes['separator'])) {
            $this->setOption('separator', $attributes['separator']);
            unset($attributes['separator']);
        }

        return parent::widget($attributes);
    }

    protected function render(AttributeSet $attributes)
    {
        $multiple = $this->getOption('multiple');

        if ($this->getOption('expanded')) {
            return $this->renderInputList(
                $this->getItemAttributes($multiple, $attributes)
            );
        } else {
            return $this->renderSelect($multiple, $attributes);
        }
    }

    private function renderSelect($multiple, AttributeSet $attributes)
    {
        $options = array();
        $values  = $this->getViewValue();

        $choices   = $this->getOption('choices');
        $preferred = $this->getOption('preferred');

        $callback = $this->checkedCallback;
        if (is_array($preferred)) {
            foreach ($preferred as $key) {
                $options[] = $this->getSelectItem($key, $choices[$key], $callback($key, $values));
                unset($choices[$key]);
            }

            $options[] = sprintf(
                '<option disabled="disabled">%s</option>',
                $this->getOption('separator')
            );
        }

        foreach ($choices as $key => $label) {
            $options[] = $this->getSelectItem($key, $label, $callback($key, $values));
        }

        if ($multiple) {
            $attributes->add('multiple', 'multiple');
            $attributes->append('name', '[]');
        }

        return sprintf(
            '<select%s>%s</select>',
            $attributes,
            implode('', $options)
        );
    }

    private function getSelectItem($key, $label, $selected)
    {
        $optionAttributes = array('value' => $key);

        if ($selected) {
            $optionAttributes['selected'] = 'selected';
        }

        return sprintf(
            '<option%s>%s</option>',
            AttributeSet::getAttributeString($optionAttributes),
            $label
        );
    }

    private function getItemAttributes($multiple, AttributeSet $attributes)
    {
        if ($attributes->has('option_attributes')) {
            $itemAttributes = (array)$attributes->get('option_attributes');
        } else {
            $itemAttributes = array();
        }
        $itemAttributes['type'] = $multiple ? 'checkbox' : 'radio';
        $itemAttributes['id']   = $itemAttributes['name'] = $this->getOption('name');
        if ($multiple) {
            $itemAttributes['name'] .= '[]';
        }

        return new AttributeSet($itemAttributes);
    }

    private function renderInputList(AttributeSet $itemAttributes)
    {
        $options  = array();
        $values   = $this->getViewValue();
        $callback = $this->checkedCallback;

        foreach ($this->getOption('choices') as $key => $label) {
            $attributes = clone $itemAttributes;

            $attributes->add('value', $key);
            $attributes->append('id', '_' . $key);

            if ($callback($key, $values)) {
                $attributes->add('checked', 'checked');
            }
            $options[] = sprintf(
                '<input%s /><label for="%s">%s</label>',
                $attributes,
                $attributes->get('id'),
                $label
            );
        }

        return implode('', $options);
    }
}
