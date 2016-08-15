/** 
 * Par */
Par = Object.Rec.extend({
  /*
   id
   uid
   tid
   suid
   //
   Questions
   */
  onload:function(json, qProto) {
    this.ref = this.suid + '.' + this.uid;
    this.Questions = (qProto || Questions).reviveFrom(this);
  },
  _getQuestionsProto:function() {
    return Questions;
  },
  reset:function() {
    if (this.Questions) 
      for (var i = 0; i < this.Questions.length; i++) 
        this.Questions[i].reset();
    return this;
  },
  getQuestionByDsync:function(dsync) {
    for (var i = 0; i < this.Questions.length; i++) {
      if (this.Questions[i].dsync == dsync)
        return this.Questions[i];
    }
  },
  anyBlank:function() {
    if (this.Questions)
      for (var i = 0; i < this.Questions.length; i++) 
        if (this.Questions[i].isBlank())
          return true;
  }
})
Pars = Object.RecArray.of(Par, {
  ajax:function(worker) {
    return {
      fetch:function(pid, callback) {
        Ajax.getr('Templates', 'getParWithInjects', pid, Pars, worker, callback);
      }
    }
  }
})
/** 
 * MyPar (wrapper: cacheable Par + Others) */
MyPar = {
  revive:function(json) {
    json.Par.Others = json.Others;
    var par = Par.revive(json.Par);
    par.map = Map.from(par.Questions, 'uid');
    return par.aug({
      //
      get:/*Question*/function(quid) {
        return this.map[quid];
      }
    })
  },
  //
  ajax:function(worker) {
    return {
      fetch:function(tid, sid, puid, callback) {
        Ajax.postr('Templates', 'getMyPar', {'tid':tid,'sid':sid,'puid':puid}, MyPar, worker, callback);
      },
      fetchOe:function(callback) {
        Ajax.postr('Templates', 'getMyParOe', null, MyPar, worker, callback);
      },
      fetchAppt:function(callback) {
        Ajax.postr('Templates', 'getMyParAppt', null, MyPar, worker, callback);
      }
    }
  }
}
/**
 * Questions */
Questions = Object.RecArray.extend({
  reviveFrom:function(par) {
    this.Par = par;
    return this.revive(par.Questions);
  },
  reviveFromParInfo:function(pi) {
    this.pi = pi;
    return this.revive(pi.questions);
  },
  getItemProto:function() {
    return Question;
  },
  ondecorateitem:function(json, i) {
    if (this.Par) {
      json.Par = this.Par;
      json.ref = this.Par.ref + '.' + json.uid;
      var others = this.Par.Others && this.Par.Others[json.uid];  // custom others
      if (others) 
        json.Others = others;  
    } else {
      json.par = this.pi.par;      
      json.qt = this.pi.par.qts[i];
    }
  }
})
/** 
 * Question (single options) */
