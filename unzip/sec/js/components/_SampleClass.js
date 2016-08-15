/**
 * SampleClass
 * Sample class description
 * Requires: ui.js
 */
SampleClass.STATIC_CONST = 0;  
/*
 * SampleClass constructor
 */
function SampleClass(arg) {
  SampleClass._newInstance(this);
}
/*
 * SampleClass class defs
 */
SampleClass.prototype = {
  _i:null,  // instance index
  /*
   * A public method 
   * Demonstrates how to reference this instance in a created anchor element
   */
  method:function(arg) {
    var self = this;
    var a = createAnchor();
    a.onclick = function() {
      self._doClick(arg)  
    }
    return a;
  },
  /*
   * A protected method
   */
  _doClick:function(arg) {
    alert(arg); 
  }
}
/*
 * SampleClass statics
 */
SampleClass._ict = 0;  // instance count
SampleClass._newInstance = function(instance) {
  instance._i = SampleClass._ict++;
  if (instance._i == 0) {
    // code for the first instance, e.g. attachEventHandler 
  }
}
SampleClass._getInstance = function(_i) {
  return SampleClass._instances[_i];
}
SampleClass._clearInstance = function(instance) {
  delete SampleClass._instances[instance._i];
}
