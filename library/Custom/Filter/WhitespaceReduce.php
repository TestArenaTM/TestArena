<?php

require_once 'Zend/Filter/Interface.php';

class Custom_Filter_WhitespaceReduce implements Zend_Filter_Interface
{
  public function filter($value)
  {
    return preg_replace('/[\s]+/', ' ', (string) $value);
  }
}