Question = Object.Rec.extend({
  /*
  id
  uid
  desc
  bt
  at
  btms
  atms
  btmu
  atmu
  listType
  brk
  defix
  mix
  mcap
  syncId
  outData
  dsync
  type
  loix
  //
  Opts
  Tests
  Actions
   */
  TYPES:{STANDARD:0,ALLERGY:1,CALC:2,DATE:3,FREE:4,MED:5,BUTTON:6,COMBO:7,DATA_HM:8,HIDDEN:9},
  //
  getProto:function(json) {
    switch (json.type) {
      case C_Question.TYPE_CALC:
      case C_Question.TYPE_FREE:
      case C_Question.TYPE_MED:
        return QuestionFree;
      case C_Question.TYPE_CALENDAR:
        return QuestionDate;
      case C_Question.TYPE_ALLERGY:
        return QuestionCombo;
      case C_Question.TYPE_COMBO:
        return QuestionCombo;
      default:
        return (json.mix) ? QuestionMulti : Question;
    }
  },
  onload:function() {
    this.sel = this.sel || [];
    this.def = this.def || [this.defix];
    this._sel = this.sel;
    this._def = this.def;
    this.bt = this.fixTags(this.bt);
    this.btms = this.fixTags(this.btms);
    this.atms = this.fixTags(this.atms);
    this.btmu = this.fixTags(this.btmu);
    this.atmu = this.fixTags(this.atmu);
    this.at = this.fixTags(this.at);
    this.brk = this.brk || 0;
    this.setr('Tests', Tests);
    this.setr('Actions', Actions);
    this.reviveOptions();
  },
  reviveOptions:function() {
    this.Opts = this.Options.reviveFrom(this, this.Opts || this.opts);
  },
  pop:function(onupdate) {
    return QuestionPop.pop(this, onupdate);
  },
  reset:function() {
    this.sel = this._sel;
    this.def = this._def;
    if (this.Opts)
      this.Opts.reset();
  },
  setDefault:function(/*int*/oix) {
    this.def = [oix];
    this.Opts.reset();
    return this;
  },
  setByValue:function(/*string*/value) {
    if (value == null)
      this.reset();
    else
      this.Opts.selectByValue(value);
    return this;
  },
  getValue:/*string*/function() {
    return this.Opts.getSelText();
  },
  isSelected:function(/*int*/oix) {
    return this.Opts.getSelIx() == oix || this.Opts.sel.has(oix);
  },
  isBlank:function() {
    return this.Opts.isBlank();
  },
  thaw:function(/*[sels,dels,sotext,motexts]*/args) {  
    this._RestoreArgs.apply(args);
    this.Opts.select(args[args.SELS]);
    this.Opts.getSingleOther().setText(args[args.SOTEXT]);
  },
  freeze:/*[sels,dels,sotext,motexts]*/function() {  
    var sels = this.Opts.sel;
    var sotext = this.Opts.getSingleOther().getText();
    return this._RestoreArgs.from(sels, null, sotext);
  },
  //
  _RestoreArgs:Object.create({
    SELS:0,DELS:1,SOTEXT:2,MOTEXTS:3,
    from:function(sels, dels, sotext, motexts) {
      return [sels, dels || [], sotext, motexts || []];
    }
  }),
  fixTags:function(t) {
    if (t) {
      t = t.replace(/\{today\}/g, today);
      t = t.replace(/\{dos\}/g, today);
      t = t.replace(/\{uname\}/g, me.User.name);
      return t;
    }
  },
  /** Options (Standard) */
  Options:Object.RecArray.extend({
    reviveFrom:function(q, opts) {  
      if (opts) {
        var self = this.revive(opts);
        self.q = q;
        self.loix = q.loix;
        self.def = q.def;
        if (q.other)
          self.appendOther();
        self.reset();
        return self;
      }
    },
    getItemProto:function(json, i) {
      return (i <= this.loix) ? Option : OptionOther;
    },
    reset:function() {
      this.resetIndexes();
      this.resetSels();
      this.resetOpts();
    },
    resetIndexes:function() {
      this.soix = this.q.loix + 1;
    },
    resetSels:function() {
      this.def = this.q.def;
      this.select(null);
    },
    resetOpts:function() {
      this.forEach(function(opt) {
        opt.reset();
      })
    },
    update:function(/*Option*/opt) {
      this[opt.index] = opt;
    },
    isSel:function() { /*true if any selected (not just defaulted)*/
      return this.sel.length > 0;
    },
    isBlank:function() { /*true if selected option is "blank" (needs a value)*/
      var o = this.getSel();
      return o && o.blank;
    },
    select:function(/*int[]*/sels) {
      this.sel = sels || [];
      selix = this.getSelIx();
      for (var i = 0; i < this.length; i++) {
        this[i].index = i;
        this[i].selected = (i == selix)
      }
    },
    selectByValue:/*int(selected)*/function(/*string*/value) { 
      var ix = this.findValue(value, 0, this.soix - 1);
      if (ix > -1) 
        this.select([ix]);
      else 
        ix = this.selectMutable(value);
      return ix;
    },
    toggle:function() {
      this.sel = (this.getSelIx() == 0) ? [1] : [0];
    },
    getSelText:/*string*/function() {
      return this.getSel().text;
    },
    getSingles:/*Option[]*/function() {
      return this.slice(0, this.soix);
    },
    getMultis:function() {
      return [];
    },
    getSingleOther:/*Option*/function() { 
      //if (this.length <= this.soix)
      //  this.appendOther();
      if (this.length > this.soix)
        return this[this.soix];
    },
    getMultiOthers:function() {
      return [];
    },
    hasTracking:function() {
      if (this._hasTrack == null) {
        for (var i = 0; i < this.length; i++) {
          if (this[i].tcat) {
            return this._hasTrack = true;
          }
        } 
        this._hasTrack = false;
      }
      return this._hasTrack;
    },
    //
    getOpts:function(ixs) {
      var a = [];
      for (var i = 0; i < ixs.length; i++) 
        a.push(this[ixs[i]]);
      return a;
    },
    getSel:function() {
      return this[this.getSelIx()];
    },
    getSelUid:function() {  
      var o = this.getSel();
      return o && o.uid;
    },
    getSels:function() {
      return [this.getSel()];
    },
    getUnsels:function() {
      return [];
    },
    getSelIx:function() {
      return (this.sel.length) ? this.sel[0] : this.def[0];
    },
    getTexts:function(opts) {
      return Array.from(opts || this.getSels(), 'text');
    },
    selectMutable:function(text) {
      this.select([this.soix]);
      this.getSingleOther().setText(text);
      return this.soix;
    },
    findValue:function(value, start, end) {
      end = end || this.length - 1;
      for (var i = start || 0; i <= end; i++) 
        if (this[i].text == value || this[i].uid == value)
          return i;
      return -1;
    },
    appendOther:function() {
      this.push(OptionOther.create(this.length));
    }
  }),
  asDummy:function/*Question*/(desc, /*string[]*/uids, /*<string>*/selected) {
    var q = this.revive(this._dummy(desc, uids, selected)).aug({
      //
      pop:function(/*fn(value, ix)*/onselect) {
        return QuestionPop.pop(this).bubble('onupdate', this._popCallback.bind(this, onselect));
      },
      popToggle:function(callback) {
        if (this.Opts.length == 2) {
          this.Opts.toggle();
          this._popCallback(callback);
        } else {
          this.pop(callback);
        }
      },
      _popCallback:function(callback) {
        callback(this.Opts.getSelText(), this.Opts.getSelIx());
      }
    })
    q.Opts.aug({
      getSingleOther:function() {
        return null;
      }
    })
    return q;
  },
  asDummy_fromMap:function(desc, /*{'key':'value',..}*/map, /*'key'*/selected) {
    var inv = Map.invert(map);
    return Question.asDummy(desc, Map.keys(inv).sort(), selected && map[selected]).aug({
      _popCallback:function(callback) {
        callback(inv[this.Opts.getSelText()], this.opts.getSelIx());
      }
    })
  },
  _dummy:function(desc, uids, selected) {  // object to revive an empty question
    var opts = [];
    var sel = [];
    uids = uids || [''];
    Array.forEach(uids, function(uid, i) {
      if (uid == selected)
        sel.push(i);
      opts.push({'uid':uid}); 
    })
    return Object.create({
      id:0,
      uid:'dummy',
      type:C_Question.TYPE_STANDARD,
      desc:desc,
      sel:sel,
      unsel:[],
      def:[],
      opts:opts,
      loix:opts.length - 1});
  }
})
/** 
 * QuestionFree - one single option (freetext) */
