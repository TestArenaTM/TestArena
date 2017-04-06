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
class Utils_Image_Exception extends Exception
{
  // 0 dla poprawnych plików
  const FILE_NOT_EXISTS         = 1;
  const IMAGE_IS_CORRUPTED      = 2;
  const UNSUPPORTED_IMAGE_TYPE  = 3;
  const FIT_NOT_SUPPORT_GIF     = 4;
  const IMAGE_IS_TOO_BIG        = 5;
  
  const MSG_FILE_NOT_EXISTS         = 'fileNotExist';
  const MSG_IMAGE_IS_CORRUPTED      = 'isCorrupted';
  const MSG_UNSUPPORTED_IMAGE_TYPE  = 'unsupportedType';
  const MSG_FIT_NOT_SUPPORT_GIF     = 'fitNotSupportGif';
  const MSG_IMAGE_IS_TOO_BIG        = 'fileSizeTooBig';
}