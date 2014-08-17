<?php

namespace Modules\Form\Elements;

use Miny\Event\EventDispatcher;
use Miny\HTTP\Request;
use Miny\HTTP\Session;
use Modules\Form\CsrfTokenProvider;
use Modules\Form\Form;
use Modules\Form\FormService;
use Modules\Validator\ValidatorService;

class TimeTest extends \PHPUnit_Framework_TestCase
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
            ->add('someProperty', 'time')
            ->getForm();
        $this->form->setOption('csrf_protection', false);
    }

    public function testSingleTimeTextField()
    {
        $element = $this->form->get('someProperty');
        $element->setOption('data_type', 'string');

        $element->setOption('widget', 'single_textfield');
        $this->assertEquals(
            '<input type="time" name="someProperty" id="someProperty" />',
            $element->widget()
        );

        $element->setOption('format', 'H/i/s');
        $this->assertEquals(
            '<input type="text" name="someProperty" id="someProperty" />',
            $element->widget()
        );

        $element->setOption('format', 'H:i:s');
        $element->setOption('empty_data', '13:42:50');

        $this->form->initialize();

        $this->assertEquals(
            '<input type="time" name="someProperty" id="someProperty" value="13:42:50" />',
            $element->widget()
        );

        $element->setOption('format', 'i:s:H');
        $element->setOption('empty_data', '30:42:16');
        $this->form->initialize();

        $this->assertEquals(
            '<input type="text" name="someProperty" id="someProperty" value="30:42:16" />',
            $element->widget()
        );
    }

    public function testDateTextFields()
    {
        $element = $this->form->get('someProperty');
        $element->setOption('data_type', 'string');

        $element->setOption('widget', 'textfields');
        $this->assertEquals(
            '<input type="text" name="someProperty[hour]" id="someProperty_hour" />' .
            '<input type="text" name="someProperty[minute]" id="someProperty_minute" />' .
            '<input type="text" name="someProperty[second]" id="someProperty_second" />',
            $element->widget()
        );

        $element->setOption('empty_data', '13:50:00');
        $this->form->initialize();
        $this->assertEquals(
            '<input type="text" name="someProperty[hour]" id="someProperty_hour" value="13" />' .
            '<input type="text" name="someProperty[minute]" id="someProperty_minute" value="50" />' .
            '<input type="text" name="someProperty[second]" id="someProperty_second" value="00" />',
            $element->widget()
        );

        $this->object->someProperty = '10:05:06';
        $this->form->initialize();
        $this->assertEquals(
            '<input type="text" name="someProperty[hour]" id="someProperty_hour" value="10" />' .
            '<input type="text" name="someProperty[minute]" id="someProperty_minute" value="05" />' .
            '<input type="text" name="someProperty[second]" id="someProperty_second" value="06" />',
            $element->widget()
        );

        $element->setOption('with_seconds', false);
        $this->assertEquals(
            '<input type="text" name="someProperty[hour]" id="someProperty_hour" value="10" />' .
            '<input type="text" name="someProperty[minute]" id="someProperty_minute" value="05" />',
            $element->widget()
        );

        $element->setOption('with_minutes', false);
        $this->assertEquals(
            '<input type="text" name="someProperty[hour]" id="someProperty_hour" value="10" />',
            $element->widget()
        );
    }

    public function testDateChoice()
    {
        $element = $this->form->get('someProperty');
        $element->setOption('data_type', 'string');
        $element->setOption('hours', ['10', '11']);
        $element->setOption('minutes', ['1', '2']);
        $element->setOption('seconds', ['1', '2', '3']);
        $element->setOption('widget', 'choice');

        $this->assertEquals(
            '<select name="someProperty[hour]" id="someProperty_hour"><option value="10">10</option><option value="11">11</option></select>' .
            '<select name="someProperty[minute]" id="someProperty_minute"><option value="01">01</option><option value="02">02</option></select>' .
            '<select name="someProperty[second]" id="someProperty_second"><option value="01">01</option><option value="02">02</option><option value="03">03</option></select>',
            $element->widget()
        );

        $this->object->someProperty = '11:01:02';
        $this->form->initialize();

        $this->assertEquals(
            '<select name="someProperty[hour]" id="someProperty_hour"><option value="10">10</option><option value="11" selected="selected">11</option></select>' .
            '<select name="someProperty[minute]" id="someProperty_minute"><option value="01" selected="selected">01</option><option value="02">02</option></select>' .
            '<select name="someProperty[second]" id="someProperty_second"><option value="01">01</option><option value="02" selected="selected">02</option><option value="03">03</option></select>',
            $element->widget()
        );

        $element->setOption('with_seconds', false);
        $this->assertEquals(
            '<select name="someProperty[hour]" id="someProperty_hour"><option value="10">10</option><option value="11" selected="selected">11</option></select>' .
            '<select name="someProperty[minute]" id="someProperty_minute"><option value="01" selected="selected">01</option><option value="02">02</option></select>',
            $element->widget()
        );

        $element->setOption('with_minutes', false);
        $this->assertEquals(
            '<select name="someProperty[hour]" id="someProperty_hour"><option value="10">10</option><option value="11" selected="selected">11</option></select>',
            $element->widget()
        );
    }

    public function testDateCanReturnDateTimeObject()
    {
        $this->form->get('someProperty')->setOption('data_type', 'datetime');

        $this->form->get('someProperty')->setOption('widget', 'single_textfield');
        $this->form->handle(
            new Request('POST', '?', [], ['someProperty' => '10:05:06'])
        );

        $this->assertInstanceOf('\DateTime', $this->object->someProperty);
        $this->assertEquals('10:05:06', $this->object->someProperty->format('H:i:s'));

        $this->form->get('someProperty')->setOption('widget', 'textfields');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'hour'   => '10',
                        'minute' => '05',
                        'second' => '07'
                    ]
                ]
            )
        );

        $this->assertInstanceOf('\DateTime', $this->object->someProperty);
        $this->assertEquals('10:05:07', $this->object->someProperty->format('H:i:s'));

        //choice sends indexes
        $this->form->get('someProperty')->setOption('hours', ['10', '11']);
        $this->form->get('someProperty')->setOption('widget', 'choice');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'hour'   => '11',
                        'minute' => '02',
                        'second' => '02'
                    ]
                ]
            )
        );

        $this->assertInstanceOf('\DateTime', $this->object->someProperty);
        $this->assertEquals('11:02:02', $this->object->someProperty->format('H:i:s'));
    }

    public function testDateCanReturnDateTimestamp()
    {
        $this->form->get('someProperty')->setOption('data_type', 'timestamp');

        $this->form->get('someProperty')->setOption('widget', 'single_textfield');
        $this->form->handle(
            new Request('POST', '?', [], ['someProperty' => '10:05:06'])
        );

        $dateTime = \DateTime::createFromFormat('H:i:s', '10:05:06');
        $this->assertEquals($dateTime->format('U'), $this->object->someProperty);

        $this->form->get('someProperty')->setOption('widget', 'textfields');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'hour'   => '10',
                        'minute' => '05',
                        'second' => '07'
                    ]
                ]
            )
        );

        $dateTime = \DateTime::createFromFormat('H:i:s', '10:05:07');
        $this->assertEquals($dateTime->format('U'), $this->object->someProperty);

        //choice sends indexes
        $this->form->get('someProperty')->setOption('hours', ['10', '11']);
        $this->form->get('someProperty')->setOption('widget', 'choice');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'hour'   => '11',
                        'minute' => '02',
                        'second' => '02'
                    ]
                ]
            )
        );

        $dateTime = \DateTime::createFromFormat('H:i:s', '11:02:02');
        $this->assertEquals($dateTime->format('U'), $this->object->someProperty);
    }

    public function testDateCanReturnDateString()
    {
        $this->form->get('someProperty')->setOption('data_type', 'string');

        $this->form->get('someProperty')->setOption('widget', 'single_textfield');
        $this->form->handle(
            new Request('POST', '?', [], ['someProperty' => '10:05:06'])
        );

        $this->assertEquals('10:05:06', $this->object->someProperty);

        $this->form->get('someProperty')->setOption('widget', 'textfields');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'hour'   => '10',
                        'minute' => '05',
                        'second' => '07'
                    ]
                ]
            )
        );

        $this->assertEquals('10:05:07', $this->object->someProperty);

        //choice sends indexes
        $this->form->get('someProperty')->setOption('hours', ['10', '11']);
        $this->form->get('someProperty')->setOption('widget', 'choice');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'hour'   => '11',
                        'minute' => '02',
                        'second' => '02'
                    ]
                ]
            )
        );

        $this->assertEquals('11:02:02', $this->object->someProperty);
    }

    public function testDateCanReturnDateArray()
    {
        $this->form->get('someProperty')->setOption('data_type', 'array');

        $this->form->get('someProperty')->setOption('widget', 'single_textfield');
        $this->form->handle(
            new Request('POST', '?', [], ['someProperty' => '10:05:06'])
        );

        $this->assertEquals(
            [
                'hour'   => '10',
                'minute' => '05',
                'second' => '06'
            ],
            $this->object->someProperty
        );

        $this->form->get('someProperty')->setOption('widget', 'textfields');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'hour'   => '10',
                        'minute' => '05',
                        'second' => '07'
                    ]
                ]
            )
        );

        $this->assertEquals(
            [
                'hour'   => '10',
                'minute' => '05',
                'second' => '07'
            ],
            $this->object->someProperty
        );

        //choice sends indexes
        $this->form->get('someProperty')->setOption('hours', ['10', '11']);
        $this->form->get('someProperty')->setOption('widget', 'choice');
        $this->form->handle(
            new Request('POST', '?', [], [
                    'someProperty' => [
                        'hour'   => '11',
                        'minute' => '02',
                        'second' => '02'
                    ]
                ]
            )
        );

        $this->assertEquals(
            [
                'hour'   => '11',
                'minute' => '02',
                'second' => '02'
            ],
            $this->object->someProperty
        );
    }
}
