<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\Form;

use Minty\Environment;
use Miny\Application\BaseApplication;
use Miny\Factory\Container;

class Module extends \Miny\Modules\Module
{

    public function getDependencies()
    {
        return array('Validator');
    }

    public function init(BaseApplication $app)
    {
        $container  = $app->getContainer();

        $module = $this;
        $container->addCallback(
            __NAMESPACE__ . '\\FormValidator',
            function (FormValidator $fv, Container $container) use ($module) {
                $session = $container->get('\\Miny\\HTTP\\Session');
                if (!isset($session['token'])) {
                    $session['token'] = sha1(mt_rand());
                }
                $module->setConfiguration('csrf_token', $session['token']);
                $fv->setCSRFToken($session['token']);
            }
        );

        $this->ifModule(
            'Templating',
            function () use ($container) {
                $container->addCallback(
                    '\\Minty\\Environment',
                    function (Environment $environment, Container $container) {
                        /** @var $extension FormExtension */
                        $extension = $container->get(__NAMESPACE__ . '\\FormExtension');
                        $environment->addExtension($extension);
                    }
                );
            }
        );
    }
}
