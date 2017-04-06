<?php

require_once 'Zend/Filter/Interface.php';

class Custom_Filter_StringIntToArrayInt implements Zend_Filter_Interface
{
  private $_delimiter = '_';
  
  public function filter($value)
  {
    $values = explode($this->_delimiter, $value);
    $result = array();

    foreach ($values as $value)
    {
      if (is_numeric($value))
      {
        $result[] = $value;
      }
    }

    return $result;
  }
}

