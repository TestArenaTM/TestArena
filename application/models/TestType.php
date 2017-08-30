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
class Application_Model_TestType extends Custom_Model_Dictionary_Abstract
{
  const OTHER_TEST        = 1;
  const TEST_CASE         = 2;
  const EXPLORATORY_TEST  = 3;
  const AUTOMATIC_TEST    = 4;
  const CHECKLIST         = 5;
  
  protected $_names = array(
    self::OTHER_TEST        => 'OTHER_TEST',
    self::TEST_CASE         => 'TEST_CASE',
    self::EXPLORATORY_TEST  => 'EXPLORATORY_TEST',
    self::AUTOMATIC_TEST    => 'AUTOMATIC_TEST',
    self::CHECKLIST         => 'CHECKLIST'
  );
}