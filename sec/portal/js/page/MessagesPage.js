/**
 * PortalPage MessagesPage
 */
MessagesPage = Html.Page.extend(function(self) {
  return {
    onbodyload:function() {
      self.tile = MessagesTile.create(self.content).bubble('onreturn', self.tile_onreturn);
    },
    tile_onreturn:function() {
      self.goHome();
    }
  }
})
MessagesTile = {
  create:function(container) {
    var My = this;
    return Html.Tile.create(container).extend(function(self) {
      return {
        onreturn:function() {},
        //
        init:function() {
          Html.H1.create('Messages').into(self);
          Html.Span.create(null, 'You have no new messages.').into(self);
          ReturnAnchor.create(self, 'Back to Home');
        }
      }
    })
  }
}
