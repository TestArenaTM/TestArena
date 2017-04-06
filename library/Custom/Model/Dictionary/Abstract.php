<?php
abstract class Custom_Model_Dictionary_Abstract extends Custom_Model_Abstract
{
  protected $_id = null;
  protected $_names = array();
  
  public function __construct($id = null)
  {
    $this->setId($id);
  }
  
  public function getId()
  {
    return $this->_id;
  }

  public function setId($id)
  {
    if ($id !== null && !array_key_exists($id, $this->_names))
    {
      
      throw new Exception('Label not exists.');
    }
    
    $this->_id = $id;
    return $this;
  }
  
  public function getName($id = false)
  {
    if ($id === false)
    {
      $id = $this->_id; 
    }
    
    if ($id !== null)
    {
      
      if (!array_key_exists($id, $this->_names))
      {
        throw new Exception('Label not exists.');
      }
      
      return $this->_names[$id];
    }
    else
    {
      return 'NONE';
    }
  }
  
  public function getNames()
  {
    return $this->_names;
  }
  
  public function __toString()
  {
    return $this->getName($this->_id);
  }
}