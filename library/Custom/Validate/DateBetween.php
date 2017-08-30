<?php
class Custom_Validate_DateBetween extends Zend_Validate_Abstract
{
  const NOT_BETWEEN = 'dateNotBetween';
  const NOT_BETWEEN_STRICT = 'dateNotBetweenStrict';

  protected $_messageTemplates = array(
      self::NOT_BETWEEN        => "Date is not between '%min%' and '%max%', inclusively.",
      self::NOT_BETWEEN_STRICT => "Date is not strictly between '%min%' and '%max%'."
  );
  
  private $_inclusive = false;
  private $_min = null;
  private $_max = null;
  
  public function __construct($options = array())
  {
    if (array_key_exists('inclusive', $options))
    {
      $this->_inclusive = $options['inclusive'];
    }
    
    if (array_key_exists('min', $options))
    {
      $this->_min = $options['min'];
    }
    
    if (array_key_exists('max', $options))
    {
      $this->_max = $options['max'];
    }
  }

  public function isValid($value)
  {
    $this->_setValue($value);
    $value = strtotime($value);

    $buf = explode(' ', $this->_min);

    if (count($buf) == 1)
    {
      $min = strtotime($this->_min.' 00:00:00');
    }
    else      
    {
      $min = strtotime($this->_min);
    }

    $buf = explode(' ', $this->_max);

    if (count($buf) == 1)
    {
      $max = strtotime($this->_max.' 23:59:59');
    }
    else      
    {
      $max = strtotime($this->_max);
    }

    if ($this->_inclusive)
    {
      if (($min !== false && $min > $value) || ($max !== false && $value > $max))
      {
        $this->_error(self::NOT_BETWEEN);
        return false;
      }
    }
    else
    {
      if (($min !== false && $min >= $value) || ($max !== false && $value >= $max))
      {
        $this->_error(self::NOT_BETWEEN_STRICT);
        return false;
      }
    }
    return true;
  }
  
  public function getInclusive()
  {
    return $this->_inclusive;
  }

  public function setInclusive($inclusive)
  {
    $this->_inclusive = $inclusive;
  }

  public function getMin()
  {
    return $this->_min;
  }

  public function setMin($min)
  {
    $this->_min = $min;
  }

  public function getMax()
  {
    return $this->_max;
  }

  public function setMax($max)
  {
    $this->_max = $max;
  }
}