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
class Project_Model_DefectTagMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_DefectTagDbTable';

  public function save(Application_Model_Defect $defect)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();    
    $valueCount = count($defect->getExtraData('tags'));
    
    $data = array();
    $values = implode(',', array_fill(0, count($defect->getExtraData('tags')), '(?, ?)'));

    foreach ($defect->getExtraData('tags') as $tagId)
    {
      $data[] = $defect->getId();
      $data[] = $tagId;
    }

    $db->delete(array('defect_id = ?' => $defect->getId()));

    if ($valueCount > 0)
    {
      $statement = $adapter->prepare('INSERT INTO '.$db->getName().' (defect_id, tag_id) VALUES '.$values);
      $statement->execute($data);
    }
  }
  
  public function saveGroup(array $defectTags)
  {
    $db = $this->_getDbTable();
    $adapter = $db->getAdapter();
    
    $data = array();
    $values = implode(',', array_fill(0, count($defectTags), '(?, ?)'));
    
    foreach ($defectTags as $defectTag)
    {
      $data[] = $defectTag->getDefectId();
      $data[] = $defectTag->getTagId();
    }
    
    $statement = $adapter->prepare('INSERT INTO '.$db->getName().' (defect_id, tag_id) VALUES '.$values);
    return $statement->execute($data);
  }

  public function deleteByDefect(Application_Model_Defect $defect)
  {
    $this->_getDbTable()->delete(array(
      'defect_id = ?' => $defect->getId()
    ));
  }

  public function deleteByDefectIds(array $defectIds)
  {
    $this->_getDbTable()->delete(array(
      'defect_id IN(?)' => $defectIds
    ));
  }
}