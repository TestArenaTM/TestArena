<?php
/*
Copyright © 2014 TestArena 

This file is part of TestArena.

TestArena is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

The full text of the GPL is in the LICENSE file.
*/
require_once 'Zend/Filter/Interface.php';

class Custom_Filter_HtmlSpecialChars implements Zend_Filter_Interface
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
    if ( htmlspecialchars_decode($value, $this->_quoteStyle) == $value )
    {
      return htmlspecialchars((string) $value, $this->_quoteStyle, $this->_charSet);
    }
    
    return $value;
    
  }
}

