<?php
/**
 * Copyright (c) 2012-2013 Soflomo.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2012-2013 Soflomo.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://ensemble.github.com
 */

namespace Soflomo\Textarea;

use Zend\ModuleManager\Feature;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;

use Soflomo\TextareaAdmin;
use Soflomo\Common\View\InjectTemplateListener;
use Soflomo\Common\Form\FormUtils;

class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\ConfigProviderInterface,
    Feature\ControllerProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__           => __DIR__ . '/src/Soflomo/Textarea',
                    __NAMESPACE__ . 'Admin' => __DIR__ . '/src/Soflomo/TextareaAdmin',
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'Soflomo\Textarea\Controller\TextController' => function($sl) {
                    $options = $sl->getServiceLocator()->get('Soflomo\Textarea\Options\ModuleOptions');
                    $em      = $sl->getServiceLocator()->get('Doctrine\ORM\EntityManager');
                    $repo    = $em->getRepository($options->getTextEntityClass());

                    $controller = new Controller\TextController($repo);
                    return $controller;
                },
                'Soflomo\TextareaAdmin\Controller\TextController' => function($sl) {
                    $em      = $sl->getServiceLocator()->get('Doctrine\ORM\EntityManager');
                    $form    = new TextareaAdmin\Form\Text;
                    $options = $sl->getServiceLocator()->get('Soflomo\Textarea\Options\ModuleOptions');

                    FormUtils::injectFilterPluginManager($form, $sl->getServiceLocator());

                    $controller = new TextareaAdmin\Controller\TextController($em, $form, $options);
                    return $controller;
                },
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Soflomo\Textarea\Options\ModuleOptions' => function($sl) {
                    $config = $sl->get('Config');
                    $config = $config['soflomo_textarea'];

                    $options = new Options\ModuleOptions($config);
                    return $options;
                },
            ),
        );
    }

    public function onBootstrap(EventInterface $event)
    {
        $app = $event->getApplication();
        $em  = $app->getEventManager()->getSharedManager();

        $this->attachTemplateListener($em);
    }

    protected function attachTemplateListener($em)
    {
        $listener    = new InjectTemplateListener;
        $controllers = array(
            'Soflomo\Textarea\Controller\TextController',
            'Soflomo\TextareaAdmin\Controller\TextController',
        );
        $em->attach($controllers, MvcEvent::EVENT_DISPATCH, array($listener, 'injectTemplate'), -80);
    }
}
