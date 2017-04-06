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
class Application_Model_FileDbTable extends Custom_Model_DbTable_Criteria_Abstract
{
  protected $_name = 'file';
  
  public function getById($id)
  {
    $sql = $this->select()
      ->from(array('f' => $this->_name), array(
        'id',
        'name',
        'extension',
        'path',
        'create_date',
        'remove_date'
      ))
      ->where('f.id = ?', $id);
    
    return $this->fetchRow($sql);
  }
  
  public function getIdByFullPath($name, $extension, $path)
  {
    $sql = $this->select()
      ->from(array('f' => $this->_name), array(
        'id'
      ))
      ->where('f.name = ?', $name)
      ->where('f.extension = ?', $extension)
      ->where('f.path = ?', $path);
    
    return $this->getAdapter()->fetchOne($sql);
  }
  
  public function getByPath($path)
  {
    $sql = $this->select()
      ->from(array('f' => $this->_name), array(
        'id',
        'name',
        'extension',
        'path',
        'attachmentCount' => new Zend_Db_Expr('COUNT(a.id)')
      ))
      ->joinLeft(array('a' => 'attachment'), 'a.file_id = f.id', array())
      ->where('f.path LIKE "'.addcslashes(addcslashes($path, '\\'), '\\').'%"')
      ->group('f.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
  
  public function attachmentExistsByFullPath($name, $extension, $path)
  {
    $sql = $this->select()
      ->from(array('f' => $this->_name), array(
        'id'
      ))
      ->join(array('a' => 'attachment'), 'a.file_id = f.id', array())
      ->where('f.name = ?', $name)
      ->where('f.extension = ?', $extension)
      ->where('f.path = ?', $path)
      ->setIntegrityCheck(false);
    
    return $this->getAdapter()->fetchOne($sql);
  }
  
  public function getAllPathBySubPath($path)
  {
    $sql = $this->select()
      ->from(array('f' => $this->_name), array(
        'path'
      ))
      ->where('f.path LIKE "'.addcslashes(addcslashes($path, '\\'), '\\').'%"')
      ->group('f.path');

    return $this->fetchAll($sql);
  }
  
  public function getForBrowserByPath($path)
  {
    $sql = $this->select()
      ->from(array('f' => $this->_name), array(
        'id',
        'name',
        'extension'
      ))
      ->where('f.path = ?', $path)
      ->order('f.name');

    return $this->fetchAll($sql);
  }
  
  public function getForBrowserById($id)
  {
    $sql = $this->select()
      ->from(array('f' => $this->_name), array(
        'id',
        'name',
        'extension',
        'path',
        'attachmentCount' => new Zend_Db_Expr('COUNT(a.id)')
      ))
      ->joinLeft(array('a' => 'attachment'), 'a.file_id = f.id', array())
      ->where('f.id = ?', $id)
      ->limit(1)
      ->setIntegrityCheck(false);

    return $this->fetchRow($sql);
  }
  
  public function getForBrowserByIds(array $ids)
  {
    $sql = $this->select()
      ->from(array('f' => $this->_name), array(
        'id',
        'name',
        'extension',
        'path',
        'attachmentCount' => new Zend_Db_Expr('COUNT(a.id)')
      ))
      ->joinLeft(array('a' => 'attachment'), 'a.file_id = f.id', array())
      ->where('f.id IN(?)', $ids)
      ->group('f.id')
      ->setIntegrityCheck(false);

    return $this->fetchAll($sql);
  }
}