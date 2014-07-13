<?php

namespace Modules\Form;

use Miny\Event\EventDispatcher;
use Miny\HTTP\Request;
use Miny\HTTP\Session;
use Modules\Validator\Rules\Blank;
use Modules\Validator\RuleSet;
use Modules\Validator\ValidatorService;

class FormServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FormService
     */
    private $formService;

    /**
     * @var ValidatorService
     */
    private $validator;

    public function setUp()
    {
        $tokenProvider = new CsrfTokenProvider(new Session(false));

        $this->validator = new ValidatorService(new EventDispatcher());
        $this->formService = new FormService($tokenProvider, $this->validator);
    }

    public function testNotSubmittedFormIsNotValid()
    {
        $object = new \stdClass();
        $form   = $this->formService->getFormBuilder($object)->getForm();

        $this->assertFalse($form->isValid());
    }

    public function testSubmitButtonClicked()
    {
        $object = new \stdClass();
        $form   = $this->formService->getFormBuilder($object)
            ->add('submit', 'submit')
            ->getForm();
        $form->setOption('csrf_protection', false);

        $form->handle(new Request('POST', '?', array(), array('submit' => '')));

        $this->assertTrue($form->get('submit')->clicked());
        $this->assertFalse(isset($object->submit));
    }

    public function testSubmitButtonCanChangeScenario()
    {
        $ruleSet = new RuleSet();
        $ruleSet->property('foo', Blank::fromArray(array('for' => 'test')));
        $this->validator->register('stdClass', $ruleSet);

        $object = new \stdClass();

        //'test' scenario: $object->foo should be Blank
        $form = $this->formService->getFormBuilder($object)
            ->add('submit', 'submit', array('validate_for' => 'test'))
            ->getForm();
        $form->setOption('csrf_protection', false);

        $request = new Request('POST', '?', array(), array('submit' => ''));

        $object->foo = 'foo';
        $form->handle($request);
        $this->assertFalse($form->isValid());

        $object->foo = '';
        $form->handle($request);
        $this->assertTrue($form->isValid());

        //'default' scenario: $object->foo is not checked
        $object->foo = 'whatever';
        $form->handle(new Request('POST', '?', array(), array('not_submit' => '')));

        $this->assertTrue($form->isValid());
        $this->assertTrue($form->get('submit')->clicked());
        $this->assertFalse(isset($object->submit));
    }

    public function testSubmitButtonNotClicked()
    {
        $request = new Request('POST', '?', array(), array('not_submit' => ''));

        $object = new \stdClass();
        $form   = $this->formService->getFormBuilder($object)
            ->add('submit', 'submit')
            ->getForm();

        $form->handle($request);

        $this->assertFalse($form->get('submit')->clicked());
        $this->assertFalse(isset($object->submit));
    }
}
