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
        $factory    = $app->getFactory();
        $parameters = $factory->getParameters();

        $fv = $factory->add('form_validator', __NAMESPACE__ . '\FormValidator');

        $factory->events->register('before_run', function() use ($factory, $parameters, $fv) {
            $session = $factory->session;
            if (!isset($session['token'])) {
                $session['token'] = sha1(mt_rand());
            }
            $parameters['form:csrf_token'] = $session['token'];
            $fv->addMethodCall('setCSRFToken', $session['token']);
        });

        $this->ifModule('Templating', function() use($factory) {
            $factory->add('form_extensions', __NAMESPACE__ . '\\FormExtension')
                    ->setArguments('&app');
            $factory->getBlueprint('template_environment')
                    ->addMethodCall('addExtension', '&form_extensions');
        });
    }
}
