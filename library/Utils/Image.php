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
class Utils_Image
{
  const MIME_GIF  = 'image/gif';
  const MIME_JPEG = 'image/jpeg';
  const MIME_PNG  = 'image/png';
  
  private $_fileName        = '';
  private $_dirName         = '';
  private $_baseName        = '';
  private $_extension       = '';
  private $_instance        = null;
  private $_width           = 0;
  private $_height          = 0;
  private $_mime            = 0;
  private $_maxFileSize     = null;
  
  public function __construct($fileName = null)
  {
    ini_set('memory_limit', '-1');
    
    if ($fileName !== null)
    {
      $this->load($fileName);
    }
  }
  
  public function setMaxFileSize($maxFileSize)
  {
    $this->_maxFileSize = $maxFileSize;
  }
  
  /**
   * Wczytuje plik z dysku
   * @param string $fileName Nazwa pliku wraz ze scieżką dostępu i rozszerzeniem.
   * @return Utils_Image
   * @throws Exception 
   */
  public function load($fileName)
  {
    if (!is_file($fileName) || !file_exists($fileName))
    {
      throw new Utils_Image_Exception(
        Utils_Image_Exception::MSG_FILE_NOT_EXISTS,
        Utils_Image_Exception::FILE_NOT_EXISTS);
    }

    $info = @getimagesize($fileName);

    switch ($info['mime'])
    {
      case self::MIME_GIF:
        $this->_instance = imagecreatefromgif($fileName);
        break;
      
      case self::MIME_JPEG:
        $this->_instance = imagecreatefromjpeg($fileName);
        break;
      
      case self::MIME_PNG:
        $this->_instance = imagecreatefrompng($fileName);
        imagealphablending($this->_instance, false);
        imagesavealpha($this->_instance, true);
        break;
      
      default:
        throw new Utils_Image_Exception(
          Utils_Image_Exception::MSG_UNSUPPORTED_IMAGE_TYPE,
          Utils_Image_Exception::UNSUPPORTED_IMAGE_TYPE);
    }
    
    if ($this->_maxFileSize !== null && filesize($fileName) > $this->_maxFileSize)
    {
      throw new Utils_Image_Exception(
        Utils_Image_Exception::MSG_IMAGE_IS_TOO_BIG,
        Utils_Image_Exception::IMAGE_IS_TOO_BIG);
    }
    
    $pInfo = pathinfo($fileName);
    $this->_width = $info[0];
    $this->_height = $info[1];
    $this->_mime = $info['mime'];
    $this->_fileName = $pInfo['filename'];
    $this->_dirName = $pInfo['dirname'];
    $this->_extension = image_type_to_extension($info[2], false);
    $this->_baseName = $this->_fileName.'.'.$this->_extension;
    return $this;
  }
  
  public function show()
  {
    switch ($this->_mime)
    {
      case self::MIME_GIF:
        header('Content-type: image/gif');
        imagegif($this->_instance);
        break;
      
      case self::MIME_JPEG:
        header('Content-type: image/jpeg');
        imagejpeg($this->_instance);
        break;
      
      case self::MIME_PNG:
        header('Content-type: image/png');
        imagealphablending($this->_instance, false);
        imagesavealpha($this->_instance, true);
        imagepng($this->_instance);
        break;
      
      default:
        throw new Utils_Image_Exception(
          Utils_Image_Exception::MSG_UNSUPPORTED_IMAGE_TYPE,
          Utils_Image_Exception::UNSUPPORTED_IMAGE_TYPE);
    }
  }
  
  /**
   * Zapisuje wczytany obrazek do pliku.
   * @param string $fileName Nazwa pliku wraz ze scieżką dostępu i rozszerzeniem.
   */
  public function saveAs($fileName)
  {
    $pInfo = pathinfo($fileName);

    switch ($pInfo['extension'])
    {
      case 'gif':
        $this->saveAsGif($pInfo['filename'], $pInfo['dirname']);
        break;
      
      case 'jpeg':
      case 'jpe':
      case 'jpg':
      case 'jif':
      case 'jfif':
      case 'jfi':
        $this->saveAsJpg($pInfo['filename'], $pInfo['dirname'], $pInfo['extension']);
        break;
      
      case 'png':
        $this->saveAsPng($pInfo['filename'], $pInfo['dirname']);
        break;
      
      default:
        throw new Utils_Image_Exception(
          Utils_Image_Exception::MSG_UNSUPPORTED_IMAGE_TYPE,
          Utils_Image_Exception::UNSUPPORTED_IMAGE_TYPE);
        break;
    }
  }
  
