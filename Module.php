<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use Miny\Application\Application;
use Modules\Form\Elements\Image;
use Modules\Form\Elements\Submit;

class Module extends \Miny\Application\Module
{
    public function getDependencies()
    {
        return array('Validator');
    }

    public function init(Application $app, $token = NULL)
    {
        $fv = $app->add('form_validator', __NAMESPACE__ . '\FormValidator');
        if (!is_null($token)) {
            $fv->addMethodCall('setCSRFToken', $token);
        }

        $app->getBlueprint('view')->addMethodCall('addMethod', 'button',
                function($url, $method, array $params = array()) use($app, $token) {

                    if (isset($params['form'])) {
                        $form_params = $params['form'];
                        unset($params['form']);
                    } else {
                        $form_params = array();
                    }
                    $form_params['action'] = $url;
                    $form_params['method'] = $method;

                    $descriptor = new FormDescriptor;
                    $descriptor->token = $app->getValue($token);

                    if (isset($params['src'])) {
                        $descriptor->addField(new Image('button', $params['src'], $params));
                    } else {
                        $value = isset($params['value']) ? $params['value'] : NULL;
                        $descriptor->addField(new Submit('button', $value, $params));
                    }
                    $form = new FormBuilder($descriptor);
                    $output = $form->begin($form_params);
                    $output .= $form->render('button');
                    $output .= $form->end();
                    return $output;
                });
    }

}