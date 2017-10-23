console/*
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
var language = new Object();
var url = new Object();
var prePopulated = new Object();
var roleEditUsers = '';
var currentRoleUsersId = 0;
var currentLanguage = 'pl';
var fileBrowserHandle = null;
var selectedAttachments = [];

language.months = new Array();
language.months['en'] = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
language.months['pl'] = ['Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'];
language.days = new Array();
language.days['en'] = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
language.days['pl'] = ['Ni', 'Po', 'Wt', 'Śr', 'Cz', 'Pi', 'So'];

$(document).ready(function() {
  $('.popbox').popbox();
  $('#scrollbar1').tinyscrollbar({ wheelSpeed: 400});	
  $('#scrollbar2').tinyscrollbar({ wheelSpeed: 400});	
  $('#scrollbar3').tinyscrollbar({ wheelSpeed: 400});
  
  // Auto laod form
  $('.j_autoLoad').change(function() {
    $(this).closest('form').submit();
  });
  
  // Funkcja do pobirania id
  var getId = function(element, elementNo) {
    var buf = element.attr('id').split('_');
    if (buf.length == elementNo) {
      return buf[elementNo - 1];
    }
    return 0;
  };
  
  // Export project
  var exportRoles = $('#exportRoles');
  var exportTasks = $('#exportTasks');
  var exportUsers = $('#exportUsers');
  var popupSelectLeastOneCheckbox = $('#j_popup_select_least_one_checkbox');
  
  if (popupSelectLeastOneCheckbox.length) {
    createPopup(popupSelectLeastOneCheckbox);
  }
  
  $('form[name=exportForm]').submit(function(event){
    if ($('.j_exportItem:checked').length === 0) {
      //event.preventDefault();
      //popupSelectLeastOneCheckbox.dialog('open');
    }
  });
  
  if (exportRoles.length && exportTasks.length && exportUsers.length) {

    var enabledExportUsers = function() {
      if (exportRoles.is(':checked') || exportTasks.is(':checked')) {
        exportUsers.prop('disabled', false);
      } else {
        exportUsers.prop('disabled', true);
        exportUsers.prop('checked', false);
      }
      
      if (exportTasks.is(':checked')) {
        exportUsers.prop('checked', true);
      }
    };
    
    exportUsers.change(function() {
      if (!$(this).is(':checked')) {
        exportTasks.prop('checked', false);
      }
      enabledExportUsers();
    });
    
    exportRoles.change(function() {
      enabledExportUsers();
    });
    
    exportTasks.change(function() {
      enabledExportUsers();
    });
  }

  // AKtywny projekt
  $('#activeProject').chosen({
    disable_search_threshold:10,
    no_results_text:language.noResultsFor
  });
  
  // Podświetlanie wybranych filtrów
  if (typeof filterDefaultValues != 'undefined') {
    for (var name in filterDefaultValues) {
      var field = $('#' + name);
      var value = filterDefaultValues[name];
      
      if (field.length) {
        if (field.prop('tagName') == 'INPUT') {
          if (field.attr('type') == 'checkbox' && field.prop('checked') != value) {
            field.parent('div').addClass('highlightFilter');
          }
          else if (field.attr('type') == 'text' && field.val() != value) {
            field.addClass('highlightFilter');
            field.parent('div').addClass('highlightFilter');
          }
        } else if (field.val() != value) {
          field.parent('div').addClass('highlightFilter');
        }        
      }
    }
  }
  
  // <select>
  $('.filter select').change(function() {
    var defaultValue = '0';

    if (typeof filterDefaultValues[$(this).attr('id')] != 'undefined') {
      var defaultValue = filterDefaultValues[$(this).attr('id')];
    }

    if ($(this).val() == defaultValue) {
      $(this).parent('div').removeClass('highlightFilter');
    } else {
      $(this).parent('div').addClass('highlightFilter');
    }
  });

  // <input type='text'>
  $('.filter input[type=text]').keyup(function() {
    if ($(this).val().length) {
      $(this).addClass('highlightFilter');
    } else {
      $(this).removeClass('highlightFilter');
    }
  });

  // <input type='checkbox'>
  $('.filter input[type=checkbox]').change(function() {
    if ($(this).is(':checked')) {
      $(this).parent('div').addClass('highlightFilter');
    } else {
      $(this).parent('div').removeClass('highlightFilter');
    }
  });
  
  //jScroll
  if ($('.j_scrollNext').length) {
    $('.j_scroll').jscroll({
      contentSelector: 'div.j_scrollPage',
      nextSelector: 'a.j_scrollNext:last',
      autoTrigger: true
    });
  }
  
  // Messages - start
  var msgThreadList = $('#j_msgThreadList');
  
  if (msgThreadList.length) {
    var msgSingleThreadList = $('#j_msgSingleThreadList');
    var msgIsBusy = $('#j_msgIsBusy');
    var msgForm = $('#j_msgForm');
    var msgThreadsContainer = $('.j_MsgThreads');
    var msgThreadElements = $('.j_MsgThread');
    var msgThreadItemTmpl = $('#j_msgThreadItemTmpl');
    var msgResponse = $('.j_msgResponse');
    var popupFieldIsEmpty = $('#j_popup_field_is_empty');
    var operationsBlocked = false;
    
    createPopup(popupFieldIsEmpty);
    
    var formVisible = function(visible) {
      if (visible) {
        msgForm.show();
        msgIsBusy.hide();
      } else {
        msgForm.hide();
        msgIsBusy.show();
      }
    };
    
    var updateMsgThreadList = function(msgThreadId, msgCategory) {
      msgCategory = typeof msgCategory !== 'undefined' ? msgCategory : msgCategoryDefault;
      $('#j_msgSingleThreadSubject').html($('#j_msgThreadSubject-' + msgThreadId).html());

      $.post(url.msgThreadList, {id : msgThreadId}, function(data) {
        var parsedData = $.parseJSON(data);

        if (parsedData.length) {
          msgThreadElements.removeClass('msgOpen');
          $('#j_MsgThread-'+msgThreadId).addClass('msgOpen');

          msgThreadItemTmpl.tmpl($.parseJSON(data)).appendTo(msgSingleThreadList);
          
          setStatusToRead(msgThreadId, msgCategory);

          formVisible(true);
          msgThreadList.animate({scrollTop:msgThreadList[0].scrollHeight}, 1000);
        } else {
          alert(language.msgNotExists);
          msgIsBusy.hide();
        }
        
        operationsBlocked = false;
      });
    };
    
    var setStatusToRead = function(itemId, itemType) {
      msgResponse.attr('id', 'j_msgResponse-'+itemId);
      
      if ($('#j_msgResponse-' + itemId).length && $('#j_msgNew-' + itemId).length) {
        $.post(url.messageRead, {'itemId': itemId, 'itemType': itemType}, function(data) {
          if ($.parseJSON(data)) {
            $('#j_msgNew-' + itemId).remove();
          }
        });
      }
    };
    
    msgThreadsContainer.on('click', '.j_MsgThread', function(event) {
      event.preventDefault();
      if (operationsBlocked){
        return;
      }
      
      operationsBlocked = true;
      
      var msgThreadId = $(this).attr('id').substring(12);
      
      formVisible(false);
      msgSingleThreadList.html('');
      updateMsgThreadList(msgThreadId);

      return false;
    });
    
    msgResponse.click(function(event) {
      event.preventDefault();
      var msgContent = $('#j_msgContent');
      var msgThreadId = $(this).prop('id').substring(14);
      
      if (msgContent.val().length > 0) {
        
        $.post(url.msgResponse + '/' + msgThreadId, {content : msgContent.val()}, function(data) {
          msgContent.val('').keyup().focus();
          var parsedData = $.parseJSON(data);
          
          if (parsedData.constructor == Object) {
            msgThreadItemTmpl.tmpl(parsedData).appendTo(msgSingleThreadList);
            
            msgThreadList.scrollTop(msgThreadList[0].scrollHeight);
            
            var addedElement = $('#j_msgSingleThreadList > li:last');
            addedElement.addClass('msgResponseAdded');
            setTimeout(function() { addedElement.removeClass('msgResponseAdded'); }, 1000);
          } else {
            if (parsedData == -1) {
              alert(language.msgResponseContentError);
            } else if (parsedData == 0) {
              alert(language.msgResponseError);
            }
            
            formVisible(false);
            msgSingleThreadList.html('');
            updateMsgThreadList(msgThreadId);
          }
        });
      } else {
        popupFieldIsEmpty.dialog('open');
      }

      return false;
    });
    
    if (typeof msgItemId !== 'undefined' && typeof msgItemType !== 'undefined') {
      updateMsgThreadList(msgItemId, msgItemType);
    }
  }
  // Messages - end
  
  // Komentarze
  var commentList = $('#j_commentList');
  var commentContent = $('#j_commentContent');
  var commentForm = $('#j_commentForm');
  var commentIsBusy = $('#j_commentIsBusy');
  var commentAdd = $('#j_addComment');
  var commentSave = $('#j_saveComment');
  var commentCancelEdit = $('#j_cancelEditComment');
  var commentCurrentId = 0;
  var commentShowOtherSubjects = $('#j_showOtherSubjects');
  
  if (typeof commentData != 'undefined' && commentList.length) {
    var popupFieldIsEmpty = $('#j_popup_field_is_empty');
    createPopup(popupFieldIsEmpty);
    
    // funkcje pomocnicze
    var updateCommentListBySubject = function() {
      $.post(url.commentList, function(data) {
        commentList.html('');
        var items = jQuery.parseJSON(data);
        $('#j_commentItem').tmpl(items).appendTo(commentList);
        formVisible(true);

        if (commentData.addOn || (!commentData.addOn && items.length)) {
          $('#comments').show();
        }
        
        if (commentShowOtherSubjects.is(':checked')) {
          $('.comment.otherSubject').show();
        }
      });
    };
    
    var formVisible = function(visible) {
      if (visible) {
        if (commentData.addOn) {
          commentForm.show();
        }
        commentIsBusy.hide();
      } else {
        if (commentData.addOn) {
          commentForm.hide();
        }
        commentIsBusy.show();
      }
    };
    
    var buttonEdit = function(enable) {
      if (enable && commentData.addOn) {
        commentAdd.hide();
        commentSave.show();
        commentCancelEdit.show();
      } else {
        commentAdd.show();
        commentSave.hide();
        commentCancelEdit.hide();
      }
    };
    
    // ustawienia poczatkowe
    buttonEdit(false);
    //formVisible(false);
    updateCommentListBySubject();
    
    // akcje
    commentShowOtherSubjects.change(function() {
      $('.comment.otherSubject').toggle();
    });
    
    commentAdd.click(function() {
      var commentContentValue = commentContent.val().trim();
      
      if (commentContentValue.length > 0) {
        formVisible(false);

        $.post(url.commentAdd, {content : commentContentValue}, function(data) {
          commentContent.val('').keyup().focus();

          if (jQuery.parseJSON(data)) {
            updateCommentListBySubject();
          }
        });
      } else {
        popupFieldIsEmpty.dialog('open');
      }
      
      return false;
    });
    
    $(document).delegate('.j_editComment', 'click', function() {
      commentCurrentId = $(this).attr('comment_id');     
      commentContent.val(htmlspecialchars_decode($('#j_comment_content_' + commentCurrentId).html().replace( /<[^p].*?>/g, '')));
      buttonEdit(true);
      return false;
    });
    
    commentSave.click(function() {
      if (commentCurrentId > 0) {
        var commentContentValue = commentContent.val().trim();
      
        if (commentContentValue.length > 0) {
          formVisible(false);

          $.post(url.commentSave, {id : commentCurrentId, content : commentContentValue}, function(data) {
            commentContent.val('').keyup().focus();

            if (jQuery.parseJSON(data)) {
              updateCommentListBySubject();
            }

            commentCurrentId = 0;
            buttonEdit(false);
          });
        } else {
          popupFieldIsEmpty.dialog('open');
        }
      }
      
      return false;
    });
    
    commentCancelEdit.click(function() {
      if (commentCurrentId > 0) {
        commentContent.val('').keyup().focus();
        commentCurrentId = 0;
        buttonEdit(false);
      }
      
      return false;
    });
    
    $(document).delegate('.j_deleteComment', 'click', function() {
      $.post(url.commentDelete, {id : $(this).attr('comment_id')}, function(data) {
        if (jQuery.parseJSON(data)) {
          updateCommentListBySubject();
        }
      });
      
      return false;
    });
    
    if (commentData['addOn']) {
      $(document).delegate('.comment', 'mouseenter', function() {
        var commentId = $(this).attr('comment_id');
        $('.j_editComment[comment_id=' + commentId + ']').show();
        $('.j_deleteComment[comment_id=' + commentId + ']').show();
      });

      $(document).delegate('.comment', 'mouseleave', function() {
        var commentId = $(this).attr('comment_id');
        $('.j_editComment[comment_id=' + commentId + ']').hide();
        $('.j_deleteComment[comment_id=' + commentId + ']').hide();
      });
    }
  }

  var popupSelectLeastOne = $('#j_popup_select_least_one');
  createPopup(popupSelectLeastOne);
  
  // Grupowe akcje dla tabel
  $('.j_group_action').click(function() {
    var form = $('form[name=CheckboxListForm]');
    
    if ($('.j_checkBoxId:checked').length === 0) {
      popupSelectLeastOne.dialog('open');
    } else {
      form.attr('action', $(this).attr('href'));
      form.submit();
    }
    
    return false;
  });
  
  // Select all checkboxes
  $('#j_selectAllCheckBoxes').change(function() {
    $('.j_checkBoxId').prop('checked', $(this).prop('checked'));
  });
  
  // Result count per page
  /*$('select[name=resultCountPerPage]').change(function() {
    $('form[name=filterForm]').submit();
  });*/
  
  //Filtrowanie tabel
  var filterButtonClick = function() {
    $('#filterAction').val('1');
    document.forms['filterForm'].submit();
  };
  
  $('#j_filterButton').click(filterButtonClick);
  
  $('#j_searchButton').click(filterButtonClick);
  
  $('#j_filterAndSaveButton').click(function() {
    $('#filterAction').val('2');
    document.forms['filterForm'].submit();
  });
  
  $('#j_restoreButton').click(function(){
    if (filterSavedValues != 'undefined') {
      for (var name in filterSavedValues) {
        var field = $('#' + name);
        var savedValue = filterSavedValues[name];
        var defaultValue = filterDefaultValues[name];

        if (typeof savedValue == 'boolean') {
          field.prop('checked', savedValue);
        } else if (savedValue.hasOwnProperty('type') && savedValue.type == 'tokenInput') {
          field.tokenInput('clear');
          
          for (var i in savedValue.values) {
            field.tokenInput('add', savedValue.values[i]);
          }          
        } else {      
          field.val(savedValue);
        }
        
        if (savedValue == defaultValue) {
          field.closest('.highlightFilter').removeClass('highlightFilter');
        } else {
          field.parent().addClass('highlightFilter');
        }        
      };
    }
  });
  
  $('#j_clearButton').click(function(){
    if (filterDefaultValues != 'undefined') {
      for (var name in filterDefaultValues) {
        var field = $('#' + name);
        var defaultValue = filterDefaultValues[name];
        
        if (typeof defaultValue == 'boolean') {
          field.prop('checked', false);
        } else if (defaultValue.hasOwnProperty('type') && defaultValue.type == 'tokenInput') {
          field.tokenInput('clear');
        } else {      
          field.val(defaultValue);
        }
        
        field.closest('.highlightFilter').removeClass('highlightFilter');
      };
    }
  });
  
  // Otwarcie linku w nowym oknie
  $('a.j_new_window').click(function() {
    window.open($(this).attr('href'));
    return false;
  });
  
  // Przeglądarka plików  
  const FILE_BROWSER_MODE_NORMAL = 0;
  const FILE_BROWSER_MODE_ATTACHMENT = 1;
  const FILE_BROWSER_MODE_PROJECT_PLAN = 2;
  const FILE_BROWSER_MODE_PROJECT_DOCUMENT = 3;
  
  var openFileBrowser = function(mode) {
    if (fileBrowserHandle === null) {
      var currentUrl = url.fileBrowser;
      
      if (mode > 0) {
        currentUrl += '/' + mode;
      }
      
      fileBrowserHandle = window.open(currentUrl, '', 'resizable=yes, width=1000, height=700');
      fileBrowserHandle.onbeforeunload  = function() { fileBrowserHandle = null; };
    } else {
      fileBrowserHandle.focus();
    }    
    /*
    var initFileBrowser = function() {
      if (!fileBrowserHandle.initFileBrowser) {
        setTimeout(initFileBrowser, 100);
      } else {
        fileBrowserHandle.initFileBrowser(selectFilesMode, onSelectFiles);
      }
    };
    
    initFileBrowser();*/
    
    var loop = setInterval(function() {
      if(fileBrowserHandle !== null && fileBrowserHandle.closed) {  
        clearInterval(loop);
        fileBrowserHandle = null;
        //alert('closed');  
      }  
    }, 1000); 

    return false;
  };
  
  $('.j_openFileBrowser').click(function() {
    openFileBrowser(FILE_BROWSER_MODE_NORMAL);
    return false;
  });
  
  // Dodawanie i usuwanie załączników  
  $('.j_attachmentId').each(function() {
    var maxIndex = getId($(this), 2);
    var id = $(this).val();
    var name = $('#attachmentNames-attachmentName_' + maxIndex).val();

    $('#j_attachmentBoxTmpl').tmpl([{
        'id': id,
        'name': name
      }]).appendTo('#j_attachments');
    selectedAttachments.push({ 'id': id, 'name': name });
  });
  
  $('.j_selectAttachments').click(function() {
    openFileBrowser(FILE_BROWSER_MODE_ATTACHMENT);
    return false;
  });
  
  $(document).on('click', '.j_removeAttachmentButton', function() {
    var id = getId($(this), 3);    
    var index = findSelectedFileIndex(id);
    
    if (index >= 0) {
      selectedAttachments.splice(index, 1);
      $(this).parent().remove();
      $('#attachmentIds-attachmentId_' + id).remove();
      $('#attachmentNames-attachmentName_' + id).remove();
    }
    
    return false;
  });
  
  // Dodawanie planu do projektu  
  $('#j_projectAddPlan').click(function() {
    openFileBrowser(FILE_BROWSER_MODE_PROJECT_PLAN);
    return false;
  });
  
  // Dodawanie dokument do projektu  
  $('#j_projectAddDocumentation').click(function() {
    openFileBrowser(FILE_BROWSER_MODE_PROJECT_DOCUMENT);
    return false;
  });
  // Przeglądarka plików - koniec

  // Komunikaty
  var infoBox = $('#j_info_box');
  if (infoBox.length > 0) {
    infoBox.slideDown();
    infoBox.children('.j_close_button').click(function() {
      infoBox.slideUp('slow');
    });
  }

  // Licznik znaków w textarea
  function showTextareaCounter(len, max) {
    if (typeof language.charactersLeft != 'undefined') {
      var x = max - len;
      if (x < 0) x = 0;
      return language.charactersLeft.replace('__left__', x).replace('__max__', max);
    }
    return false;
  }
  
  if (typeof language.charactersLeft != 'undefined') {
    $('textarea').each(function() {
      var max = $(this).attr('maxlength');
      var len = $(this).val().length;
      
      $(this).after('<span>' + showTextareaCounter(len, max) + '</span>');

      $(this).keyup(function(){
        var len = $(this).val().length;
        if (len > max) {
          $(this).val($(this).val().substring(0, max));
        }
        $(this).parent().children('span').text(showTextareaCounter(len, max));
      });
    });
  }
  
  // Przycisk anuluj
  var cancelButton = $('.j_cancel_button a');
  var backUrl = $('#backUrl0');

  if (cancelButton.length > 0 && backUrl.length > 0) {
    cancelButton.attr('href', backUrl.val());
  }
  
  //ajax - pobieranie listy projektów
  if (typeof url.projectListAjax != 'undefined') {
    $('#projects').tokenInput(url.projectListAjax, {
      theme: 'facebook',
      prePopulate: typeof prePopulated.projects != 'undefined' ? prePopulated.projects : null,
      tokenLimit: typeof projectTokenLimit != 'undefined' ? projectTokenLimit : null,
      hintText: language.enterPhraseSearched,
      noResultsText: language.noResults,
      searchingText: language.searching,
      minChars: 0,
      preventDuplicates: true
    });
    
    if (language.projectsPlaceholde != 'undefined') {
      $('#token-input-projects').attr('placeholder', language.projectsPlaceholder);
    }
  }

  //ajax - pobieranie listy środowisk
  var initEnvironmentTokenInput = function(id)
  {
    if (typeof url.environmentListAjax != 'undefined') {
         $('#'+id).tokenInput(url.environmentListAjax, {
          theme: 'facebook',
          prePopulate: typeof prePopulated.environments != 'undefined' ? prePopulated.environments : null,
          hintText: language.enterPhraseSearched,
          noResultsText: language.noResults,
          searchingText: language.searching,
          minChars: 0,
          preventDuplicates: true
        });
      }
  };
  
  initEnvironmentTokenInput('environments');
  initEnvironmentTokenInput('step2-stepTwo-environments');
  
  //ajax - pobieranie listy wersji
  var initVersionTokenInput = function(id)
  {
    if (typeof url.versionListAjax != 'undefined') {
      $('#'+id).tokenInput(url.versionListAjax, {
        theme: 'facebook',
        prePopulate: typeof prePopulated.versions != 'undefined' ? prePopulated.versions : null,
        hintText: language.enterPhraseSearched,
        noResultsText: language.noResults,
        searchingText: language.searching,
        minChars: 0,
        preventDuplicates: true
      });
    }
  };
  
  initVersionTokenInput('versions');
  initVersionTokenInput('step2-stepTwo-versions');
  
  //ajax - pobieranie listy tagów
  var initTagTokenInput = function(id)
  {
    if (typeof url.tagListAjax != 'undefined') {
      $('#'+id).tokenInput(url.tagListAjax, {
        theme: 'facebook',
        prePopulate: typeof prePopulated.tags != 'undefined' ? prePopulated.tags : null,
        hintText: language.enterPhraseSearched,
        noResultsText: language.noResults,
        searchingText: language.searching,
        minChars: 0,
        preventDuplicates: true,
        isModifiedInputTokenPosition: typeof isSearchTags != 'undefined' ? isSearchTags : false,
      });
    }
  };
  
  initTagTokenInput('tags');
  initTagTokenInput('step1-stepOne-tags');
  
  //ajax - pobieranie listy userów
  if (typeof url.userListAjax != 'undefined') {
    var users = $('#users');
    if (users.length > 0) {
      users.tokenInput(url.userListAjax, {
        theme: 'facebook',
        prePopulate: typeof prePopulated.users != 'undefined' ? prePopulated.users : null,
        propertyToSearch: 'name',
        hintText: language.enterPhraseSearched,
        noResultsText: language.noResults,
        searchingText: language.searching,
        minChars: 0,
        preventDuplicates: true
      });
      
      if (language.usersPlaceholder != 'undeifned') {
        $('#token-input-users').attr('placeholder', language.usersPlaceholder);
      }
    }

    // Edycja użytkowników we właściwościach projektu
    $('.j_editUsers').each(function() {
      $(this).click(function() {
        var id = getId($(this), 3);
        
        if (currentRoleUsersId != id) {
          if (currentRoleUsersId > 0) {
            cancelEditUsersProject();
          }

          var users = $('#j_users_' + id);
          var prePopulatedName = 'users_' + id;
          roleEditUsers = users.html().trim();

          users.html('<input type="text" value="" id="users" name="users" style="display: none;">');
          $('#users').tokenInput(url.userListAjax, {
            theme: 'facebook',
            prePopulate: ((typeof prePopulated[prePopulatedName] != 'undefined') ? prePopulated[prePopulatedName] : null),
            propertyToSearch: 'name',
            hintText: language.enterPhraseSearched,
            noResultsText: language.noResults,
            searchingText: language.searching,
            minChars: 0,
            preventDuplicates: true
          });

          currentRoleUsersId = id;
          $('#j_saveUsers_' + id).show();
          $('#j_cancelUsers_' + id).show();
          $('#j_editUsersButton_' + id).hide();
        }
        
        return false;
      });
    });
    
    var saveEditUsersProject = function() {
      $('#j_saveUsers_' + currentRoleUsersId).hide();
      $('#j_cancelUsers_' + currentRoleUsersId).hide();
      var htmlData = '';              
      var values = $('#users').tokenInput('get');
      prePopulated['users_' + currentRoleUsersId] = new Array();
      
      for (i in values) {
        htmlData += ' ' + values[i].name;
        prePopulated['users_' + currentRoleUsersId][i] = {'name': values[i].name, 'id': values[i].id};
      }

      $('#j_users_' + currentRoleUsersId).html(htmlData);
      $('#j_editUsersButton_' + currentRoleUsersId).show();
      currentRoleUsersId = 0;
    };
    
    var cancelEditUsersProject = function() {
      $('#j_saveUsers_' + currentRoleUsersId).hide();
      $('#j_cancelUsers_' + currentRoleUsersId).hide();
      $('#j_users_' + currentRoleUsersId).html(roleEditUsers);
      $('#j_editUsersButton_' + currentRoleUsersId).show();
      currentRoleUsersId = 0;
    };
    
    $('.j_saveUsers').each(function() {
      $(this).hide().click(function() {
        var id = getId($(this), 3);

        $.post($(this).attr('href'), {users : $('#users').val(), authtoken : $('#authtoken').val()}, function(data) {
          data = jQuery.parseJSON(data);
          if (typeof data.authtoken != 'undefined') {
            var authtoken = $('#authtoken');
            authtoken.val(data.authtoken);
          }
          
          switch (data.status) {
            case 'SUCCESS':
              saveEditUsersProject(id);
              break;
              
            case 'ERROR':
            default:
              showErrorMessages(data.errors.users);
              showErrorMessages(data.errors.authtoken);
              break;
          }
        });
        
        return false;
      });
    });
    
    $('.j_cancelUsers').each(function() {
      $(this).hide().click(function() {
        cancelEditUsersProject();
        return false;
      });
    });
  }
  
  // Yes/No Popup
  if (typeof popupYesNoName != 'undefined') {
    var names = new Array();
    
    if (!$.isArray(popupYesNoName)) {
      names.push(popupYesNoName);
    } else {
      names = popupYesNoName;
    }
    
    $.each(names, function(index, name) {
      var popupDelete = $('#j_popup_' + name);
      popupDelete.dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        width: 450,
        maxHeight: 600
      });

      $('.j_' + name).each( function() {
        var deleteObj = $(this);

        deleteObj.click(function() {
          if (deleteObj.attr('href') != '#') {
            popupDelete.dialog({
              buttons: [
                {
                  text: language['yes'],
                  click: function() {
                    $(this).dialog('close');
                    window.location = deleteObj.attr('href');
                  }
                },
                {
                  text: language['no'],
                  click: function() {
                    $(this).dialog('close');
                  }
                }
              ]
            });

            $('.box').hide();
            popupDelete.dialog('open');
          }
          
          return false;
        });
      });
    });
  }
  
  // Default language for date picker
  //$.datepicker.setDefaults($.datepicker.regional['pl']);

  // Start and end date
  var initStartEndDateDatepicker = function(startDate, endDate) {
      if (startDate.length > 0 && endDate.length > 0) {
        startDate.datepicker({
          showAnim: 'slideDown',
          dateFormat: 'yy-mm-dd',
          changeMonth: true,
          changeYear: true,
          onClose: function(selectedDate) {
            if (selectedDate != '') endDate.datepicker('option', 'minDate', selectedDate);
          }
        });

        endDate.datepicker({
          showAnim: 'slideDown',
          dateFormat: 'yy-mm-dd',
          changeMonth: true,
          changeYear: true,
          onClose: function(selectedDate) {
            if (selectedDate != '') startDate.datepicker('option', 'maxDate', selectedDate);
          }
        });
        
        var getDate = function(element) {
          var date;
          try {
            date = $.datepicker.parseDate(dateFormat, element.value);
          } catch(error) {
            date = null;
          }

          return date;
        };
        
        startDate.on('change', function() {
          endDate.datepicker('option', 'minDate', getDate(this));
        });
        endDate.on('change', function() {
          startDate.datepicker('option', 'maxDate', getDate(this));  
        });
        
        if (typeof minStartDate != 'undefined') {
          startDate.datepicker('option', 'minDate', minStartDate);
        }

        if (typeof maxStartDate != 'undefined') {
          startDate.datepicker('option', 'maxDate', maxStartDate);
        }

        if (typeof minEndDate != 'undefined') {
          endDate.datepicker('option', 'minDate', minEndDate);
        }

        if (typeof maxEndDate != 'undefined') {
          endDate.datepicker('option', 'maxDate', maxEndDate);
        }
      }
  }
  
  initStartEndDateDatepicker($('#startDate'), $('#endDate'));
  initStartEndDateDatepicker($('#step1-stepOne-startDate'), $('#step1-stepOne-endDate'));
  
  // Date
  var date = $('.j_date');
  if (date.length > 0) {

    date.datepicker({
      showAnim: 'slideDown',
      dateFormat: 'yy-mm-dd',
      changeMonth: true,
      changeYear: true
    });
    
    if (typeof minDate != 'undefined') {
      date.datepicker('option', 'minDate', minDate);
    }
    
    if (typeof maxDate != 'undefined') {
      date.datepicker('option', 'maxDate', maxDate);
    }
  }

  // Datetime
  var date = $('.j_datetime');
  if (date.length > 0) {
    var options = {
      showAnim: 'slideDown',
      dateFormat: 'yy-mm-dd',
      changeMonth: true,
      changeYear: true,
      timeFormat: 'HH:mm',
      hour: 23,
      minute: 59
    };
    
    if (typeof minDate != 'undefined') {
      options.minDate = minDate;
    }

    if (typeof maxDate != 'undefined') {
      options.maxDate = maxDate;
    }
    
    date.datetimepicker(options);
  }
  
  // Autocomplete list
  if (typeof autocompleteData != 'undefined') {
    $(autocompleteData.textInputName).autocomplete({
      source: function(request, response){
        $.post(autocompleteData.url, {q: request.term}, function(data) {
           response($.map($.parseJSON(data), function(item) {
            return {
              label: item.name,
              value: item.name,
              id: item.id
            };
          }));
        });
      },
      minLength: 0,
      select: function( event, ui ) {
        $(autocompleteData.dstName).val(ui.item.id);
      },
      change: function( event, ui ) {
        $(autocompleteData.textInputName).val( ui.item? ui.item.label : '' );
        $(autocompleteData.dstName).val( ui.item? ui.item.id : '' );
      }
    })
    .focusout(function() {
      if (!$(this).val()) {
        $(autocompleteData.dstName).val('');
      }
    });
  }
  
  // Autocomplete list 2
  if (typeof autocompleteData2 != 'undefined') {
    $(autocompleteData2.textInputName).autocomplete({
      source: function(request, response){
        $.post(autocompleteData2.url, {q: request.term}, function(data) {
           response($.map($.parseJSON(data), function(item) {
            return {
              label: item.name,
              value: item.name,
              id: item.id
            };
          }));
        });
      },
      minLength: 0,
      select: function( event, ui ) {
        $(autocompleteData2.dstName).val(ui.item.id);
      },
      change: function( event, ui ) {
        $(autocompleteData2.dstName).val( ui.item? ui.item.id : '' );
      }
    })
    .focusout(function() {
      if (!$(this).val()) {
        $(autocompleteData2.dstName).val('');
      }
    });
  }
  
  // Zarządzanie testami w zadaniu
  var getTaskTestViewUrl = function(type, id) {
    switch (type)
    {
      case '1':
        return url.taskOtherTestView.replace('/0', '/' + id);
        
      case '2':
        return url.taskTestCaseView.replace('/0', '/' + id);
        
      case '3':
        return url.taskExploratoryTestView.replace('/0', '/' + id);
        
      case '4':
        return url.taskAutomaticTestView.replace('/0', '/' + id);
        
      case '5':
        return url.taskChecklistView.replace('/0', '/' + id);
        
    };
  };
  
  $('#j_addTestToTask').click(function() {
    if ($('#j_testId').val() > 0) {
      $.post($(this).attr('href'), {testId : $('#j_testId').val()}, function(data) {
        var result = jQuery.parseJSON(data);
        switch (result.status) {
          case 'SUCCESS':
            if ($('#j_testContent').length === 0) {
              $('#j_taskTestItemsTmpl').tmpl().appendTo('#j_testBox');
            }
            
            var str = $('#j_testName').val();
            var index = str.indexOf(' ');
            $('#j_taskTestItemTmpl').tmpl([{
                'name': str.substr(index + 1, str.length - index - 1),
                'objectNumber': str.substr(0, index),
                'viewUrl': getTaskTestViewUrl(result.data.testType, result.data.taskTestId),
                'deleteUrl': url.deleteTestFromTask.replace('/0', '/' + result.data.taskTestId),
                'taskTestId': result.data.taskTestId
            }]).appendTo('#j_testList');
            $('#j_testId').val('');
            $('#j_testName').val('');
            break;

          case 'ERROR':
          default:
            showErrorMessages(result.errors);
            break;
        }
      });
    } else {
      alert(language.addTestToTaskMustFillField);
    }
    
    $('#j_testName').focus();
    return false;
  });
  
  $(document).on('click', '.j_deleteTestFromTask', function() {
    $.post($(this).attr('href'), function(data) {
      var result = jQuery.parseJSON(data);
        switch (result.status) {
          case 'SUCCESS':
            $('#j_testItem_' + result.data.taskTestId).remove();

            if ($('#j_testContent ul li').length === 0) {
              $('#j_testContent').remove();
            }
            break;
          
          case 'ERROR':
          default:
            showErrorMessages(result.errors);
            break;
        }
    });

    return false;
  });
  
  //ajax - pobieranie statusów defektów
  $('.j_defectStatus').each(function() {
    $.post(url.defectStatusAjax, {
        key: $(this).attr('key'), 
        name: $('.j_defectText[key=' + $(this).attr('key') + ']').html(), 
        id: $(this).attr('id')
      }, function(data) {
      var parsedData = $.parseJSON(data);
      var statusField = $('.j_defectStatus[key="' + parsedData.key + '"]');
      var summaryLinkField = $('.j_defectLink[key="' + parsedData.key + '"]');
      var summaryTextField = $('.j_defectText[key="' + parsedData.key + '"]');
      statusField.removeClass('smallLoader');
      
      if (parsedData.status == 'OK') {
        statusField.html(parsedData.data.status);
        summaryLinkField.find('a').html(parsedData.key + ' ' + parsedData.data.summary);
        summaryTextField.html(parsedData.key + ' ' + parsedData.data.summary);
      } else if (parsedData.status == 'NOT_EXISTS') {
        summaryLinkField.hide();
        summaryTextField.show();
        statusField.html(language.defectNotExists);
      }
    });
  });  
  
  // Lista autocomplete do defektów w zadaniu
  if (typeof autocompleteDataForDefect != 'undefined') {
    $(autocompleteDataForDefect.textInputName).autocomplete({
      source: function(request, response){
        $.post(autocompleteDataForDefect.url, {no: request.term}, function(data) {
           response($.map($.parseJSON(data), function(item) {
            return {
              label: item.name,
              value: item.name,
              id: item.id
            };
          }));
        });
      },
      minLength: autocompleteDataForDefect.minLength,
      select: function( event, ui ) {
        $(autocompleteDataForDefect.dstName).val(ui.item.id);
      },
      change: function( event, ui ) {
        $(autocompleteDataForDefect.dstName).val( ui.item ? ui.item.id : '' );
      }
    })
    .click(function() {
      $(autocompleteDataForDefect.textInputName).val(autocompleteDataForDefect.projectPrefix);
      $(autocompleteDataForDefect.dstName).val('');
    })
    .focusout(function() {
      if (!$(this).val()) {
        $(autocompleteDataForDefect.dstName).val('');
      }
      if ($(autocompleteDataForDefect.textInputName).val() == autocompleteDataForDefect.projectPrefix) {
        $(autocompleteDataForDefect.textInputName).val('');
      }
    });
  }
  
  // Zarządzanie defektami w zadaniu
  $('#j_addDefectToTask').click(function() {
    if ($('#j_defectId').val() > 0) {
      $.post($(this).attr('href'), {defectId: $('#j_defectId').val()}, function(data) {
        var result = jQuery.parseJSON(data);
        switch (result.status) {
          case 'SUCCESS':
            if ($('#j_defectContent').length === 0) {
              $('#j_taskDefectItemsTmpl').tmpl().appendTo('#j_defectBox');
            }
            
            if (result.data.defectType == defectTypeInternal) {
              $('#j_taskDefectInternalItemTmpl').tmpl([{
                'name': result.data.name,
                'objectNumber': result.data.objectNumber,
                'status': result.data.status,
                'rowStatus': result.data.rowStatus,
                'viewUrl': url.taskDefectView.replace(new RegExp("0$","m"), result.data.id),
                'deleteUrl': url.deleteDefectFromTask.replace(new RegExp("0$","m"), result.data.id),
                'id': result.data.id
              }]).appendTo('#j_defectList');
            } else {
              $('#j_taskDefectItemTmpl').tmpl([{
                'name': result.data.name,
                'objectNumber': result.data.objectNumber,
                'status': result.data.status,
                'viewUrl': url.taskDefectView.replace(new RegExp("0$","m"), result.data.id),
                'deleteUrl': url.deleteDefectFromTask.replace(new RegExp("0$","m"), result.data.id),
                'id': result.data.id
              }]).appendTo('#j_defectList');
            }
            
            $('#j_defectId').val('');
            $('#j_defectName').val('');
            break;

          case 'ERROR':
          default:
            showErrorMessages(result.errors);
            break;
        }
      });
    } else {
      alert(language.addDefectToTaskMustFillField);
    }
    
    $('#j_defectName').focus();
    return false;
  });
  
  $(document).on('click', '.j_deleteDefectFromTask', function() {
    $.post($(this).attr('href'), function(data) {
      var result = jQuery.parseJSON(data);
        switch (result.status) {
          case 'SUCCESS':
            $('#j_defectItem_' + result.data.id).remove();

            if ($('#j_defectContent ul li').length === 0) {
              $('#j_defectContent').remove();
            }
            break;
          
          case 'ERROR':
          default:
            showErrorMessages(result.errors);
            break;
        }
    });

    return false;
  });
  
  // Active project
  var activeProject = $('#activeProject');
  if (activeProject.length > 0) {
    activeProject.change(function() {
      $('form[name=project]').submit();
    });
  }  
  
  if ($('.j_projectsForm').length)
  {
    $('.j_projectsFormBussy').hide();
    $('.j_projectsForm').show();  
  }

  // array_map
  if (!Array.prototype.map) {
    Array.prototype.map = function(fun) {
      var len = this.length;
      if (typeof fun != 'function')
        throw new TypeError();

      var res = new Array(len);
      var thisp = arguments[1];
      for (var i = 0; i < len; i++) {
        if (i in this) {
          res[i] = fun.call(thisp, this[i], i, this);
        }
      }

      return res;
    };
  }
  
  // Przypisz do mnie
  var assignToMe = $('#j_assignToMe');
  var assigneeId = $('#assigneeId');
  var assigneeName = $('#assigneeName');
  
  if (typeof currentUser != 'undefined' && assignToMe.length && assigneeId.length && assigneeName.length) {
    assignToMe.click(function() {
      assigneeId.val(currentUser.id);
      assigneeName.val(currentUser.name);
      return false;
    });
  }
  
  // Wyczyszczenie pola assigneeName po kliknięciu
  $('#assigneeName').click(function(){
    $(this).val('');
  });
  
  // Autocomplete release and phase for add/edit task  
  if (typeof autocompleteRelease != 'undefined') {
    $(autocompleteRelease.textInputName).autocomplete({
      source: function(request, response){
        $.post(autocompleteRelease.url, {q: request.term}, function(data) {
           response($.map($.parseJSON(data), function(item) {
            return {
              label: item.name,
              value: item.name,
              id: item.id,
              startDate: item.startDate,
              endDate: item.endDate
            };
          }));
        });
      },
      minLength: 0,
      select: function( event, ui ) {
        $(autocompleteRelease.dstName).val(ui.item.id);
      },
      change: function( event, ui ) {
        var nowDate = new Date();
        
        if (ui.item !== null) {
          $(autocompleteRelease.dstName).val( ui.item? ui.item.id : '' );

          /*var minDateTime = Date.parse(ui.item.startDate);
          var nowDateTime = (new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0)).getTime();

          if (minDateTime < nowDateTime) {
            $('#dueDate').datetimepicker('option', 'minDate', nowDate);
          } else {
            $('#dueDate').datetimepicker('option', 'minDate', ui.item.startDate); 
          }*/

          $('#dueDate').datetimepicker('option', 'minDate', ui.item.startDate); 
          $('#dueDate').datetimepicker('option', 'maxDate', ui.item.endDate);
        } else {
          $('#dueDate').datetimepicker('option', 'minDate', null); 
          $('#dueDate').datetimepicker('option', 'maxDate', null);
        }
      }
    })
    .focusout(function() {
      if (!$(this).val()) {
          $(autocompleteRelease.dstName).val('');
      }
    });
  }
  
  // number picker
  $('#duration').spinner({
    min: 0,
    max: 999999999,
    allowNull: true,
    step: 5
  }).on('#duration', function () {
     var val = this.value,
         $this = $(this),
         max = $this.spinner('option', 'max'),
         min = $this.spinner('option', 'min');
         if (!val.match(/^\d+$/)) val = 0; //we want only number, no alpha
     this.value = val > max ? max : val < min ? min : val;
  });
  
  
  $('.color').spectrum({
    preferredFormat: "hex",
    showInput: true,
    showPaletteOnly: true,
    togglePaletteOnly: true,
    allowEmpty: true,
    palette: [
      ["#000","#444","#666","#999","#ccc","#eee","#f3f3f3","#fff"],
      ["#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f"],
      ["#f4cccc","#fce5cd","#fff2cc","#d9ead3","#d0e0e3","#cfe2f3","#d9d2e9","#ead1dc"],
      ["#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd"],
      ["#e06666","#f6b26b","#ffd966","#93c47d","#76a5af","#6fa8dc","#8e7cc3","#c27ba0"],
      ["#c00","#e69138","#f1c232","#6aa84f","#45818e","#3d85c6","#674ea7","#a64d79"],
      ["#900","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47"],
      ["#600","#783f04","#7f6000","#274e13","#0c343d","#073763","#20124d","#4c1130"]
    ],
    hideAfterPaletteSelect: true
  });

  // Admin - role
  
  
  // Role settings - apply default role
  if (typeof defaultRoleTypesSettings != 'undefined') {
    autoCheck();
    changeRoleType();
    
    $('#rsMCheck').click(function() {
      checkAll( this.id, 'roleSettings' );
    });
    
    $('select[id=type]').change(function () {
      applyDefaultRoleSettings(defaultRoleTypesSettings[$('select option:selected').val()]);
      autoCheck();
    });  
  }
  
  if (typeof defaultRoleTypesSettings != 'undefined' && typeof defaultRoleTypes != 'undefined') {
    $('INPUT[name^=roleSettings][type="checkbox"], #rsMCheck').change(function () {
      changeRoleType();
      autoCheck();

    });  
  }
  // Admin - role - end
  
  // MultiSelect - start
  if (typeof multiSelectConfig != 'undefined') {
    function MultiSelect(name) {
      var _name = name;
      var _items = new Array();
      var _allItems = Array();
        
      for (var i = 0; i < multiSelectConfig.allIds.length; i++) {
        _allItems.push({ 
          id: multiSelectConfig.allIds[i],
          checked: true
        });
      }

      var _getItemId = function(checkBox) {
        var parts = checkBox.attr('name').split('_');

        var ids = $.map(parts, function(part) {
          if ($.isNumeric(part)) {
            return parseInt(part);
          }
        });

        return ids.length > 0 ? ids[0] : 0;
      };

      var _getItem = function(id) {
        var indexes = $.map(_items, function(item, index) {
          if (item.id == id) {
            return index;
          }
        });

        return indexes.length > 0 ? _items[indexes[0]] : null;
      };

      var _setSelectAllCheckBox = function() {
        var checked = $('.j_multiSelect_item[value="' + _name + '"]:checked').length;
        var total = $('.j_multiSelect_item[value="' + _name + '"]').length;

        if (checked > 0 && checked == total) {
          $('.j_multiSelect_selectAll[value="' + _name + '"').prop('checked', true);
        } else {
          $('.j_multiSelect_selectAll[value="' + _name + '"').prop('checked', false);
        }
      };

      var _setButtons = function(numberOfSelected) {
        var sendButton = $('.j_multiSelect_sendButton_' + _name);
        var container = $('#j_multiSelect_couterContainer');
        var unselectAllButton = $('#j_multiSelect_unselectAllButton_' + _name);
        var selectAllButton = $('#j_multiSelect_selectAllButton_' + _name);
        
        if (_allItems.length > numberOfSelected) {
          selectAllButton.removeClass('disabled');
        } else {
          selectAllButton.addClass('disabled');
        }

        if (numberOfSelected == 0) {
          if (sendButton.attr('href') != '#') {
            sendButton
              .data('href', sendButton.attr('href'))
              .attr('href', '#')
              .addClass('disabled');
          }

          unselectAllButton.addClass('disabled');
          container.hide();
        } else {
          if (sendButton.attr('href') == '#') {
            sendButton
              .attr('href', sendButton.data('href'))
              .removeClass('disabled');
          }

          unselectAllButton.removeClass('disabled');
          container.find('#j_multiSelect_couterValue').text(numberOfSelected);
          container.show();
        }
      };

      // Init
      $.post(multiSelectConfig.urls.load, { name : _name }, function(response) {
        var data = $.parseJSON(response);

        if (data.status != 'undefined' && data.status == 'OK') {
          _items = data.items;

          $('.j_multiSelect_item[value="' + _name + '"]').each(function() {
            var id = _getItemId($(this));
            var item = _getItem(id);

            if (item != null) {
              $(this).prop('checked', item.checked);
            } else {
              var newItem = { id: id, checked: false };
              _items.push(newItem);
            }
          });

          _setSelectAllCheckBox();
          _setButtons(data.numberOfSelected);
        }
      });

      // Select item on page
      $('.j_multiSelect_item[value="' + _name + '"]').change(function() {      
        var checkBox = $(this);
        var item = _getItem(_getItemId(checkBox));
        var checked = checkBox.prop('checked');

        if (item.checked != checked) {
          $.post(multiSelectConfig.urls.save, { name : _name, items: JSON.stringify([{ id: item.id, checked: checked }]) }, function(response) {
            var data = $.parseJSON(response);

            if (data.status != 'undefined' && data.status == 'OK') {
              item.checked = checked;
              _setSelectAllCheckBox();
              _setButtons(data.numberOfSelected);
            } else {
              checkBox.prop('checked', !checked);
            }
          });
        }
      });

      // Select all items on page
      $('.j_multiSelect_selectAll[value="' + _name + '"]').change(function() {
        var checked = $(this).prop('checked');
        var checkBoxes = new Array();
        var changedItems = new Array();

        $('.j_multiSelect_item[value="' + _name + '"]').each(function(){
          var item = _getItem(_getItemId($(this)));

          if (item.checked != checked) {
            item.checked = checked;
            changedItems.push(item);
            checkBoxes.push($(this));
          }
        });

        if (changedItems.length > 0) {
          $.post(multiSelectConfig.urls.save, { name : _name, items: JSON.stringify(changedItems) }, function(response) {
            var data = $.parseJSON(response);

            if (data.status != 'undefined' && data.status == 'OK') {
              for (i = 0; i < checkBoxes.length; i++) {
                checkBoxes[i].prop('checked', checked);
              };

              _setSelectAllCheckBox();
              _setButtons(data.numberOfSelected);
            } else {
              for (i = 0; i < changedItems.length; i++) {
                changedItems[i].checked = !checked;
              };
            }
          });
        }
      });

      // Select all
      $('#j_multiSelect_selectAllButton_' + _name).click(function() {
        $.post(multiSelectConfig.urls.save, { name : _name, items: JSON.stringify(_allItems) }, function(response) {
          var data = $.parseJSON(response);

          if (data.status != 'undefined' && data.status == 'OK') {
            $('.j_multiSelect_item[value="' + _name + '"]').each(function(){
              var id = _getItemId($(this));
              var item = _getItem(id);

              if (item != null) {
                item.checked = true;
              } else {
                _items.push({ id: id, checked: true });
              }
              
              $(this).prop('checked', true);
            });

            _setSelectAllCheckBox();
            _setButtons(data.numberOfSelected);
          }
        });

        return false;
      }); 

      // Unselect all
      $('#j_multiSelect_unselectAllButton_' + _name).click(function() {
        $.post(multiSelectConfig.urls.clear, { name : _name }, function(response) {
          var data = $.parseJSON(response);

          if (data.status != 'undefined' && data.status == 'OK') {
            $('.j_multiSelect_item[value="' + _name + '"]').each(function() {
              var id = _getItemId($(this));
              var item = _getItem(id);

              if (item != null) {
                item.checked = false;
                $(this).prop('checked', false);
              } else {
                var newItem = { id: id, checked: false };
                _items.push(newItem);
              }
            });

            _setSelectAllCheckBox();
            _setButtons(data.numberOfSelected);
          }
        });

        return false;
      }); 
    }

    $('.j_multiSelect_selectAll').each(function() {
      new MultiSelect($(this).attr('value'));
    });
  }
  // MultiSelect - end
  
  // Checklist items
  var checklistItems = $('#j_checklistItems');
  var checklistItemTmpl = $('#j_checklistItemTmpl');
  var nextChecklistIndex = 0;
  
  if (checklistItems.length > 0) {
    /**
     * Ustalenie następnego indeksu dla listy kontrolnej
     */
    checklistItems.find('.j_checklistItem').each(function() {
      var index = getId($(this), 3);
      
      if ($.isNumeric(index) && index >= nextChecklistIndex) {
        nextChecklistIndex = Number(index) + 1;
      }
    });
    
    /**
     * Obsługa przycisku dodawania elementu do listy kontrolenj
     */
    var addChecklistItem = function() {
      checklistItemTmpl.tmpl([{
        'index': nextChecklistIndex,
        'id': 0,
        'name': ''
      }]).appendTo('#j_checklistItems');
      $('#itemName_' + nextChecklistIndex).focus();
      nextChecklistIndex++;
      return false;
    };
    
    $('#j_addChecklistItemButton').click(addChecklistItem);
    
    $(document).on('keypress', '.j_checklistItemName', function(e) {
      if (e.keyCode == 13) {
        addChecklistItem();
        e.preventDefault();
        return false;
      }
    });
  
    /**
     * Obsługa przycisku usuwania elementu z listy kontrolnej
     */
    $(document).on('click', '.j_removeChecklistItemButton', function() {
      var index = getId($(this), 3);    

      if (index >= 0) {
        $(this).parent().remove();
      }

      return false;
    });
  }
  
  // LightBox
  lightbox.option({
    'resizeDuration': 400,
    'albumLabel': language.lightBoxLabel,
    'disableScrolling': true
  });
  
  //clone release
  if (typeof cloneStep2 != 'undefined' && cloneStep2 === true)
  {
      var sortableListSelector = '#jSortableTasks .sortable-list';
      var sortableListSelectedSelector = '#jSortableTasks .sortable-list-selected';
      
      var cloneOptionalFieldEnable = function(element) {
        element.prop('disabled', false);
        element.parent().parent().show('slow');
      }

      var cloneOptionalFieldDisable = function(element) {
        element.prop('disabled', true).val('');
        element.parent().parent().hide('slow');
      }

      var cloneAllOptionalFieldsDisable = function() {
        $('.cloneOptionalField').each(function() {
          cloneOptionalFieldDisable($('#'+$(this).prop('id')));
        });
      }
      
      var cloneAllOptionalFieldsEnable = function() {
        $('.cloneOptionalField').each(function() {
          cloneOptionalFieldEnable($('#'+$(this).prop('id')));
        });
      }
      
      var cloneAllOptionalFieldsToggleVisibility = function()
      {
        if ($(sortableListSelectedSelector+' li').length > 0)
        {
          cloneAllOptionalFieldsEnable();
        }
        else
        {
          cloneAllOptionalFieldsDisable();  
        }
      }
      
      $(sortableListSelector).sortable({
        connectWith: sortableListSelectedSelector,
        placeholder: 'sortablePlaceholder',
        receive: function (event, ui) {
          ui.item.children("input:first").prop('disabled', 'disabled');
          
          cloneAllOptionalFieldsToggleVisibility();
        },
        forcePlaceholderSize:true
      });
      $(sortableListSelectedSelector).sortable({
        connectWith: sortableListSelector,
        placeholder: 'sortablePlaceholder',
        receive: function (event, ui) {
          ui.item.children("input:first").prop('disabled', false);
          
          cloneAllOptionalFieldsToggleVisibility();
        },
        forcePlaceholderSize:true
      });

      $('.move-all').click(function() {
        $(sortableListSelector + ' li input').prop('disabled', false);
        $(sortableListSelector + ' li').appendTo(sortableListSelectedSelector);
        cloneAllOptionalFieldsEnable();
        return false;
      });

      $('.cancel-all').click(function() {
        $(sortableListSelectedSelector + ' li input').prop('disabled', 'disabled');
        $(sortableListSelectedSelector + ' li').appendTo(sortableListSelector);
        cloneAllOptionalFieldsDisable();  
        return false;
      });
  }
  //clone release - end
});

