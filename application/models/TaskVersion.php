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
class Application_Model_TaskVersion extends Custom_Model_Standard_Abstract
{
  protected $_map = array(
    'task_id' =>' taskId',
    'version_id'  => 'versionId'
  );
  
  private $_taskId = null;
  private $_versionId = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getTaskId()
  {
    return $this->_taskId;
  }

  public function getVersionId()
  {
    return $this->_versionId;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setTaskId($taskId)
  {
    $this->_taskId = $taskId;
    return $this;
  }

  public function setVersionId($versionId)
  {
    $this->_versionId = $versionId;
    return $this;
  }
  // </editor-fold>
}