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
class Project_Model_AttachmentDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'attachment';
  
  public function getName()
  {
    return $this->_name;
  }
  
  public function getForProject($projectId)
  {
    $sql = $this->select()
      ->from(array('a' => $this->_name), array(
        'id',
        'type',
        'create_date'
      ))
      ->join(array('f' => 'file'), 'f.id = a.file_id', $this->_createAlias('file', array(
        'id',
        'project'.self::TABLE_CONNECTOR.'id' => 'project_id',
        'name',
        'extension',
        'subpath'
      )))
      ->where('a.type IN (?)', array(
        Application_Model_AttachmentType::PROJECT_PLAN,
        Application_Model_AttachmentType::DOCUMENTATION
      ))
      ->where('a.subject_id = ?', $projectId)
      ->group('a.id')
      ->order('a.create_date')
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($sql);
  }
}