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
abstract class Custom_Model_Standard_Avatar_Abstract extends Custom_Model_Standard_Abstract implements Custom_Interface_BaseAvatarDirectoryName
{
  protected $_baseAvatarDirectoryName = null;
  
  const DEFAULT_AVATAR_NAME = 'default.jpg';
  const AVATAR_DIRECTORY      = 1;
  const AVATAR_DIRECTORY_MINI = 2;
  const AVATAR_DIRCTORY_TEMP  = 3;
  
  public function __construct(array $properties = null, $setExtraData = true)
  {
    $this->setBaseAvatarDirectoryName();
    parent::__construct($properties, $setExtraData);
  }
  
  public function getBaseAvatarDirectoryName()
  {
    return $this->_baseAvatarDirectoryName;
  }
  
  /**
   * Sprawdza czy dany użytkownik ma ustawionego własnego avatara. Jeśli wartość parametru $mini będzie ustawiona na true to sprawdzenie będzie dotyczyło miniaturki avatara.
   * @param bool $mini
   * @return bool 
   */
  public function avatarExists($mini = false)
  {
    $path = $this->getAvatarDirectory($mini).DIRECTORY_SEPARATOR . $this->getId();
    return file_exists($path.'.jpeg') || file_exists($path.'.png');
  }
  
  /**
   * Zwraca ścieżkę dostępu do katalogu według podanego typu. Są 3 typy:
   * - katalog główny z avatarami (AVATAR_DIRECTORY = 1);
   * - katalog z miniaturkami avatarów (AVATAR_DIRECTORY_MINI = 2);
   * - katalog tymczasowy dla avatarów (AVATAR_DIRCTORY_TEMP = 3).
   * @param int $type Możliwe wartości: AVATAR_DIRECTORY = 1, AVATAR_DIRECTORY_MINI = 2, AVATAR_DIRCTORY_TEMP = 3.
   * @return string 
   */
  public function getAvatarDirectory($type)
  {
    $path = _AVATAR_UPLOAD_DIR . DIRECTORY_SEPARATOR . $this->getBaseAvatarDirectoryName();
    switch ($type)
    {
      case self::AVATAR_DIRECTORY_MINI:
        $path .= DIRECTORY_SEPARATOR.'mini';
        break;
      
      case self::AVATAR_DIRCTORY_TEMP:
        $path .= DIRECTORY_SEPARATOR.'temp';
        break;
    }
    return $path;
  }
  
  /**
   * Zwraca pełną ścieżkę do pliku z avatarem. Jeśli wartość parametru $mini będzie ustawiona na true to zostanie zwrócona ścieżka do miniaturki avatara.
   * @param bool $mini
   * @return string 
   */
  public function getAvatar($mini = false, $defaultAvatarName = self::DEFAULT_AVATAR_NAME)
  {
    $path = $this->getAvatarDirectory($mini).DIRECTORY_SEPARATOR;
    $fileName = $defaultAvatarName;

    if (file_exists($path.$this->getId().'.jpeg'))
    {
      $fileName = $this->getId().'.jpeg';
    }
    
    if (file_exists($path.$this->getId().'.png'))
    {
      $fileName = $this->getId().'.png';
    }
    
    return $path.$fileName;
  }
  
  public function getAvatarUrl($mini = false, $defaultAvatarName = self::DEFAULT_AVATAR_NAME)
  {
    $path = $this->getAvatarDirectory($mini ? self::AVATAR_DIRECTORY_MINI : self::AVATAR_DIRECTORY).DIRECTORY_SEPARATOR;
    $fileName = $defaultAvatarName;

    if (file_exists($path.$this->getId().'.jpeg'))
    {
      $fileName = $this->getId().'.jpeg';
    }
    
    if (file_exists($path.$this->getId().'.png'))
    {
      $fileName = $this->getId().'.png';
    }
    
    $baseUrl = (isset(Zend_Registry::get('config')->frontBaseUrl)) ? Zend_Registry::get('config')->frontBaseUrl : Zend_Registry::get('config')->baseUrl ;
    //echo $fileName.' '.$baseUrl.'/upload/avatars/'.$this->getBaseAvatarDirectoryName().'/'.($mini ? 'mini/' : '').$fileName.'?'.time();die; 
    return $baseUrl.'/upload/avatars/'.$this->getBaseAvatarDirectoryName().'/'.($mini ? 'mini/' : '').$fileName.'?'.time();
  }
}