QuestionFree = Question.extend({
  //
  getValue:/*string*/function() {
    return this.Opts.getSingle().getText();
  },
  setDefaultText:function(/*string*/text) {
    this.Opts.getSingle().setDefaultText(text);
  },
  /** Options (Free) */
  Options:Question.Options.extend({
    getItemProto:function(json, i) {
      return OptionMutable;
    },
    resetIndexes:function() {
      this.soix = 0;
    },
    selectByValue:function(value) {
      this.selectMutable(value);
    },
    getSel:function() {
      return this.getSingle();
    },
    getSingle:function() {
      return this[0];
    },
    isSel:function() {
      return this.getSingle().isChanged();
    }
  }),
  //
  asDummy:function(desc) {
    return this.revive(this._dummy(desc));
  },
  _dummy:function(desc) {  
    return Question._dummy.call(this).aug({
      'uid':'$dummy',
      'type':C_Question.TYPE_FREE,
      'desc':desc || 'Free Text'
    })
  }
})
/**
 * QuestionDate */
QuestionDate = QuestionFree.extend({
  onload:function() {
    QuestionFree.onload.call(this);
    switch (this.uid.substr(this.uid.length - 1)) {
      case '_':
        this.format = DateValue.FMT_DEFAULT;
        break;
      case '%':
        this.format = DateValue.FMT_VERBOSE;
        break;
      default:
        this.format = DateValue.FMT_SENTENCE;
    }
  },
  //
  asDummy:function(desc, format) {
    var uid;
    if (format && format < DateValue.FMT_DEFAULT)
      uid = (format == DateValue.FMT_VERBOSE) ? '%date%' : '%date';
    else
      uid = '%date_';
    return this.revive(this._dummy(desc, uid));
  },
  asDummy_verbose:function(desc) {
    return QuestionDate.asDummy(desc, DateValue.FMT_VERBOSE);
  },
  asDummy_sentence:function(desc) {
    return QuestionDate.asDummy(desc, DateValue.FMT_SENTENCE);
  },
  _dummy:function(desc, uid) {
    return Question._dummy.call(this, null, ['unknown']).aug({
      uid:uid,
      type:C_Question.TYPE_CALENDAR,
      desc:desc || 'Date Entry'
    })
  }
})
/**
 * QuestionMulti - single and multi options */
