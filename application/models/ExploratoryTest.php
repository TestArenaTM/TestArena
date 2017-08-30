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
class Application_Model_ExploratoryTest extends Application_Model_Test implements Custom_Interface_Test
{
  protected $_map = array(
    'ordinal_no'      => 'ordinalNo',
    'create_date'     => 'createDate',
    'family_id'       => 'familyId',
    'current_version' => 'currentVersion',
    'test_card'       => 'testCard',
    'create_date'     => 'createDate',
    'family_id'       => 'familyId',
    'current_version' => 'currentVersion'
  );
  
  private $_duration = null;
  private $_testCard = null;
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getDuration()
  {
    return $this->_duration;
  }
  
  public function getDurationForView()
  {
    if ($this->_duration == 0)
    {
      $t = new Custom_Translate();
      $this->_duration = $t->translate('Nieokreślona', null, 'general');
    }  
    return $this->_duration;
  }

  public function getTestCard()
  {
    return $this->_testCard;
  }
  // </editor-fold>
  
  // <editor-fold defaultstate="collapsed" desc="Setters">
  public function setDuration($duration)
  {
    $this->_duration = $duration;
    return $this;
  }

  public function setTestCard($testCard)
  {
    $this->_testCard = $testCard;
    return $this;
  }
  // </editor-fold>
}