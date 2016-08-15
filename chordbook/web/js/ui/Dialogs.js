/** Dialogs */
var Confirm = function($this) {
  return ui($this, {
    //
    init:function() {
      this.$text = $this.find('.text');
      this.$yes = $this.find('.yes')
        .on('click', this.$yes_onclick.bind(this));
      this.$yes.button(); 
    },
    show:function(caption, button, /*fn*/onyes) {
      this.onyes = onyes;
      this.$text.text(caption);
      this.$yes.find('span.ui-btn-text').text(button);
      $.mobile.changePage($this, {role:'dialog'});
    },
    show_asDelete:function(onyes) {
      this.show('Are you sure? This operation cannot be undone.', 'Delete', onyes);
    },
    //
    $yes_onclick:function() {
      this.onyes && this.onyes();
    }
  })
}
