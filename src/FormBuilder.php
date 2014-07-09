<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENCE file.
 */

namespace Modules\Form;

use Minty\Compiler\TemplateFunction;
use Miny\Factory\ParameterContainer;
use Modules\Form\Elements\Image;
use Modules\Form\Elements\Submit;
use Minty\Extension;

class FormExtension extends Extension
{
    /**
     * @var ParameterContainer
     */
    private $parameterContainer;

    public function __construct(ParameterContainer $parameterContainer)
    {
        $this->parameterContainer = $parameterContainer;
    }

    public function getExtensionName()
    {
        return 'miny/form';
    }

    public function getFunctions()
    {
        return array(
            new TemplateFunction('button', array($this, 'buttonFunction'), array('is_safe' => 'html')),
        );
    }

    public function buttonFunction($url, $method, array $params = array())
    {
        if (isset($params['form'])) {
            $form_params = $params['form'];
            unset($params['form']);
        } else {
            $form_params = array();
        }
        $form_params['action'] = $url;
        $form_params['method'] = $method;

        $descriptor = new FormDescriptor;
        if (isset($this->parameterContainer['Form:csrf_token'])) {
            $descriptor->token = $this->parameterContainer['Form']['csrf_token'];
        }
        if (isset($params['src'])) {
            $descriptor->addField(new Image('button', $params['src'], $params));
        } else {
            $value = isset($params['value']) ? $params['value'] : null;
            $descriptor->addField(new Submit('button', $value, $params));
        }
        $form   = new Form($descriptor,);
        $output = $form->begin($form_params);
        $output .= $form->render('button');
        $output .= $form->end();

        return $output;
    }
}
