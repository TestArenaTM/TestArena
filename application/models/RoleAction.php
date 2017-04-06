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
class Application_Model_RoleAction extends Custom_Model_Dictionary_Abstract
{
  const PROJECT_ATTACHMENT                 = 1;
  const PROJECT_STATUS                     = 2;
  const RELEASE_AND_PHASE_MANAGEMENT       = 3;
  
  const ENVIRONMENT_ADD                    = 4;
  const ENVIRONMENT_MODIFY                 = 5;
  
  const VERSION_ADD                        = 6;
  const VERSION_MODIFY                     = 7;
  
  const TEST_ADD                           = 8;
  const TEST_EDIT_CREATED_BY_YOU           = 9;
  const TEST_EDIT_ALL                      = 10;
  
  const TASK_ADD                           = 11;
  const TASK_EDIT_CREATED_BY_YOU           = 12;
  const TASK_EDIT_ASSIGNED_TO_YOU          = 13;
  const TASK_EDIT_ALL                      = 14;
  const TASK_CHANGE_STATUS_CREATED_BY_YOU  = 15;
  const TASK_CHANGE_STATUS_ASSIGNED_TO_YOU = 16;
  const TASK_CHANGE_STATUS_ALL             = 17;
  const TASK_ASSIGN_CREATED_BY_YOU         = 18;
  const TASK_ASSIGN_ASSIGNED_TO_YOU        = 19;
  const TASK_ASSIGN_ASSIGNED_BY_YOU        = 20;
  const TASK_ASSIGN_ALL                    = 21;
  const TASK_ATTACHMENT_CREATED_BY_YOU     = 22;
  const TASK_ATTACHMENT_ASSIGNED_TO_YOU    = 23;
  const TASK_ATTACHMENT_ALL                = 24;
  const TASK_TEST_MODIFY_CREATED_BY_YOU    = 25;
  const TASK_TEST_MODIFY_ASSIGNED_TO_YOU   = 26;
  const TASK_TEST_MODIFY_ALL               = 27;
  const TASK_DEFECT_MODIFY_CREATED_BY_YOU  = 28;
  const TASK_DEFECT_MODIFY_ASSIGNED_TO_YOU = 29;
  const TASK_DEFECT_MODIFY_ALL             = 30;
  
  const REPORT_GENERATE                    = 31;
  
  const DEFECT_ADD                         = 32;
  const DEFECT_EDIT_CREATED_BY_YOU         = 33;
  const DEFECT_EDIT_ALL                    = 34;
  
  
  protected $_names = array(
    self::PROJECT_ATTACHMENT                 => 'PROJECT_ATTACHMENT',
    self::PROJECT_STATUS                     => 'PROJECT_STATUS',
    self::RELEASE_AND_PHASE_MANAGEMENT       => 'RELEASE_AND_PHASE_MANAGEMENT',
    self::ENVIRONMENT_ADD                    => 'ENVIRONMENT_ADD',
    self::ENVIRONMENT_MODIFY                 => 'ENVIRONMENT_MODIFY',
    self::VERSION_ADD                        => 'VERSION_ADD',
    self::VERSION_MODIFY                     => 'VERSION_MODIFY',
    self::TEST_ADD                           => 'TEST_ADD',
    self::TEST_EDIT_CREATED_BY_YOU           => 'TEST_EDIT_CREATED_BY_YOU',
    self::TEST_EDIT_ALL                      => 'TEST_EDIT_ALL',
    self::TASK_ADD                           => 'TASK_ADD',
    self::TASK_EDIT_CREATED_BY_YOU           => 'TASK_EDIT_CREATED_BY_YOU',
    self::TASK_EDIT_ASSIGNED_TO_YOU          => 'TASK_EDIT_ASSIGNED_TO_YOU',
    self::TASK_EDIT_ALL                      => 'TASK_EDIT_ALL',
    self::TASK_CHANGE_STATUS_CREATED_BY_YOU  => 'TASK_CHANGE_STATUS_CREATED_BY_YOU',
    self::TASK_CHANGE_STATUS_ASSIGNED_TO_YOU => 'TASK_CHANGE_STATUS_ASSIGNED_TO_YOU',
    self::TASK_CHANGE_STATUS_ALL             => 'TASK_CHANGE_STATUS_ALL',
    self::TASK_ASSIGN_CREATED_BY_YOU         => 'TASK_ASSIGN_CREATED_BY_YOU',
    self::TASK_ASSIGN_ASSIGNED_TO_YOU        => 'TASK_ASSIGN_ASSIGNED_TO_YOU',
    self::TASK_ASSIGN_ASSIGNED_BY_YOU        => 'TASK_ASSIGN_ASSIGNED_BY_YOU',
    self::TASK_ASSIGN_ALL                    => 'TASK_ASSIGN_ALL',
    self::TASK_ATTACHMENT_CREATED_BY_YOU     => 'TASK_ATTACHMENT_CREATED_BY_YOU',
    self::TASK_ATTACHMENT_ASSIGNED_TO_YOU    => 'TASK_ATTACHMENT_ASSIGNED_TO_YOU',
    self::TASK_ATTACHMENT_ALL                => 'TASK_ATTACHMENT_ALL',
    self::TASK_TEST_MODIFY_CREATED_BY_YOU    => 'TASK_TEST_MODIFY_CREATED_BY_YOU',
    self::TASK_TEST_MODIFY_ASSIGNED_TO_YOU   => 'TASK_TEST_MODIFY_ASSIGNED_TO_YOU',
    self::TASK_TEST_MODIFY_ALL               => 'TASK_TEST_MODIFY_ALL',
    self::TASK_DEFECT_MODIFY_CREATED_BY_YOU  => 'TASK_DEFECT_MODIFY_CREATED_BY_YOU',
    self::TASK_DEFECT_MODIFY_ASSIGNED_TO_YOU => 'TASK_DEFECT_MODIFY_ASSIGNED_TO_YOU',
    self::TASK_DEFECT_MODIFY_ALL             => 'TASK_DEFECT_MODIFY_ALL',
    self::REPORT_GENERATE                    => 'REPORT_GENERATE',
    self::DEFECT_ADD                         => 'DEFECT_ADD',
    self::DEFECT_EDIT_CREATED_BY_YOU         => 'DEFECT_EDIT_CREATED_BY_YOU',
    self::DEFECT_EDIT_ALL                    => 'DEFECT_EDIT_ALL'
  );
  
  public function __construct($id = null)
  {
    if (null !== $id)
    {
      parent::__construct($id);
    }
  }
}