function checkAll(id, name)
{
  $('INPUT[name^=' + name + '][type="checkbox"]').prop('checked', $('#' + id).is(':checked'));
}

function applyDefaultRoleSettings(settings)
{
  $('INPUT[name^=roleSettings][type="checkbox"]').prop('checked', false);
  
  var settingsLength = settings.length;
  
  if (settingsLength !== 0)
  {
    for (var i = 0; i < settingsLength; i++)
    {
      $('INPUT[name^=roleSettings][type="checkbox"][id$=_' + settings[i] + ']').prop('checked', true);
    }
  }
}

function changeRoleType()
{
  var checkedRoleSettings = $('INPUT[name^=roleSettings][type="checkbox"]:checked');
  var equalDefaultLength = false;
  
  for (var key in defaultRoleTypesSettings) {
    if (checkedRoleSettings.length == defaultRoleTypesSettings[key].length) {
      equalDefaultLength = true;
    }
  }
  
  if (true == equalDefaultLength) {
    var defaultRoleIndex = checkIfRoleIsDefault(checkedRoleSettings);
    
    if (false !== defaultRoleIndex) {
      $('select[id=type]').val(defaultRoleIndex);
    } else {
      $('select[id=type]').val(defaultRoleTypes['CUSTOM']);
    }
  } else {
    $('select[id=type]').val(defaultRoleTypes['CUSTOM']);
  }
}

