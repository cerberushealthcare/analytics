/**
 * Anchor ClientSelector
 */
ClientSelector = {
  create:function(defaultText, showRequired) {
    return Html.AnchorAction.create('client').extend(function(self) {
      return {
        onset:function(rec) {},
        //
        init:function() {
          self.set();
        },
        reset:function() {
          self.set();
          self._dirty = false;
          return self;
        },
        set:function(rec) {  // Client
          self.rec = rec;
          self.setText((rec) ? rec.name : defaultText || 'Select a patient');
          if (showRequired) 
            self.style.color = (rec) ? '' : 'red';
          return self;
        },
        /*
         * @arg int cid
         * @arg string name
         */
        setValueText:function(cid, name) {
          self.rec = {'clientId':cid};
          self.setText(name);
        },
        /*
         * @return int
         */
        getValue:function() {
          return self.rec.clientId;
        },
        /*
         * @return string
         */
        getText:function() {
          return self.innerText;
        },
        /*
         * @return bool
         */
        isDirty:function() {
          return self._dirty;
        },
        //
        onclick:function() {
          PatientSelector.pop(function(client) {
            self._dirty = true;
            self.set(client);
            self.onset(client);
          })
        }
      }
    })
  },
  create_asRequired:function(defaultText) {
    return ClientSelector.create(defaultText, true);
  }
}
