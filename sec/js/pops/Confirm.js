/**
 * Confirm Pop
 */
Confirm = {
  _pop:null,
  _callback:null,
  //
  pop:function(text, yesCap, yesCls, noCap, noCls, callback) {
    this._callback = callback;
    this._build(text, yesCap, yesCls, noCap, noCls);
    Pop.show(this._pop.id);
  },
  popYesNo:function(text, callback) {
    this.pop(text, 'Yes', null , 'No', null, callback);
  },
  popDirtyExit:function(noun, callback) {
    noun = denull(noun, 'entry');
    text = 'This ' + noun + ' has unsaved changes. Do you want to save before exiting?';
    this.pop(text, 'Save and Exit', 'save', "Don't Save, Just Exit", null, callback);
  },
  pClose:function(confirmed) {
    this._callback(confirmed);
    Pop.close();
  },
  pCloseYes:function() {
    this.pClose(true);
  },
  pCloseNo:function() {
    this.pClose(false);
  },
  pCloseCancel:function() {
    Pop.close();
  },
  //
  _build:function(text, yesCap, yesCls, noCap, noCls) {
    this._pop = Pop.Builder.build(this, 'pop-conf', 'Confirmation Required', '500px');
    Pop.Builder.div('question').innerHTML = text;
    var cmdBar = Pop.Builder.cmdBar();
    cmdBar.button(yesCap, yesCls, this.pCloseYes);
    cmdBar.button(noCap, noCls, this.pCloseNo);
    cmdBar.cancel(this.pCloseCancel);
  }
};
