<?php
class Custom_Validate_Tags extends Custom_Validate_DbCountAbstract
{
  const VERSION_INVALID_IDS = 'tagInvalidIds';
  const VERSION_NOT_EXISTS   = 'tagNotExists';
  
  protected $_messageTemplates = array
  (
    self::VERSION_INVALID_IDS => 'Tag ids can only be numbers.',
    self::VERSION_NOT_EXISTS => 'Tag not exists in database.'
  );
    
  protected function _initOptions(array &$options)
  {
    $options['table'] = 'tag';
    $options['field'] = 'id';
  }
  
  public function isValid($value)
  {
    $this->_setValue($value);
    $tags = explode(',', $value);
    
    foreach($tags as $tag)
    {
      if (!is_numeric($tag) || $tag <= 0)
      {
        $this->_error(self::VERSION_INVALID_IDS);
        return false;
      }
    }
    
    $result = $this->_countSelect($value, $tags);
    
    if (count($tags) != $result)
    {
      $this->_error(self::VERSION_NOT_EXISTS);
      return false;
    }
    
    return true;
  }
}