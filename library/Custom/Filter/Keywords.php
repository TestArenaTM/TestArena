<?php

require_once 'Zend/Filter/Interface.php';

class Custom_Filter_Keywords extends Zend_Filter_StringTrim
{
  public function filter($value)
  {
    return $this->_initFilter($value);
  }
  
  private function _initFilter(&$value)
  {
    $keywords = explode(',', $value);
    $value = '';
    
    foreach ($keywords as $keyword)
    {
      $keyword = $this->_trim($keyword);
      
      if ($keyword != '')
      {
        if ($value != '')
        {
          $value .= ',';
        }

        $value .= $keyword;
      }
    }
    
    return $this->_trim($value);
  }
  
  private function _trim($value)
  {
    if (null === $this->_charList)
    {
      return $this->_unicodeTrim((string) $value);
    }
    else
    {
      return $this->_unicodeTrim((string) $value, $this->_charList);
    }
  }
}

