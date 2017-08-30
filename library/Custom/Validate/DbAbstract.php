<?php
abstract class Custom_Validate_DbAbstract extends Zend_Validate_Db_Abstract
{ 
  public function __construct($options = array())
  {
    if ($options === null)
    {
      $options = array();
    }
    
    $this->_initOptions($options);//print_r($options);echo 'b';
    parent::__construct($options);
  }
  
  abstract protected function _initOptions(array &$options);
}