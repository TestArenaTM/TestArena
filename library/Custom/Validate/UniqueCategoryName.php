<?php
class Custom_Validate_UniqueCategoryName extends Custom_Validate_DbUniqueAbstract
{
  const ERROR_EXISTS = 'categoryNameExists';
  
  protected $_messageTemplates = array (
    self::ERROR_EXISTS => 'Category name already exists!'
  );
  
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'category';
    $options['field'] = 'name';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $result = $this->_uniqueSelect($value);

    if (mb_strtolower($result[$this->_field]) == mb_strtolower($value))
    {
      $this->_error(self::ERROR_EXISTS);
      return false;
    }

    return true;
  }
}