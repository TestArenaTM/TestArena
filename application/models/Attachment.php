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
class Application_Model_Attachment extends Custom_Model_Standard_Abstract
{
  protected $_map = array(
    'subject_id'  => 'subject_id',
    'create_date' => 'createDate'
  );
  
  private $_id          = null;
  private $_file        = null;
  private $_subjectId   = null;
  private $_type        = null;
  private $_createDate  = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }
  
  function getFile()
  {
    return $this->_file;
  }

  function getSubjectId()
  {
    return $this->_subjectId;
  }

  function getType()
  {
    return $this->_type;
  }

  function getTypeId()
  {
    return $this->_type->getId();
  }
  
  function getCreateDate()
  {
    return $this->_createDate;
  }
  // </editor-fold>

  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
    return $this;
  }

  public function setFile($propertyName, $propertyValue)
  {
    if (null === $this->_file)
    {
      $this->_file = new Application_Model_File(array($propertyName => $propertyValue));
    }
    else
    {
      $this->getFile()->setProperty($propertyName, $propertyValue);
    }
    
    return $this;
  }

  public function setFileObject(Application_Model_File $file)
  {
    $this->_file = $file;
    return $this;
  }

  function setSubjectId($subjectId)
  {
    $this->_subjectId = $subjectId;
    return $this;
  }
  
  public function setType($id)
  {
    $this->_type = new Application_Model_AttachmentType($id);
    return $this;
  }
  
  function setCreateDate($createDate)
  {
    $this->_createDate = $createDate;
    return $this;
  }
  // </editor-fold>  
}