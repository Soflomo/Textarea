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

namespace Soflomo\TextareaAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Doctrine\ORM\EntityManager;
use Soflomo\TextareaAdmin\Form\Text as TextForm;
use Soflomo\Textarea\Options\ModuleOptions;

use Soflomo\Textarea\Exception\TextNotFoundException;

class TextController extends AbstractActionController
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var TextForm
     */
    protected $form;

    /**
     * @var ModuleOptions
     */
    protected $options;

    public function __construct(EntityManager $em, TextForm $form, ModuleOptions $options)
    {
        $this->em      = $em;
        $this->form    = $form;
        $this->options = $options;
    }

    public function editAction()
    {
        $text = $this->getText();
        $form = $this->getForm();
        $form->bind($text);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form->setData($data);

            if ($form->isValid()) {
                $this->getEntityManager()->flush();
            }
        }

        return new ViewModel(array(
            'form' => $form,
            'text' => $text
        ));
    }

    protected function getEntityManager()
    {
        return $this->em;
    }

    protected function getForm()
    {
        return $this->form;
    }

    protected function getOptions()
    {
        return $this->options;
    }

    protected function getText()
    {
        $page = $this->event->getRouteMatch()->getParam('page');
        $id   = $page->getModuleId();

        $class = $this->getOptions()->getTextEntityClass();
        $text  = $this->getEntityManager()->find($class, $id);

        if (null === $text) {
            throw new TextNotFoundException(sprintf(
                'Cannot find a text for page id "%s"', $id
            ));
        }

        return $text;
    }
}
