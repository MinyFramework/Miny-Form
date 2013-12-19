<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use Miny\Application\BaseApplication;

class Module extends \Miny\Application\Module
{

    public function getDependencies()
    {
        return array('Validator');
    }

    public function init(BaseApplication $app, $token = NULL)
    {
        $fv = $app->add('form_validator', __NAMESPACE__ . '\FormValidator');
        if (!is_null($token)) {
            $app['form:csrf_token'] = $token;
            $fv->addMethodCall('setCSRFToken', $token);
        }

        $this->ifModule('Templating', function() use($app) {
            $app->add('form_extensions', __NAMESPACE__ . '\\FormExtension')
                    ->setArguments('&app');
            $app->getBlueprint('template_environment')
                    ->addMethodCall('addExtension', '&form_extensions');
        });
    }
}