function checkIfRoleIsDefault(checkedRoleSettings)
{
  for (var key in defaultRoleTypes) {
    if (defaultRoleTypesSettings[key].length == checkedRoleSettings.length) {
      var status = true;
      checkedRoleSettings.each(function() {
        if ($.inArray(parseInt($(this).val()), defaultRoleTypesSettings[key]) < 0) {
          status = false;
        }
      });

      if (true == status) {
        return key;
      }
    }
  }
  
  return false;
}

function autoCheck()
{
  $('#rsMCheck').prop('checked', $('INPUT[name^=roleSettings][type="checkbox"]').length === $('INPUT[name^=roleSettings][type="checkbox"]:checked').length);
}

(function( $ ) {
		$.widget( 'ui.combobox', {
			_create: function() {
				var input,
					self = this,
					select = this.element.hide(),
					selected = select.children( ':selected' ),
					value = selected.val() ? selected.text() : '',
					wrapper = this.wrapper = $( '<span>' )
						.addClass( 'ui-combobox' )
						.insertAfter( select );

				input = $( '<input>' )
					.appendTo( wrapper )
					.val( value )
					.addClass( 'ui-state-default ui-combobox-input' )
					.autocomplete({
						delay: 0,
						minLength: 0,
						source: function( request, response ) {
							var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), 'i' );
							response( select.children( 'option' ).map(function() {
								var text = $( this ).text();
								if ( this.value && ( !request.term || matcher.test(text) ) )
									return {
										label: text.replace(
											new RegExp(
												'(?![^&;]+;)(?!<[^<>]*)(' +
												$.ui.autocomplete.escapeRegex(request.term) +
												')(?![^<>]*>)(?![^&;]+;)', 'gi'
											), '<strong>$1</strong>' ),
										value: text,
										option: this
									};
							}) );
						},
						select: function( event, ui ) {
							ui.item.option.selected = true;
							self._trigger( 'selected', event, {
								item: ui.item.option
							});
						},
						change: function( event, ui ) {
							if ( !ui.item ) {
								var matcher = new RegExp( '^' + $.ui.autocomplete.escapeRegex( $(this).val() ) + '$', 'i' ),
									valid = false;
								select.children( 'option' ).each(function() {
									if ( $( this ).text().match( matcher ) ) {
										this.selected = valid = true;
										return false;
									}
								});
								if ( !valid ) {
									// remove invalid value, as it didn't match anything
									$( this ).val( '' );
									select.val( '' );
									input.data( 'autocomplete' ).term = '';
									return false;
								}
							}
						}
					})
					.addClass( 'ui-widget ui-widget-content ui-corner-left' );

				input.data( 'autocomplete' )._renderItem = function( ul, item ) {
					return $( '<li></li>' )
						.data( 'item.autocomplete', item )
						.append( '<a>' + item.label + '</a>' )
						.appendTo( ul );
				};

				$( '<a>' )
					.attr( 'tabIndex', -1 )
					.attr( 'title', 'Show All Items' )
					.appendTo( wrapper )
					.button({
						icons: {
							primary: 'ui-icon-triangle-1-s'
						},
						text: false
					})
					.removeClass( 'ui-corner-all' )
					.addClass( 'ui-corner-right ui-combobox-toggle' )
					.click(function() {
						// close if already visible
						if ( input.autocomplete( 'widget' ).is( ':visible' ) ) {
							input.autocomplete( 'close' );
							return;
						}

						// work around a bug (likely same cause as #5265)
						$( this ).blur();

						// pass empty string as value to search for, displaying all results
						input.autocomplete( 'search', '' );
						input.focus();
					});
			},

			destroy: function() {
				this.wrapper.remove();
				this.element.show();
				$.Widget.prototype.destroy.call( this );
			}
		});
	})( jQuery );
  
