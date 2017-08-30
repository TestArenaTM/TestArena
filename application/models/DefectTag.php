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
class Application_Model_DefectTag extends Custom_Model_Standard_Abstract
{
  protected $_map = array(
    'defect_id' =>' defectId',
    'tag_id'    => 'tagId'
  );
  
  private $_defectId = null;
  private $_tagId = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getDefectId()
  {
    return $this->_defectId;
  }

  public function getTagId()
  {
    return $this->_tagId;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setDefectId($defectId)
  {
    $this->_defectId = $defectId;
    return $this;
  }

  public function setTagId($tagId)
  {
    $this->_tagId = $tagId;
    return $this;
  }
  // </editor-fold>
}