<?php

class Project_Form_TaskFilterForTest extends Custom_Form_AbstractFilter
{
  protected $_releaseList;
  protected $_releaseDefault;

  public function __construct($options = null)
  {
    if (!array_key_exists('releaseList', $options))
    {
      throw new Exception('Release list is not defined in form.');
    }

    if (!array_key_exists('releaseDefault', $options))
    {
      throw new Exception('Release default is not defined in form.');
    }

    $this->_releaseDefault = $options['releaseDefault'];
    $this->_releaseList = $options['releaseList'];
    parent::__construct($options);
  }

  public function init()
  {
    parent::init();
    $this->setMethod('get');
    $this->setName('filterForm');
    $t = new Custom_Translate();

    $this->addElement('select', 'release', array(
      'required'    => false,
      'multiOptions' => array(
        0  => $t->translate('[Wszystkie]', array(), 'general'),
        -1 => $t->translate('[Bez wydania]', array(), 'general')
      )
    ));
    $this->getElement('release')->addMultiOptions($this->_releaseList);
  }

  public function getDefaultValues()
  {
    return json_encode(array(
      'release' => $this->_releaseDefault,
    ));
  }

  public function getSavedValues()
  {
    return json_encode(array(
      'release' => $this->_releaseDefault,
    ));
  }
}