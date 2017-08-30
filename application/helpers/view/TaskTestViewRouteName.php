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
class Zend_View_Helper_TaskTestViewRouteName extends Zend_View_Helper_Abstract
{
  const ROUTE_OTHER_TEST_VIEW       = 'task_other_test_view';
  const ROUTE_TEST_CASE_VIEW        = 'task_test_case_view';
  const ROUTE_EXPLORATORY_TEST_VIEW = 'task_exploratory_test_view';
  const ROUTE_AUTOMATIC_TEST_VIEW   = 'task_automatic_test_view';
  const ROUTE_CHECKLIST_VIEW        = 'task_checklist_view';
  
  public function taskTestViewRouteName(Custom_Interface_Test $test)
  {
    switch ($test->getTypeId())
    {
      case Application_Model_TestType::OTHER_TEST:
        return self::ROUTE_OTHER_TEST_VIEW;
        
      case Application_Model_TestType::TEST_CASE:
        return self::ROUTE_TEST_CASE_VIEW;
      
      case Application_Model_TestType::EXPLORATORY_TEST:
        return self::ROUTE_EXPLORATORY_TEST_VIEW;
      
      case Application_Model_TestType::AUTOMATIC_TEST:
        return self::ROUTE_AUTOMATIC_TEST_VIEW;
      
      case Application_Model_TestType::CHECKLIST:
        return self::ROUTE_CHECKLIST_VIEW;
    }
    
    return null;
  }
}