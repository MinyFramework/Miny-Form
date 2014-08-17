<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

use Modules\Form\AbstractConverter;
use Modules\Form\AbstractFormElement;
use Modules\Form\AttributeSet;
use Modules\Form\Converters\DateTimeToArrayConverter;
use Modules\Form\Converters\DateTimeToStringConverter;
use Modules\Form\Converters\DateTimeToTimestampConverter;
use Modules\Form\Converters\NullConverter;

class Time extends AbstractFormElement
{
    /**
     * @var AbstractConverter
     */
    private $viewConverter;

    /**
     * @var AbstractConverter
     */
    private $modelConverter;

    protected function getDefaultOptions()
    {
        $default = [
            'widget'       => 'choice',
            'format'       => 'H:i:s',
            'data_type'    => 'datetime',
            'with_minutes' => true,
            'with_seconds' => true,
            'field_order'  => ['hour', 'minute', 'second'],
            'hours'        => range(0, 23),
            'minutes'      => range(0, 59),
            'seconds'      => range(0, 59)
        ];

        return array_merge(parent::getDefaultOptions(), $default);
    }

    public function initialize()
    {
        $this->viewConverter  = $this->createViewConverter();
        $this->modelConverter = $this->createModelConverter();

        parent::initialize();
    }

    private function createModelConverter()
    {
        $type = $this->getOption('data_type');
        switch ($type) {
            case 'datetime':
                return new NullConverter();

            case 'string':
                return new DateTimeToStringConverter($this->getOption('format'));

            case 'timestamp':
                return new DateTimeToTimestampConverter();

            case 'array':
                return new DateTimeToArrayConverter(['hour', 'minute', 'second']);
        }
        throw new \UnexpectedValueException("Invalid date type: {$type}");
    }

    private function createViewConverter()
    {
        $widget = $this->getOption('widget');
        switch ($widget) {
            case 'choice':
            case 'textfields':
                return new DateTimeToArrayConverter(['hour', 'minute', 'second']);

            case 'single_textfield':
                return new DateTimeToStringConverter($this->getOption('format'));
        }
        throw new \UnexpectedValueException("Invalid date widget: {$widget}");
    }

    protected function toModelValue($value)
    {
        if ($value === null) {
            return $value;
        }

        return $this->modelConverter->convert(
            $this->viewConverter->convertBack($value)
        );
    }

    protected function toViewValue($value)
    {
        if ($value === null) {
            return $value;
        }

        return $this->viewConverter->convert(
            $this->modelConverter->convertBack($value)
        );
    }

    protected function render(AttributeSet $attributes)
    {
        $widget = $this->getOption('widget');
        switch ($widget) {
            case 'single_textfield':
                return $this->renderTextfield($attributes);

            case 'choice':
                return $this->renderChoices($attributes);

            case 'textfields':
                return $this->renderTextfields($attributes);

            default:
                throw new \UnexpectedValueException("Invalid date widget: {$widget}");
        }
    }

    /**
     * @param AttributeSet $attributes
     *
     * @return string
     */
    protected function renderTextfield(AttributeSet $attributes)
    {
        $viewValue = $this->getViewValue();
        if ($viewValue !== null) {
            $attributes->add('value', $viewValue);
        }

        if ($this->getOption('format') === 'H:i:s') {
            return sprintf('<input type="time"%s />', $attributes);
        } else {
            return sprintf('<input type="text"%s />', $attributes);
        }
    }

    /**
     * @param AttributeSet $attributes
     *
     * @return string
     */
    protected function renderTextfields(AttributeSet $attributes)
    {
        $viewValue = $this->getViewValue();
        if ($viewValue === null) {
            $viewValue = [
                'hour'   => '',
                'minute' => '',
                'second' => ''
            ];
        }

        $output = '';
        $fields = $this->getOption('field_order');
        if ($this->getOption('with_seconds') === false) {
            unset($fields[array_search('second', $fields)]);
        }
        if ($this->getOption('with_minutes') === false) {
            unset($fields[array_search('minute', $fields)]);
        }
        foreach ($fields as $key) {
            $attr = clone $attributes;
            $attr->append('name', "[{$key}]");
            $attr->append('id', '_' . $key);

            if ($viewValue[$key] !== '') {
                $attr->add('value', $viewValue[$key]);
            }
            $output .= sprintf('<input type="text"%s />', $attr);
        }

        return $output;
    }

    /**
     * @param AttributeSet $attributes
     *
     * @return string
     */
    protected function renderChoices(AttributeSet $attributes)
    {
        $viewValue = $this->getViewValue();
        if ($viewValue === null) {
            $selected = [
                'hour'   => null,
                'minute' => null,
                'second' => null
            ];
        } else {
            $selected = [
                'hour'   => $viewValue['hour'],
                'minute' => sprintf('%02s', $viewValue['minute']),
                'second' => sprintf('%02s', $viewValue['second'])
            ];
        }
        $output = '';
        $fields = $this->getOption('field_order');
        if ($this->getOption('with_seconds') === false) {
            unset($fields[array_search('second', $fields)]);
        }
        if ($this->getOption('with_minutes') === false) {
            unset($fields[array_search('minute', $fields)]);
        }
        foreach ($fields as $key) {
            $attr = clone $attributes;

            $attr->append('name', "[{$key}]");
            $attr->append('id', '_' . $key);

            $options = [];
            foreach ($this->getOption($key . 's') as $label) {
                $label       = sprintf('%02s', $label);
                $optionAttrs = ['value' => $label];

                if ($selected[$key] === $label) {
                    $optionAttrs['selected'] = 'selected';
                }
                $options[] = sprintf(
                    '<option%s>%s</option>',
                    AttributeSet::getAttributeString($optionAttrs),
                    $label
                );
            }
            $output .= sprintf('<select%s>%s</select>', $attr, implode('', $options));
        }

        return $output;
    }
}
