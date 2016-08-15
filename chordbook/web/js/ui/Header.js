/** Header bar */
var Header = function($this) {
  return ui($this, {
    onclickleft:function() {},
    onclickright:function() {},
    //
    init:function() {
      this.$left = $this.find('.l');
      this.$left.find('A')
        .on('click', Function.defer(this, 'onclickleft'));
      this.$middle = $this.find('.m');
      this.$right = $this.find('.r');
      this.$right.find('A')
        .on('click', Function.defer(this, 'onclickright'));
    },
    setTitle:function(text) {
      this.$middle.text(text);
      return this;
    }
  })
}
