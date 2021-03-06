/**
 * Template Form
 * Fills in <ul> entry form of JQuestion (template popup) fields 
 * Builds record object 
 */
TemplateForm.Q_DEF_FREETEXT = 0;
TemplateForm.Q_DEF_CALENDAR = 1;
//
TemplateForm.VALUES_ALWAYS_ARRAY = 0;  // Values saved as arrays
TemplateForm.VALUES_MIXED = 1;         // Values saved as strings for single-opt questions, arrays for multi
//
TemplateForm.NAV_NONE = 0;             // Nav: none
TemplateForm.NAV_NEXT_ON_LINE = 1;     // Nav: next field on this line
TemplateForm.NAV_NEXT_ON_FORM = 2;     // Nav: next field on form
//
TemplateForm.DESERIALIZED = true;
/*
 * Constructor
 * - ul: parent <ul> to contain this form; ul.tform will be set to this instance
 * - firstLabelClass: optional, default "first"
 * - qCache: {qKey:JQuestion,..} optional; if not supplied, shared cache used
 * - onChangeCallback: optional callback(q) when a question is changed
 * - navigation: optional (default NAV_NONE)
 */
function TemplateForm(ul, firstLabelClass, qCache, onChangeCallback, navigation) {
  this.ul = _$(ul).clean();
  this.ul.tform = this;
  this.ul.firstLabelClass = firstLabelClass || "first";
  if (qCache)
    this.qCache = Question.reviveAll(qCache);
  else
    this.qCache = TemplateForm._standardQCache;
  this.onChange = onChangeCallback;
  this.nav = navigation || TemplateForm.NAV_NONE;
  this.dirty = false;
  this.fields = {};
  this.lastA = null;
}
TemplateForm.prototype = {
  qCache:null,
  onChange:null,
  ul:null,
  li:null,
  dirty:null,
  fields:null,  // {fid:q,..}
  lastA:null,   // last <a> rendered 
  /*
   * Start <li> entry
   * - id, className: optional
   */
  addLi:function(id, className) {
    this.li = this.ul.li(className).setId(id);
    if (this.nav == TemplateForm.NAV_NEXT_ON_LINE) {
      this.lastA = null;
    }
  },
  /*
   * Start <li> entry and append (see append)
   */
  addLiAppend:function(labelText, key, fid, value, className, qDefaultType) {
    this.addLi();
    this.append(labelText, key, fid, value, className, qDefaultType);
  },
  /*
   * Append question entry field to <li>
   * - labelText: optional
   * - key: question's qCache key (may be null if never cached, but then fid is required)
   * - fid: optional field instance ID to use when multiple instances of question share cache key
   * - value: current value to assign to question
   *          if non-combo, may be either string or string array (direct value assignment) or record object {"fid":value,..} (value lookup)    
   *          if combo, supply as ['single',['multi',..]]
   * - className: optional (e.g. "qr" for fixed width)
   * - qDefaultType: optional (default Q_DEF_FREETEXT)
   * - labelClass: optional
   */
  append:function(labelText, key, fid, value, className, qDefaultType, labelClass) {
    var q = this._getQuestion(key, fid, qDefaultType, value, labelText);
    q.tform = this;
    q.deftype = qDefaultType;
    this.fields[q.fid] = q;
    this.appendLabel(labelText, labelClass);
    var a = createAnchor(null, null, "df", null, null, "TemplateForm._showAnchor(this)");
    q.a = a;
    a.q = q;
    this._setAnchorText(a);
    this._setAnchorNav(a);
    a.priorText = a.innerText;
    var span = Html.Span.create(String.trim("q " + (className)) || '');
    span.appendChild(a);
    this.li.appendChild(span);
  },
  /*
   * Append <label>Text</label>
   * - labelClass: optional
   */
  appendLabel:function(labelText, labelClass) {
    if (labelText != null) {
      labelClass = labelClass || (this.li.innerHTML) ? null : this.ul.firstLabelClass;
      this.li.appendChild(Html.Label.create(labelClass, labelText));
    }
  },
  /*
   * Set navigation break
   */
  setNavBreak:function() {
    this.lastA = null;
  },
  /*
   * Bring up question pop for field
   */
  popup:function(fid) {
    TemplateForm._showAnchor(this.fields[fid].a);
  },
  /*
   * Returns true if any question entry field changed
   */
   isDirty:function() {
     return this.dirty;
   },  
  /*
   * Generate record from assigned field values
   * - valuesAs: optional (default VALUES_ALWAYS_ARRAY)
   * - fieldPrefix: optional string to prepend to each field name
   * - deserializedValues: optional, default false 
   * Returns {fid:value,..} where value is 'seltext' or ['seltext','seltext',..] depending upon question type
   */
  buildRecord:function(valuesAs, fieldPrefix, deserializedValues) {
    var rec = {};
    valuesAs = valuesAs || TemplateForm.VALUES_ALWAYS_ARRAY;
    for (var fid in this.fields) {
      var q = this.fields[fid];
      if (fieldPrefix != null) {
        fid = fieldPrefix + fid;
      }
      rec[fid] = this._getValue(q, valuesAs, deserializedValues);
    }
    return rec;
  },
  /*
   * Set formatted option text (e.g. calendar) by question key
   */
  setFormattedText:function(key, text) {
    var q = this.qCache[key];
    if (q) {
      if (text == null) {
        //q.sel = [];
        q.reset();
      } else {
        //qSetFormattedOption(q, text);
        q.setByValue(text);
      }
      this._setAnchorText(q.a);
    }
  },
  //
  _getQuestion:function(key, fid, type, value, lbl) {
    var q;
    if (key) {
      q = this.qCache[key];
    }
    if (q == null) {  // question not in cache; create new instance
      //q = (type == TemplateForm.Q_DEF_CALENDAR) ? newCalendarQuestion(lbl) : newFreetextQuestion(lbl);
      q = (type == TemplateForm.Q_DEF_CALENDAR) ? QuestionDate.asDummy(lbl) : QuestionFree.asDummy(lbl);
      this.qCache[key] = q;
    }
    if (key && fid) {
      //q = clone(q);
      q = Question.revive(q);
    }
    q.key = key;
    q.fid = fid || key;
    if (q.cbo) {
      // qSetByValueCombo(q, value[0], value[1]);
      q.setByValue(value);
    } else {
      if (Map.is(value)) {
        //qSetByValues(q, value[q.fid]);
        q.setByValue(value[q.fid]);
      } else {
        //qSetByValues(q, value);
        q.setByValue(value);
      }
    }
    return q;
  },
  _getText:function(q) {  // get question text to display
    if (q.showUid) {
      return q.Opts.getSelUid();  // qSelUid(q);
    } else {
      return q.getValue();  // qSelText(q);
    }
  },
  _getValue:function(q, valuesAs, deserialized) {  // get question value to save in record
    if (valuesAs == TemplateForm.VALUES_ALWAYS_ARRAY || q.mix != null) {
      var values = (q.Opts.isSel()) ? q.Opts.getTexts() : [];  // qOptTextArray(q.opts, q.sel, 0);
      return (deserialized) ? values : Json.encode(values);
    } else {
      //return (q.sel.length == 0) ? null : this._getText(q);
      return (q.Opts.isSel()) ? this._getText(q) : null;
    }
  },
  _setAnchorText:function(a) {
    var q = a.q;
    if (q.Opts.isSel()) {
      var text = this._getText(q);
      if (q.deftype == TemplateForm.Q_DEF_CALENDAR) {
        text = DateUi.extractDate(text);
      } 
      a.innerText = text;
      a.className = "";
    } else {
      a.innerHTML = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
      a.className = "df";
    }
  },
  _setAnchorNav:function(a) {
    if (this.lastA) {
      this.lastA.next = a;
      a.prev = this.lastA;
    }
    if (this.nav != TemplateForm.NAV_NONE) 
      this.lastA = a;
  }
}
/*
 * Static protected
 */
TemplateForm._standardQCache = {};
TemplateForm._showAnchor = function(a, useLastPos) {
  if (! useLastPos)
    Pop.cacheMousePos();
  var q = a.q;
  var pos = (useLastPos) ? Pop.POS_LAST : Pop.POS_CURSOR;
  if (q.Opts.getSingle)
    q.Opts.getSingle()._text = '';  // make default text blank because form shows blank (disregards whatever default value really is, e.g. '98' for vitals O2)
  QuestionPopEntry.pop(q, TemplateForm._popQuestionCallback, pos);
  //showQuestion(a.q, null, useLastPos, true, TemplateForm._popQuestionCallback);
}
TemplateForm._popQuestionCallback = function(q) {
  q.tform._setAnchorText(q.a);
  if (q.a.innerText != q.a.priorText) {
    q.tform.dirty = true;
    if (q.tform.onChange) {
      q.tform.onChange(q);
    }
  }
  q.a.priorText = q.a.innerText;
  if (q.a.next) 
    if (q.a.next.className == 'df')
      TemplateForm._showAnchor(q.a.next, true);
}
  