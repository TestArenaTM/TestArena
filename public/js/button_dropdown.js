function classButton_dropdown() {
  this.open = function (name) {
    $('#button_dropdown_list_' + name).show();
    $('#button_dropdown_button_'+ name).addClass('button_dropdown_open');
  };

  this.close = function (name) {
      $('#button_dropdown_list_' + name).hide();
      $('#button_dropdown_button_'+ name).removeClass('button_dropdown_open');
  };

  this.isOpen = function (name) {
    let isOpen = $('#button_dropdown_list_' + name).css('display') == 'block';
    return isOpen;
  };

  this.button = function (name) {
    if (this.isOpen(name)) {
      this.close(name);
    } else {
      this.open(name);
    }
  }

  this.autoClose = function (name) {
    let self = this;
    $(document).mouseup(function(e)
    {
      let container = $("#button_dropdown_button_"+ name);
      if (!container.is(e.target) && container.has(e.target).length === 0) {
        self.close(name);
      }
    });
  }

}
let button_dropdown = new classButton_dropdown();
