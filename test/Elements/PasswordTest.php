<?php

namespace Modules\Form\Elements;

use Miny\Event\EventDispatcher;
use Miny\HTTP\Request;
use Miny\HTTP\Session;
use Modules\Form\CsrfTokenProvider;
use Modules\Form\Form;
use Modules\Form\FormService;
use Modules\Validator\ValidatorService;

class PasswordTest extends \PHPUnit_Framework_TestCase
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
            ->add('someProperty', 'password')
            ->getForm();
        $this->form->setOption('csrf_protection', false);
    }

    public function testPasswordField()
    {
        $request = new Request('POST', '?', [], ['someProperty' => 'foo']);
        $this->assertEquals(
            '<input type="password" name="someProperty" id="someProperty" />',
            $this->form->get('someProperty')->widget()
        );

        $this->form->handle($request);
        $this->assertEquals('foo', $this->object->someProperty);

        $this->assertEquals(
            '<input type="password" name="someProperty" id="someProperty" />',
            $this->form->get('someProperty')->widget()
        );
    }

    public function testPasswordFieldShouldNotHaveDefaultData()
    {
        $this->object->someProperty = 'some value';
        $this->form->initialize();
        $widget = $this->form->get('someProperty')->widget();
        $this->assertEquals(
            '<input type="password" name="someProperty" id="someProperty" />',
            $widget
        );
    }
}
