<?php
/*
Copyright © 2014 TestArena 

This file is part of TestArena.

TestArena is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

The full text of the GPL is in the LICENSE file.
*/
class Application_Form_MultiPage extends Zend_Form
{
  public function prepareSubForm($spec, $actionRoute, $isLastSubForm = false, $showSubmitButton = true, $hasRemainingFormsLeft = true)
  {
    $subForm = null;
    
    if (is_string($spec))
    {
      $subForm = $this->{$spec};
    }
    elseif ($spec instanceof Zend_Form_SubForm || ($spec instanceof Zend_Form && $spec->isArray()))
    {
      $subForm = $spec;
    }
    else
    {
      throw new Exception('Invalid argument passed to '.__FUNCTION__.'()');
    }

    $this->setSubFormDecoratorsT($subForm)->addSubFormAction($subForm);

    if ($showSubmitButton)
    {
      $this->addSubmitButton($subForm, $isLastSubForm, $hasRemainingFormsLeft);
    }

    $subForm->setAction($actionRoute);

    return $subForm;
  }

  /**
   * Sets default form decorators to sub form unless custom decorators have
   * been set previously.
   * @param  Zend_Form_SubForm $subForm
   * @return Form_MultiPage
   */
  public function setSubFormDecoratorsT(Zend_Form_SubForm $subForm)
  {
    $subForm->setDecorators(array(
      'FormElements',
      array(
        'HtmlTag', array(
          'tag'   => 'div',
          'class' => 'zfForm')
        ),
      'Form'
    ));

    return $this;
  }

  /**
   * Adds a submit button to @subForm.
   * @param  Zend_Form_SubForm $subForm
   * @param bool? $isLastSubForm
   * @param string? $continueLabel
   * @param string? $finishLabel
   * @return Zend_Form
   */
  public function addSubmitButton(Zend_Form_SubForm $subForm, $isLastSubForm = false, $hasRemainingFormsLeft = true)
  {
    $t     = new Custom_Translate();
    $label = ($isLastSubForm || !$hasRemainingFormsLeft) ? $t->translate('Zapisz i zakończ', null, 'administration/project-wizard/index') : $t->translate('Zapisz i kontynuuj', null, 'administration/project-wizard/index');

    $subForm->addElement(new Zend_Form_Element_Submit(
      'submit',
      array(
        'label'    => $label,
        'required' => false,
        'ignore'   => true,
        'attribs' => array ('style' => 'margin-left: -40px'),
      )
    ));

    return $this;
  }

  /**
   * Adds action and method to sub form.
   * @param Zend_Form_SubForm $subForm
   * @return Zend_Form_SubForm
   */
  public function addSubFormAction(Zend_Form_SubForm $subForm)
  {
    $view   = Zend_Layout::getMvcInstance()->getView();
    $action = $view->url();

    $subForm->setAction($action)
            ->setAttrib('enctype', 'multipart/form-data')
            ->setMethod('post');
    return $this;
  }
}