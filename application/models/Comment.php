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
class Application_Model_Comment extends Custom_Model_Standard_Abstract
{
  protected $_map = array(
    'subject_id'    => 'subjectId',
    'subject_type'  => 'subjectType',
    'create_date'   => 'createDate',
    'modify_date'   => 'modifyDate',    
  );
  
  private $_id          = null;
  private $_subjectId   = null;
  private $_subjectType = null;
  private $_user        = null;
  private $_status      = null;
  private $_content     = null;
  private $_createDate  = null;
  private $_modifyDate  = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }
  
  public function getSubjectId()
  {
    return $this->_subjectId;
  }
  
  public function getSubjectType()
  {
    return $this->_subjectType;
  }
  
  public function getSubjectTypeId()
  {
    return $this->_subjectType->getId();
  }

  public function getUser()
  {
    return $this->_user;
  }
  
  public function getStatus()
  {
    return $this->_status;
  }
  
  public function getStatusId()
  {
    return $this->_status->getId();
  }
    
  public function getContent()
  {
    return $this->_content;
  }
  
  public function getCreateDate()
  {
    return $this->_createDate;
  }
  
  public function getModifyDate()
  {
    return $this->_modifyDate;
  }
  // </editor-fold>

  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
    return $this;
  }
  
  public function setSubjectId($subjectId)
  {
    $this->_subjectId = $subjectId;
    return $this;
  }

  public function setSubjectType($id)
  {
    $this->_subjectType = new Application_Model_CommentSubjectType($id);
    return $this;
  }

  public function setUser($propertyName, $propertyValue)
  {
    if (null === $this->_user)
    {
      $this->_user = new Application_Model_User(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getUser()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }

  public function setUserObject(Application_Model_User $user)
  {
    $this->_user = $user;
    return $this;
  }

  public function setStatus($id)
  {
    $this->_status = new Application_Model_CommentStatus($id);
    return $this;
  }
  
  public function setContent($content)
  {
    $this->_content = $content;
    return $this;
  }
  
  public function setCreateDate($date)
  {
    $this->_createDate = $date;
    return $this;
  }
  
  public function setModifyDate($date)
  {
    if ($date != '0000-00-00 00:00:00')
    {
      $this->_modifyDate = $date;
    }
    return $this;
  }
  // </editor-fold>  
}