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
class Utils_Api_Jira
{
  static public function projectExists($key, $url, $userName = null, $password = null)
  {
    $result = self::getProject($key, $url, $userName, $password);
    return isset($result->key) && $result->key == $key;
  }
  
  static public function getProject($key, $url, $userName = null, $password = null)
  {
    return self::get('project/'.$key, $url, $userName, $password);
  }
  
  static public function getIssue($key, $url, $userName = null, $password = null, array $fields = array())
  {
    return self::get('issue/'.$key, $url, $userName, $password, array('fields' => $fields));
  }
  
  static public function getIssueSummary($key, $url, $userName = null, $password = null)
  {
    $result = self::get('issue/'.$key.'?fields=summary', $url, $userName, $password);
    return $result === false ? false :$result->fields->summary;
  }
  
  static public function getIssueStatus($key, $url, $userName = null, $password = null)
  {
    $result = self::get('issue/'.$key.'?fields=status', $url, $userName, $password);
    return $result === false ? false : $result->fields->status->name;
  }
  
  static public function getIssueSummaryAndStatus($key, $url, $userName = null, $password = null)
  {
    $result = self::get('issue/'.$key.'?fields=status,summary', $url, $userName, $password);
    
    if ($result === false)
    {
      return false;
    }
    
    return array(
      'summary' => $result->fields->summary,
      'status'  => $result->fields->status->name
    );
  }
  
  static public function get($name, $url, $userName = null, $password = null, array $parammeters = array())
  {
    $url = trim($url, '/').'/rest/api/latest/'.$name.self::_prepareParameters($parammeters);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    if ($userName !== null && $password !== null)
    {
      curl_setopt($ch, CURLOPT_USERPWD, $userName.':'.$password);
    }
    
    $result = json_decode(curl_exec($ch));
    curl_close($ch);

    if ($result === null)
    {
      throw new Exception('JIRA REST: Incorrect URL or incorrect authorization data.');
    }
    
    return isset($result->errorMessages) ? false : $result;
  }
  
  static private function _prepareParameters(array $parammeters = array())
  {
    $result = array();
    
    if (!empty($parammeters))
    {
      foreach ($parammeters as $name => $value)
      {
        if (is_array($value))
        {
          if (!empty($value))
          {
            $result[$name] = $name.'='.urlencode(implode(',', $value));
          }
        }
        else
        {
          $result[$name] = $name.'='.urlencode($value);
        }
      }
    }
    
    return empty($result) ? '' : '?'.  implode('&', $result);
  }
}