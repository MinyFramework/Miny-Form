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

class Date extends AbstractFormElement
{
    /**
     * @var AbstractConverter
     */
    private $viewConverter;

    /**
     * @var AbstractConverter
     */
    private $modelConverter;

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
                return new DateTimeToArrayConverter(array('year', 'month', 'day'));
        }
        throw new \UnexpectedValueException("Invalid date type: {$type}");
    }

    private function createViewConverter()
    {
        $widget = $this->getOption('widget');
        switch ($widget) {
            case 'choice':
            case 'textfields':
                return new DateTimeToArrayConverter(array('year', 'month', 'day'));

            case 'single_textfield':
                return new DateTimeToStringConverter($this->getOption('format'));
        }
        throw new \UnexpectedValueException("Invalid date widget: {$widget}");
    }

    protected function getDefaultOptions()
    {
        $current = new \DateTime('now');
        $year    = (int)$current->format('Y');

        $default = array(
            'widget'      => 'choice',
            'format'      => 'Y-m-d',
            'data_type'   => 'datetime',
            'field_order' => array('year', 'month', 'day'),
            'years'       => range($year - 5, $year + 5),
            'months'      => range(1, 12),
            'days'        => range(1, 31)
        );

        return array_merge(parent::getDefaultOptions(), $default);
    }

    protected function toModelValue($value)
    {
        if ($value === null) {
            return $value;
        }
        $dateTime = $this->viewConverter->convertBack($value);
        $dateTime->setTime(0, 0, 0);

        return $this->modelConverter->convert($dateTime);
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

        if ($this->getOption('format') === 'Y-m-d') {
            return sprintf('<input type="date"%s />', $attributes);
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
            $viewValue = array(
                'year'  => '',
                'month' => '',
                'day'   => ''
            );
        }

        $output = '';
        foreach ($this->getOption('field_order') as $key) {
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
            $selected = array(
                'year'  => null,
                'month' => null,
                'day'   => null
            );
        } else {
            $selected = array(
                'year'  => $viewValue['year'],
                'month' => sprintf('%02s', $viewValue['month']),
                'day'   => sprintf('%02s', $viewValue['day'])
            );
        }
        $output = '';
        foreach ($this->getOption('field_order') as $key) {
            $attr = clone $attributes;

            $attr->append('name', "[{$key}]");
            $attr->append('id', '_' . $key);

            $options = array();
            foreach ($this->getOption($key . 's') as $label) {
                $label       = sprintf('%02s', $label);
                $optionAttrs = array('value' => $label);

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
