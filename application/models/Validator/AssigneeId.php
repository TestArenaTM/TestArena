<?php
class Application_Model_Validator_AssigneeId extends Custom_InputData_Abstract
{
  public function initValidators()
  {
    $this->addValidators('assigneeId', array(
      new Custom_Validate_Id()
    ), true);
  }
  
  public function initFilters()
  {
    $this->addFilters('assigneeId', array(
      new Zend_Filter_Int()
    ));
  }  
}