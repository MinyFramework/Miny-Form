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

class Time extends AbstractFormElement
{
    protected function getDefaultOptions()
    {
        $current = new \DateTime('now');

        $default = array(
            'widget'       => 'choice',
            'format'       => 'H:i:s',
            'data_type'    => 'datetime',
            'with_minutes' => true,
            'with_seconds' => true,
            'field_order'  => array('hour', 'minute', 'second'),
            'hours'        => range(0, 23),
            'minutes'      => range(0, 59),
            'seconds'      => range(0, 59)
        );

        return array_merge(parent::getDefaultOptions(), $default);
    }

    /**
     * @param $value
     *
     * @throws \UnexpectedValueException
     * @return \DateTime
     */
    private function convertViewToIntermediateData($value)
    {
        $widget = $this->getOption('widget');

        switch ($widget) {
            case 'choice':
                $hours   = $this->getOption('hours');
                $minutes = $this->getOption('minutes');
                $seconds = $this->getOption('seconds');

                $hour   = $hours[$value['hour']];
                $minute = $minutes[$value['minute']];
                $second = $seconds[$value['second']];

                $date = new \DateTime("{$hour}:{$minute}:{$second}");
                break;

            case 'textfields':
                $date = new \DateTime("{$value['hour']}:{$value['minute']}:{$value['second']}");
                break;

            case 'single_textfield':
                $date = \DateTime::createFromFormat($this->getOption('format'), $value);
                break;

            default:
                throw new \UnexpectedValueException("Invalid date widget: {$widget}");
        }

        return $date;
    }

    /**
     * @param $value
     *
     * @throws \UnexpectedValueException
     * @return \DateTime
     */
    private function convertModelToIntermediateData($value)
    {
        $type = $this->getOption('data_type');

        switch ($type) {
            case 'datetime':
                return $value;

            case 'string':
                return \DateTime::createFromFormat($this->getOption('format'), $value);

            case 'timestamp':
                $date = new \DateTime();
                $date->setTimestamp($value);

                return $date;

            case 'array':
                $date = new \DateTime();
                $date->setTime($value['hour'], $value['minute'], $value['second']);

                return $date;

            default:
                throw new \UnexpectedValueException("Invalid date type: {$type}");
        }
    }

    protected function toModelValue($value)
    {
        if ($value === null) {
            return $value;
        }
        $dateTime = $this->convertViewToIntermediateData($value);
        $type     = $this->getOption('data_type');
        switch ($type) {
            case 'datetime':
                return $dateTime;

            case 'timestamp':
                return $dateTime->getTimestamp();

            case 'array':
                return $this->datetimeToArray($dateTime);

            case 'string':
                return $dateTime->format($this->getOption('format'));

            default:
                throw new \UnexpectedValueException("Invalid date type: {$type}");
        }
    }

    protected function toViewValue($value)
    {
        if ($value === null) {
            return $value;
        }
        $dateTime = $this->convertModelToIntermediateData($value);
        $widget   = $this->getOption('widget');

        switch ($widget) {
            case 'choice':
                return $this->datetimeToArray($dateTime);

            case 'textfields':
                return $this->datetimeToArray($dateTime);

            case 'single_textfield':
                return $dateTime->format($this->getOption('format'));

            default:
                throw new \UnexpectedValueException("Invalid date widget: {$widget}");
        }
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
            $viewValue = array(
                'hour'   => '',
                'minute' => '',
                'second' => ''
            );
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
            $selected = array(
                'hour'   => null,
                'minute' => null,
                'second' => null
            );
        } else {
            $selected = array(
                'hour'   => array_search($viewValue['hour'], $this->getOption('hours')),
                'minute' => array_search($viewValue['minute'], $this->getOption('minutes')),
                'second' => array_search($viewValue['second'], $this->getOption('seconds'))
            );
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

            $options = array();
            foreach ($this->getOption($key . 's') as $item => $label) {
                $optionAttrs = array('value' => $item);

                if ($selected[$key] === $item) {
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

    private function datetimeToArray(\DateTime $dateTime)
    {
        return array(
            'hour'   => $dateTime->format('H'),
            'minute' => $dateTime->format('i'),
            'second' => $dateTime->format('s')
        );
    }
}
