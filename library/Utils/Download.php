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
class Utils_Download
{
  private $_fileName;
  private $_alliance;

  public function __construct($fileName, $alliance = null)
  {
    $this->_fileName = $fileName;
    $this->_alliance = $alliance === null ? $fileName : $alliance;

    //$userAgent = new Zend_Http_UserAgent();
    //if ($userAgent->getDevice()->getBrowser() == 'MSIE')
    //{
      $this->_alliance = iconv('UTF-8', 'cp1250', $this->_alliance);
    //}
  }

  public function save()
  {
    if (file_exists($this->_fileName))
    {
      set_time_limit(0); 
      header('Pragma: public');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Cache-Control: public');
      header('Content-Description: File Transfer');
      //header('Content-Type: application/force-download; charset=utf-8');
      header('Content-Type: application/octet-stream; charset=utf-8');
      header('Content-Disposition: attachment; filename="'.$this->_alliance.'"');
      header('Content-Transfer-Encoding: binary');
      header('Content-Length: '.filesize($this->_fileName)); 
      echo file_get_contents($this->_fileName);
      return true;
    }
    
    return false;
  }
}