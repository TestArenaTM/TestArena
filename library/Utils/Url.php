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
class Utils_Url
{
  static public function getCurrent()
  {
    return 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
  }
  
  static public function getReferer()
  {
    return empty($_SERVER['HTTP_REFERER']) ? self::getCurrent() : $_SERVER['HTTP_REFERER'];
  }  
  
  /*static private $_instance = null;
  private $_baseUrl     = null;
  private $_currentUrl  = null;
  private $_prevUrl     = null;
  private $_backUrl     = null;
  
  private function __construct() {}
  private function __clone() {}
  
  static public function getInstance()
  {
    if (null === self::$_instance)
    {
      self::$_instance = new Utils_Url();
    }
    return self::$_instance;
  }
  
  public function init($baseUrl, $isAjax)
  {
    $this->_baseUrl = $baseUrl;    

    if (!$isAjax)
    {
      if ($this->_currentUrl !== null)
      {
        $this->_prevUrl = $this->_currentUrl;
      }
      $this->_currentUrl = $this->_baseUrl.$_SERVER['REQUEST_URI'];
    }
  }
  
  public function setBackUrl()
  {
    $this->_backUrl = $this->_baseUrl.$_SERVER['REQUEST_URI'];
  }
  
  public function getCurrentUrl()
  {
    return $this->_currentUrl;
  }
  
  public function getprevUrl()
  {
    return $this->_prevUrl;
  }
  
  public function getBackUrl()
  {
    return $this->_backUrl;
  }
  
  public function currentUrlEqualPrevUrl()
  {
    return $this->_currentUrl == $this->_prevUrl;
  }
  */
}

/*
class Utils_BackUrl
{
  static private $_instance = null;
  private $_history         = array();
  private $_baseUrl         = '';
  private $_clearHistory    = true;
  private $_isSet           = false;
  private $_lastIndex       = -1;
  
  private function __construct() {}
  private function __clone() {}
  
  static public function getInstance()
  {
    if (null === self::$_instance)
    {
      self::$_instance = new Utils_BackUrl();
    }
    return self::$_instance;
  }
  
  public function setBaseUrl($baseUrl)
  {
    $this->_baseUrl = $baseUrl;    
  }
  
  public function init()
  {
    $this->_clearHistory = false;
    $this->_isSet = false;
  }
  
  public function clear()
  {
    if ($this->_clearHistory)
    {
      $this->_history = array();
    }
  }

  private function _getLastKey($default = null)
  {
    return $this->_lastIndex >= 0 ? $this->_history[$this->_lastIndex]['key'] : $default;
  }
  
  private function _updateLastUri()
  {
    $this->_history[$this->_lastIndex]['uri'] = $_SERVER['REQUEST_URI'];
  }
  
  private function _pushUri($key)
  {
    array_push($this->_history, array(
      'key' => $key,
      'url' => $_SERVER['REQUEST_URI']));
    $this->_lastIndex++;
  }
  
  private function _popUri()
  {
    $result = array_pop($this->_history);
    if ($result !== null)
    {
      $this->_lastIndex--;
    }
    return $result;
  }
  
  public function set($uniqueKey)
  {
    if ($this->_getLastKey() == $uniqueKey)
    {
      $this->_updateLastUri();
    }
    else
    {
      $this->_pushUri($uniqueKey);
    }
    $this->_clearHistory = false;
    $this->_isSet = true;
  }
  
  private function _getCurrentUri($default = null)
  {
    if ($this->_lastIndex >= 0)
    {
      $index = $this->_lastIndex;
      if ($this->_isSet)
      {
        $index--;
      }
      return $this->_history[$index]['uri'];
    }
    return $default;
  }
  
  public function get()
  {
    $url = $this->_baseUrl;
    if (($backUrl = $this->_getCurrentUri()) !== null)
    {
      $url .= $backUrl;
    }
    $this->_clearHistory = false;
    return $url;
  }
}*/