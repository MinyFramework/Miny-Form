<?php

namespace Modules\Form\Elements;

use Miny\HTTP\Session;
use Modules\Form\Form;
use Modules\Form\FormService;
use Modules\Validator\ValidatorService;

class ResetTest extends \PHPUnit_Framework_TestCase
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

        $this->object = new \stdClass();
    }

    public function testResetButton()
    {
        $this->form = $this->formService
            ->getFormBuilder($this->object)
            ->add('reset', 'reset')
            ->getForm();
        $this->form->setOption('csrf_protection', false);

        $widget = $this->form->get('reset')->widget();
        $this->assertEquals(
            '<button type="reset" name="reset" id="reset">Reset</button>',
            $widget
        );
    }
}
