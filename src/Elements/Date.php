<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form\Elements;

use Modules\Form\AbstractFormElement;

class Date extends AbstractFormElement
{
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
                $years  = $this->getOption('years');
                $months = $this->getOption('months');
                $days   = $this->getOption('days');

                $year  = $years[$value['year']];
                $month = $months[$value['month']];
                $day   = $days[$value['day']];

                $date = new \DateTime("{$year}-{$month}-{$day}");
                break;

            case 'textfields':
                $date = new \DateTime("{$value['year']}-{$value['month']}-{$value['day']}");
                break;

            case 'single_textfield':
                $date = \DateTime::createFromFormat($this->getOption('format'), $value);
                break;

            default:
                throw new \UnexpectedValueException("Invalid date widget: {$widget}");
        }
        $date->setTime(0, 0, 0);

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
                $date->setDate($value['year'], $value['month'], $value['day']);

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

    protected function render(array $attributes)
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
     * @param array $attributes
     *
     * @return string
     */
    protected function renderTextfield(array $attributes)
    {
        $viewValue = $this->getViewValue();
        if ($viewValue !== null) {
            $attributes['value'] = $viewValue;
        }

        $attributeList = $this->attributes($attributes);
        if ($this->getOption('format') === 'Y-m-d') {
            return sprintf('<input type="date"%s />', $attributeList);
        } else {
            return sprintf('<input type="text"%s />', $attributeList);
        }
    }

    /**
     * @param array $attributes
     *
     * @return string
     */
    protected function renderTextfields(array $attributes)
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
            $attr = $attributes;
            $attr['name'] .= "[{$key}]";
            $attr['id'] .= "_{$key}";
            if ($viewValue[$key] !== '') {
                $attr['value'] = $viewValue[$key];
            }
            $attributeList = $this->attributes($attr);
            $output .= sprintf('<input type="text"%s />', $attributeList);
        }

        return $output;
    }

    /**
     * @param array $attributes
     *
     * @return string
     */
    protected function renderChoices(array $attributes)
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
                'year'  => array_search($viewValue['year'], $this->getOption('years')),
                'month' => array_search($viewValue['month'], $this->getOption('months')),
                'day'   => array_search($viewValue['day'], $this->getOption('days'))
            );
        }
        $output = '';
        foreach ($this->getOption('field_order') as $key) {
            $attr = $attributes;
            $attr['name'] .= "[{$key}]";
            $attr['id'] .= "_{$key}";
            $attributeList = $this->attributes($attr);
            $options       = array();
            foreach ($this->getOption($key . 's') as $item => $label) {
                $optionAttrs = array('value' => $item);

                if ($selected[$key] === $item) {
                    $optionAttrs['selected'] = 'selected';
                }
                $options[] = sprintf(
                    '<option%s>%s</option>',
                    $this->attributes($optionAttrs),
                    $label
                );
            }
            $output .= sprintf('<select%s>%s</select>', $attributeList, implode('', $options));
        }

        return $output;
    }

    private function datetimeToArray(\DateTime $dateTime)
    {
        return array(
            'year'  => $dateTime->format('Y'),
            'month' => $dateTime->format('m'),
            'day'   => $dateTime->format('d')
        );
    }
}
