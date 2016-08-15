/**
 * Anchor ClientSelector
 */
ClientSelector = {
  create:function() {
    return Html.AnchorAction.create('client').extend(function(self) {
      return {
        onset:function(rec) {},
        //
        init:function() {
          self.set();
        },
        /*
         * @arg Client rec (optional) 
         */
        set:function(rec) {
          self.rec = rec;
          self.setText((rec) ? rec.name : 'Select a patient');
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
        //
        onclick:function() {
          PatientSelector.pop(function(client) {
            self.set(client);
            self.onset(client);
          })
        }
      }
    })
  }
}
