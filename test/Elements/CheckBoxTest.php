<?php

namespace Modules\Form\Elements;

use Miny\Event\EventDispatcher;
use Miny\HTTP\Request;
use Miny\HTTP\Session;
use Modules\Form\CsrfTokenProvider;
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
        $tokenProvider     = new CsrfTokenProvider(new Session(false));
        $this->validator   = new ValidatorService(new EventDispatcher());
        $this->formService = new FormService($tokenProvider, $this->validator);

        $this->object = (object)['someProperty' => null];
        $this->form   = $this->formService
            ->getFormBuilder($this->object)
            ->add('someProperty', 'checkbox')
            ->getForm();
        $this->form->setOption('csrf_protection', false);
    }

    public function testCheckBox()
    {
        $request = new Request('POST', '?', [], []);
        $this->form->handle($request);
        $this->assertFalse($this->object->someProperty);

        $widget = $this->form->get('someProperty')->widget();
        $this->assertEquals(
            '<input type="checkbox" name="someProperty" id="someProperty" />',
            $widget
        );

        $request = new Request('POST', '?', [], ['someProperty' => 'on']);
        $this->form->handle($request);
        $this->assertTrue($this->object->someProperty);

        $this->assertEquals(
            '<input type="checkbox" name="someProperty" id="someProperty" checked="checked" />',
            $this->form->get('someProperty')->widget()
        );
    }

    public function testCheckBoxWithModelData()
    {
        $this->object->someProperty = true;
        $this->form->initialize();
        $this->assertEquals(
            '<input type="checkbox" name="someProperty" id="someProperty" checked="checked" />',
            $this->form->get('someProperty')->widget()
        );

        $this->object->someProperty = false;
        $this->form->initialize();
        $this->assertEquals(
            '<input type="checkbox" name="someProperty" id="someProperty" />',
            $this->form->get('someProperty')->widget()
        );
    }
}
