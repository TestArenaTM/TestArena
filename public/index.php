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
require_once realpath(dirname(__FILE__) . '/../const.php');
require_once realpath(dirname(__FILE__) . '/../application/const.php');
require_once _ROOT_DIR.DIRECTORY_SEPARATOR.'const.php';
require_once _UTILS_PATH.'/Text.php';

Utils_Text::checkMagicQuotes();

header("Cache-Control: server");

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    _LIBRARY_PATH,
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';
  
// Create application, bootstrap, and run
$application = new Zend_Application(
  APPLICATION_ENV,
  _APPLICATION_CONFIG
);

$application->bootstrap()
            ->run();