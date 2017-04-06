<?php

require_once 'Zend/Validate/Abstract.php';

class Custom_Validate_Tags extends Custom_ValidateRegexAbstract
{
  const TO_MANY = 'toManyTags';
  const WRONG_TAG = 'wrongTag';
  const BLOCK_TAG = 'blockTag';
  
  private $_tagName = null;

  protected $_messageTemplates = array (
    self::TO_MANY => 'Only 5 tags is allowed.',
    self::WRONG_TAG => 'Ooops! Something went wrong! One tag or more can be incorrect.',
    self::BLOCK_TAG => 'Tag %value% is blocked.'
  );
  
  public function isValid($value)
  {
    $this->_setValue($value);

    $tags = explode(',', $value);
    
    // ilość tagów
    if (count($tags) > 5) {
      $this->_error(self::TO_MANY);
      return false;
    }
    
    // poprawność tagów
    foreach ($tags as $tag)
    {
      $tag = Utils_Text::unicodeTrim($tag);
      
      if (!preg_match('/^[-'.$this->_getLanguagePattern().'0-9\s]{2,20}$/u', $tag))
      {
        $this->_setValue($tag);
        $this->_tagName = Zend_Filter::filterStatic($tag, 'HtmlSpecialChars', array(), array('Custom_Filter'));
        $this->_error(self::WRONG_TAG);
        return false;
      }
      
      // sprawdzanie czy dany tagjest zablokowany
      $tagModelDb = new Application_Model_DbTable_Tag();
      $values = $tagModelDb->checkTagIsBlock($tag);
      if (!empty($values))
      {
        $this->_setValue($tag);
        $this->_tagName = Zend_Filter::filterStatic($tag, 'HtmlSpecialChars', array(), array('Custom_Filter'));
        $this->_error(self::BLOCK_TAG);
        return false;
      }
    }

    return true;
  }
  
  public function getTagName()
  {
    return $this->_tagName;
  }
}