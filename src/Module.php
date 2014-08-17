<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <bugadani@gmail.com>
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
        return ['Validator'];
    }

    public function init(BaseApplication $app)
    {
        $container = $app->getContainer();

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
