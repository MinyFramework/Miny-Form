<?php

namespace Modules\Form\Elements;

use Miny\HTTP\Request;
use Miny\HTTP\Session;
use Modules\Form\Form;
use Modules\Form\FormService;
use Modules\Validator\ValidatorService;

class CheckBoxTest extends \PHPUnit_Framework_TestCase
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
        $session           = new Session(false);
        $this->validator   = new ValidatorService();
        $this->formService = new FormService($session, $this->validator);

        $this->object = (object)array('someProperty' => null);
        $this->form   = $this->formService
            ->getFormBuilder($this->object)
            ->add('someProperty', 'checkbox')
            ->getForm();
    }

    public function testCheckBox()
    {
        $request = new Request('POST', '?', array(), array());
        $this->form->handle($request);
        $this->assertFalse($this->object->someProperty);

        $this->assertEquals(
            '<input type="checkbox" name="someProperty" />',
            $this->form->get('someProperty')->widget()
        );

        $request = new Request('POST', '?', array(), array('someProperty' => 'on'));
        $this->form->handle($request);
        $this->assertTrue($this->object->someProperty);

        $this->assertEquals(
            '<input type="checkbox" name="someProperty" checked="checked" />',
            $this->form->get('someProperty')->widget()
        );
    }

    public function testCheckBoxWithModelData()
    {
        $this->object->someProperty = true;
        $this->form->initialize();
        $this->assertEquals(
            '<input type="checkbox" name="someProperty" checked="checked" />',
            $this->form->get('someProperty')->widget()
        );

        $this->object->someProperty = false;
        $this->form->initialize();
        $this->assertEquals(
            '<input type="checkbox" name="someProperty" />',
            $this->form->get('someProperty')->widget()
        );
    }
}
