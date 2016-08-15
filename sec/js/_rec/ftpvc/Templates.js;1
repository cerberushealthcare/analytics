/**
 * ParInfos
 */
ParInfos = {
  revive:function(pis) {
    return ParInfo.reviveAll(pis);
  }
}
/**
 * Rec ParInfo
 */
ParInfo = Object.Rec.extend({
  onload:function() {
    this.questions = Questions.reviveFrom(this);
  },
  reviveOne:function(pis) {
    return this.revive(pis[0]);
  }
})
/**
 * RecArray Questions
 */
Questions = Object.RecArray.extend({
  reviveFrom:function(pi) {
    this.pi = pi;
    return this.revive(pi.questions);
  },
  getItemProto:function() {
    return Question;
  },
  onreviveitem:function(json, i) {
    json.par = this.pi.par;
    json.qt = this.pi.par.qts[i];
  }
})
/**
 * Rec Question 
 * Single options
 */
Question = Object.Rec.extend({
  TYPES:{STANDARD:0,ALLERGY:1,CALC:2,DATE:3,FREE:4,MED:5,BUTTON:6,COMBO:7,DATA_HM:8,HIDDEN:9},
  //
  getProto:function(json) {
    switch (json.type) {
      case Question.TYPES.CALC:
      case Question.TYPES.FREE:
      case Question.TYPES.DATE:
        return QuestionFree;
      case Question.TYPES.ALLERGY:
        return QuestionAllergy;
      case Question.TYPES.COMBO:
        return QuestionCombo;
      default:
        return (json.mix) ? QuestionMulti : Question;
    }
  },
  onload:function() {
    if (this.opts)
      this.opts = this.Options.reviveFrom(this); 
  },
  _RestoreArgs:Object.create({
    SELS:0,DELS:1,SOTEXT:2,MOTEXTS:3,
    from:function(sels, dels, sotext, motexts) {
      return [sels, dels || [], sotext, motexts || []];
    }
  }),
  pop:function() {
    return QuestionPop.pop(this);
  },
  /*
   * Deserialize question from persisted state
   * @arg array args [sels,dels,sotext,motexts]
   */
  thaw:function(args) {
    this._RestoreArgs.apply(args);
    this.opts.select(args[args.SELS]);
    this.opts.getSingleOther().setText(args[args.SOTEXT]);
  },
  /*
   * Serialize question into persistable state
   * @return [sels,dels,sotext,motexts]
   */
  freeze:function() {
    var sels = this.opts.sel;
    var sotext = this.opts.getSingleOther().getText();
    return this._RestoreArgs.from(sels, null, sotext);
  },
  /*
   * @arg Option opt 
   */
  updateSingle:function(opt) {
    this.opts.update(opt);
    this.opts.select([opt.index]);
  },
  /*
   * @arg string desc 
   * @arg string[] uids for options ['yes','no']
   * @arg selected 'yes' (optional)
   * @return Question
   */
  asDummy:function(desc, uids, selected) {
    return this.revive(this._dummy(desc, uids, selected)).aug({
      /*
       * @callback(value, ix) when selected
       */
      pop:function(callback) {
        return QuestionPop.pop(this).bubble('onupdate', this._popCallback.bind(this, callback));
      },
      popToggle:function(callback) {
        if (this.opts.length == 2) {
          this.opts.toggle();
          this._popCallback(callback);
        } else {
          this.pop(callback);
        }
      },
      _popCallback:function(callback) {
        callback(this.opts.getSelText(), this.opts.getSelIx());
      }
    });
  },
  _dummy:function(desc, uids, selected) {  // object to revive an empty question
    var opts = [];
    var sel = [];
    uids = uids || ['dummy'];
    Array.forEach(uids, function(uid, i) {
      if (uid == selected)
        sel.push(i);
      opts.push({'uid':uid}); 
    })
    return Object.create({
      'id':0,
      'uid':'dummy',
      'type':Question.TYPES.STANDARD,
      'desc':desc,
      'sel':sel,
      'unsel':[],
      'def':[],
      'opts':opts,
      'loix':opts.length - 1});
  },
  /*
   * Array Options (Standard)
   */
  Options:Object.RecArray.extend({
    reviveFrom:function(q) {
      this.q = q;
      this.def = q.def;
      this.loix = q.loix;
      this.setIndexes(q);
      this.reset();
      return this.revive(q.opts, Option.setPrototyper(this.prototyper.bind(this)));
    },
    prototyper:function(json, i) {
      return (i <= this.loix) ? Option : OptionMutable;
    },
    setIndexes:function(q) {
      this.soix = q.loix + 1;
    },
    reset:function() {
      this.select(this.q.sel);
    },
    /*
     * @arg Option opt
     */
    update:function(opt) {
      this[opt.index] = opt;
    },
    /*
     * @return true if any selected (not just defaulted)
     */
    isSel:function() {
      return this.sel.length > 0;
    },
    /*
     * @arg int[] sels
     */
    select:function(sels) {
      this.sel = sels;
      selix = this.getSelIx();
      for (var i = 0; i < this.length; i++) { 
        this[i].index = i;
        this[i].selected = (i == selix)
      }
    },
    /*
     * @arg string value
     * @return int selected ix
     */
    selectByValue:function(value) {
      var ix = this.findValue(value, 0, this.soix);
      if (ix > -1) 
        this.select([ix]);
      else 
        ix = this.selectMutable(value);
      return ix;
    },
    toggle:function() {
      this.sel = (this.getSelIx() == 0) ? [1] : [0];
    },
    /*
     * @return string
     */
    getSelText:function() {
      return this.getSel().text;
    },
    /*
     * @return [Option,..]
     */
    getSingles:function() {
      return Object.clone(this.slice(0, this.soix));
    },
    getMultis:function() {
      return [];
    },
    /*
     * @return Option
     */
    getSingleOther:function() {
      return this[this.soix];
    },
    getMultiOthers:function() {
      return [];
    },
    //
    getOpts:function(ixs) {
      var a = [];
      Array.forEach(ixs, function(i) {
        a.push(this[i]);
      });
      return a;
    },
    getSel:function() {
      return this[this.getSelIx()];
    },
    getSelIx:function() {
      return (this.sel.length) ? this.sel[0] : this.def[0];
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
    }
  })
})
/**
 * Question QuestionFree
 * One single option (freetext)
 */
