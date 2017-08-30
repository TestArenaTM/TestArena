<?php

class Zend_View_Helper_PdfPt extends Zend_View_Helper_Abstract
{
  public function pdfPt($name, $text, $value, array $parameters = null)
  {
    $translate = new Custom_Translate();
    return $translate->pluralTranslate($text, $value, $parameters, 'pdf_'.$name);
  }
}