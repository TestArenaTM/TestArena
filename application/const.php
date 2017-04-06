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
// Define root path directory
defined('_ROOT_DIR')
    || define('_ROOT_DIR', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR.'../'));

// Define path to application frontend directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(_ROOT_DIR . DIRECTORY_SEPARATOR.'application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Define path to application backend directory
defined('BACKEND_PATH')
    || define('BACKEND_PATH', _ROOT_DIR . DIRECTORY_SEPARATOR.'backend');

// Define path to application config directory
defined('_LIBRARY_PATH')
    || define('_LIBRARY_PATH', _ROOT_DIR . DIRECTORY_SEPARATOR.'library');

defined('_UTILS_PATH')
    || define('_UTILS_PATH', _LIBRARY_PATH . DIRECTORY_SEPARATOR.'Utils');

// Define path to application config directory
defined('_APPLICATION_CONFIG_PATH')
    || define('_APPLICATION_CONFIG_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR.'configs');

// Define path to application config file
defined('_APPLICATION_CONFIG')
    || define('_APPLICATION_CONFIG', _APPLICATION_CONFIG_PATH . DIRECTORY_SEPARATOR.'application.ini');

// Define path to language file
defined('_LANGUAGE_PATH')
    || define('_LANGUAGE_PATH', APPLICATION_PATH . DIRECTORY_SEPARATOR.'languages');

// Define path to public directory
defined('_FRONT_PUBLIC_DIR')
    || define('_FRONT_PUBLIC_DIR', _ROOT_DIR . DIRECTORY_SEPARATOR.'public');

// Define path to upload directory
defined('_UPLOAD_BASEDIR')
    || define('_UPLOAD_BASEDIR', _FRONT_PUBLIC_DIR . DIRECTORY_SEPARATOR.'upload');

// Define path to files upload directory
defined('_FILE_UPLOAD_DIR')
    || define('_FILE_UPLOAD_DIR', _UPLOAD_BASEDIR . DIRECTORY_SEPARATOR.'files');

// Define path to avatars upload directory
defined('_AVATAR_UPLOAD_DIR')
    || define('_AVATAR_UPLOAD_DIR', _UPLOAD_BASEDIR . DIRECTORY_SEPARATOR.'avatars');

// Define path to advertisemenets frontend upload directory
defined('_ADVERTISEMENT_UPLOAD_DIR')
    || define('_ADVERTISEMENT_UPLOAD_DIR', _UPLOAD_BASEDIR . DIRECTORY_SEPARATOR.'advertisements');

// Define path to materials upload directory
defined('_MATERIALS_UPLOAD_DIR')
    || define('_MATERIALS_UPLOAD_DIR', _UPLOAD_BASEDIR . DIRECTORY_SEPARATOR.'materials');

// Define path to image directory
defined('_IMAGE_BASEDIR')
    || define('_IMAGE_BASEDIR', _FRONT_PUBLIC_DIR . DIRECTORY_SEPARATOR.'img');

// Define path to application cache directory
defined('_CACHEDIR')
    || define('_CACHEDIR', _ROOT_DIR . DIRECTORY_SEPARATOR.'cache/application');

// Define path to application session cache directory
defined('_CACHESESSDIR')
    || define('_CACHESESSDIR', _CACHEDIR . DIRECTORY_SEPARATOR.'session');

// Define path to application log directory
defined('_LOGDIR')
    || define('_LOGDIR', _ROOT_DIR . DIRECTORY_SEPARATOR.'cache/application-logs');

// Define path to application log directory
defined('_INFO_LOG_DIR')
    || define('_INFO_LOG_DIR', _ROOT_DIR . DIRECTORY_SEPARATOR.'logs');

defined('_ACTION_HELPERS_PATH')
    || define('_ACTION_HELPERS_PATH', APPLICATION_PATH .DIRECTORY_SEPARATOR.'helpers/action');

defined('_EMAILS_PATH') 
    || define('_EMAILS_PATH', _FRONT_PUBLIC_DIR.DIRECTORY_SEPARATOR.'emails');

// Define path to temp directory
defined('_TEMP_PATH')
    || define('_TEMP_PATH', realpath(_ROOT_DIR . DIRECTORY_SEPARATOR.'temp'));