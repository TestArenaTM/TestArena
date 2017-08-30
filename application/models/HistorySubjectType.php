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
class Application_Model_HistorySubjectType extends Custom_Model_Dictionary_Abstract
{
  const TASK              = 1;
  const OTHER_TEST        = 2;
  const TEST_CASE         = 3;
  const EXPLORATORY_TEST  = 4;
  const DEFECT            = 5;
  const TASK_TEST         = 6;
  const AUTOMATIC_TEST    = 7;
  const CHECKLIST         = 8;
  
  protected $_names = array(
    self::TASK              => 'TASK',
    self::OTHER_TEST        => 'OTHER_TEST',
    self::TEST_CASE         => 'TEST_CASE',
    self::EXPLORATORY_TEST  => 'EXPLORATORY_TEST',
    self::DEFECT            => 'DEFECT',
    self::TASK_TEST         => 'TASK_TEST',
    self::AUTOMATIC_TEST    => 'AUTOMATIC_TEST',
    self::CHECKLIST         => 'CHECKLIST'
  );
}