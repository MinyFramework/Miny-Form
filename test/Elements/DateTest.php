<?php

namespace Modules\Form\Elements;

use Miny\Event\EventDispatcher;
use Miny\HTTP\Request;
use Miny\HTTP\Session;
use Modules\Form\CsrfTokenProvider;
use Modules\Form\Form;
use Modules\Form\FormService;
use Modules\Validator\ValidatorService;

class DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FormService
     */
    private $formService;

    /**
     * @var ValidatorService
     */
    private $validator;
    private $object;

    /**
     * @var Form
     */
    private $form;

    public function setUp()
    {
        $tokenProvider     = new CsrfTokenProvider(new Session(false));
        $this->validator   = new ValidatorService(new EventDispatcher());
        $this->formService = new FormService($tokenProvider, $this->validator);

        $this->object = (object)['someProperty' => null];
        $this->form   = $this->formService
            ->getFormBuilder($this->object)
            ->add('someProperty', 'date')
            ->getForm();
        $this->form->setOption('csrf_protection', false);
    }

    public function testSingleDateTextField()
    {
        $element = $this->form->get('someProperty');
        $element->setOption('data_type', 'string');

        $element->setOption('widget', 'single_textfield');
        $this->assertEquals(
            '<input type="date" name="someProperty" id="someProperty" />',
            $element->widget()
        );

        $element->setOption('format', 'M-d-y');
        $this->assertEquals(
            '<input type="text" name="someProperty" id="someProperty" />',
            $element->widget()
        );

        $element->setOption('format', 'Y-m-d');
        $element->setOption('empty_data', '2010-05-07');

        $this->form->initialize();

        $this->assertEquals(
            '<input type="date" name="someProperty" id="someProperty" value="2010-05-07" />',
            $element->widget()
        );

        $element->setOption('format', 'm-Y-d');
        $element->setOption('empty_data', '05-2010-07');
        $this->form->initialize();

        $this->assertEquals(
            '<input type="text" name="someProperty" id="someProperty" value="05-2010-07" />',
            $element->widget()
        );
    }

    public function testDateTextFields()
    {
        $element = $this->form->get('someProperty');
        $element->setOption('data_type', 'string');

        $element->setOption('widget', 'textfields');
        $this->assertEquals(
            '<input type="text" name="someProperty[year]" id="someProperty_year" />' .
            '<input type="text" name="someProperty[month]" id="someProperty_month" />' .
            '<input type="text" name="someProperty[day]" id="someProperty_day" />',
            $element->widget()
        );

        $element->setOption('empty_data', '2010-05-07');
        $this->form->initialize();

        $this->assertEquals(
            '<input type="text" name="someProperty[year]" id="someProperty_year" value="2010" />' .
            '<input type="text" name="someProperty[month]" id="someProperty_month" value="05" />' .
            '<input type="text" name="someProperty[day]" id="someProperty_day" value="07" />',
            $element->widget()
        );

        $this->object->someProperty = '2010-05-06';
        $this->form->initialize();
        $this->assertEquals(
            '<input type="text" name="someProperty[year]" id="someProperty_year" value="2010" />' .
            '<input type="text" name="someProperty[month]" id="someProperty_month" value="05" />' .
            '<input type="text" name="someProperty[day]" id="someProperty_day" value="06" />',
            $element->widget()
        );
    }

    public function testDateChoice()
    {
        $element = $this->form->get('someProperty');
        $element->setOption('data_type', 'string');
        $element->setOption('years', ['2010', '2011']);
        $element->setOption('months', ['1', '2']);
        $element->setOption('days', ['1', '2', '3']);
        $element->setOption('widget', 'choice');

        $this->assertEquals(
            '<select name="someProperty[year]" id="someProperty_year"><option value="2010">2010</option><option value="2011">2011</option></select>' .
            '<select name="someProperty[month]" id="someProperty_month"><option value="01">01</option><option value="02">02</option></select>' .
            '<select name="someProperty[day]" id="someProperty_day"><option value="01">01</option><option value="02">02</option><option value="03">03</option></select>',
            $element->widget()
        );

        $this->object->someProperty = '2011-01-02';
        $this->form->initialize();
        $this->assertEquals(
            '<select name="someProperty[year]" id="someProperty_year"><option value="2010">2010</option><option value="2011" selected="selected">2011</option></select>' .
            '<select name="someProperty[month]" id="someProperty_month"><option value="01" selected="selected">01</option><option value="02">02</option></select>' .
            '<select name="someProperty[day]" id="someProperty_day"><option value="01">01</option><option value="02" selected="selected">02</option><option value="03">03</option></select>',
            $element->widget()
        );
    }

    public function testDateCanReturnDateTimeObject()
    {
        $field = $this->form->get('someProperty');

        $field->setOption('data_type', 'datetime');
        $field->setOption('widget', 'single_textfield');

        $this->form->handle(
            new Request('POST', '?', [], ['someProperty' => '2010-05-06'])
        );

        $this->assertInstanceOf('\DateTime', $this->object->someProperty);
        $this->assertEquals('2010-05-06', $this->object->someProperty->format('Y-m-d'));

        $field->setOption('widget', 'textfields');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'year'  => '2010',
                        'month' => '05',
                        'day'   => '07'
                    ]
                ]
            )
        );

        $this->assertInstanceOf('\DateTime', $this->object->someProperty);
        $this->assertEquals('2010-05-07', $this->object->someProperty->format('Y-m-d'));

        //choice sends indexes
        $field->setOption('years', ['2010', '2011']);
        $field->setOption('widget', 'choice');

        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'year'  => '2011',
                        'month' => '03',
                        'day'   => '03'
                    ]
                ]
            )
        );

        $this->assertInstanceOf('\DateTime', $this->object->someProperty);
        $this->assertEquals('2011-03-03', $this->object->someProperty->format('Y-m-d'));
    }

    public function testDateCanReturnDateTimestamp()
    {
        $this->form->get('someProperty')->setOption('data_type', 'timestamp');

        $this->form->get('someProperty')->setOption('widget', 'single_textfield');
        $this->form->handle(
            new Request('POST', '?', [], ['someProperty' => '2010-05-06'])
        );

        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', '2010-05-06 00:00:00');
        $this->assertEquals($dateTime->format('U'), $this->object->someProperty);

        $this->form->get('someProperty')->setOption('widget', 'textfields');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'year'  => '2010',
                        'month' => '05',
                        'day'   => '07'
                    ]
                ]
            )
        );

        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', '2010-05-07 00:00:00');
        $this->assertEquals($dateTime->format('U'), $this->object->someProperty);

        //choice sends indexes
        $this->form->get('someProperty')->setOption('years', ['2010', '2011']);
        $this->form->get('someProperty')->setOption('widget', 'choice');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'year'  => '2011',
                        'month' => '03',
                        'day'   => '03'
                    ]
                ]
            )
        );

        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', '2011-03-03 00:00:00');
        $this->assertEquals($dateTime->format('U'), $this->object->someProperty);
    }

    public function testDateCanReturnDateString()
    {
        $this->form->get('someProperty')->setOption('data_type', 'string');

        $this->form->get('someProperty')->setOption('widget', 'single_textfield');
        $this->form->handle(
            new Request('POST', '?', [], ['someProperty' => '2010-05-06'])
        );

        $this->assertEquals('2010-05-06', $this->object->someProperty);

        $this->form->get('someProperty')->setOption('widget', 'textfields');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'year'  => '2010',
                        'month' => '05',
                        'day'   => '07'
                    ]
                ]
            )
        );

        $this->assertEquals('2010-05-07', $this->object->someProperty);

        //choice sends indexes
        $this->form->get('someProperty')->setOption('years', ['2010', '2011']);
        $this->form->get('someProperty')->setOption('widget', 'choice');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'year'  => '2011',
                        'month' => '03',
                        'day'   => '03'
                    ]
                ]
            )
        );

        $this->assertEquals('2011-03-03', $this->object->someProperty);
    }

    public function testDateCanReturnDateArray()
    {
        $this->form->get('someProperty')->setOption('data_type', 'array');

        $this->form->get('someProperty')->setOption('widget', 'single_textfield');
        $this->form->handle(
            new Request('POST', '?', [], ['someProperty' => '2010-05-06'])
        );

        $this->assertEquals(
            [
                'year'  => '2010',
                'month' => '05',
                'day'   => '06'
            ],
            $this->object->someProperty
        );

        $this->form->get('someProperty')->setOption('widget', 'textfields');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'year'  => '2010',
                        'month' => '05',
                        'day'   => '07'
                    ]
                ]
            )
        );

        $this->assertEquals(
            [
                'year'  => '2010',
                'month' => '05',
                'day'   => '07'
            ],
            $this->object->someProperty
        );

        //choice sends indexes
        $this->form->get('someProperty')->setOption('years', ['2010', '2011']);
        $this->form->get('someProperty')->setOption('widget', 'choice');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'year'  => '2011',
                        'month' => '03',
                        'day'   => '03'
                    ]
                ]
            )
        );

        $this->assertEquals(
            [
                'year'  => '2011',
                'month' => '03',
                'day'   => '03'
            ],
            $this->object->someProperty
        );
    }
}
