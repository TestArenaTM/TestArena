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
class Project_TagController extends Custom_Controller_Action_Application_Project_Abstract
{
  public function preDispatch()
  {
    parent::preDispatch();

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
  }
  
  private function _getFilterForm()
  {
    return new Project_Form_TagFilter(array('action' => $this->_projectUrl(array(), 'tag_list')));
  }
    
  public function indexAction()
  {
    $this->_setCurrentBackUrl('tag_list');
    $request = $this->_getRequestForFilter(Application_Model_FilterGroup::TAGS);
    $filterForm = $this->_getFilterForm();
    
    if ($filterForm->isValid($request->getParams()))
    {
      $this->_filterAction($filterForm->getValues(), 'tag');
      $tagMapper = new Project_Model_TagMapper();
      list($list, $paginator) = $tagMapper->getAll($request);
    }
    else
    {
      $list = array();
      $paginator = null;
    }
    
    $filter = $this->_user->getFilter(Application_Model_FilterGroup::TAGS);
    
    if ($filter !== null)
    {
      $filterForm->prepareSavedValues($filter->getData());
    }
    
    $this->_setTranslateTitle();
    $this->view->tags = $list;
    $this->view->paginator = $paginator;
    $this->view->request = $request;
    $this->view->filterForm = $filterForm;
    $this->view->accessTagManagement = $this->_checkAccess(Application_Model_RoleAction::TAG_MANAGEMENT);
  }
    
  public function viewAction()
  {
    $tag = $this->_getValidTagForView();
    $this->_setTranslateTitle(array('name' => $tag->getName()), 'headTitle');
    $this->view->tag = $tag;
    $this->view->accessTagManagement = $this->_checkAccess(Application_Model_RoleAction::TAG_MANAGEMENT);
    $this->view->backUrl = $this->_getBackUrl('tag_list', $this->_projectUrl(array(), 'tag_list'));
  }
  
  public function listAjaxAction()
  {
    $this->checkUserSession(true, true);
    
    $tagMapper = new Project_Model_TagMapper();
    $htmlSpecialCharsFilter = new Custom_Filter_HtmlSpecialCharsDefault();
    
    $result = $tagMapper->getAllAjax($this->getRequest());
    
    if (count($result) > 0)
    {
      foreach($result as $key => $item)
      {
        $result[$key]['name'] = $htmlSpecialCharsFilter->filter($item['name']);
      }
    }
    
    echo json_encode($result);
    exit;
  }
    
  private function _getAddTagForm()
  {
    return new Project_Form_AddTag(array(
      'action'    => $this->_projectUrl(array('projectId' => $this->_project->getId()), 'tag_add_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId()
    ));
  }
  
  public function addAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TAG_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getAddTagForm();
  }

  public function addProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TAG_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $request = $this->getRequest();

    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'tag_list');
    }
    
    $form = $this->_getAddTagForm();
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      return $this->render('add');
    }
    
    $tag = new Application_Model_Tag($form->getValues());
    $tag->setProjectObject($this->_project);
    $tagMapper = new Project_Model_TagMapper();
    $t = new Custom_Translate();

    if ($tagMapper->add($tag))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->projectRedirect($form->getBackUrl());
  }
  
  private function _getEditTagForm(Application_Model_Tag $tag)
  {
    $form = new Project_Form_EditTag(array(
      'action'    => $this->_projectUrl(array('id' => $tag->getId()), 'tag_edit_process'),
      'method'    => 'post',
      'projectId' => $this->_project->getId(),
      'id'        => $tag->getId()
    ));

    return $form->populate($tag->getExtraData('rowData'));
  }
  
  public function editAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TAG_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $tag = $this->_getValidTagForEdit();
    $this->_setTranslateTitle();
    $this->view->form = $this->_getEditTagForm($tag);
    $this->view->tag = $tag;
  }

  public function editProcessAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TAG_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $request = $this->getRequest();
    
    if (!$request->isPost())
    {
      return $this->projectRedirect(array(), 'tag_list');
    }
    
    $tag = $this->_getValidTagForEdit();
    $form = $this->_getEditTagForm($tag);
    
    if (!$form->isValid($request->getPost()))
    {
      $this->_setTranslateTitle();
      $this->view->form = $form;
      $this->view->tag = $tag;
      return $this->render('edit');
    }
    
    $t = new Custom_Translate();
    $tagMapper = new Project_Model_TagMapper();
    $tag->setProperties($form->getValues());
    $tag->setProjectObject($this->_project);

    if ($tagMapper->save($tag))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    $this->projectRedirect($form->getBackUrl());
  }
  
  public function deleteAction()
  {
    $this->_checkAccess(Application_Model_RoleAction::TAG_MANAGEMENT, true);
    
    $this->_project->checkFinished();
    $tag = $this->_getValidTag();
    $t = new Custom_Translate();
    $tagMapper = new Project_Model_TagMapper();
    
    if ($tagMapper->delete($tag))
    {
      $this->_messageBox->set($t->translate('statusSuccess'), Custom_MessageBox::TYPE_INFO);
    }
    else
    {
      $this->_messageBox->set($t->translate('statusError'), Custom_MessageBox::TYPE_ERROR);
    }
    
    return $this->projectRedirect(array(), 'tag_list');
  }
  
  private function _getValidTag()
  {
    $idValidator = new Application_Model_Validator_Id();
    
    if (!$idValidator->isValid($this->_getAllParams()))
    {
      throw new Custom_404Exception();
    }
    
    $tag = new Application_Model_Tag($idValidator->getFilteredValues());
    $tag->setProjectObject($this->_project);
    return $tag;
  }
  
  private function _getValidTagForEdit()
  {
    $tag = $this->_getValidTag();
    $tagMapper = new Project_Model_TagMapper();
    $rowData = $tagMapper->getForEdit($tag);
    
    if ($rowData === false)
    {
      throw new Custom_404Exception();
    }
    
    return $tag->setExtraData('rowData', $rowData);
  }
  
  private function _getValidTagForView()
  {
    $tag = $this->_getValidTag();
    $tagMapper = new Project_Model_TagMapper();
    
    if ($tagMapper->getForView($tag) === false)
    {
      throw new Custom_404Exception();
    }
    
    return $tag;
  }
}