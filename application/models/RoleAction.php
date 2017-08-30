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
  const PROJECT_ATTACHMENT                   = 1;//
  const PROJECT_STATUS                       = 2;//
  const REPORT_GENERATE                      = 3;//
  const RELEASE_MANAGEMENT                   = 4;//
  
  const VERSION_MANAGEMENT                   = 5;//
  const ENVIRONMENT_MANAGEMENT               = 6;//
  const TAG_MANAGEMENT                       = 40;//
  
  const TASK_ADD                             = 7;//
  const TASK_ASSIGN_ALL                      = 8;//
  const TASK_EDIT_ALL                        = 9;//
  const TASK_DELETE_ALL                      = 10;
  const TASK_CHANGE_STATUS_ALL               = 11;
  const TASK_EDIT_CREATED_BY_YOU             = 12;//
  const TASK_DELETE_CREATED_BY_YOU           = 13;
  const TASK_CHANGE_STATUS_CREATED_BY_YOU    = 14;
  const TASK_CHANGE_STATUS_ASSIGNED_TO_YOU   = 15;
  const TASK_EDIT_ASSIGNED_TO_YOU            = 16;//
  const TASK_DELETE_ASSIGNED_TO_YOU          = 17;
  
  const DEFECT_ADD                           = 18;//
  const DEFECT_ASSIGN_ALL                    = 19;//
  const DEFECT_EDIT_ALL                      = 20;//
  const DEFECT_DELETE_ALL                    = 21;
  const DEFECT_CHANGE_STATUS_ALL             = 22;//
  const DEFECT_EDIT_CREATED_BY_YOU           = 23;//
  const DEFECT_DELETE_CREATED_BY_YOU         = 24;
  const DEFECT_CHANGE_STATUS_CREATED_BY_YOU  = 25;//
  const DEFECT_DELETE_ASSIGNED_TO_YOU        = 26;
  const DEFECT_CHANGE_STATUS_ASSIGNED_TO_YOU = 27;//
  const DEFECT_EDIT_ASSIGNED_TO_YOU          = 28;//
  
  const TEST_ADD                             = 29;//
  const TEST_EDIT_ALL                        = 30;//
  const TEST_EDIT_CREATED_BY_YOU             = 31;//
  const TEST_DELETE_ALL                      = 32;//
  const TEST_DELETE_CREATED_BY_YOU           = 33;//
  const TASK_TEST_MODIFY_ALL                 = 34;//
  const TASK_TEST_MODIFY_ASSIGNED_TO_YOU     = 35;//
  const TASK_TEST_MODIFY_CREATED_BY_YOU      = 36;//
  const TASK_DEFECT_MODIFY_ALL               = 37;//
  const TASK_DEFECT_MODIFY_ASSIGNED_TO_YOU   = 38;//
  const TASK_DEFECT_MODIFY_CREATED_BY_YOU    = 39;//
  
  protected $_names = array(
    self::PROJECT_ATTACHMENT                   => 'PROJECT_ATTACHMENT',
    self::PROJECT_STATUS                       => 'PROJECT_STATUS',
    self::REPORT_GENERATE                      => 'REPORT_GENERATE',
    self::RELEASE_MANAGEMENT                   => 'RELEASE_MANAGEMENT',
    self::VERSION_MANAGEMENT                   => 'VERSION_MANAGEMENT',
    self::ENVIRONMENT_MANAGEMENT               => 'ENVIRONMENT_MANAGEMENT',
    self::TAG_MANAGEMENT                       => 'TAG_MANAGEMENT',
    self::TASK_ADD                             => 'TASK_ADD',
    self::TASK_ASSIGN_ALL                      => 'TASK_ASSIGN_ALL',
    self::TASK_EDIT_ALL                        => 'TASK_EDIT_ALL',
    self::TASK_DELETE_ALL                      => 'TASK_DELETE_ALL',
    self::TASK_CHANGE_STATUS_ALL               => 'TASK_CHANGE_STATUS_ALL',
    self::TASK_EDIT_CREATED_BY_YOU             => 'TASK_EDIT_CREATED_BY_YOU',
    self::TASK_DELETE_CREATED_BY_YOU           => 'TASK_DELETE_CREATED_BY_YOU',
    self::TASK_CHANGE_STATUS_CREATED_BY_YOU    => 'TASK_CHANGE_STATUS_CREATED_BY_YOU',
    self::TASK_CHANGE_STATUS_ASSIGNED_TO_YOU   => 'TASK_CHANGE_STATUS_ASSIGNED_TO_YOU',
    self::TASK_EDIT_ASSIGNED_TO_YOU            => 'TASK_EDIT_ASSIGNED_TO_YOU',
    self::TASK_DELETE_ASSIGNED_TO_YOU          => 'TASK_DELETE_ASSIGNED_TO_YOU',
    self::DEFECT_ADD                           => 'DEFECT_ADD',
    self::DEFECT_ASSIGN_ALL                    => 'DEFECT_ASSIGN_ALL',
    self::DEFECT_EDIT_ALL                      => 'DEFECT_EDIT_ALL',
    self::DEFECT_DELETE_ALL                    => 'DEFECT_DELETE_ALL',
    self::DEFECT_CHANGE_STATUS_ALL             => 'DEFECT_CHANGE_STATUS_ALL',
    self::DEFECT_EDIT_CREATED_BY_YOU           => 'DEFECT_EDIT_CREATED_BY_YOU',
    self::DEFECT_DELETE_CREATED_BY_YOU         => 'DEFECT_DELETE_CREATED_BY_YOU',
    self::DEFECT_CHANGE_STATUS_CREATED_BY_YOU  => 'DEFECT_CHANGE_STATUS_CREATED_BY_YOU',
    self::DEFECT_DELETE_ASSIGNED_TO_YOU        => 'DEFECT_DELETE_ASSIGNED_TO_YOU',
    self::DEFECT_CHANGE_STATUS_ASSIGNED_TO_YOU => 'DEFECT_CHANGE_STATUS_ASSIGNED_TO_YOU',
    self::DEFECT_EDIT_ASSIGNED_TO_YOU          => 'DEFECT_EDIT_ASSIGNED_TO_YOU',
    self::TEST_ADD                             => 'TEST_ADD',
    self::TEST_EDIT_ALL                        => 'TEST_EDIT_ALL',
    self::TEST_EDIT_CREATED_BY_YOU             => 'TEST_EDIT_CREATED_BY_YOU',
    self::TEST_DELETE_ALL                      => 'TEST_DELETE_ALL',
    self::TEST_DELETE_CREATED_BY_YOU           => 'TEST_DELETE_CREATED_BY_YOU',
    self::TASK_TEST_MODIFY_ALL                 => 'TASK_TEST_MODIFY_ALL',
    self::TASK_TEST_MODIFY_ASSIGNED_TO_YOU     => 'TASK_TEST_MODIFY_ASSIGNED_TO_YOU',
    self::TASK_TEST_MODIFY_CREATED_BY_YOU      => 'TASK_TEST_MODIFY_CREATED_BY_YOU',
    self::TASK_DEFECT_MODIFY_ALL               => 'TASK_DEFECT_MODIFY_ALL',
    self::TASK_DEFECT_MODIFY_ASSIGNED_TO_YOU   => 'TASK_DEFECT_MODIFY_ASSIGNED_TO_YOU',
    self::TASK_DEFECT_MODIFY_CREATED_BY_YOU    => 'TASK_DEFECT_MODIFY_CREATED_BY_YOU'
  );
  
  public function __construct($id = null)
  {
    if (null !== $id)
    {
      parent::__construct($id);
    }
  }
}