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
class Application_Model_AttachmentType extends Custom_Model_Dictionary_Abstract
{
  const TEST_ATTACHMENT   = 1;
  const TASK_ATTACHMENT   = 2;
  const PROJECT_PLAN      = 3;
  const DOCUMENTATION     = 4;
  const DEFECT_ATTACHMENT = 5;
  
  protected $_names = array(
    self::TEST_ATTACHMENT   => 'TEST_ATTACHMENT',
    self::TASK_ATTACHMENT   => 'TASK_ATTACHMENT',
    self::PROJECT_PLAN      => 'PROJECT_PLAN',
    self::DOCUMENTATION     => 'DOCUMENTATION',
    self::DEFECT_ATTACHMENT => 'DEFECT_ATTACHMENT'
  );
}