QuestionMulti = Question.extend({
  multi:true,
  reviveOptions:function() {
    this.Opts = this.Options.reviveFrom(this, this.Opts || this.opts);
    this._Saves = OptionOther.serializeSaves(this.Opts.getSaves());
  },
  thaw:function(args) {
    this._RestoreArgs.apply(args);
    this.Opts.select(args[args.SELS], args[args.DELS], args[args.MOTEXTS]);
  },
  freeze:function() {
    var sels = this.Opts.sel;
    var dels = this.Opts.del;
    var motexts = this.Opts.getMultiOtherTexts();
    return this._RestoreArgs.from(sels, dels, null, motexts);
  },
  updateCustomOthers:function(/*Option[]*/saves) {
    var sv = OptionOther.asSaveValues(saves);
    var s = OptionOther.serializeSaves(sv);
    if (s != this._Saves) {
      this._Saves = s;
      Ajax.Templates.saveCustomOthers(this, s);
      this.Opts.setSaved(saves);
      this.Others = sv;
    }
  },
  setByOptions:function(/*Option[]*/multis, /*Option[]*/others) {
    var sels = multis.concat(others).filter(function(opt){return (opt.selected) ? opt.index : null});
    var dels = multis.filter(function(opt){return (opt.deleted) ? opt.index : null});
    var motexts = others.filter(function(opt){return (opt.selected) ? opt.getSaveValue() : null});
    this.Opts.select(sels, dels, motexts);
  },
  setByValue:function(/*string|string[]*/values) {
    if (values == null)
      this.reset();
    else 
      this.Opts.selectByValue(values);
    return this;
  },
  /** Options (Multi) */
  Options:Question.Options.extend({
    reviveFrom:function(q, opts) {  
      if (opts) {
        var self = this.revive(opts);
        self.q = q;
        self.loix = q.loix;
        self.def = q.def;
        self.reset();
        return self;
      }
    },
    getItemProto:function(json, i) {
      if (i < this.mix)
        return Option;
      else
        return (i <= this.loix) ? OptionMulti : OptionOther;
    },
    resetIndexes:function() {
      var q = this.q;
      this.mix = q.mix;
      this.soix = null;
      this.moix = q.loix + 1;
    },
    resetSels:function() {
      this.select(null, null);
    },
    resetOpts:function() {
      this.removeOthers();
      this.each(function(opt) {
        opt.reset();
      })
      if (this.q.Others) 
        this.append(OptionOther.asSavedCustoms(this.q.Others, this.length));
      if (this.q.other)
        this.appendOther();
    },
    select:function(sels, dels, motexts) {
      this.sel = (sels && sels.sortNumeric()) || [];
      this.del = (dels && dels.sortNumeric()) || [];
      this.unsel = [];
      sels = (this.sel.length) ? this.sel.reset() : this.def.reset();
      dels = this.del.reset();
      for (var i = 0; i < this.length; i++) {
        this[i].index = i;
        this[i].selected = false;
        if (i < this.moix) {
          if (i === sels.current()) {
            this[i].selected = true;
            sels.next();
          } else {
            if (i >= this.mix) 
              if (i === dels.current()) {
                this[i].deleted = true;
                dels.next();
              } else {
                this[i].deleted = false;
                this.unsel.push(i);
              }
          }
        }
      }
      if (motexts) {
        this.fillTo(sels.end() + 1);
        for (var i = motexts.length - 1; i >= 0; i--) {
          var ix = sels.current();
          this[ix].setText(motexts[i]);
          this[ix].selected = true;
          sels.prev();
        }
      }
    },
    selectByValue:function(value) {
      if (Array.is(value))
        this.selectByValues(value);
      else
        this.selectByValues([value]);
    },
    selectByValues:function(values) {
      var sels = [];
      var ix, value;
      for (var i = 0; i < values.length; i++) {
        value = values[i];
        ix = this.findValue(value);
        if (ix == -1) 
          ix = this.setLastMutable(value);
        sels.push(ix);
      }
      this.select(sels);
    },
    getSelText:function(jt) {
      var opt = this.getSel();
      if (opt) {
        var text = opt.text;
        if (text.indexOf('{all}') >= 0) 
          text = text.replace(/\{all\}/, this.getUnselText());
        return text;
      } else {
        return this.joinText(this.getTexts(), jt, true);
      }
    },
    getUnselText:function(jt) {
      return this.joinText(this.getTexts(this.getUnsels()), jt, false);
    },
    getSingles:function() {
      return (this.mix == 0) ? null : this.slice(0, this.mix);
    },
    getMultis:function() {
      return this.slice(this.mix, this.moix);
    },
    getSingleOther:function() {
      return null;
    },
    getMultiOthers:function() {
      return this.slice(this.moix);
    },
    removeOthers:function() {
      this.remove(this.moix, this.length);
    },
    getSaves:/*Option[]*/function() {
      return this.filterOn('saved');
    },
    setSaved:function(/*Option[]*/saves) {
      var map = Map.from(saves, 'index');
      this.getMultiOthers().each(function(opt) {
        opt.saved = Boolean.from(map[opt.index]);
      })
    },
    //
    getSel:/*Option*/function() {  
      var opts = this.getSels();
      if (opts[0].index < this.mix)
        return opts[0];
    },
    getSels:/*Option[]*/function() {  
      return this.getOpts(this.getSelIx());
    },
    getUnsels:function() {
      return this.getOpts(this.unsel);
    },
    getSelIx:function() {
      return (this.sel.length) ? this.sel : this.def;
    },
    setMutable:function(ix, text) {
      this.fillTo(ix);
      this[ix].setText(text);
      return this[ix];
    },
    setLastMutable:function(text) {
      var ix = this.length - 1;
      this[ix].setText(text);
      this.appendOther(); 
      return ix;
    },
    getMultiOtherTexts:function() {
      var a = [];
      for (var i = this.moix; i < this.length; i++) 
        if (this[i].selected)
          a.push(this[i].getText());
      return a;
    },
    fillTo:function(lastIx) {
      for (var i = this.length; i <= lastIx; i++) 
        this.appendOther();
    },
    popOthers:function() {
      while (this.length > this.moix)
        this.pop();
    },
    joinText:function(opts, type, and) {
      switch (type) {
        case 1:  // LF
        case 2:  // BULLET
          return opts.join('<br/>');
        case 3:  // SPACE
          return opts.join(' ');
        default:
          if (opts.length == 1) {
            return opts[0];
          } else {
            var last = opts.pop();
            return opts.join(', ') + (and ? ' and ' : ' or ') + last;
          }
      }
    }
  })
})
/** 
 * QuestionCombo */
