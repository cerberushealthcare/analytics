(function($) {
  $.fn.extend({
    maxHeight:function(pad) {
      $this = $(this);
      function mh() {
        $this.height($(window).height() - pad);
      }
      if ($(window).data('mhbound') == null) {
        $(window)
          .on('orientationchange', mh)
          .on('resize', mh)
          .data('mhbound', 1);
        mh();
      }
      return $this;
    },
    noTapDelay:function() {
      if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent))  
        $(this).on('tap', function(e) {
          $(this).trigger('click');
          e.preventDefault();
        })
      return $(this);
    },
    noBounce:function() {
      // TODO
    }
  })
})(jQuery);
