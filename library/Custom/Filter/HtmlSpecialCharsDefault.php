<?php

require_once 'Zend/Filter/Interface.php';

class Custom_Filter_HtmlSpecialCharsDefault implements Zend_Filter_Interface
{
  /**
    * Corresponds to second htmlspecialchars() argument
    *
    * @var integer
    */
  protected $_quoteStyle;

  /**
    * Corresponds to third htmlspecialchars() argument
    *
    * @var string
    */
  protected $_charSet;

  /**
    * Sets filter options
    *
    * @param  integer $quoteStyle
    * @param  string  $charSet
    * @return void
    */
  public function __construct( $quoteStyle = ENT_QUOTES, $charSet = 'UTF-8' )
  {
    $this->_quoteStyle = $quoteStyle;
    $this->_charSet    = $charSet;
  }

  /**
    * Returns the quoteStyle option
    *
    * @return integer
    */
  public function getQuoteStyle()
  {
    return $this->_quoteStyle;
  }

  /**
    * Sets the quoteStyle option
    *
    * @param  integer $quoteStyle
    * @return Losc_Filter_HtmlSpecialChars Provides a fluent interface
    */
  public function setQuoteStyle($quoteStyle)
  {
    $this->_quoteStyle = $quoteStyle;
    return $this;
  }

  /**
    * Returns the charSet option
    *
    * @return string
    */
  public function getCharSet()
  {
    return $this->_charSet;
  }

  /**
    * Sets the charSet option
    *
    * @param  string $charSet
    * @return Losc_Filter_HtmlSpecialChars Provides a fluent interface
    */
  public function setCharSet($charSet)
  {
    $this->_charSet = $charSet;
    return $this;
  }

  /**
    * Defined by Zend_Filter_Interface
    *
    * Returns the string $value, converting characters to their corresponding HTML entity
    * equivalents where they exist
    *
    * @param  string $value
    * @return string
    */
  public function filter($value)
  {
    return htmlspecialchars((string) $value, $this->_quoteStyle, $this->_charSet);
  }
}

