<?php

namespace Modules\Form\Elements;

use Miny\HTTP\Request;
use Miny\HTTP\Session;
use Modules\Form\Form;
use Modules\Form\FormService;
use Modules\Validator\ValidatorService;

class TextTest extends \PHPUnit_Framework_TestCase
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
            ->add('someProperty', 'text')
            ->getForm();
    }

    public function testTextField()
    {
        $request = new Request('POST', '?', array(), array('someProperty' => 'foo'));
        $this->assertEquals(
            '<input type="text" name="someProperty" id="someProperty" />',
            $this->form->get('someProperty')->widget()
        );

        $this->form->handle($request);
        $this->assertEquals('foo', $this->object->someProperty);

        $this->assertEquals(
            '<input type="text" name="someProperty" id="someProperty" value="foo" />',
            $this->form->get('someProperty')->widget()
        );
    }

    public function testTextFieldWithDefaultData()
    {
        $this->object->someProperty = 'some value';
        $this->form->initialize();
        $this->assertEquals(
            '<input type="text" name="someProperty" id="someProperty" value="some value" />',
            $this->form->get('someProperty')->widget()
        );
    }
}
