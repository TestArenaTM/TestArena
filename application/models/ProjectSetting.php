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
class Application_Model_ProjectSetting extends Custom_Model_Dictionary_Abstract
{
  const VERSIONS_ENABLED     = 1;
  const ENVIRONMENTS_ENABLED = 2;
  const RELEASES_ENABLED     = 3;
  const PHASES_ENABLED       = 4;
  const DEFECTS_ENABLED      = 5;
  const TESTS_ENABLED        = 6;
  
  protected $_names = array(
    self::VERSIONS_ENABLED     => 'VERSIONS_ENABLED',
    self::ENVIRONMENTS_ENABLED => 'ENVIRONMENTS_ENABLED',
    self::RELEASES_ENABLED     => 'RELEASES_ENABLED',
    self::PHASES_ENABLED       => 'PHASES_ENABLED',
    self::DEFECTS_ENABLED      => 'DEFECTS_ENABLED',
    self::TESTS_ENABLED        => 'TESTS_ENABLED',
  );
  
  public function __construct($id = null)
  {
    if (null !== $id)
    {
      parent::__construct($id);
    }
  }
}