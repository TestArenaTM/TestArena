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
class Application_Model_HistoryType extends Custom_Model_Dictionary_Abstract
{
  const CHANGE_OTHER_TEST       = 1;
  const CHANGE_TEST_CASE        = 2;
  const CHANGE_EXPLORATORY_TEST = 3;
  const CREATE_TASK             = 4;
  const CHANGE_TASK             = 5;
  const CHANGE_TASK_STATUS      = 6;
  const ADD_TEST_TO_TASK        = 7;
  const DELETE_TEST_FROM_TASK   = 8;
  const CREATE_DEFECT           = 9;
  const CHANGE_DEFECT           = 10;
  const CHANGE_DEFECT_STATUS    = 11;
  const ADD_DEFECT_TO_TASK      = 12;
  const DELETE_DEFECT_FROM_TASK = 13;
  
  protected $_names = array(
    self::CHANGE_OTHER_TEST       => 'CHANGE_OTHER_TEST',
    self::CHANGE_TEST_CASE        => 'CHANGE_TEST_CASE',
    self::CHANGE_EXPLORATORY_TEST => 'CHANGE_EXPLORATORY_TEST',
    self::CREATE_TASK             => 'CREATE_TASK',
    self::CHANGE_TASK             => 'CHANGE_TASK',
    self::CHANGE_TASK_STATUS      => 'CHANGE_TASK_STATUS',
    self::ADD_TEST_TO_TASK        => 'ADD_TEST_TO_TASK',
    self::DELETE_TEST_FROM_TASK   => 'DELETE_TEST_FROM_TASK',
    self::CREATE_DEFECT           => 'CREATE_DEFECT',
    self::CHANGE_DEFECT           => 'CHANGE_DEFECT',
    self::CHANGE_DEFECT_STATUS    => 'CHANGE_DEFECT_STATUS',
    self::ADD_DEFECT_TO_TASK      => 'ADD_DEFECT_TO_TASK',
    self::DELETE_DEFECT_FROM_TASK => 'DELETE_DEFECT_FROM_TASK'
  );
}