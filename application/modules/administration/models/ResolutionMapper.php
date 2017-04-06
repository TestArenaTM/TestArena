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
class Administration_Model_ResolutionMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Administration_Model_ResolutionDbTable';
  
  public function getAllByProject(Application_Model_Project $project)
  {
    $rows = $this->_getDbTable()->getAllByProject($project->getId());    
    $list = array();
    
    foreach ($rows as $row)
    {
      $list[] = new Application_Model_Resolution($row->toArray());
    }

    return $list;
  }
  
  public function add(Application_Model_Resolution $resolution)
  {
    $data = array(
      'project_id'  => $resolution->getProject()->getId(),
      'name'        => $resolution->getName(),
      'color'       => $resolution->getColor(),
      'description' => $resolution->getDescription()
    );
    
    try
    {
      $resolution->setid($this->_getDbTable()->insert($data));
      return true;
    } catch (Exception $e) {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function save(Application_Model_Resolution $resolution)
  {
    $data = array(
      'name'        => $resolution->getName(),
      'color'       => $resolution->getColor(),
      'description' => $resolution->getDescription()
    );
    
    try
    {
      $this->_getDbTable()->update($data, array('id = ?' => $resolution->getId()));
      return true;
    } catch (Exception $e) {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function delete(Application_Model_Resolution $resolution)
  {
    try
    {
      $this->_getDbTable()->delete(array('id = ?' => $resolution->getId()));
      return true;
    } catch (Exception $e) {
      Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
      return false;
    }
  }
  
  public function getForView(Application_Model_Resolution $resolution)
  {
    $row = $this->_getDbTable()->getForView($resolution->getId());
    
    if (empty($row))
    {
      return false;
    }
    
    return $resolution->setDbProperties($row->toArray());
  }
  
  public function getForEdit(Application_Model_Resolution $resolution)
  {
    $row = $this->_getDbTable()->getForEdit($resolution->getId());
    
    if (empty($row))
    {
      return false;
    }
    
    $row = $row->toArray();
    $resolution->setDbProperties($row);
    return $resolution->map($row);
  }
}