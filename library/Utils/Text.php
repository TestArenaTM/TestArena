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
class Utils_Text
{
  static function createCamelHumps($name, $delimiter = '_')
  {
    $result = "";
    $members = explode($delimiter, $name);

    foreach ($members as $member)
    {
      if ($result != "")
      {
        $member = ucfirst($member);
      }
      $result .= $member;
    }
    
    return $result;
  }
    
  static public function unicodeTrim($text)
  {
    return preg_replace(array('/^[\s]+/u', '/[\s]+$/u'), array('', ''), $text);
  }
  
  //temporary fix for htmlspecialchars problem
  static public function htmlspecialcharsInputDecode(&$value)
  {
    $value = htmlspecialchars_decode($value, ENT_QUOTES);
  }
  
  static public function generateToken()
  {
    return md5(uniqid(rand(), true));
  }
  
  static public function checkMagicQuotes()
  {
    //set_magic_quotes_runtime(FALSE);

    if (get_magic_quotes_gpc())
    {
      /*
      All these global variables are slash-encoded by default,
      because    magic_quotes_gpc is set by default!
      (And magic_quotes_gpc affects more than just $_GET, $_POST, and $_COOKIE)
      */
      $_SERVER          = self::stripslashesArray($_SERVER);
      $_GET             = self::stripslashesArray($_GET);
      $_POST            = self::stripslashesArray($_POST);
      $_COOKIE          = self::stripslashesArray($_COOKIE);
      $_ENV             = self::stripslashesArray($_ENV);
      $_REQUEST         = self::stripslashesArray($_REQUEST);
      $HTTP_SERVER_VARS = self::stripslashesArray($HTTP_SERVER_VARS);
      $HTTP_GET_VARS    = self::stripslashesArray($HTTP_GET_VARS);
      $HTTP_POST_VARS   = self::stripslashesArray($HTTP_POST_VARS);
      $HTTP_COOKIE_VARS = self::stripslashesArray($HTTP_COOKIE_VARS);
      $HTTP_ENV_VARS    = self::stripslashesArray($HTTP_ENV_VARS);
      if (isset ($_SESSION))
      { #These are unconfirmed (?)
        $_SESSION          = self::stripslashesArray($_SESSION, '');
        $HTTP_SESSION_VARS = self::stripslashesArray($HTTP_SESSION_VARS, '');
      }
    }
  }

  static function stripslashesArray($data)
  {
    if (is_array($data))
    {
      foreach ($data as $key => $value)
      {
        $data[$key] = self::stripslashesArray($value);
      }
      return $data;
    }
    else
    {
      return stripslashes($data);
    }
  }
  
  static function getFirstSentence($text)
  {
    $text = strip_tags($text);
    $pos = mb_strpos($text, '.', 0, 'UTF-8');
    $length = $pos === false ? mb_strlen($text, 'UTF-8') : $pos + 1;
    return mb_substr($text, 0, $length, 'UTF-8');
  }
}