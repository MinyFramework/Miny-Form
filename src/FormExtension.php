<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENCE file.
 */

namespace Modules\Form;

use Miny\Application\BaseApplication;
use Miny\Factory\ParameterContainer;
use Modules\Form\Elements\Image;
use Modules\Form\Elements\Submit;
use Modules\Templating\Compiler\Functions\MethodFunction;
use Modules\Templating\Extension;

class FormExtension extends Extension
{
    /**
     * @var Container
     */
    private $parameterContainer;

    public function __construct(ParameterContainer $parameterContainer)
    {
        parent::__construct();
        $this->parameterContainer = $parameterContainer;
    }

    public function getExtensionName()
    {
        return 'min/form';
    }

    public function getFunctions()
    {
        return array(
            new MethodFunction('button', 'buttonFunction', array('is_safe' => true)),
        );
    }

    public function buttonFunction($url, $method, array $params = array())
    {
        $config = $this->parameterContainer;
        if (isset($params['form'])) {
            $form_params = $params['form'];
            unset($params['form']);
        } else {
            $form_params = array();
        }
        $form_params['action'] = $url;
        $form_params['method'] = $method;

        $descriptor = new FormDescriptor;
        if (isset($config['form:csrf_token'])) {
            $descriptor->token = $config['form']['csrf_token'];
        }
        if (isset($params['src'])) {
            $descriptor->addField(new Image('button', $params['src'], $params));
        } else {
            $value = isset($params['value']) ? $params['value'] : NULL;
            $descriptor->addField(new Submit('button', $value, $params));
        }
        $form   = new FormBuilder($descriptor);
        $output = $form->begin($form_params);
        $output .= $form->render('button');
        $output .= $form->end();
        return $output;
    }
}
