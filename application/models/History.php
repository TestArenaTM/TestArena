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
class Application_Model_History extends Custom_Model_Standard_Abstract
{
  protected $_map = array(
    'subject_id'    => 'subjectId',
    'subject_type'  => 'subjectType'
  );
  
  private $_id          = null;
  private $_user        = null;
  private $_date        = null;
  private $_subjectId   = null;
  private $_subjectType = null;
  private $_type        = null;
  private $_field1      = null;
  private $_field2      = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }

  public function getUser()
  {
    return $this->_user;
  }

  public function getDate()
  {
    return $this->_date;
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

  public function getType()
  {
    return $this->_type;
  }

  public function getTypeId()
  {
    return $this->_type->getId();
  }

  public function getField1()
  {
    return $this->_field1;
  }

  public function getField2()
  {
    return $this->_field2;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
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

  public function setDate($date)
  {
    $this->_date = $date;
  }

  public function setSubjectObject(Custom_Interface_HistorySubject $historySubject)
  {
    $this->setSubjectId($historySubject->getIdForHistory());

    switch (get_class($historySubject))
    {
      case 'Application_Model_Task':
        $this->setSubjectType(Application_Model_HistorySubjectType::TASK);
        break;

      case 'Application_Model_Test':
        $this->setSubjectType(Application_Model_HistorySubjectType::OTHER_TEST);
        break;
      
      case 'Application_Model_TestCase':
        $this->setSubjectType(Application_Model_HistorySubjectType::TEST_CASE);
        break;
      
      case 'Application_Model_ExploratoryTest':
        $this->setSubjectType(Application_Model_HistorySubjectType::EXPLORATORY_TEST);
        break;
      
      case 'Application_Model_AutomaticTest':
        $this->setSubjectType(Application_Model_HistorySubjectType::AUTOMATIC_TEST);
        break;
      
      case 'Application_Model_Checklist':
        $this->setSubjectType(Application_Model_HistorySubjectType::CHECKLIST);
        break;
      
      case 'Application_Model_Defect':
        $this->setSubjectType(Application_Model_HistorySubjectType::DEFECT);
        break;
    }
  }

  public function setSubjectId($subjectId)
  {
    $this->_subjectId = $subjectId;
  }

  public function setSubjectType($id)
  {
    $this->_subjectType = new Application_Model_HistorySubjectType($id);
  }

  public function setType($id)
  {
    $this->_type = new Application_Model_HistoryType($id);
  }

  public function setField1($field1)
  {
    $this->_field1 = $field1;
  }

  public function setField2($field2)
  {
    $this->_field2 = $field2;
  }
  // </editor-fold>
}