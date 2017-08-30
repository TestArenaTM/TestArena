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
class Project_ReleaseCloneController extends Custom_Controller_Action_Application_Project_MultiPage_Abstract
{
  const NAMESPACE_NAME = 'ReleaseClone';
  
  protected $_formClass     = 'Project_Form_ReleaseCloneMultiForm';
  protected $_formClassName = 'ReleaseCloneMultiForm';
  
  protected $_subFormLabels = array(
    'step1' => 'releaseCloneStep1',
    'step2' => 'releaseCloneStepVerification'
  );
  
  protected $_viewScriptsForStep = array(
    'step1' => '_releaseCloneStep1.phtml',
    'step2' => '_releaseCloneStepVerification.phtml'
  );
  
  public function preDispatch()
  {
    parent::preDispatch();
    $this->checkUserSession(true);
    
    if (!$this->getRequest()->isXmlHttpRequest())
    {
      if ($this->_project === null)
      {
        throw new Custom_404Exception();
      }
      
      if (!in_array($this->getRequest()->getActionName(), array('index', 'view')))
      {
        $this->_project->checkFinished();
        $this->_project->checkSuspended();
      }
    }
    
    Zend_Registry::set('projectId', $this->_project->getId());
  }
  
  public function init()
  {
    parent::init();
    
    $release = $this->_getValidReleaseForClone();
    Zend_Registry::set('release', $release);
    
    $this->_namespace = self::NAMESPACE_NAME.$this->_user->getId().'_'.Zend_Registry::get('release')->getId() ;
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
    $this->getSessionNamespace()->lock();
    
    $release    = Zend_Registry::get('release');
    $formValues = $this->_prepareEntireFormData();
    
    $t = new Custom_Translate();
    
    $releaseMapper = new Project_Model_ReleaseMapper();
    $release->setProperties($formValues);
    $release->setExtraData('authUser', $this->_user);
    
    if ($releaseMapper->cloneRelease($release))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
      $returnUrl = $this->_projectUrl(array('id' => $release->getId()), 'release_view');
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
      $returnUrl = $this->_projectUrl(array(), 'release_list');
    }
    
    $this->_formCompleteRedirectUrl = $returnUrl;
  }
  
  protected function _prepareEntireFormData()
  {
    $data = Utils_Array::flatten($this->getSessionNamespaceData(), 4);
    
    if (count($data) > 0)
    {
      foreach ($data as $key => $value)
      {
        if (preg_match('/^task_[0-9]+/', $key))
        {
          if ($value !== null)
          {
            $tmp = explode('_', $key);
            $data['tasks'][] = $tmp[1];
          }

          unset($data[$key]);
        }
      }
    }
    
    return $data;
  }
  
  private function _getValidRelease()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    $release = new Application_Model_Release($idValidator->getFilteredValues());
    $release->setProjectObject($this->_project);
    return $release;
  }
  
  private function _getValidReleaseForClone()
  {
    $release = $this->_getValidRelease();
    $releaseMapper = new Project_Model_ReleaseMapper();
    $rowData = $releaseMapper->getForEdit($release);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    $rowData['startDate'] = $rowData['endDate'];
    unset($rowData['endDate']);
    $release->setEndDate(null);
    
    return $release->setExtraData('rowData', $rowData);
  }
}