QuestionCombo = QuestionMulti.extend({
  thaw:function(args) {
    this._RestoreArgs.apply(args);
    this.Opts.select(args[args.SELS], args[args.DELS], args[args.MOTEXTS]);
    this.Opts.getSingleOther().setText(args[args.SOTEXT]);
  },
  freeze:function() {
    var sels = this.Opts.sel;
    var dels = this.Opts.del;
    var motexts = this.Opts.getMultiOtherTexts();
    var sotext = this.Opts.getSingleOther().getText();
    return this._RestoreArgs.from(sels, dels, sotext, motexts);
  },
  updateSingle:null,
  updateMultis:null,
  updateCombo:function(/*Option*/single, /*Option[]*/multis, /*Option[]*/others) {
    multis.unshift(single);
    QuestionMulti.updateMultis.call(this, multis, others);
  },
  /** Options (Combo) */
  Options:QuestionMulti.Options.extend({
    getItemProto:function(json, i) {
      if (i < this.mix)
        return (i < this.mix - 1) ? Option : OptionOther;
      else
        return (i <= this.loix) ? OptionMulti : OptionOther;
    },
    resetIndexes:function() {
      QuestionMulti.Options.resetIndexes.call(this, this.q);
      this.soix = this.q.mix - 1;
    },
    selectByValue:function(value) {
      return Question.Options.getSingleOther.call(this, value);
    },
    selectByValues:function(/*[single,multi,multi,multi,..]*/values) {
      var ix = this.selectByValue(values.shift());
      QuestionMulti.Options.selectByValues.call(this, values);
      this.sel.unshift(ix);
    },
    getSingleSel:/*Option*/function() {
      return this[this.getSelIx()[0]];
    },
    getSelText:function() {
      var texts = this.getSelTexts();
      return texts.shift() + ': ' + this.joinText(texts);
    },
    getSingleOther:function() {
      return Question.Options.getSingleOther.call(this);
    }
  })
})
/**
 * QuestionComboAllergy */
