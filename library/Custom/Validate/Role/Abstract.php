<?php
abstract class Custom_Validate_Role_Abstract extends Zend_Validate_Db_Abstract
{
  protected function _query($value)
  {
    $select = $this->getSelect();
    
    $result = $select->getAdapter()->fetchOne(
      $select,
      array('value' => $value),
      Zend_Db::FETCH_ASSOC
      );

    return $result;
  }
}