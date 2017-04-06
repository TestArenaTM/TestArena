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
class Custom_CookieLaw
{
  static private $_show = false;
  
  static public function init()
  {
    $cookieName = self::_createCookieName();
    
    if (!array_key_exists($cookieName, $_COOKIE) || $_COOKIE[$cookieName] == 1)
    {
      setcookie($cookieName, 1, time() + 31536000, '/', Zend_Registry::get('config')->cookie_domain);
      self::$_show = true;
    }
  }
  
  static public function hide()
  {
    $cookieName = self::_createCookieName();
    setcookie($cookieName, 0, time() + 31536000, '/', Zend_Registry::get('config')->cookie_domain);
  }
  
  static public function show()
  {
    return self::$_show;
  }
  
  static private function _createCookieName()
  {
    return 'cookie_law'.str_replace('.', '_', Zend_Registry::get('config')->cookie_domain);
  }
}