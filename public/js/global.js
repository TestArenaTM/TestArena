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
var language = new Object();
var url = new Object();
var prePopulated = new Object();
var roleEditUsers = '';
var currentRoleUsersId = 0;
var currentLanguage = 'pl';
var fileBrowserHandle = null;

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

  // Phase gantt
  if (typeof phaseGanttSource != 'undefined') {
    $('#j_phaseGantt').gantt({
      source: phaseGanttSource,
      navigate: 'scroll',
      scale: 'days',
      maxScale: 'months',
      minScale: 'days',
      itemsPerPage: 10,
      useCookie: false,
      months: language.months[currentLanguage],
      dow: language.days[currentLanguage],
      onItemClick: function(data) {
        document.location = data.url;
      }
    });
  }
  
  // Funkcja do pobirania id
  var getId = function(element, elementNo) {
    var buf = element.attr('id').split('_');
    if (buf.length == elementNo) {
      return buf[elementNo - 1];
    }
    return 0;
  };
  
  // Export project
  var exportReleases = $('#exportReleases');
  var exportPhases = $('#exportPhases');
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
  
  if (exportReleases.length && exportPhases.length) {
    exportReleases.change(function() {
      if (!$(this).is(':checked')) {
        exportPhases.prop('checked', false);
      }
    });

    exportPhases.change(function() {
      if ($(this).is(':checked')) {
        exportReleases.prop('checked', true);
      }
    });
  }
  
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
  // <select>
  $('.filter select option[value!=0][selected=selected]').each(function() {
    var parentSelect = $(this).parent();
    
    if (parentSelect.attr('id') !== 'resultCountPerPage') {
      parentSelect.parent('div').addClass('highlightFilter');
    }
  });
  
  $('.filter select').change(function() {
    if ($(this).attr('id') !== 'resultCountPerPage') {
      if ($(this).val() != '0') {
        $(this).parent('div').addClass('highlightFilter');
      } else {
        $(this).parent('div').removeClass('highlightFilter');
      }
    }
  });

  // <input type='text'>
  $('.filter input[type=text][value!=]').each(function() {
    $(this).addClass('highlightFilter');
  });
  
  $('.filter input[type=text]').keyup(function() {
    if ($(this).val().length) {
      $(this).addClass('highlightFilter');
    } else {
      $(this).removeClass('highlightFilter');
    }
  });

  // <input type='checkbox'>
  $('.filter input[type=checkbox]:checked').each(function() {
    $(this).parent('div').addClass('highlightFilter');
  });
  
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
  
  if (typeof commentData != 'undefined' && commentList.length) {
    var popupFieldIsEmpty = $('#j_popup_field_is_empty');
    createPopup(popupFieldIsEmpty);
    
    // funkcje pomocnicze
    var updateCommentListBySubject = function() {
      $.post(url.commentListBySubject, {subjectId : commentData.subjectId, subjectType : commentData.subjectType}, function(data) {
        commentList.html('');
        var items = jQuery.parseJSON(data);
        $('#j_commentItem').tmpl(items).appendTo(commentList);
        formVisible(true);

        if (commentData['addOn'] || (!commentData['addOn'] && items.length)) {
          $('#comments').show();
        }
      });
    };
    
    var formVisible = function(visible) {
      if (visible) {
        if (commentData['addOn']) {
          commentForm.show();
        }
        commentIsBusy.hide();
      } else {
        if (commentData['addOn']) {
          commentForm.hide();
        }
        commentIsBusy.show();
      }
    };
    
    var buttonEdit = function(enable) {
      if (enable && commentData['addOn']) {
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
    commentAdd.click(function() {
      var commentContentValue = commentContent.val().trim();
      
      if (commentContentValue.length > 0) {
        formVisible(false);

        $.post(url.commentAdd, {subjectId : commentData.subjectId, subjectType : commentData.subjectType, content : commentContentValue}, function(data) {
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
  $('#j_filterButton').click(function() {
    document.forms['filterForm'].submit();
  });
  $('#j_searchButton').click(function() {
    document.forms['filterForm'].submit();
  });
  
  // Otwarcie linku w nowym oknie
  $('a.j_new_window').click(function() {
    window.open($(this).attr('href'));
    return false;
  });
  
  // Przeglądarka plików
  var selectedIds = [];
  var selectedNames = [];
  var maxIndex = 0;
  
  $('.j_attachmentId').each(function() {
    maxIndex = getId($(this), 2);
    var id = $(this).val();
    var name = $('#attachmentNames-attachmentName_' + maxIndex).val();

    $('#j_attachmentBoxTmpl').tmpl([{
        'index': maxIndex, 
        'id': id,
        'name': name
      }]).appendTo('#j_attachments');
    selectedNames[maxIndex] = name;
    selectedIds[maxIndex++] = id;
  });

  var fileBrowserSelectFiles = function(fileNames) {
    for (var i in fileNames) {
      if (selectedIds.indexOf(fileNames[i].id) === -1) {
        $('#j_fullAttachmentObjectTmpl').tmpl([{
            'index': maxIndex,
            'id': fileNames[i].id,
            'name': fileNames[i].fullname
          }]).appendTo('#j_attachments');
        selectedNames[maxIndex] = fileNames[i].fullname;
        selectedIds[maxIndex++] = fileNames[i].id;
      }
    }
  };
  
  $(document).on('click', '.j_removeAttachmentButton', function() {
    var index = getId($(this), 3);
    selectedIds.splice(index, 1);
    selectedNames.splice(index, 1);
    $(this).parent().remove();
    $('#attachmentIds-attachmentId_' + index).remove();
    $('#attachmentNames-attachmentName_' + index).remove();
    return false;
  });
  
  var openFileBrowser = function(onSelectFiles, selectFiles) {
    if (fileBrowserHandle === null) {
      fileBrowserHandle = window.open(url.fileBrowser, '', 'width=1000, height=700');
    } else {
      fileBrowserHandle.focus();
    }
    
    var loop = setInterval(function() {
      if(fileBrowserHandle !== null && fileBrowserHandle.closed) {  
        clearInterval(loop);
        fileBrowserHandle = null;
        //alert('closed');  
      }  
    }, 1000); 

    fileBrowserHandle.selectFiles = selectFiles;
    fileBrowserHandle.onSelectFiles = onSelectFiles;
    return false;
  };
  
  $('.j_openFileBrowser').click(function() {
    openFileBrowser(fileBrowserSelectFiles, false);
    return false;
  });
  
  $('.j_taskSelectAttachments').click(function() {
    openFileBrowser(fileBrowserSelectFiles, true);
    return false;
  });
  
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
  
  $('#j_projectAddPlan').click(function() {
    openFileBrowser(projectAddPlanFileBrowserSelectFiles, true);
    return false;
  });
  
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
  
  $('#j_projectAddDocumentation').click(function() {
    openFileBrowser(projectAddDocumentationFileBrowserSelectFiles, true);
    return false;
  });
  
  $('.j_projectDeleteAttachment').click(function() {
    $.post($(this).attr('href'), function(data) {
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
    
    return false;
  });
 
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
      return language.charactersLeft.replace('__left__', max - len).replace('__max__', max);
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
  }

  //ajax - pobieranie listy środowisk
  if (typeof url.environmentListAjax != 'undefined') {
    $('#environments').tokenInput(url.environmentListAjax, {
      theme: 'facebook',
      prePopulate: typeof prePopulated.environments != 'undefined' ? prePopulated.environments : null,
      hintText: language.enterPhraseSearched,
      noResultsText: language.noResults,
      searchingText: language.searching,
      minChars: 0,
      preventDuplicates: true
    });
  }

  //ajax - pobieranie listy wersji
  if (typeof url.versionListAjax != 'undefined') {
    $('#versions').tokenInput(url.versionListAjax, {
      theme: 'facebook',
      prePopulate: typeof prePopulated.versions != 'undefined' ? prePopulated.versions : null,
      hintText: language.enterPhraseSearched,
      noResultsText: language.noResults,
      searchingText: language.searching,
      minChars: 0,
      preventDuplicates: true
    });
  }
  
  //ajax - pobieranie listy userów
  if (typeof url.userListAjax != 'undefined') {
    var users = $('#users');
    if (users.length > 0) {
      $('#users').tokenInput(url.userListAjax, {
        theme: 'facebook',
        prePopulate: typeof prePopulated.users != 'undefined' ? prePopulated.users : null,
        propertyToSearch: 'name',
        hintText: language.enterPhraseSearched,
        noResultsText: language.noResults,
        searchingText: language.searching,
        minChars: 0,
        preventDuplicates: true
      });
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
    var popupDelete = $('#j_popup_' + popupYesNoName);
    popupDelete.dialog({
      autoOpen: false,
      resizable: false,
      modal: true,
      width: 450    
    });

    $('.j_' + popupYesNoName).each( function() {
      var deleteObj = $(this);

      deleteObj.click(function() {
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
        return false;
      });
    });
  }
  
  // Default language for date picker
  //$.datepicker.setDefaults($.datepicker.regional['pl']);

  // Start and end date
  var endDate = $('#endDate');
  var startDate = $('#startDate');
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

    date.datetimepicker({
      showAnim: 'slideDown',
      dateFormat: 'yy-mm-dd',
      changeMonth: true,
      changeYear: true,
      timeFormat: 'HH:mm',
      hour: 23,
      minute: 59
    });
    
    if (typeof minDate != 'undefined') {
      
      date.datetimepicker('option', 'minDate', minDate);
      date.datetimepicker('option', 'timeFormat', 'HH:mm');
    }
    
    if (typeof maxDate != 'undefined') {
      date.datetimepicker('option', 'maxDate', maxDate);
      date.datetimepicker('option', 'timeFormat', 'HH:mm');
    }
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
            $(autocompleteData.dstName).val( ui.item? ui.item.id : '' );
      }
    })
    .focusout(function() {
      if (!$(this).val()) {
          $(autocompleteData.dstName).val('');
      }
    });
  }
  
  // Lista autocomplete do testów w zadaniu
  if (typeof autocompleteDataForTest != 'undefined') {
    $(autocompleteDataForTest.textInputName).autocomplete({
      source: function(request, response){
        $.post(autocompleteDataForTest.url, {q: request.term}, function(data) {
           response($.map($.parseJSON(data), function(item) {
            return {
              label: item.name,
              value: item.name,
              id: item.id
            };
          }));
        });
      },
      minLength: autocompleteDataForTest.minLength,
      select: function( event, ui ) {
        $(autocompleteDataForTest.dstName).val(ui.item.id);
      },
      change: function( event, ui ) {
            $(autocompleteDataForTest.dstName).val( ui.item ? ui.item.id : '' );
      }
    })
    .click(function() {
      $(autocompleteDataForTest.textInputName).val(autocompleteDataForTest.projectPrefix);
      $(autocompleteDataForTest.dstName).val('');
    })
    .focusout(function() {
      if (!$(this).val()) {
          $(autocompleteDataForTest.dstName).val('');
      }
      if ($(autocompleteDataForTest.textInputName).val() == autocompleteDataForTest.projectPrefix) {
        $(autocompleteDataForTest.textInputName).val('');
      }
    });
  }
  
  // Zarządzanie testami w zadaniu
  var getTaskTestViewUrl = function(type, id) {
    switch (type)
    {
      case '1':
        return url.taskOtherTestView.replace('0', id);
        
      case '2':
        return url.taskTestCaseView.replace('0', id);
        
      case '3':
        return url.taskExploratoryTestView.replace('0', id);
        
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
                'viewUrl': getTaskTestViewUrl(result.data.testType, result.data.testId),
                'deleteUrl': url.deleteTestFromTask.replace('0', result.data.testId),
                'testId': result.data.testId
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
            $('#j_testItem_' + result.data.testId).remove();

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
           response($.map($.parseJSON(data), function(item) {console.log(data);
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
  
  // Autocomplete release for phase
  if (typeof autocompleteReleaseForPhase != 'undefined') {
    var startDate = $('#startDate');
    var endDate = $('#endDate');
    
    if ($(autocompleteReleaseForPhase.dstName).val().length) {
      startDate.prop('disabled', false);
      endDate.prop('disabled', false);
    } else {
      startDate.prop('disabled', true);
      endDate .prop('disabled', true);
    }
    
    $(autocompleteReleaseForPhase.textInputName).autocomplete({
      source: function(request, response){
        $.post(autocompleteReleaseForPhase.url, {q: request.term}, function(data) {
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
      minLength: 2,
      select: function( event, ui ) {
        startDate.prop('disabled', false);
        endDate.prop('disabled', false);
        $(autocompleteReleaseForPhase.dstName).val(ui.item.id);
        if (ui.item.startDate !== null) {
          startDate.datepicker('option', 'minDate', ui.item.startDate);
          endDate.datepicker('option', 'minDate', ui.item.startDate);
          startDate.datepicker('setDate', new Date());
        }
        if (ui.item.endDate !== null) {
          startDate.datepicker('option', 'maxDate', ui.item.endDate);
          endDate.datepicker('option', 'maxDate', ui.item.endDate);
          endDate.val(ui.item.endDate);
        }
      }
    });
  }
  
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
  if (typeof autocompleteForTask != 'undefined') {
    
    // włączenie/wyłączenie faz
    if ($(autocompleteForTask.release.textInputName).val() == '') {
      $(autocompleteForTask.release.dstName).val('');
      $(autocompleteForTask.phase.dstName).val('');
      $(autocompleteForTask.phase.textInputName).prop('disabled', true);
    } else if ($(autocompleteForTask.phase.textInputName).val() == '') {
      $(autocompleteForTask.phase.dstName).val('');
    }
    
    // wyczyszczenie pola wydania po kliknięciu
    $(autocompleteForTask.release.textInputName).click(function(){
      $(this).val('');
      $(autocompleteForTask.release.dstName).val('');
      $(autocompleteForTask.phase.dstName).val('');
      $(autocompleteForTask.phase.textInputName).val('');
      $(autocompleteForTask.phase.textInputName).prop('disabled', true);
    });
    
    // wyczyszczenie pola fazy po kliknięciu
    $(autocompleteForTask.phase.textInputName).click(function(){
      $(this).val('');
      $(autocompleteForTask.phase.dstName).val('');
    });
    
    // autocomplete wydania
    $(autocompleteForTask.release.textInputName).autocomplete({
      source: function(request, response){
        $.post(autocompleteForTask.release.url, {q: request.term}, function(data) {
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
        $(autocompleteForTask.phase.textInputName).prop('disabled', false);
        $(autocompleteForTask.release.dstName).val(ui.item.id);

          var minDateTime = Date.parse(ui.item.startDate);
          var nowDate = new Date();
          var nowDateTime = (new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0)).getTime();

          if (minDateTime < nowDateTime) {
            $('#dueDate').datetimepicker('option', 'minDate', nowDate);
          } else {
            $('#dueDate').datetimepicker('option', 'minDate', ui.item.startDate); 
          }
        
        $('#dueDate').datetimepicker('option', 'maxDate', ui.item.endDate);
      }
    });
    
    // autocomplete fazy
    $(autocompleteForTask.phase.textInputName).autocomplete({
      source: function(request, response){
        $.post(autocompleteForTask.phase.url, {q: request.term, 'releaseId': $(autocompleteForTask.release.dstName).val()}, function(data) {
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
        $(autocompleteForTask.phase.dstName).val(ui.item.id);
        var minDateTime = Date.parse(ui.item.startDate);
        var nowDate = new Date();
        var nowDateTime = (new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0)).getTime();

        if (minDateTime < nowDateTime) {
          $('#dueDate').datetimepicker('option', 'minDate', nowDate);
        } else {
          $('#dueDate').datetimepicker('option', 'minDate', ui.item.startDate); 
        }
        
        $('#dueDate').datetimepicker('option', 'maxDate', ui.item.endDate);
      }
    });
  }
  
  // Phase list by release ------------------------------------------------------!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
  var releaseId = $('#releaseId');
  var phaseId = $('#phaseId');
  var phaseRow = $('#phaseRow');
  
  if (releaseId.length && phaseId.length && typeof defaultReleaseId != 'undefined' && typeof defaultPhaseId != 'undefined' && phaseRow.length) {
    if (releaseId.val() == defaultReleaseId) {
      phaseRow.hide();
    }
    
    releaseId.change(function() {
      phaseRow.hide();
      phaseId.empty();
      
      if ($(this).val() == defaultReleaseId) {
        phaseId.append('<option selected="selected" value="' + defaultPhaseId + '">' + language.defaultPhaseName + '</option>');
      } else {
        $.post(url.phaseListAjax, {releaseId : $(this).val()}, function(data) {
          data = jQuery.parseJSON(data);

          for (var i in data) {
            phaseId.append('<option value="' + data[i].id + '">' + (data[i].name == null ? language.defaultPhaseName : data[i].name) + '</option>');
          }
          
          phaseRow.show();
        });
      }
    });
  }

  // Phase list by release for filter
  var release = $('#release');
  var phase = $('#phase');
  
  if (release.length && phase.length) {
    
    if (release.val() <= 0) {
      phase.prop('disabled', true);
    }
    
    release.change(function() {
      phase.prop('disabled', true);
      phase.empty();
    
      if ($(this).val() > 0) {
        phase.append('<option value="0">' + language.all + '</option>');
        phase.append('<option value="-1">' + language.defaultPhaseName + '</option>');        
        $.post(url.phaseListAjax, {releaseId : $(this).val()}, function(data) {
          data = jQuery.parseJSON(data);

          for (i in data) {
            phase.append('<option value="' + data[i].id + '">' + data[i].name + '</option>');
          }

          phase.prop('disabled', false);
        });
      } else if ($(this).val() < 0) {
        phase.append('<option value="-1">' + language.defaultPhaseName + '</option>');  
      } else {
        phase.append('<option value="0">' + language.all + '</option>');
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