function createPopup(popup, autoOpen, width) {
  popup.dialog({
    autoOpen: autoOpen,
    modal: true,
    resizable: false,
    width: width,
    maxHeight: 600,
    open: function(event, ui) {
      disable_scroll();
      
      // Wycentrownie okienka
      var t = $(this).parent();
      t.offset({
        top: ($(window).height() / 2) - (t.height() / 2),
        left: ($(window).width() / 2) - (t.width() / 2)
      });
    },
    close: function(event, ui) {enable_scroll()}
  });
}

function htmlspecialchars_decode(string, quote_style) {
  var optTemp = 0,
    i = 0,
    noquotes = false;
  if (typeof quote_style === 'undefined') {
    quote_style = 2;
  }
  string = string.toString()
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');
  var OPTS = {
    'ENT_NOQUOTES': 0,
    'ENT_HTML_QUOTE_SINGLE': 1,
    'ENT_HTML_QUOTE_DOUBLE': 2,
    'ENT_COMPAT': 2,
    'ENT_QUOTES': 3,
    'ENT_IGNORE': 4
  };
  if (quote_style === 0) {
    noquotes = true;
  }
  if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
    quote_style = [].concat(quote_style);
    for (i = 0; i < quote_style.length; i++) {
      // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
      if (OPTS[quote_style[i]] === 0) {
        noquotes = true;
      } else if (OPTS[quote_style[i]]) {
        optTemp = optTemp | OPTS[quote_style[i]];
      }
    }
    quote_style = optTemp;
  }
  if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
    string = string.replace(/&#0*39;/g, '"'); // PHP doesn't currently escape if more than one 0, but it should
    // string = string.replace(/&apos;|&#x0*27;/g, '''); // This would also be useful here, but not a part of PHP
  }
  if (!noquotes) {
    string = string.replace(/&quot;/g, '"');
  }
  // Put this in last place to avoid escape being double-decoded
  string = string.replace(/&amp;/g, '&');

  return string;
}

  // Popup whit OK
  var createPopup = function(popup) {
    popup.dialog({
      autoOpen: false,
      resizable: false,
      modal: true,
      width: 450,
      maxHeight: 600,
      buttons: [{
        text: language['ok'],
        click: function() {
          $(this).dialog('close');
        }
      }]
    });  
  };

