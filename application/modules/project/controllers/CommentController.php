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
class Project_CommentController extends Custom_Controller_Action_Application_Project_Abstract
{
  public function listBySubjectAjaxAction()
  {
    $request = $this->getRequest();
    $this->checkUserSession(true, true);

    $comment = new Application_Model_Comment();
    $comment->setSubjectId($request->getParam('subjectId'));
    $comment->setSubjectType($request->getParam('subjectType'));

    $commentMapper = new Project_Model_CommentMapper();
    echo json_encode($commentMapper->getBySubjectAjax($comment, $this->_user));
    exit;
  }
  
  public function listByTaskAjaxAction()
  {
    $request = $this->getRequest();
    $this->checkUserSession(true, true);

    $task = new Application_Model_Task();
    $task->setId($request->getParam('taskId'));

    $commentMapper = new Project_Model_CommentMapper();
    echo json_encode($commentMapper->getByTaskAjax($task, $this->_user, $this->view));
    exit;
  }
  
  public function addAjaxAction()
  {
    $request = $this->getRequest();
    $this->checkUserSession(true, true);
    $commentMapper = new Project_Model_CommentMapper();
    $comment = new Application_Model_Comment();
    $comment->setSubjectId($request->getParam('subjectId'));
    $comment->setSubjectType($request->getParam('subjectType'));
    $comment->setContent($request->getParam('content'));
    $comment->setUserObject($this->_user);
    echo json_encode($commentMapper->add($comment));
    exit;
  }  
  
  public function saveAjaxAction()
  {
    $request = $this->getRequest();
    $this->checkUserSession(true, true);
    $commentMapper = new Project_Model_CommentMapper();
    $comment = new Application_Model_Comment();
    $comment->setId($request->getParam('id'));
    $comment->setContent($request->getParam('content'));
    $comment->setUserObject($this->_user);
    echo json_encode($commentMapper->save($comment));
    exit;
  }  
  
  public function deleteAjaxAction()
  {
    $request = $this->getRequest();
    $this->checkUserSession(true, true);
    $commentMapper = new Project_Model_CommentMapper();
    $comment = new Application_Model_Comment();
    $comment->setId($request->getParam('id'));
    $comment->setUserObject($this->_user);
    echo json_encode($commentMapper->delete($comment));
    exit;
  }  
}