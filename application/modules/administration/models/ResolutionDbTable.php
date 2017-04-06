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
class Administration_Model_ResolutionDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'resolution';
  
  public function getName()
  {
    return $this->_name;
  }
  
  public function getAllByProject($projectId)
  {
    $sql1 = '(SELECT COUNT(*) FROM task WHERE task.resolution_id = r.id)';
    $sql2 = '(SELECT COUNT(*) FROM task_test WHERE task_test.resolution_id = r.id)';
    
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name',
        'color',
        'description',
        'taskCount' => new Zend_Db_Expr($sql1),
        'testCount' => new Zend_Db_Expr($sql2)
      ))
      ->where('r.project_id = ?', $projectId)
      ->group('r.id')
      ->setIntegrityCheck(false);
    
    return $this->fetchAll($sql);
  }
  
  public function getForView($id)
  {
    $sql1 = '(SELECT COUNT(*) FROM task WHERE task.resolution_id = r.id)';
    $sql2 = '(SELECT COUNT(*) FROM task_test WHERE task_test.resolution_id = r.id)';
    
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name',
        'color',
        'description',
        'taskCount' => new Zend_Db_Expr($sql1),
        'testCount' => new Zend_Db_Expr($sql2)
      ))
      ->join(array('p' => 'project'), 'p.id = r.project_id', $this->_createAlias('project', array(
        'id',
        'status'
      )))
      ->where('r.id = ?', $id)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
  
  public function getForEdit($id)
  {
    $sql = $this->select()
      ->from(array('r' => $this->_name), array(
        'id',
        'name',
        'color',
        'description'
      ))
      ->join(array('p' => 'project'), 'p.id = r.project_id', $this->_createAlias('project', array(
        'id',
        'status'
      )))
      ->where('r.id = ?', $id)
      ->where('p.status != ?', Application_Model_ProjectStatus::FINISHED)
      ->setIntegrityCheck(false);
    
    return $this->fetchRow($sql);
  }
}