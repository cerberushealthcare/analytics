(function($) {
  $.fn.extend({
    maxHeight:function(pad) {
      var $this = $(this);
      var mh = function() {
        $this.height($(window).height() - (pad || 0));
      }
      $(window)
        .on('orientationchange', mh)
        .on('resize', mh)
        .data('mhbound', 1);
      mh();
      return $this;
    },
    quick:function(onclick, context) {
      var $this = $(this);
      if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        $this.on('touchstart', function(e) {
          $(this).trigger('click');
          e.preventDefault();
        })
      }
      if (onclick) {
        if (context) {
          onclick = onclick.bind(context);
        }
        $this.on('click', onclick);
      }
      // var e = $._data($(this).get(0), 'events');
      // {click:[{handler:fn},{handler:fn},..],tap:[..],..}
      return $this;
    },
    noBounce:function() {
      // TODO
    }
  })
})(jQuery);
