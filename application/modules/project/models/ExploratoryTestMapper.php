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
class Project_Model_ExploratoryTestMapper extends Custom_Model_Mapper_Abstract
{
  protected $_dbTableClass = 'Project_Model_ExploratoryTestDbTable';
  
  public function add(Application_Model_ExploratoryTest $exploratoryTest)
  {
    $db = $this->_getDbTable();
    
    $data = array(
      'test_id'   => $exploratoryTest->getId(),
      'duration'  => $exploratoryTest->getDuration(),
      'test_card' => $exploratoryTest->getTestCard(),
    );
    
    return $db->insert($data);
  }
  
  public function edit(Application_Model_ExploratoryTest $exploratoryTest)
  {
    $db = $this->_getDbTable();
    
    $data = array(
      'duration'  => $exploratoryTest->getDuration(),
      'test_card' => $exploratoryTest->getTestCard(),
    );
    
    return $db->update($data, array('test_id = ?' => $exploratoryTest->getId())) == 1;
  }
}