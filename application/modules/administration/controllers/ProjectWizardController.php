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
class Administration_ProjectWizardController extends Custom_Controller_Action_Administration_MultiPageAbstract
{
  const NAMESPACE_NAME = 'ProjectWizard';
  
  protected $_formClass     = 'Administration_Form_WizardAddProjectMultiForm';
  protected $_formClassName = 'WizardAddProjectMultiForm';
  
  protected $_subFormLabels = array(
    'step1' => 'addProjectStep1',
    'step2' => 'addProjectStepVerification'
  );
  
  protected $_viewScriptsForStep = array(
    'step1' => '_addProjectStep1.phtml',
    'step2' => '_addProjectStepVerification.phtml'
  );
  
  public function preDispatch()
  {
    parent::preDispatch();
    $this->checkUserSession(true);
    
    if (!$this->_user->getAdministrator())
    {
      throw new Custom_AccessDeniedException();
    }
    
    $this->_helper->layout->setLayout('administration');
  }
  
  public function init()
  {
    parent::init();
    
    $this->_namespace = self::NAMESPACE_NAME.$this->_user->getId();
  }

  public function indexAction()
  {
    parent::indexAction();
  }

  public function processAction()
  {
    parent::processAction();
  }
  
  protected function processValidForm()
  {
    $this->getSessionNamespace()->lock(); //lock against further registration submission
    
    $projectMapper = new Administration_Model_ProjectMapper();
    $project       = new Application_Model_Project($this->_prepareEntireFormData());
    $t             = new Custom_Translate();
    $defaultProjectData = Zend_Registry::get('config')->defaultProject;
    
    $project->setResolutions(array(
      array('name' => $t->translate('DEFAULT_SUCCESS_RESOLUTION'), 'color' => $defaultProjectData->successResolutionColor),
      array('name' => $t->translate('DEFAULT_FAIL_RESOLUTION'), 'color' => $defaultProjectData->failResolutionColor)
    ));
    
    if ($projectMapper->addFromWizard($project))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->_formCompleteRedirectUrl = $this->_url(array('id' => $project->getId()), 'admin_project_view');
  }
  
  private function _prepareEntireFormData()
  {
    return Utils_Array::flatten($this->getSessionNamespaceData(), 4);
  }
}