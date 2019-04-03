function classDialogIframe() {
  this.open = function (url, title, settings) {
    let defaultSettings =
    {
      autoOpen: false,
      resizable: false,
      draggable: false,
      modal: true,
      position: ['center+45', 'top' ],
      open: function(event, ui) {
        
        $('#dialog_iframe').on('load', function(){
          $(".ui-dialog").css('margin-top', '80px');
        });
      },
      width: 900,
      height: 850
    };

    Object.assign(defaultSettings, settings);

    $("#dialog_content").dialog(defaultSettings);

    $("span.ui-dialog-title").text(title);
    $('#dialog_iframe').attr('src', url);
    $('#dialog_content').dialog('open');
    
      
    $('#dialog_content').on('dialogclose', function(event, defaultSettings) {
      $('#dialog_iframe').attr('src', '');
      $('#dialog_content').dialog('destroy');
    });
  };

  this.close = function()
  {
    $('#dialog_content').dialog('close');
    $('#dialog_iframe').attr('src', '');
  }
}

var DialogIframe = new classDialogIframe();