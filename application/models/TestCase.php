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
class Application_Model_TestCase extends Application_Model_Test implements Custom_Interface_Test
{
  private $_presuppositions = null;
  private $_result          = null;  
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getPresuppositions()
  {
    return $this->_presuppositions;
  }

  public function getResult()
  {
    return $this->_result;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setPresuppositions($presuppositions)
  {
    $this->_presuppositions = $presuppositions;
    return $this;
  }

  public function setResult($result)
  {
    $this->_result = $result;
    return $this;
  }
  // </editor-fold>
  
  public function __toString()
  {
    return __CLASS__;
  }
}