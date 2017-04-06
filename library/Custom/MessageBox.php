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
class Custom_MessageBox
{
  const TYPE_INFO    = 'info';
  const TYPE_WARNING = 'warning';
  const TYPE_ERROR   = 'error';
  
  const STATUS_EMPTY   = 0;
  const STATUS_FILLED  = 1;
  const STATUS_PRESENT = 2;
  const STATUS_CLEAR   = 3;
  
  static private $_instance = null;
  private $_session = null;

  private function __construct() 
  {
    $this->_session = new Zend_Session_Namespace('MessageBox');
    if (!isset($this->_session->status) || $this->_session->status == self::STATUS_CLEAR)
    {
      $this->_session->status = self::STATUS_EMPTY;
    }
    elseif ($this->_session->status != self::STATUS_EMPTY)
    {
      $this->_session->status++;
    }
  }
  
  private function __clone() {}
  
  static public function getInstance()
  {
    if (null === self::$_instance)
    {
      self::$_instance = new Custom_MessageBox();
    }
    return self::$_instance;
  }
  
  public function present()
  {
    return $this->_session->status == self::STATUS_PRESENT;
  }

  public function set($text, $type = self::TYPE_INFO)
  {
    $this->_session->text = $text;
    $this->_session->type = $type;
    $this->_session->status = self::STATUS_FILLED;
  }
  
  public function getText()
  {
    return $this->_session->text;
  }
  
  public function getType()
  {
    return $this->_session->type;
  }
  
  public function __get($name)
  {
    $method = 'get'.ucfirst($name);
    if (method_exists($this, $method))
    {
      return $this->$method();
    }
    throw new Exception('Invalid '.get_class($this).' property.');
  }
}