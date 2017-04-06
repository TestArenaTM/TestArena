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
class Application_Model_DefectStatus extends Custom_Model_Dictionary_Abstract
{
  const OPEN        = 1;
  const IN_PROGRESS = 2;
  const FINISHED    = 3;
  const RESOLVED    = 4;
  const INVALID     = 5;
  const SUCCESS     = 6;
  const FAIL        = 7;
  const REOPEN      = 8;
  
  protected $_names = array(
    self::OPEN        => 'OPEN',
    self::IN_PROGRESS => 'IN_PROGRESS',
    self::FINISHED    => 'FINISHED',
    self::RESOLVED    => 'RESOLVED',
    self::INVALID     => 'INVALID',
    self::SUCCESS     => 'SUCCESS',
    self::FAIL        => 'FAIL',
    self::REOPEN      => 'REOPEN'
  );
}