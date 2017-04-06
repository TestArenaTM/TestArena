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
class Custom_Db_Profiler_Log extends Zend_Db_Profiler
{
  /**
  * Zend_Log instance
  * @var Zend_Log
  */
  protected $_log;

  /**
  * counter of the total elapsed time
  * @var double 
  */
  protected $_totalElapsedTime;


  public function __construct($enabled = false)
  {
    parent::__construct($enabled);
    
    $this->_log = new Zend_Log();
    $writer = new Zend_Log_Writer_Stream(_LOGDIR . '/db-queries.log');
    $this->_log->addWriter($writer);
  }

  /**
  * Intercept the query end and log the profiling data.
  *
  * @param  integer $queryId
  * @throws Zend_Db_Profiler_Exception
  * @return void
  */
  public function queryEnd($queryId)
  {
    $state = parent::queryEnd($queryId);

    if (!$this->getEnabled() || $state == self::IGNORED)
    {
      return;
    }

    // get profile of the current query
    $profile = $this->getQueryProfile($queryId);

    // update totalElapsedTime counter
    $this->_totalElapsedTime += $profile->getElapsedSecs();

    // create the message to be logged
    $message = "\r\nElapsed Secs: " . round($profile->getElapsedSecs(), 5) . "\r\n";
    $message .= "Query: " . $profile->getQuery() . "\r\n";

    // log the message as INFO message
    $this->_log->info($message);
  }
}
