/**
 * Lookup 
 * Global static
 * Requires: Ajax.js
 */
var Lookup = {
  //
  T_CLIENT_SEARCH_CUSTOM:'ClientSearchCustom',
  T_DEFAULT_SEND_TO:'DefaultSendTo',
  T_PRINT_TEMPLATE_CUSTOMS:'PrintTemplateCustoms',
  T_REPLICATE_OVERRIDE_FS:'ReplicateOverrideFs',
  T_VAC_CHART:'VacChart',
  //
  NO_CALLBACK:false,
  //
  _cache:{},  // {key:rec}
  /*
   * Client Search Customizations
   */
  getClientSearchCustom:function(callback) {
    this._getLookup(Lookup.T_CLIENT_SEARCH_CUSTOM, null, callback);
  },
  saveClientSearchCustom:function(rec) {
    this._saveLookup(Lookup.T_CLIENT_SEARCH_CUSTOM, null, rec, Lookup.NO_CALLBACK);
  },
  resetClientSearchCustom:function(callback) {
    this._confirmResetLookup(Lookup.T_CLIENT_SEARCH_CUSTOM, null, callback);
  },
  /*
   * Default Send To
   */
  saveDefaultSendTo:function(id) {
    this._saveLookup(Lookup.T_DEFAULT_SEND_TO, null, id, Lookup.NO_CALLBACK);
  },
  /*
   * Replicate Override Facesheet
   */
  saveReplicateOverrideFs:function(value) {
    this._saveLookup(Lookup.T_REPLICATE_OVERRIDE_FS, null, value, Lookup.NO_CALLBACK);
  },
  /*
   * Vaccine Chart
   */
  getVacChart:function(callback) {
    this._getLookup(Lookup.T_VAC_CHART, null, callback);
  },
  /*
   * Print/Template Customizations
   */
  getPrintTemplateCustoms:function(callback) {
    this._getLookup(Lookup.T_PRINT_TEMPLATE_CUSTOMS, null, callback);
  },
  //
  _confirmResetLookup:function(table, id, callback) {
    var self = this;
    Pop.Confirm.showYesNoCancel('This will reset to the default settings. Are you sure?',
      function(confirmed) {
        if (confirmed) 
          self._resetLookup(table, id, callback)
      },
      true);
  },
  _getLookup:function(table, id, callback) {
    var action = 'get' + table;
    var scb = Ajax.buildScopedCallback(callback, action);
    var rec = this._getCacheRecord(table, id);
    var self = this;
    if (rec) {
      Ajax.callScopedCallback(scb, rec);
    } else {
      Ajax.get(Ajax.SVR_LOOKUP, action, id, 
        function(data) {
          self._getLookupCallback(table, id, scb, data)
        });
    }
  },
  _getLookupCallback:function(table, id, scb, rec) {
    this._setCacheRecord(table, id, rec);
    Ajax.callScopedCallback(scb, rec);
  },
  _saveLookup:function(table, id, rec, callback) {
    var action = 'save' + table;
    var scb = Ajax.buildScopedCallback(Ajax.defaultCallback(callback, action));
    this._setCacheRecord(table, id, rec);
    var self = this;
    var arg = {
      'id':id,
      'value':Json.encode(rec)};
    Ajax.post(Ajax.SVR_LOOKUP, action, arg, 
      function(data) {
        self._saveLookupCallback(scb, data);
      });
  },
  _saveLookupCallback:function(scb, data) {
    Ajax.callScopedCallback(scb, data);
  },
  _resetLookup:function(table, id, callback) {
    var action = 'reset' + table;
    var scb = Ajax.buildScopedCallback(Ajax.defaultCallback(callback, action));
    this._setCacheRecord(table, id, null);
    var self = this;
    Ajax.get(Ajax.SVR_LOOKUP, action, id, 
      function(data) {
        self._resetLookupCallback(scb, data)
      });
  },
  _resetLookupCallback:function(scb, rec) {
    Ajax.callScopedCallback(scb, rec);
  },
  _setCacheRecord:function(table, id, rec) {
    var cacheKey = this._getCacheKey(table, id);
    if (cacheKey) { 
      this._cache[cacheKey] = rec;
    }
  },
  _getCacheRecord:function(table, id) {
    var cacheKey = this._getCacheKey(table, id);
    return (cacheKey) ? this._cache[cacheKey] : null;
  },
  _getCacheKey:function(table, id) {
    switch (table) {
      default:
        return table; 
    }
  }
}
