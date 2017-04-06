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
class Application_Model_File extends Custom_Model_Standard_Abstract
{
  protected $_map = array(
    'is_temporary'  => 'isTemporary',
    'create_date'   => 'createDate',
    'remove_date'   => 'removeDate',    
  );
  
  private $_id          = null;
  private $_isTemporary = null;
  private $_name        = null;
  private $_extension   = null;
  private $_path        = null;
  private $_createDate  = null;
  private $_removeDate  = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getId()
  {
    return $this->_id;
  }
  
  function getIsTemporary()
  {
    return $this->_isTemporary;
  }
    
  public function getName()
  {
    return $this->_name;
  }
  
  public function getExtension()
  {
    return $this->_extension;
  }
  
  public function getCreateDate()
  {
    return $this->_createDate;
  }
  
  public function getRemoveDate()
  {
    return $this->_removeDate;
  }
  // </editor-fold>

  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setId($id)
  {
    $this->_id = (int)$id;
    return $this;
  }
  
  function setIsTemporary($isTemporary)
  {
    $this->_isTemporary = (int)$isTemporary;
    return $this;
  }
  
  public function setName($name)
  {
    $this->_name = $name;
    return $this;
  }
  
  public function setExtension($extension)
  {
    $this->_extension = $extension;
    return $this;
  }
  
  public function setPath($path)
  {
    $this->_path = $path;
    return $this;
  }

  public function setCreateDate($date)
  {
    $this->_createDate = $date;
    return $this;
  }
  
  public function setRemoveDate($date)
  {
    if ($date != '0000-00-00 00:00:00')
    {
      $this->_removeDate = $date;
    }
    return $this;
  }
  // </editor-fold>  
  
  public function setDates($removeDayOffset = false)
  {
    $now = time();
    $this->setCreateDate(date('Y-m-d H:i:s', $now));
    $removeDate = '0000-00-00 00:00:00';
    
    if ($removeDayOffset !== false)
    {
      $day = 60 * 60 * 24;
      $removeDate = date('Y-m-d H:i:s', $now + ($removeDayOffset * $day));
    }
    
    $this->setRemoveDate($removeDate);
  }
  
  public function setByFullPath($fullPath)
  {
    $info = pathinfo($fullPath);
    $this->setName($info['filename']);
    $this->setExtension($info['extension']);
    $this->setPath($info['dirname'].DIRECTORY_SEPARATOR);
  }
  
  public function getFullName()
  {
    return $this->getName().'.'.$this->getExtension();
  }
  
  public function getPath()
  {
    return $this->_path;
  }
  
  public function getFullPath($systemNameEncoding = false)
  {
    if ($systemNameEncoding)
    {
      $encoding = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'CP1250' : 'ISO-8859-2';
      return iconv('UTF-8', $encoding, $this->getPath().$this->getFullName());
    }
    
    return $this->getPath().$this->getFullName();
  }
  
  public function getSubPath(Application_Model_Project $project)
  {
    $fullPath = $this->getFullPath();
    $startIndex = strlen(_FILE_UPLOAD_DIR.DIRECTORY_SEPARATOR.$project->getId());
    return substr($fullPath, $startIndex, strlen($fullPath) - $startIndex);
  }
}