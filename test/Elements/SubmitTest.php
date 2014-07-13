<?php

namespace Modules\Form\Elements;

use Miny\Event\EventDispatcher;
use Miny\HTTP\Session;
use Modules\Form\CsrfTokenProvider;
use Modules\Form\Form;
use Modules\Form\FormService;
use Modules\Validator\ValidatorService;

class SubmitTest extends \PHPUnit_Framework_TestCase
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
        $this->validator = new ValidatorService(new EventDispatcher());
        $this->formService = new FormService($tokenProvider, $this->validator);

        $this->object = new \stdClass();
    }

    public function testSubmitButton()
    {
        $this->form = $this->formService
            ->getFormBuilder($this->object)
            ->add('submit', 'submit')
            ->getForm();
        $this->form->setOption('csrf_protection', false);

        $widget = $this->form->get('submit')->widget();
        $this->assertEquals(
            '<button name="submit" id="submit" type="submit">Submit</button>',
            $widget
        );
    }

    public function testSubmitImageButton()
    {
        $this->form = $this->formService
            ->getFormBuilder($this->object)
            ->add('submit', 'submit', array('label' => 'image_src', 'widget' => 'image'))
            ->getForm();
        $this->form->setOption('csrf_protection', false);

        $widget = $this->form->get('submit')->widget();
        $this->assertEquals(
            '<input name="submit" id="submit" type="image" src="image_src" />',
            $widget
        );
    }
}
