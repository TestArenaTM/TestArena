<?php
abstract class Custom_Model_Dictionary_Filter_Abstract extends Custom_Model_Dictionary_Abstract
{
  public function __construct($id = null)
  {
    if (null !== $id)
    {
      parent::__construct($id);
    }
  }
  
  public function getIdsByName($phrase)
  {
    $keys = array();
    
    foreach ($this->_names as $key => $name)
    {
      if (stripos($name, $phrase) === 0)
      {
        $keys[] = $key;
      }
    }
    
    return $keys;
  }
}