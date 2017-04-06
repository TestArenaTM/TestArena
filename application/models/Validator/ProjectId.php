<?php
class Application_Model_Validator_ProjectId extends Custom_InputData_Abstract
{
  public function initValidators()
  {
    $this->addValidators('projectId', array(
      new Custom_Validate_Id()
    ), true);
  }
  
  public function initFilters()
  {
    $this->addFilters('projectId', array(
      new Zend_Filter_Int()
    ));
  }  
}