QuestionFree = Question.extend({
  _dummy:function(desc) {  
    return Question._dummy.call(this).aug({
      'uid':'$dummy',
      'type':Question.TYPES.FREE,
      'desc':desc || 'Free Text'
    })
  },
  /*
   * @arg string value
   */
  setByValue:function(value) {
    this.opts.selectByValue(value);
    return this;
  },
  /*
   * @return string
   */
  getValue:function() {
    return this.opts.getSingle().getText();
  },
  /*
   * Array Options (Free)
   */
  Options:Question.Options.extend({
    prototyper:function(json, i) {
      return OptionMutable;
    },
    setIndexes:function(q) {
      this.soix = 0;
    },
    selectByValue:function(value) {
      this.selectMutable(value);
    },
    getSingle:function() {
      return this.getSingleOther();
    }
  })
})
/**
 * QuestionFree QuestionDate
 */
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
  asDummy:function(desc, format) {
    var uid;
    if (format && format < DateValue.FMT_DEFAULT)
      uid = (format == DateValue.FMT_VERBOSE) ? '%date%' : '%date';
    else
      uid = '%date_';
    return this.revive(this._dummy(desc, uid));
  },
  _dummy:function(desc, uid) {  
    return Question._dummy.call(this).aug({
      'uid':uid,
      'type':Question.TYPES.DATE,
      'desc':desc || 'Date Entry'
    })
  }
})
/**
 * Question QuestionMulti
 * Single and multi options
 */
