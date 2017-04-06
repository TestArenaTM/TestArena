<?php
class Application_Model_Validator_Ids extends Custom_InputData_Abstract
{
  public function initValidators()
  {
    $this->addValidators('ids', array(
      new Custom_Validate_Ids()
    ), true);
  }
  
  public function initFilters()
  {
    $this->addFilters('ids', array(
      new Custom_Filter_StringIntToArrayInt()
    ));
  }  
}