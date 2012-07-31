<?php

/**
 * This file is part of the Miny framework.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version accepted by the author in accordance with section
 * 14 of the GNU General Public License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   Miny/Modules/Form
 * @copyright 2012 Dániel Buga <daniel@bugadani.hu>
 * @license   http://www.gnu.org/licenses/gpl.txt
 *            GNU General Public License
 * @version   1.0
 */

namespace Modules\Form;

use Miny\Application\Application;
use Modules\Form\Elements\Button;
use Modules\Form\Elements\Image;

class Module extends \Miny\Application\Module
{
    public function init(Application $app, $token = NULL)
    {
        $fv = $app->add('form_validator', __NAMESPACE__ . '\FormValidator');
        if (!is_null($token)) {
            $fv->addMethodCall('setCSRFToken', $token);
        }
        $session = $app->session;

        $app->getDescriptor('view')->addMethodCall('addMethod', 'button',
                function($url, $method, array $params = array()) use($session) {

                    if (isset($params['form'])) {
                        $form_params = $params['form'];
                        unset($params['form']);
                    } else {
                        $form_params = array();
                    }
                    $form_params['action'] = $url;
                    $form_params['method'] = $method;
                    $descriptor = new FormDescriptor;

                    $descriptor->token = $session['token'];

                    if (isset($params['src'])) {
                        $descriptor->addField(new Image('button', $params['src'], $params));
                    } else {
                        $value = isset($params['value']) ? $params['value'] : NULL;
                        $descriptor->addField(new Button('button', $value, $params));
                    }
                    $form = new FormBuilder($descriptor);
                    $output = $form->begin($form_params);
                    $output .= $form->render('button');
                    $output.= $form->end();
                    return $output;
                });
    }

}