QuestionMulti = Question.extend({
  multi:true,
  thaw:function(args) {
    this._RestoreArgs.apply(args);
    this.opts.select(args[args.SELS], args[args.DELS], args[args.MOTEXTS]);
  },
  freeze:function() {
    var sels = this.opts.sel;
    var dels = this.opts.del;
    var motexts = this.opts.getMultiOtherTexts();
    return this._RestoreArgs.from(sels, dels, null, motexts);
  },
  /*
   * @arg Option opt 
   */
  updateSingle:function(opt) {
    this.opts.select([opt.index]);
  },
  /*
   * @arg Option[] multis
   * @arg Option[] others
   */
  updateMultis:function(multis, others) {
    this.opts.updateAll(multis);
    var sels = multis.concat(others).filter(function(opt){return (opt.selected) ? opt.index : null});
    var dels = multis.filter(function(opt){return (opt.deleted) ? opt.index : null});
    var motexts = others.filter(function(opt){return (opt.selected) ? opt.text : null});
    this.opts.select(sels, dels, motexts);
  },
  /*
   * Array Options (Multi)
   */
  Options:Question.Options.extend({
    prototyper:function(json, i) {
      if (i < this.mix)
        return Option;
      else
        return (i <= this.loix) ? OptionMulti : OptionMutable;
    },
    setIndexes:function(q) {
      this.mix = q.mix;
      this.soix = null;
      this.moix = q.loix + 1;
      this.unsel = q.unsel;
      this.del = [];
    },
    reset:function() {
      this.unsel = this.q.unsel;
      this.del = this.q.del;
      this.select(this.q.sel, null);
    },
    /*
     * @arg Option[] opts
     */
    updateAll:function(opts) {
      for (var i = 0; i < opts.length; i++)
        this.update(opts[i]);
    },
    select:function(sels, dels, motexts) {
      this.sel = sels;
      this.del = dels || [];
      this.unsel = [];
      sels = (sels.length) ? sels.reset() : this.def.reset();
      dels = this.del.reset();
      for (var i = 0; i < this.length; i++) {
        this[i].index = i;
        if (i < this.moix) {
          if (i === sels.current()) {
            this[i].selected = true;
            sels.next();
          } else {
            this[i].selected = false;
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
    selectByValue:null,
    selectByValues:function(values) {
      var sels = [];
      var ix;
      Array.forEach(values, function(value) {
        ix = this.findValue(value);
        if (ix == -1) 
          ix = this.setLastMutable(text);
        sels.push(ix);
      })
      this.select(sels);
    },
    getSelText:function() {
      return this.joinText(this.getSelTexts());      
    },
    getSingles:function() {
      return (this.mix == 0) ? null : Object.clone(this.slice(0, this.mix));
    },
    getMultis:function() {
      return Object.clone(this.slice(this.mix, this.moix));
    },
    getSingleOther:function() {
      return null;
    },
    getMultiOthers:function() {
      return this.slice(this.moix);
    },
    //
    getTexts:function(a) {
      var a = [];
      Array.forEach(this.getSelIx(), function(i) {
        a.push(this[i].text);
      });
      return a;
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
    appendOther:function() {
      this.push(OptionNewOther.create(this.length));
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
 * QuestionMulti QuestionCombo
 */
QuestionCombo = QuestionMulti.extend({
  thaw:function(args) {
    this._RestoreArgs.apply(args);
    this.opts.select(args[args.SELS], args[args.DELS], args[args.MOTEXTS]);
    this.opts.getSingleOther().setText(args[args.SOTEXT]);
  },
  freeze:function() {
    var sels = this.opts.sel;
    var dels = this.opts.del;
    var motexts = this.opts.getMultiOtherTexts();
    var sotext = this.opts.getSingleOther().getText();
    return this._RestoreArgs.from(sels, dels, sotext, motexts);
  },
  updateSingle:null,
  updateMultis:null,
  /*
   * @arg Option single 
   * @arg Option[] multis
   * @arg Option[] others
   */
  updateCombo:function(single, multis, others) {
    multis.unshift(single);
    QuestionMulti.updateMultis.call(this, multis, others);
  },
  /*
   * Array Options (Combo)
   */
  Options:QuestionMulti.Options.extend({
    prototyper:function(json, i) {
      if (i < this.mix)
        return (i < this.mix - 1) ? Option : OptionMutable;
      else
        return (i <= this.loix) ? OptionMulti : OptionMutable;
    },
    setIndexes:function(q) {
      QuestionMulti.Options.setIndexes.call(this, q);
      this.soix = q.mix - 1;
    },
    selectByValue:function(value) {
      return Question.Options.getSingleOther.call(this, value);
    },
    /*
     * @arg Option[] values [single,multi,multi,multi,..]
     */
    selectByValues:function(values) {
      var ix = this.selectByValue(values.shift());
      QuestionMulti.Options.selectByValues.call(this, values);
      this.sel.unshift(ix);
    },
    /*
     * @return Option
     */
    getSingleSel:function() {
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
 * QuestionCombo QuestionComboAllergy
 */
QuestionComboAllergy = QuestionCombo.extend({
  // TODO
})
/**
 * Rec Option
 */
Option = Object.Rec.extend({
  index:null,
  selected:null,
  onload:function() {
    this.text = this.text || this.uid;
    this.blank = this.text.substr(0, 1) == '(';
  }
}),
/**
 * Option OptionMutable
 */
OptionMutable = Option.extend({
  mutable:true,
  setText:function(text) {
    text = String.trim(String.denull(text));
    if (text == '') {
      this.reset();
    } else if (text != this.text) {
      this._text = this.text;
      this._uid = this.uid;
      this._blank = this.blank;
      this.text = text;
      this.uid = text;
      this.blank = null;
    }
  },
  reset:function() {
    if (this._text) {
      this.text = this._text;
      this.uid = this._uid;
      this.blank = this._blank;
    }
  },
  getText:function() {
    if (this._text)
      return this.text;  // return only modified text 
  }
})
/**
 * Option OptionMulti
 */
OptionMulti = Option.extend({
  deleted:null
})
/**
 * OptionMutable OptionNewOther
 */
OptionNewOther = OptionMutable.extend({
  uid:'other',
  text:'other',
  desc:'other',
  create:function(index) {
    var o = OptionMutable.create.call(this);
    o.index = index;
    return o;
  }
})