  /**
   * Zapisuje wczytany obrazek do pliku.
   * @param mixed $fileName Wyłącznie nazwa pliku bez rozszerzenia.
   * @param mixed $dirName Ścieżka dostępu do katalogu.
   */
  public function save($fileName = false, $dirName = false)
  {
    switch ($this->_mime)
    {
      case self::MIME_GIF:
        $this->saveAsGif($fileName, $dirName);
        break;
      
      case self::MIME_JPEG:
        $this->saveAsJpg($fileName, $dirName, $this->_extension);
        break;
      
      case self::MIME_PNG:
        $this->saveAsPng($fileName, $dirName);
        break;
    }
  }
  
  private function _getFileNameWithDir($fileName = false, $dirName = false, $extension = false)
  {
    if (false === $fileName)
    {
      $fileName = $this->_fileName;
    }
    
    if (false === $dirName)
    {
      $dirName = $this->_dirName;
    }
    
    if (false === $extension)
    {
      $extension = $this->_extension;
    }
    
    return $dirName.DIRECTORY_SEPARATOR.$fileName.'.'.$extension;
  }
  
  /**
   * Zapisuje wczytany obrazek jako plik typu GIF.
   * @param mixed $fileName Wyłącznie nazwa pliku bez rozszerzenia.
   * @param mixed $dirName Ścieżka dostępu do katalogu.
   */
  public function saveAsGif($fileName = false, $dirName = false)
  {
    //todo wyjatek na false
    imagegif($this->_instance, $this->_getFileNameWithDir($fileName, $dirName, 'gif'));
  }
  
  /**
   * Zapisuje wczytany obrazek jako plik typu JPG.
   * @param mixed $fileName Wyłącznie nazwa pliku bez rozszerzenia.
   * @param mixed $dirName Ścieżka dostępu do katalogu.
   */
  public function saveAsJpg($fileName = false, $dirName = false, $extension = false)
  {
    //todo wyjatek na false
    imagejpeg($this->_instance, $this->_getFileNameWithDir($fileName, $dirName, $extension === false ? 'jpg' : $extension), 100);
  }
  
  /**
   * Zapisuje wczytany obrazek jako plik typu PNG.
   * @param mixed $fileName Wyłącznie nazwa pliku bez rozszerzenia.
   * @param mixed $dirName Ścieżka dostępu do katalogu.
   */
  public function saveAsPng($fileName = false, $dirName = false)
  {
    //todo wyjatek na false
    imagealphablending($this->_instance, false);
    imagesavealpha($this->_instance, true);
    imagepng($this->_instance, $this->_getFileNameWithDir($fileName, $dirName, 'png'));
  }
  
  /**
   * Dopasowuje obrazek do podanych wielkości w obu kierunkach (pomiejszanie i powiększanie).
   * Jeśli nie zostanie podany któryś nowy wymiar to obrazek zostanie dopasowany tylko do podanego wymiaru.
   * Nie działa z plikami typu GIF.
   * @param int $width
   * @param int $height
   * @return Utils_Image
   * @throws Exception 
   */
  function fit($width, $height)
  {
    if (self::MIME_GIF == $this->_mime)
    {
      throw new Utils_Image_Exception(
        Utils_Image_Exception::MSG_FIT_NOT_SUPPORT_GIF,
        Utils_Image_Exception::FIT_NOT_SUPPORT_GIF);
    }
    
    $width = (int)$width;
    $height = (int)$height;
    if ($width > 0 && $height > 0)
    {
      $xscale = $this->_width / $width;
      $yscale = $this->_height / $height;

      if ($yscale > $xscale)
      {
        $width = round($this->_width * (1 / $yscale));
        $height = round($this->_height * (1 / $yscale));
      }
      else
      {
        $width = round($this->_width * (1 / $xscale));
        $height = round($this->_height * (1 / $xscale));
      }
    }    
    elseif ($width > 0)
    {
      $scale = $this->_width / $width;
      $width = round($this->_width * (1 / $scale));
      $height = round($this->_height * (1 / $scale));
    }
    elseif ($height > 0)
    {
      $scale = $this->_height / $height;
      $width = round($this->_width * (1 / $scale));
      $height = round($this->_height * (1 / $scale));
    }
    else
    {
      return $this;
    }
        
    $instance = imagecreatetruecolor($width, $height);
    if ($this->_mime == self::MIME_PNG)
    {
      imagealphablending($instance, false);
      imagesavealpha($instance, true);
    }
    imagecopyresampled($instance, $this->_instance, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);
    $this->_instance = $instance;
    $this->_width = $width;
    $this->_height = $height;
    
    return $this;
  }
  
