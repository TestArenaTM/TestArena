<?php
class Custom_Validate_DateEarlier extends Zend_Validate_Abstract
{
  const NOT_EARLIER = 'dateNotEarlier';
  const NOT_EARLIER_STRICT = 'dateNotEarlierStrict';

  protected $_messageTemplates = array(
    self::NOT_EARLIER => 'The date must be earlier than the date \'%value%\'.',
    self::NOT_EARLIER_STRICT => 'The date must be earlier or equal than the date \'%value%\'.'
  );
  
  private $_inclusive = false;
  private $_date = null;
  
  public function __construct($options = array())
  {
    if (array_key_exists('inclusive', $options))
    {
      $this->_inclusive = $options['inclusive'];
    }
    
    if (array_key_exists('date', $options))
    {
      $this->_date = $options['date'];
    }
  }

  public function isValid($value)
  {
    $this->_setValue($value);
    $value = strtotime($value);
    $date = strtotime($this->_date);

    if ($this->_inclusive)
    {
      if ($date !== false && $value > $date)
      {
        $this->_error(self::NOT_EARLIER);
        return false;
      }
    }
    else
    {
      if ($date !== false && $value >= $date)
      {
        $this->_error(self::NOT_EARLIER_STRICT);
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

  public function getDate()
  {
    return $this->_date;
  }

  public function setDate($date)
  {
    $this->_date = $date;
  }
}