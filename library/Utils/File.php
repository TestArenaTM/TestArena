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
class Utils_File
{
  private $_url       = null;
  private $_filePath  = null;
  private $_userAgent = 'Mozilla/5.0 (Windows NT 6.0; WOW64; en-US; rv:11.0) Gecko/20100101 Firefox/11.0';
  
  private $_curlReturnTransfer    = true;
  private $_curlBinaryTransfer    = true;
  private $_curlHeader            = false;
  private $_curlConnectionTimeout = 5;
  private $_curlTimeout           = 10;
  private $_curlAutoreferer       = true;
  private $_curlFollowLocation    = true;
  private $_curlSslVerifyHost     = 2;
  private $_curlSslVerifyPeer     = false;
  
  public function __construct( array $options = null )
  {
    $this->_setOptions($options);
  }
  
  private function _setOptions( array $options = null )
  {
    if ( null === $options )
    {
      return $this;
    }
    
    foreach ($options as $key => $value)
    {
      $option = '_' . $key;
      $this->$option = $value;
    }
    return $this;
  }
  
  public function saveFromExternalByCurl()
  {
    try
    {
      $curl = curl_init( $this->_prepareUrl($this->_url) );
      $fp   = fopen( $this->_filePath, 'wb');

      curl_setopt($curl, CURLOPT_RETURNTRANSFER, $this->_curlReturnTransfer);
      curl_setopt($curl, CURLOPT_BINARYTRANSFER, $this->_curlBinaryTransfer);
      curl_setopt($curl, CURLOPT_FILE, $fp);

      curl_setopt($curl, CURLOPT_HEADER, $this->_curlHeader);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->_curlConnectionTimeout);
      curl_setopt($curl, CURLOPT_TIMEOUT, $this->_curlTimeout);

      curl_setopt($curl, CURLOPT_AUTOREFERER, $this->_curlAutoreferer);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $this->_curlFollowLocation);
      curl_setopt($curl, CURLOPT_USERAGENT, $this->_userAgent);

      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->_curlSslVerifyHost);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->_curlSslVerifyPeer);

      $curlResponse = curl_exec($curl);
      
      //var_dump(curl_getinfo($curl));
      //var_dump(curl_error($curl));
      
      curl_close($curl);
      fclose($fp);
      
      if ($curlResponse === false)
      {
        return false;
      }
     
      return true;
    }
    catch (Exception $e)
    {
      return false;
    }
  }
  
  private function _prepareUrl( $url )
  {
    $url = $this->_getFacebookSafeImageThumbnailRealUrl($url);
    
    return $url;
  }
  
  private function _getFacebookSafeImageThumbnailRealUrl( $url )
  {
    if ( strpos($url, 'external.ak.fbcdn.net/safe_image.php') !== false )
    {
      $urlQuery = parse_url(html_entity_decode($url), PHP_URL_QUERY);
      parse_str($urlQuery, $params);
      
      if ( !empty($params['url']) )
      {
        return $params['url'];
      }
    }
    
    return $url;
  }
  
}