var showErrorMessages = function(messages) {
  if (messages.length > 0) {
    for (key in messages) {
      alert(messages[key]);
    }
  }
};


  
  // Usuwanie załacznika z projektu
  $(document).on('click', '.j_projectDeleteAttachment', function() {
    var deletedObject = $(this);
    
    var popupDelete = $('#j_popup_delete_attachment');
    popupDelete.dialog({
      autoOpen: false,
      resizable: false,
      modal: true,
      width: 450,
      maxHeight: 600 
    });

    popupDelete.dialog({
      buttons: 
      [
        {
          text: language['yes'],
          click: function() {
            $(this).dialog('close');

            $.post(deletedObject.attr('href'), function(data) {
              data = jQuery.parseJSON(data);
              switch (data.status) {
                case 'SUCCESS':
                  window.location = url.current;
                  break;

                case 'ERROR':
                default:
                  showErrorMessages(data.errors);
                  break;
              }
            });
          }
        },
        {
          text: language['no'],
          click: function() {
            $(this).dialog('close');
          }
        }
      ]
    });

    $('.box').hide();
    popupDelete.dialog('open');
    return false;
  }); 
  
  // Uruachamianie funkcji zwrotnej
  var findSelectedFileIndex = function(id) {
    var indexes = $.map(selectedAttachments, function(obj, index){
      if (obj.id == id) {
        return index;
      }
    });

    return indexes.length > 0 ? indexes[0] : -1;
  };
  
  var addAttachmentsFileBrowserSelectFiles = function(fileNames) {
    for (var i in fileNames) {
      if (findSelectedFileIndex(fileNames[i].id) < 0) {
        $('#j_fullAttachmentObjectTmpl').tmpl([{
            'id': fileNames[i].id,
            'name': fileNames[i].fullname
          }]).appendTo('#j_attachments');
        selectedAttachments.push({ 'id': fileNames[i].id, 'name': fileNames[i].fullname });
      }
    }
  };
  
  var projectAddPlanFileBrowserSelectFiles = function(fileNames) {
    var ids = [];
    
    for (var i in fileNames) {
      ids[ids.length] = fileNames[i].id;
    }
     
    $.post($('#j_projectAddPlan').attr('href'), {ids : ids.join('_')}, function(data) {
      data = jQuery.parseJSON(data);
      switch (data.status) {
        case 'SUCCESS':
          window.location = url.current;
          break;

        case 'ERROR':
        default:
          $('ul.box').hide();
          showErrorMessages(data.errors);
          break;
      }
    });
  };
  
  var projectAddDocumentationFileBrowserSelectFiles = function(fileNames) {
    var ids = [];
    
    for (var i in fileNames) {
      ids[ids.length] = fileNames[i].id;
    }

    $.post($('#j_projectAddDocumentation').attr('href'), {ids : ids.join('_')}, function(data) {
      data = jQuery.parseJSON(data);
      switch (data.status) {
        case 'SUCCESS':
          window.location = url.current;
          break;

        case 'ERROR':
        default:
          $('ul.box').hide();
          showErrorMessages(data.errors);
          break;
      }
    });
  };
  
  var onSelectFilesInFileBrowser = function(mode, fileNames) {
    if (mode == 1) {
      addAttachmentsFileBrowserSelectFiles(fileNames);
    } else if (mode == 2) {
      projectAddPlanFileBrowserSelectFiles(fileNames);
    } else if (mode == 3) {
      projectAddDocumentationFileBrowserSelectFiles(fileNames);
    }
  };