  /**
   * Dopasowuje obrazek do podanych wielkości w obu kierunkach (pomiejszanie i powiększanie)
   * tylko wtedy kiedy przekracza podane wielkości.
   * Jeśli nie zostanie podany któryś nowy wymiar to obrazek zostanie dopasowany tylko do podanego wymiaru.
   * Nie działa z plikami typu GIF.
   * @param int $width
   * @param int $height
   * @return Utils_Image
   * @throws Exception 
   */
  function fitIn($width, $height)
  {
    if (self::MIME_GIF == $this->_mime)
    {
      throw new Utils_Image_Exception(
        Utils_Image_Exception::MSG_FIT_NOT_SUPPORT_GIF,
        Utils_Image_Exception::FIT_NOT_SUPPORT_GIF);
    }
    
    $width = (int)$width;
    $height = (int)$height;
    if ($width > 0 && $height > 0)
    {
      $xscale = $this->_width / $width;
      $yscale = $this->_height / $height;
      
      if ($xscale <= 1 && $yscale <= 1)
      {
        return $this;
      }
      
      if ($yscale > $xscale)
      {
        $width = round($this->_width * (1 / $yscale));
        $height = round($this->_height * (1 / $yscale));
      }
      else
      {
        $width = round($this->_width * (1 / $xscale));
        $height = round($this->_height * (1 / $xscale));
      }
    }    
    elseif ($width > 0)
    {
      $scale = $this->_width / $width;
      if ($scale <= 1)
      {
        return $this;
      }
      $width = round($this->_width * (1 / $scale));
      $height = round($this->_height * (1 / $scale));
    }
    elseif ($height > 0)
    {
      $scale = $this->_height / $height;
      if ($scale <= 1)
      {
        return $this;
      }
      $width = round($this->_width * (1 / $scale));
      $height = round($this->_height * (1 / $scale));
    }
    else
    {
      return $this;
    }

    $instance = imagecreatetruecolor($width, $height);
    if ($this->_mime == self::MIME_PNG)
    {
      imagealphablending($instance, false);
      imagesavealpha($instance, true);
    }
    imagecopyresampled($instance, $this->_instance, 0, 0, 0, 0, $width, $height, $this->_width, $this->_height);
    $this->_instance = $instance;
    $this->_width = $width;
    $this->_height = $height;
    
    return $this;
  }
  
  public function fitAndCrop($width, $height)
  {
    if ($this->_width > $this->_height)
    {
      $this->fit(false, $height);
    }
    else
    {
      $this->fit($width, false);
    }

    $x = (int)(($this->_width - $width) / 2);
    $y = (int)(($this->_height - $height) / 2);
    $this->crop($x, $y, $width, $height);
    
    return $this;
  }
  
  public function crop($x, $y, $width, $height)
  {
    $instance = imagecreatetruecolor($width, $height);
    if ($this->_mime == self::MIME_PNG)
    {
      imagealphablending($instance, false);
      imagesavealpha($instance, true);
    }
    imagecopyresized($instance, $this->_instance, 0, 0, $x, $y, $width, $height, $width, $height);
    $this->_instance = $instance;
    $this->_width = $width;
    $this->_height = $height;
    
    return $this;
  }
  
  public function __get($name)
  {
    $method = 'get'.ucfirst($name);
    if (!method_exists($this, $method))
    {
      throw new Exception('Class property does not exist.');
    }
    
    return $this->$method();
  }
  
  // <editor-fold defaultstate="collapsed" desc="Getters">
  public function getWidth()
  {
    return $this->_width;
  }
  
  public function getHeight()
  {
    return $this->_height;
  }
  
  public function getMime()
  {
    return $this->_mime;
  }
  
  public function getFileName()
  {
    return $this->_fileName;
  }
  
  public function getBaseName()
  {
    return $this->_baseName;
  }
  
  public function getDirName()
  {
    return $this->_dirName;
  }
  
  public function getExtension()
  {
    return $this->_extension;
  }
  
  public function getInstance()
  {
    return $this->_instance;
  }
  // </editor-fold>
}