<?php
class Custom_Validate_Date extends Zend_Validate_Date
{
  public function isValid($value)
  {
    if (parent::isValid($value))
    {
      list($year, $month, $day) = sscanf($value, '%d-%d-%d');

      if (!checkdate($month, $day, $year))
      {
        $this->_error(self::INVALID_DATE);
        return false;
      }
      
      return true;
    }

    return false;
  }
}