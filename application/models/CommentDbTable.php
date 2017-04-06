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
class Application_Model_CommentDbTable extends Custom_Model_DbTable_Abstract
{
  protected $_name = 'comment';

  public function getBySubjectAjax($subjectId, $subjectType)
  {
    $sql = $this->select()
      ->from(array('c' => $this->_name), array(
        'id',
        'userId' => 'user_id',
        'content',
        'createDate' => 'create_date',
        'modifyDate' => 'modify_date'
      ))
      ->joinInner(array('u' => 'user'), 'c.user_id = u.id', array(
        'userName' => new Zend_Db_Expr('CONCAT(firstname, " ", lastname)')
      ))
      ->where('c.subject_id = ?', $subjectId)
      ->where('c.subject_type = ?', $subjectType)
      ->where('c.status = ?', Application_Model_CommentStatus::ACTIVE)
      ->order('c.id')
      ->setIntegrityCheck(false);
    return $this->fetchAll($sql);
  }
}