QuestionComboAllergy = QuestionCombo.extend({
  // TODO
})
/**
 * Option */
Option = Object.Rec.extend({
  /*
   uid
   desc
   text
   shape
   coords
   syncId
   cpt
   tcat
   */
  index:null,
  selected:null,
  onload:function() {
    this.text = Question.fixTags(this.text || this.uid) || '';
    this.blank = this.text.substr(0, 1) == '(';
  },
  reset:function() {}
}),
/**
 * OptionMulti */
OptionMulti = Option.extend({
  deleted:null
})
/**
 * OptionMutable */
OptionMutable = Option.extend({
  mutable:true,
  onload:function() {
    Option.onload.call(this);
    if (Object.isUndefined(this._uid)) {
      this._uid = this.uid;
      this._text = this.text;
      this._blank = this.blank;
    }
  },
  setText:function(text) {
    text = String.trim(String.denull(text));
    if (text != this.text) {
      if (text == '') {
        this.reset();
      } else {
        this.text = text;
        this.uid = text;
        this.blank = null;
      }
    }
  },
  setDefaultText:function(text) {
    text = String.trim(String.denull(text));
    if (text) {
      this.text = text;
      this._text = text;
      this.blank = false;
      this._blank = false;
    }
  },
  getText:function(text) {
    return this.text;
  },
  isChanged:function() {
    return this.text != this._text;
  },
  reset:function() {
    this.text = this._text;
    this.uid = this._uid;
    this.blank = this._blank;
    this.selected = false;
  }
})
/**
 * OptionOther */
