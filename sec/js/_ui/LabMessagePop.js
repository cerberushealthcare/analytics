LabMessagePop = {
  /*
   * @arg OruMessage rec 
   */
  pop:function(rec, id) {
    return Html.Pop.singleton_pop.apply(LabMessagePop, arguments);
  },
  /*
   * @arg int id (to have popup retrieve message by hl7_inbox_id)
   */
  pop_forInbox:function(id) {
        /* Page.pop('lab-message.php', id); return; */
    return Html.Pop.singleton_pop.call(LabMessagePop, null, id); 
  },
  //
  create:function() {
    var My = this;
    return Html.Pop.create('Lab Message', 600).withFrame().extend(function(self) {
      return {
        //
        init:function() {
          self.Div = Html.ScrollDiv.create(self.frame).setHeight(500);
          self.Cmd = Html.CmdBar.create(self.content).exit(self.close); 
        },
        onshow:function(rec, id) {
          if (rec) {
            self.load(rec);
          } else {
            self.invisible();
            Ajax.Labs.getInboxMessage(id, function(rec) {
              self.visible();
              if (rec) { 
                self.load(rec);
              } else {
                self.close();
                Pop.Msg.showCritical('The message could not be retrieved.');
              }
            })
          }
        },
        load:function(rec) {
          rec.asList().into(self.Div.clean());
        }
        //
      }
    })
  }
}
