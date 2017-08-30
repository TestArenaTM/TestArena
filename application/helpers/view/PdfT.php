<?php

class Zend_View_Helper_PdfT extends Zend_View_Helper_Abstract
{
  public function pdfT($name, $text, array $parameters = null)
  {
    $translate = new Custom_Translate();
    return $translate->translate($text, $parameters, 'pdf_'.$name);
  }
}