OptionOther = OptionMutable.extend({
  other:true,
  saved:null,
  getText:function() {
    if (this.text != this._text)
      return this.text;   
  },
  setText:function(text) {
    var a = String.denull(text).split('~');
    text = a[0];
    this.cpt = a[1];
    this.tcat = a[2];
    OptionMutable.setText.call(this, text);
  },
  getSaveValue:function() {
    var a = [this.text].pushIfNotNull(this.cpt).pushIfNotNull(this.tcat);
    return a.join('~');
  },
  setFromIpc:function(/*Ipc*/rec) {
    if (rec) {
      this.setText(rec.name + '~' + rec.ipc + '~' + rec.cat);
    } else {
      this.text = this._text;
      this.uid = this._uid;
      this.blank = this._blank;
    }
  },
  //
  create:function(index) {
    return OptionOther.revive({
      uid:'other',
      text:'other',
      desc:'other',
      index:index});
  },
  asSavedCustoms:function(values, ix) {
    var a = [];
    values.each(function(value) {
      a.push(OptionOther.asSaved(value, ix++));
    })
    return a;
  },
  asSaved:function(value, index) {
    var a = value.split('~');
    var text = a[0];
    var cpt = a.length && a[1];
    var tcat = a.length && a[2];
    return OptionOther.revive({
      saved:true,
      uid:'other',
      text:text,
      desc:text,
      cpt:cpt,
      tcat:tcat,
      index:index});
  },
  asSaveValues:function(opts) {
    var a = [];
    Array.each(opts, function(o) {
      a.push(o.getSaveValue());
    })
    return a;
  },
  serializeSaves:function(a) {
    return a.join('`');
  }
})
/**
 * Test */
Test = Object.Rec.extend({
  /*
   id
   args
   op
   */
  getQrefArg:function() {
    switch (this.id) {
      case C_Test.IS_SEL:
      case C_Test.NOT_SEL:
        return this.args[0];
    }
  }
})
Tests = Object.RecArray.of(Test, {
  //
  getQrefs:function() {
    var qref, qrefs = {};
    this.each(function(test) {
      qref = test.getQrefArg();
      if (qref)
        qrefs[qref] = qref;
    })
    return Map.keys(qrefs);
  }
})
/**
 * Action */
Action = Object.Rec.extend({
  /*
   Tests
   id
   args
   */
  onload:function() {
    this.setr('Tests', Tests);
  }
})
Actions = Object.RecArray.of(Action, {
  //
})
/**
 * ParInfo (JParInfo format) */
ParInfo = Object.Rec.extend({
  onload:function() {
    this.questions = Questions.reviveFromParInfo(this);
    this.Questions = this.questions;
  },
  reset:function() {
    if (this.questions) 
      for (var i = 0; i < this.questions.length; i++) 
        this.questions[i].reset();
    return this;
  },
  getQuestionByDsync:function(dsync) {
    for (var i = 0; i < this.questions.length; i++) {
      if (this.questions[i].dsync == dsync)
        return this.questions[i];
    }
  },
  //
  ajax:function() {
    return {
      fetch:function(pid, callback) {
        Ajax.JTemplates.getParInfo(pid, callback);
      },
      fetchByRef:function(ref, tid, callback) {
        Ajax.JTemplates.getParInfoByRef(ref, tid, callback);
      }
    }
  },
  reviveOne:function(pis) {
    return this.revive(pis[0]);
  }
})
//
ParInfos = Object.RecArray.of(ParInfo, {
  //
  ajax:function() {
    return {
      fetch:function(pid, callback) {
        Ajax.JTemplates.getParInfos(pid, callback);
      }
    }
  }
})
