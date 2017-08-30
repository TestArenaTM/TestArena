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
var onSelectFiles;

$(document).ready(function() {
  if (typeof config != 'undefined') {
    /* 
     * Funkcje pomocnicze
     */

    var currentPath = config.directorySeperator;
    var fileList = $('#fileList');
    var directoryList = $('#directoryList');
    var statusBarCurrentPath = $('#currentPath');
    var fileUploadStatus = $('#fileUploadStatus');
    var totalFilesToUpload = 0;
    var numberFilesUploaded = 0;
    var fileTypes = {
      'Image': ['png', 'jpg', 'gif'],
      'Text': ['txt', 'doc', 'xls', 'csv', 'xml', 'html', 'pdf']
    };

    /* Inicjacja */
    var init = function() {
      $('#selectFilesButton').hide();
      fileUploadStatus.hide();

      /* Tryb pracy przeglądarki plików jako wybór plików */
      if (config.mode > 0) {
        $('#selectFilesButton').show();
      } else {
        $('#selectFilesButton').hide();
      }
    };

    /* Wyświetlanie błędów */
    var showError = function(errorMsg) {
      if (typeof config.errorMessages[errorMsg] !== 'undefined') {
        alert(config.errorMessages[errorMsg]);
      } else {
        alert(errorMsg);
      }
    };
    
    /* Zwraca odpowiedni typ w zależności od rozszerzenia pliku */
    var getFileType = function(extension) {
      extension = extension.toLowerCase();
      
      for (var fileType in fileTypes) {
        if ($.inArray(extension, fileTypes[fileType]) >= 0) {
          return fileType;
        };
      }
      
      return 'File';
    };

    /* Pobranie liczby z identyfikatora tagu */
    var getId = function(object) {
      var buf = object.prop('id').split('_', 2);
      
      if (buf.length === 2) {
        return buf[1];
      }
      
      return 0;
    };
    
    /* Rekurencja do budowy drzewa katalogów */
    var showDirectories = function(rootObj, list) {
      var ul = rootObj.append('<ul>').find('ul');
      var path = '';
      
      for (var i in list) {
        if (list[i].dirname == config.directorySeperator) {
          path = list[i].dirname + list[i].basename;
        } else {
          path = list[i].dirname + config.directorySeperator + list[i].basename;
        }
        
        $('#directoryTreeTmpl').tmpl([{'path': path, 'name': list[i].basename}]).appendTo(ul);

        if (typeof list[i].items != 'undefined') {
          showDirectories(ul.find('li:last'), list[i].items);
        }
      }
    };
    
    /* Odświeża listę katalogów */
    var refreshDirectoryList = function() {
      $.post(config.directoryListUrl, function(data) {
        var list = jQuery.parseJSON(data);
        directoryList.html('');
        var ul = directoryList.append('<ul>').find('ul');
        $('#directoryTreeTmpl').tmpl([{'path': config.directorySeperator, 'name': config.texts.root}]).appendTo(ul);
        showDirectories(ul.find('li:last'), list);
        $('.directory[path="' + currentPath + '"]').addClass('selected');
      });
    };

    /* Odświeża listę plików */
    var refreshFileList = function() {
      $.post(config.fileListUrl, {path: encodeURIComponent(currentPath)}, function(data) {
        var list = jQuery.parseJSON(data);
        fileList.html('');
        var table = fileList.append('<table>').find('table');
        table.append('<tr><td>' + config.texts.name + '</td><td>' + config.texts.actions + '</td></tr>');
        var i = currentPath.substring(0, currentPath.length-1).lastIndexOf(config.directorySeperator);
        var path = currentPath.substr(0, i);
        
        if (path.length === 0) {
          path = config.directorySeperator;
        }
        if (currentPath !== config.directorySeperator) {
          $('#directoryUpTmpl').tmpl([{
              'path': path
            }]).appendTo(table);
        }
        
        for (var i in list.directories) {//console.log(list.directories[i]);
          if (currentPath === config.directorySeperator) {
            path = currentPath + list.directories[i];
          } else {
            path = currentPath + config.directorySeperator + list.directories[i];
          }
          $('#directoryTmpl').tmpl([{
              'index': i,
              'path': path, 
              'name': list.directories[i]
            }]).appendTo(table);
        }

        for (var i in list.files) {
          $('#fileTmpl').tmpl([{
              'fullname': list.files[i].fullname,
              'name': list.files[i].name,
              'fileType': getFileType(list.files[i].extension),
              'id': list.files[i].id,
              'previewImageUrl': config.previewImageUrl.replace('0', list.files[i].id)
            }]).appendTo(table);
        }
        
        statusBarCurrentPath.html(currentPath.replace(new RegExp('\\' + config.directorySeperator, 'g'), '/'));
      });
    };
    
    /* Ukrycie tła dla popupów */
    var hidePopup = function() {
      $('#popupBackground').hide();
      $('.popup').hide();
    };
    $('#popupBackground').click(hidePopup);
    
    /* 
     * Zdarzenia
     */
    
    /* Zamykanie okna */
    $('#closeButton').click(function() { 
      window.close();
      return false;
    });
    
    /* Kliknięcie w katalog */
    $(document).on('click', '.directory', function() {
      currentPath = $(this).attr('path');
      refreshFileList();
      $('.directory').removeClass('selected');
      $('.directory[path="' + currentPath + '"]').addClass('selected');
      return false;
    });
    
    /* Usunięcie katalogu */
    $(document).on('click', '.removeDirectoryButton', function() {
      $.post(config.removeDirectoryUrl, {path: encodeURIComponent(currentPath + config.directorySeperator + $(this).attr('directoryName'))}, function(data) {
        if (data === 'OK') {
          refreshDirectoryList();
          refreshFileList();
        } else {
          showError(data);
        }
      });
      
      return false;
    });
    
    /* Kliknięcie w plik */
    $(document).on('click', '.file', function() {
      var checkBox = $('#selectedFile_' + getId($(this)));
      checkBox.prop('checked', !checkBox.prop('checked'));
      return false;
    });
    
    /* Usunięcie pliku */
    $(document).on('click', '.removeFileButton', function() {
      $.post(config.removeFileUrl, {id: getId($(this))}, function(data) {
        if (data == 'OK') {
          refreshFileList();
        } else {
          showError(data);
        }
      });
      
      return false;
    });
    
    /* Podgląd obrazków */
    $(document).on({
      mouseenter: function (data) {
        var previewImage = $('#previewImage_' + getId($(this)));
        if (!previewImage.is(':visible')) {
          previewImage
                  .css('left', data.clientX + 1)
                  .css('top', data.clientY + 1)
                  .show();
        }
      },
      mousemove: function (data) {
        var previewImage = $('#previewImage_' + getId($(this)));
        if (previewImage.is(':visible')) {
          previewImage
                  .css('left', data.clientX + 1)
                  .css('top', data.clientY + 1);
        }
      },
      mouseleave: function () {
        var previewImage = $('#previewImage_' + getId($(this)));
        if (previewImage.is(':visible')) {
          previewImage.hide();
        }
      }
    }, '.fileImage .file');
    
    /* Otwarcie popupu tworzenia katalogu */
    $('#createDirectoryButton').click(function() {
      $('#popupBackground').show();
      $('#createDirectoryPopup').show();
      $('#directoryName').focus();
      return false;
    });
    
    /* Tworzenie katalogu */
    var createDirectory = function() {
      var directoryName = $('#directoryName');
      var value = directoryName.val().trim();

      if (value != '') {
        $.post(config.createDirectoryUrl, {path: encodeURIComponent(currentPath + config.directorySeperator + value)}, function(data) {
          if (data === 'OK') {
            hidePopup();
            directoryName.val('');
            refreshDirectoryList();
            refreshFileList();
          } else {
            showError(data);
          }
        });
      } else {
        showError('DIRECTORY_NAME_IS_EMPTY');
        directoryName.val('');
        directoryName.focus();
      }
    };
    
    $('#createDirectoryPopupButton').click(function() {
      createDirectory();      
      return false;
    });
    
    $('#directoryName').keyup(function(e) {
      if (e.keyCode == 13) {
        createDirectory();
      }
      
      return false;
    });
    
    /* Otwarcie popupu zmiany nazwy katalogu */
    $(document).on('click', '.renameDirectoryButton', function() {
      var name = $(this).attr('directoryName');
      $('#oldDirectoryName').val(name);
      $('#newDirectoryName').val(name);
      $('#popupBackground').show();
      $('#renameDirectoryPopup').show();
      $('#newDirectoryName').focus();
      return false;
    });
    
    /* Zmiana nazwy katalogu */
    var renameDirectory = function() {
      var oldName = $('#oldDirectoryName').val();
      var newName = $('#newDirectoryName').val().trim();
      
      if (oldName === newName) {
        hidePopup();
      } else {
        if (newName != '') {
          $.post(config.renameDirectoryUrl, {
            path: encodeURIComponent(currentPath + config.directorySeperator + oldName),
            newPath: encodeURIComponent(currentPath + config.directorySeperator + newName)
          }, function(data) {
            if (data === 'DESTINATION_DIRECTORY_ALREADY_EXISTS') {
              showError(data);
              $('#newDirectoryName').focus();
            } else {
              if (data === 'OK') {
                hidePopup();
                refreshDirectoryList();
                refreshFileList();
                
              } else {
                hidePopup();
                showError(data);
              }
            }
          });
        } else {
          showError('NEW_DIRECTORY_NAME_IS_EMPTY');
          $('#newDirectoryName').focus();
        }
      }
    };
    
    $('#renameDirectoryPopupButton').click(function() {
      renameDirectory();
      return false;
    });
    
    $('#newDirectoryName').keyup(function(e) {
      if (e.keyCode == 13) {
        renameDirectory();
      }
      
      return false;
    });
    
    /* Otwarcie popupu zmiany nazwy pliku */
    $(document).on('click', '.renameFileButton', function() {
      var name = $(this).attr('fileName');
      $('#renamedFileId').val(getId($(this)));
      $('#oldFileName').val(name);
      $('#newFileName').val(name);
      $('#popupBackground').show();
      $('#renameFilePopup').show();
      $('#newFileName').focus();
      return false;
    });
    
    /* Zmiana nazwy pliku */
    var renameFile = function() {
      var newName = $('#newFileName').val().trim();
      
      if ($('#oldFileName').val() === newName) {
        hidePopup();
      } else {
        if (newName != '') {
          $.post(config.renameFileUrl, {
            id: $('#renamedFileId').val(),
            newName: encodeURIComponent(newName)
          }, function(data) {
            hidePopup();

            if (data === 'OK') {
              refreshFileList();
            } else {
              showError(data);
            }
          });
        } else {
          showError('FILE_NAME_IS_EMPTY');
          $('#newName').focus();
        }
      }
    };
    
    $('#renameFilePopupButton').click(function() {
      renameFile();
      return false;
    });
    
    $('#newFileName').keyup(function(e) {
      if (e.keyCode == 13) {
        renameFile();
      }
      
      return false;
    });

    /* Pobierz plik */
    $(document).on('click', '.downloadFileButton', function() {
      window.open(config.downloadFileUrl.replace('0', getId($(this))));
      return false;
    });
    
    /* Wybranie zaznaczonych plików i przesłanie ich do rodzica */
    $('#selectFilesButton').click(function() {
      var files = [];
      
      $('.selectedFile:checked').each(function() {
        files[files.length] = {
          'id': getId($(this)),
          'fullname': $(this).val()
        };
      });
      
      if (files.length) {
        window.opener.onSelectFilesInFileBrowser(config.mode, files);
        window.close();
      } else {
        showError('NO_FILE_SELECTED');
      }
    
      return false;
    });
    
    /* Zaznaczenie wszystkiego */
    $('#selectAllButton').click(function() {
      $('#fileList input[type=checkbox]').prop('checked', true);    
      return false;
    });
    
    /* Odznaczenie wszystkiego */
    $('#unselectAllButton').click(function() {
      $('#fileList input[type=checkbox]').prop('checked', false);    
      return false;
    });
    
    /* Usuń zaznaczone */
    $('#deleteSelectedButton').click(function() {
      $(this).prop('disabled', true);
      var fileIds = [];
      var directories = [];
      
      $('.selectedFile:checked').each(function() {
        fileIds[fileIds.length] = getId($(this));
      });
      
      $('.selectedDirectory:checked').each(function() {
        if (currentPath == config.directorySeperator) {
          directories[directories.length] = currentPath + $(this).val();
        } else {
          directories[directories.length] = currentPath + config.directorySeperator + $(this).val();
        }
      });
      
      //console.log(fileIds);
      //console.log(directories);

      $.post(config.removeUrl, {directories: directories, fileIds: fileIds}, function(data) {
        if (data !== 'OK') {
          showError(data);
        }

        refreshDirectoryList();
        refreshFileList();
        $(this).prop('disabled', false);
      });
      
      return false;
    });
    
    /*
     * Upload plików
     */
    
    /* Stanu aktualny przesyłania pliku */
    function onUploadFilesProgress(event) {
      $('#numberFilesUploaded').html(numberFilesUploaded);
    }
    
    /* Wyświetlenie błedów po wysyłce plików */
    var endFilesUploaded = function() {
      fileUploadStatus.hide();
      var errorMsg = '';

      if (uploadErrors.length > 0) {
        errorMsg += config.errorMessages['UPLOAD_FILE_ERROR'] + uploadErrors.join("\n");
      }

      if (uploadExistsErrors.length > 0) {
        if (errorMsg.length > 0) {
          errorMsg += "\n\n";
        }

        errorMsg += config.errorMessages['UPLOAD_FILE_EXISTS'] + uploadExistsErrors.join("\n");
      }

      if (uploadForbiddentExtensionsErrors.length > 0) {
        if (errorMsg.length > 0) {
          errorMsg += "\n\n";
        }

        errorMsg += config.errorMessages['UPLOAD_FILE_FORBIDDEN_EXTENSION'] + uploadForbiddentExtensionsErrors.join("\n");
      }

      if (uploadTooLargeFiles.length > 0) {
        if (errorMsg.length > 0) {
          errorMsg += "\n\n";
        }

        errorMsg += config.errorMessages['UPLOADED_FILE_IS_TOO_LARGE'] + uploadTooLargeFiles.join("\n");
      }  

      if (errorMsg.length > 0) {
        alert(errorMsg);
      }

      refreshFileList();
    };
    
    /* Zakończenie przesyłania pliku */
    var uploadErrors = new Array();
    var uploadExistsErrors = new Array();
    var uploadForbiddentExtensionsErrors = new Array();
    var uploadTooLargeFiles = new Array();
    
    function onEndFilesUploaded(event) {
      numberFilesUploaded++;//console.log(event.target.response);
      $('#numberFilesUploaded').html(numberFilesUploaded);
      var result = jQuery.parseJSON(event.target.response);

      if (result.status === 'ERROR') {
        if (result.fileNames.error.length > 0) {
          uploadErrors[uploadErrors.length] = result.fileNames.error.join(', ');
        }
        
        if (result.fileNames.exists.length > 0) {
          uploadExistsErrors[uploadExistsErrors.length] = result.fileNames.exists.join(', ');
        }

        if (typeof result.fileNames.forbiddenExtensions != 'undefined' && result.fileNames.forbiddenExtensions.length > 0) {
          uploadForbiddentExtensionsErrors[uploadForbiddentExtensionsErrors.length] = result.fileNames.forbiddenExtensions.join(', ');
        }
      }

      if (totalFilesToUpload === numberFilesUploaded) {
        endFilesUploaded();
      }
    } 
    
    /* Rozpoczęcie przesyłania pliku */
    var startUploadFile = function(file) {
      var form = new FormData();
      form.append('file', file);
      form.append('path', encodeURIComponent(currentPath));

      // http://www.dobreprogramy.pl/_r2d2_/Upload-plikow-z-wykorzystaniem-PHP-i-AJAX,55515.html
      var xhr = new XMLHttpRequest();
      xhr.upload.addEventListener('progress', onUploadFilesProgress, false);
      xhr.addEventListener('load', onEndFilesUploaded, false);
      //xhr.addEventListener('error', bladWysylania, false);
      //xhr.addEventListener('abort', przerwanieWysylania, false);
      xhr.open('post', config.fileUploadUrl, true);
      xhr.send(form);
    };
    
    /* Kliknięcie w przycisk uruchamiający okienko wyboru plików */
    $('#uploadFilesButton').click(function() {
      $('#files').val('').click();
      return false;
    });
    
    /* Zdarzenie wywoływane po zamknięciu okienka wyboru plików */
    $('#files').change(function() {
      uploadErrors = new Array();
      uploadExistsErrors = new Array();
      uploadForbiddentExtensionsErrors = new Array();
      uploadTooLargeFiles = new Array();
      
      fileUploadStatus.show();
      numberFilesUploaded = 0;
      totalFilesToUpload = document.getElementById('files').files.length;
      $('#totalFilesToUpload').html(totalFilesToUpload);
      
      for (var i = 0; i < totalFilesToUpload; i++) {
        var file = document.getElementById('files').files[i];

        if (file.size <= 8388608) {
          startUploadFile(file);
        } else {
          numberFilesUploaded++;
          uploadTooLargeFiles[uploadTooLargeFiles.length] = file.name;
        }
      }
      
      if (uploadTooLargeFiles.length === totalFilesToUpload) {
        endFilesUploaded();
      }
      
      return false;
    });
    
    /*
     * Uruchomienie wszystkiego
     */    
    init();
    refreshDirectoryList();
    refreshFileList();
  } else {
    console.error('You must fill config variable.');
  }
});
