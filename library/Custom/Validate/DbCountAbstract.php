<?php
abstract class Custom_Validate_DbCountAbstract extends Custom_Validate_DbAbstract
{ 
  private function _countQuery($value)
  {
    $select = $this->getSelect();
    
    $result = $select->getAdapter()->fetchOne(
      $select,
      array('value' => $value),
      Zend_Db::FETCH_ASSOC
    );

    return $result;
  }
  
  protected function _countSelect($value, $ids)
  {
    $db = $this->getAdapter();
    $select = new Zend_Db_Select($db);
    $select->from($this->_table, array('COUNT(id)'), $this->_schema);
    $select->where($db->quoteIdentifier($this->_field, true).' IN (?)', $ids); 
    $this->setSelect($select);
    return $this->_countQuery($value);
  }
}