<?php
class Custom_Pdf
{
  private static $_defaultOptions = array(
      'mode'              => '',
      'format'            => 'A4',
      'default_font_size' => 0,
      'default_font'      => '',
      'mgl'               => 15,
      'mgr'               => 15,
      'mgt'               => 16,
      'mgb'               => 16,
      'mgh'               => 9,
      'mgf'               => 9,
      'orientation'       => 'P'
    );
  
  public static function create(array $options = array(), $cssFilePath = false)
  {
    $config = Zend_Registry::get('config')->pdf;
    $path = $config->libraryPath;
    Zend_Loader::loadFile('mpdf.php', $path, true);
    $defaultOptions = self::$_defaultOptions;
    
    foreach ($config->get('defaultOptions', array()) as $key => $value)
    {
      $defaultOptions[$key] = $value;
    }
   
    $options = array_merge($defaultOptions, $options);
    $pdf = new mPDF(
      $options['mode'], 
      $options['format'], 
      $options['default_font_size'], 
      $options['default_font'], 
      $options['mgl'], 
      $options['mgr'], 
      $options['mgt'], 
      $options['mgb'], 
      $options['mgh'], 
      $options['mgf'], 
      $options['orientation']); 
    
    if ($cssFilePath !== false)
    {
      $pdf->WriteHTML(file_get_contents($cssFilePath), 1);
    }
    
    return $pdf;
  }
}