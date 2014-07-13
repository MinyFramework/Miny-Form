<?php

namespace Modules\Form\Elements;

use Miny\HTTP\Request;
use Miny\HTTP\Session;
use Modules\Form\CsrfTokenProvider;
use Modules\Form\Form;
use Modules\Form\FormService;
use Modules\Validator\ValidatorService;

class ChoiceTest extends \PHPUnit_Framework_TestCase
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
        $this->validator   = new ValidatorService();
        $this->formService = new FormService($tokenProvider, $this->validator);

        $this->object = (object)array('someProperty' => null);
        $this->form   = $this->formService
            ->getFormBuilder($this->object)
            ->add('someProperty', 'choice', array('choices' => array('a', 'b', 'c')))
            ->getForm();
        $this->form->setOption('csrf_protection', false);
    }

    public function testSingleSelect()
    {
        $element = $this->form->get('someProperty');

        $widget = $element->widget();
        $this->assertEquals(
            '<select name="someProperty" id="someProperty">' .
            '<option value="0">a</option>' .
            '<option value="1">b</option>' .
            '<option value="2">c</option>' .
            '</select>',
            $widget
        );

        $this->form->handle(new Request('POST', '?', array(), array('someProperty' => 1)));
        $this->assertEquals(1, $this->object->someProperty);

        $widget = $element->widget();
        $this->assertEquals(
            '<select name="someProperty" id="someProperty">' .
            '<option value="0">a</option>' .
            '<option value="1" selected="selected">b</option>' .
            '<option value="2">c</option>' .
            '</select>',
            $widget
        );

        $element->setOption('preferred', array('2'));
        $element->setOption('separator', '========');
        $widget = $element->widget();
        $this->assertEquals(
            '<select name="someProperty" id="someProperty">' .
            '<option value="2">c</option>' .
            '<option disabled="disabled">========</option>' .
            '<option value="0">a</option>' .
            '<option value="1" selected="selected">b</option>' .
            '</select>',
            $widget
        );
    }

    public function testMultipleSelect()
    {
        $element = $this->form->get('someProperty');

        $element->setOption('multiple', true);
        $this->form->initialize();

        $widget = $element->widget();

        $this->assertEquals(
            '<select name="someProperty[]" id="someProperty" multiple="multiple">' .
            '<option value="0">a</option>' .
            '<option value="1">b</option>' .
            '<option value="2">c</option>' .
            '</select>',
            $widget
        );

        $this->form->handle(
            new Request('POST', '?', array(), array('someProperty' => array(1, 2)))
        );
        $this->assertEquals(array(1, 2), $this->object->someProperty);

        $widget = $element->widget();
        $this->assertEquals(
            '<select name="someProperty[]" id="someProperty" multiple="multiple">' .
            '<option value="0">a</option>' .
            '<option value="1" selected="selected">b</option>' .
            '<option value="2" selected="selected">c</option>' .
            '</select>',
            $widget
        );

        $element->setOption('preferred', array('2'));
        $element->setOption('separator', '========');
        $widget = $element->widget();
        $this->assertEquals(
            '<select name="someProperty[]" id="someProperty" multiple="multiple">' .
            '<option value="2" selected="selected">c</option>' .
            '<option disabled="disabled">========</option>' .
            '<option value="0">a</option>' .
            '<option value="1" selected="selected">b</option>' .
            '</select>',
            $widget
        );
    }

    public function testRadioGroup()
    {
        $element = $this->form->get('someProperty');

        $element->setOption('multiple', false);
        $element->setOption('expanded', true);
        $this->form->initialize();

        $widget = $element->widget();
        $this->assertEquals(
            '<input type="radio" name="someProperty" id="someProperty_0" value="0" /><label for="someProperty_0">a</label>' .
            '<input type="radio" name="someProperty" id="someProperty_1" value="1" /><label for="someProperty_1">b</label>' .
            '<input type="radio" name="someProperty" id="someProperty_2" value="2" /><label for="someProperty_2">c</label>',
            $widget
        );

        $this->form->handle(new Request('POST', '?', array(), array('someProperty' => 1)));
        $this->assertEquals(1, $this->object->someProperty);

        $widget = $element->widget();
        $this->assertEquals(
            '<input type="radio" name="someProperty" id="someProperty_0" value="0" /><label for="someProperty_0">a</label>' .
            '<input type="radio" name="someProperty" id="someProperty_1" value="1" checked="checked" /><label for="someProperty_1">b</label>' .
            '<input type="radio" name="someProperty" id="someProperty_2" value="2" /><label for="someProperty_2">c</label>',
            $widget
        );
    }

    public function testCheckBoxGroup()
    {
        $element = $this->form->get('someProperty');

        $element->setOption('expanded', true);
        $element->setOption('multiple', true);
        $this->form->initialize();

        $this->assertEquals(
            '<input type="checkbox" name="someProperty[]" id="someProperty_0" value="0" /><label for="someProperty_0">a</label>' .
            '<input type="checkbox" name="someProperty[]" id="someProperty_1" value="1" /><label for="someProperty_1">b</label>' .
            '<input type="checkbox" name="someProperty[]" id="someProperty_2" value="2" /><label for="someProperty_2">c</label>',
            $element->widget()
        );

        $this->form->handle(
            new Request('POST', '?', array(), array('someProperty' => array(1, 2)))
        );
        $this->assertEquals(array(1, 2), $this->object->someProperty);

        $this->assertEquals(
            '<input type="checkbox" name="someProperty[]" id="someProperty_0" value="0" /><label for="someProperty_0">a</label>' .
            '<input type="checkbox" name="someProperty[]" id="someProperty_1" value="1" checked="checked" /><label for="someProperty_1">b</label>' .
            '<input type="checkbox" name="someProperty[]" id="someProperty_2" value="2" checked="checked" /><label for="someProperty_2">c</label>',
            $element->widget()
        );
    }

    public function testIncorrectValues()
    {
        $this->form->handle(new Request('POST', '?', array(), array('someProperty' => 3)));
        $this->assertEquals(null, $this->object->someProperty);
    }

    public function testIncorrectValuesFromMultipleSelect()
    {
        $this->form->get('someProperty')->setOption('multiple', true);

        $this->form->handle(
            new Request('POST', '?', array(), array('someProperty' => array(1, 3)))
        );
        $this->assertEquals(array(1), $this->object->someProperty);
    }
}
