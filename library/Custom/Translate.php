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
/*
 	  print_r($translate->_('dom'));
	  echo $translate->plural('dom', '', 5);
 */
class Custom_Translate
{
  const TRANSLATE_ADAPTER = 'gettext';
  const FILE_EXTENSION    = '.mo';
  const SEPERATOR         = '__';
  private $_language;
  
  public function __construct()
  {
    $this->_language = Zend_Registry::get('Zend_Locale')->getLanguage();
  }
  
  private function _getTranslateObject($name = false)
  {
    if (!$name)
    {
      $name = (string)$name;
      $front = Zend_Controller_Front::getInstance();
      $request = $front->getRequest();
      $actionName = $request->getActionName();

      $buf = explode('-', $actionName);
      $n = count($buf);

      if ($buf[$n - 1] == 'process')
      {
        if ($n == 1)
        {
          $actionName = 'index';
        }
        else
        {
          unset($buf[$n - 1]);
          $actionName = implode('-', $buf);
        }
      }

      $translateName = 'translate_action_'.$actionName;
    }
    else
    {
      $translateName = 'translate_'.$name;
    }
    
    if (Zend_Registry::isRegistered($translateName))
    {
      $translate = Zend_Registry::get($translateName);
    }
    else
    {
      if (!$name)
      {
        $fileName = _LANGUAGE_PATH.DIRECTORY_SEPARATOR
          .$this->_language.DIRECTORY_SEPARATOR
          .$request->getModuleName().DIRECTORY_SEPARATOR
          .$request->getControllerName().DIRECTORY_SEPARATOR
          .$actionName.self::FILE_EXTENSION;
      }
      else
      {
        $fileName = _LANGUAGE_PATH.DIRECTORY_SEPARATOR
          .$this->_language.DIRECTORY_SEPARATOR
          .str_replace('_', DIRECTORY_SEPARATOR, $name).self::FILE_EXTENSION;
      }

      if (!file_exists($fileName))
      {
        throw new Exception('File translations of "'.$fileName.'" does not exist for the language "'.$this->_language.'".');
      }
      
      $translate = new Zend_Translate(array(
        'adapter' => self::TRANSLATE_ADAPTER,
        'content' => $fileName,
        'locale'  => $this->_language
      ));
      Zend_Registry::set($translateName, $translate);
    }

    return $translate;
  }

  public function translate($text, array $parameters = null, $name = false)
  {
    $text = (string)$text;
    $translate = $this->_getTranslateObject($name);

    if (!$translate->isTranslated($text))
    {
      throw new Exception('The selected phrase "'.$text.'" has not been translated to the language "'.$this->_language.'".');
    }
    
    $result = $translate->_($text);
    return $this->_setParameters($result, $parameters);    
  }
  
  public function pluralTranslate($text, $value, array $parameters = null, $name = false)
  {
    $text = (string)$text;
    $value = (int)$value;
    $parameters['value'] = $value;
    $translate = $this->_getTranslateObject($name);
    $result = $translate->plural($text, '', $value);
    
    if ($result == $text)
    {
      throw new Exception('The selected phrase "'.$text.'" has not been translated to the language "'.$this->_language.'".');
    }

    return $this->_setParameters($result, $parameters);
  }

  private function _setParameters($text, array $parameters = null)
  {
    if ($parameters !== null && mb_strpos($text, self::SEPERATOR, 0, 'UTF-8') !== false)
    {
      foreach ($parameters as $key => $value)
      {
        $text =  preg_replace('/'.self::SEPERATOR.$key.self::SEPERATOR.'/u', $value, $text);
      }
    }
    
    return $text;
  }
}