<?php

require_once 'Zend/Filter/Interface.php';

class Custom_Filter_Url implements Zend_Filter_Interface
{
  public function filter($value)
  {
    if(strlen($value) > 0 && !preg_match('/^(http[s]?|ftp):\/\//', $value))
    {
      return 'http://'.$value;
    }

    return $value;
  }
}

