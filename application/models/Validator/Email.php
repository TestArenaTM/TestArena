<?php
class Application_Model_Validator_Email extends Custom_InputData_Abstract
{
  public function initValidators()
  {
    $this->addValidators('email', array(
      new Zend_Validate_EmailAddress()
    ), true);
  }
  
  public function initFilters()
  {
    $this->addFilters('email', array(
      new Zend_Filter_StringTrim()
    ));
  }  
}