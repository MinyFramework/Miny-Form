<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use Miny\Application\BaseApplication;

class Module extends \Miny\Modules\Module
{

    public function getDependencies()
    {
        return array('Validator');
    }

    public function init(BaseApplication $app)
    {
        $fv = $app->add('form_validator', __NAMESPACE__ . '\FormValidator');

        $app->events->register('before_run', function() use ($app, $fv) {
            $session = $app->session;
            if (!isset($session['token'])) {
                $session['token'] = sha1(mt_rand());
            }
            $app['form:csrf_token'] = $session['token'];
            $fv->addMethodCall('setCSRFToken', $session['token']);
        });

        $this->ifModule('Templating', function() use($app) {
            $app->add('form_extensions', __NAMESPACE__ . '\\FormExtension')
                    ->setArguments('&app');
            $app->getBlueprint('template_environment')
                    ->addMethodCall('addExtension', '&form_extensions');
        });
    }
}
