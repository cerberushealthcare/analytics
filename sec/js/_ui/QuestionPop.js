/**
 * QuestionPop 
 */
QuestionPop = {
  //
  pop:function(/*Question*/q, onupdate/*Question*/, /*<bool>*/noToggle, /*int<POS_CURSOR>*/pos) {
    switch (q.type) {
      case C_Question.TYPE_COMBO:
        return QPopCombo.pop(q, onupdate, pos);
      case C_Question.TYPE_FREE:
        return QPopFreeText.pop(q, onupdate, pos);
      case C_Question.TYPE_CALC:
        return QPopCalc.pop(q, onupdate, pos);
      case C_Question.TYPE_CALENDAR:
        return QPopCalendar.pop(q, onupdate, pos);
      case C_Question.TYPE_ALLERGY:
        return;
      case C_Question.TYPE_MED:
        return QPopLegacyMed.pop(q, onupdate, pos);
      case C_Question.TYPE_BUTTON:
        if (! noToggle) {
          q.Opts.toggle();
          onupdate(q);
          return;
        }
    }
    return (q.multi) ? QPopMulti.pop(q, onupdate, pos) : QPopStandard.pop(q, onupdate, pos);
  }
}
QuestionPopEntry = {
  //
  pop:function(/*Question*/q, onupdate/*Question*/, /*int<POS_CENTER>*/pos) {  
    var My = this;
    var self = QuestionPop.pop(q, onupdate, true, pos || Pop.POS_CENTER);
    if (self.clearTile == null) {
      self.aug({
        init:function() {
          self.clearTile = My.ClearTile.create().before(self.content.firstChild)
            .bubble('onclear', self.clear_onclick)
            .bubble('oncancel', self.close);
        },
        clear_onclick:function() {
          self.q.setByValue(null);
          self.close();
          self.onupdate(self.q);
        },
        onclose:function() {
          self.clearTile.hide();
        }
      })
    } else {
      self.clearTile.show();
    }
    return self;
  },
  pop_lastPos:function(q, onupdate) {
    this.pop(q, onupdate, Pop.POS_LAST);
  },
  ClearTile:{
    create:function() {
      return Html.Div.create('pop-cmd-qp').extend(function(self) {
        return {
          onclear:function() {},
          oncancel:function() {},
          //
          init:function() {
            Html.Anchor.create('cmd delete-red', 'Clear').into(self)
              .bubble('onclick', self, 'onclear');
            Html.Anchor.create('cmd none', 'Cancel').into(self)
              .bubble('onclick', self, 'oncancel');
          }
        }
      })
    }
  }
}
QPop = {
  aug:function(augs) {
    return Object.create({
      pop:function(q, onupdate, pos) {
        return Html.Pop.singleton_pop.apply(this, arguments);
      }
    }, augs);
  }
}
/**
 * QPop QPopStandard
 *   SingleOptsTile singleTile
 */
QPopStandard = QPop.aug({
  create:function() {
    return Html.Pop.create().extend(QPopStandard, function(self) {
      return {
        onupdate:function(q) {},  
        //
        init:function() {
          self.singleTile = SingleOptsTile.create(self.content).hide()
            .bubble('onselectopt', self.singleTile_onselect);
        },
        //
        pop:function(/*Question*/q, /*fn(Question)*/onupdate, /*int<POS_CURSOR>*/pos) {
          self.POP_POS = pos || Pop.POS_CURSOR;
          if (self.POP_POS == Pop.POS_CURSOR)
            Pop.cacheMousePos();
          var args = Array.prototype.slice.call(arguments);
          async(function() {
            Html.Pop.Proto(self).pop.apply(self, args);
          })
          return self;
        },
        onshow:function(q, onupdate) {
          self.load(q);
          if (onupdate)
            self.onupdate = onupdate;
          self.resize();
          self.reposition();
        },
        load:function(q) {
          self.q = q;
          self.setCaption(q.desc);
          self.sopts = Object.clone(q.Opts.getSingles());
          self.design = self.getDesign();
          self.loadSingleTile();
        },
        resize:function() {
          self.singleTile.resize();
        },
        reset:function() {
          self.singleTile.reset();
        },
        //
        singleTile_onselect:function(opt) {
          self.q.setByValue(opt.text);
          self.close();
          self.onupdate(self.q);
        },
        loadSingleTile:function() {
          self.singleTile.load(self.sopts, self.design.scols, self.q.Opts.getSingleOther()).show();
        },
        getDesign:function() {
          var s = self.sopts.length;
          var scols = 3;
          if (s > 16)
            scols = 0;
          else if (s > 10)
            scols = 4;
          return {
            'scols':scols}
        }
      }
    })
  }
})
/**
 * QPopStandard QPopMulti
 *   SingleOptsTile singleTile
 *   MultiOptsTile multiTile
 */
QPopMulti = QPop.aug({
  create:function() {
    return QPopStandard.create().extend(QPopMulti, function(self, parent) {
      return {
        onupdate:function(q) {},
        //
        init:function() {
          self.singleTile.addClass('mb5');
          self.multiTile = MultiOptsTile.create(self.content)
            .bubble('onapplyopts', self, 'multiTile_onapply')
            .hide(); 
        }, 
        load:function(q) {
          self.mopts = Object.clone(q.Opts.getMultis());
          parent(QPopStandard).load(q);
          self.loadMultiTile();
        },
        resize:function() {
          self.singleTile.resize();
          self.multiTile.resize();
        },
        setMaxHeight:function(i) {
          if (! self.q.multiOnly)
            i -= self.singleTile.getHeight();
          self.multiTile.setMaxHeight(i);
        },
        reset:function() {
          self.singleTile.reset();
          self.multiTile.reset();
        },
        //
        multiTile_onapply:function(multis, others, customs) {
          self.q.setByOptions(multis, others);
          self.q.updateCustomOthers(customs);
          self.close();
          self.onupdate(self.q);
        },
        loadSingleTile:function() {
          if (self.q.multiOnly || Array.isEmpty(self.sopts)) {
            self.singleTile.hide();
          } else {
            parent(QPopStandard).loadSingleTile();
            self.singleTile.show();
          }
        },
        loadMultiTile:function() {
          if (Array.isEmpty(self.mopts)) {
            self.multiTile.hide();
          } else {
            var others = self.q.hideOthers ? [] : Object.clone(self.q.Opts.getMultiOthers());
            var asIpcOthers = self.q.Opts.hasTracking();
            self.multiTile.load(self.mopts, self.design.mcols, self.design.tristate, others, asIpcOthers, self.q.mcap).show();
          }
        },
        getDesign:function() {
          var s = self.sopts.length, m = self.mopts.length;
          var scols = 3, mcols = 1, tristate;
          if (m > 60) {
            mcols = 4; scols = 6;
          } else if (m > 20) {
            mcols = 3; scols = 5;
          } else if (m > 10) {
            mcols = 2; scols = 4;
          } else if (s > 10) {
            scols = 4;
          }
          if (s > 16)
            scols = 0;
          return {
            'scols':scols,
            'mcols':mcols,
            'tristate':(self.q && self.q.btmu)}
        }
      }
    })
  }
})
/**
 * QPopMulti QPopCombo
 *   Tile page1
 *     SingleOptsTile singleTile
 *   Tile page2
 *     MultiOptsTile multiTile
 */
QPopCombo = QPop.aug({
  create:function() {
    return QPopMulti.create().extend(QPopCombo, function(self, parent) {
      return {
        onupdate:function(q) {},
        //
        init:function() {
          self.page1 = Html.Tile.create(self.content).append(self.singleTile).hide();
          self.page2 = Html.Tile.create(self.content).append(self.multiTile).hide();
          self.multiTile.cap = self.createBackAnchorCap(self.multiTile.cap);
          self.singleTile.other.cb.caption('insert', 'Select Free Text');
        }, 
        load:function(q) {
          self.sopt = Object.clone(q.Opts.getSingleSel());
          self.mopts = Object.clone(q.Opts.getMultis());
          parent(QPopMulti).load(q);
          self.loadMultiTile();
          self.showPage(q.Opts.isSel() ? 2 : 1);
        },
        resize:function() {
          self.showPage(1);
          self.singleTile.resize();
          self.showPage(2);
          self.multiTile.resize();
          self.showPage();
        },
        //
        showPage:function(p) {
          if (p)
            self.p = p;
          else
            p = self.p;
          if (p == 1) {
            self.page2.hide();
            self.page1.show();
          } else {
            self.page1.hide();
            self.page2.show();
          }
        },
        singleTile_onselect:function(opt) {
          self.sopt = opt;
          self.showPage(2);
        },
        multiTile_onapply:function(multis, others) {
          self.q.updateCombo(self.sopt, multis, others);
          self.close();
          self.onupdate(self.q);
        },
        createBackAnchorCap:function(div) {
          div.setClass('ComboCap');
          var cap = Html.Tile.create(div, 'Cap');  
          Html.AnchorNoFocus.create('back', 'Back').bubble('onclick', self.back_onclick).into(div);
          return cap;
        },
        back_onclick:function() {
          self.showPage(1);
        }
      }
    })
  }
})
/**
 * Tile SingleOptsTile
 *   TableCol table [OptAnchor,..]
 *   ScrollDiv list [OptAnchor,..]
 *   Tile other
 *     InputText input
 *     CmdBar cb
 */
SingleOptsTile = {
  create:function(into) {
    var self = Html.Tile.create(into, 'SingleOptsTile');
    return self.aug({
      onselectopt:function(opt) {},
      //
      init:function() {
        self.table = SingleOptsTile.Table.create(self).bubble('onselectopt', self);
        self.list = SingleOptsTile.List.create(self).bubble('onselectopt', self);
        self.other = SingleOptsTile.Other.create(self).bubble('onselectopt', self);
      },
      load:function(/*Opt[]*/opts, /*int<0=scrolling>*/cols, /*[Opt]*/other) {
        self.cols = cols;
        if (cols == 0) {
          self.table.hide();
          self.list.load(opts).show();
        } else {
          self.table.load(opts, cols).show();
          self.list.hide();
        }
        if (other)
          self.other.load(other).show();
        else
          self.other.hide();
        return self;
      },
      reset:function() {
        self.other.reset();
      },
      resize:function() {
        if (self.cols == 0)
          self.list.scrollToSelected();
        self.other.resize();
      }
    })
  },
  Table:{
    create:function(into) {
      var self = Html.Tile.create(into, 'SingleOptsTable');
      var table = Html.TableCol.create(self);
      return self.aug({
        onselectopt:function(opt) {},
        //
        load:function(opts, cols) {
          table.reset(cols);
          opts.forEach(function(opt) {
            table.add(OptAnchor.create(opt).bubble('onselectopt', self));
          });
          return self;
        }
      })
    }
  },
  List:{
    create:function(into) {
      var self = Html.ScrollDiv.create(into, 'SingleOptsList');
      return self.aug({
        onselectopt:function(opt) {},
        //
        load:function(opts) {
          self.clean();
          self.selected = null;
          opts.forEach(function(opt) {
            var a = OptAnchor.create(opt).bubble('onselectopt', self);
            self.add(a);
            if (opt.selected)
              self.selected = a;
          });
          return self;
        },
        scrollToSelected:function() {
          if (self.selected) 
            self.scrollTop = self.selected.offsetTop - 30;
        }
      })
    }
  },
  Other:{
    create:function(into) {
      var self = Html.Tile.create(into, 'SingleOtherTile');
      return self.aug({
        onselectopt:function(opt) {},
        //
        init:function() {
          self.input = Html.InputText.create().noSelectFocus().into(self).bubble('onfocus', self.input_onfocus);
          self.cb = Html.CmdBar.create(self).button('Insert Free Text', self.insert_onclick, 'none', 'insert');
        },
        load:function(opt) {
          self.opt = opt;
          self.input.setValue(opt.text);
          self.input.addClassIf('sel', opt.selected);
          self.setWidth();
          return self;
        },
        resize:function() {
          var w = self.getDim().width - 20;
          if (w > 0)
            self.setWidth(w);
        },
        reset:function() {
          self.setWidth();
        },
        //
        setWidth:function(w) {
          self.input.setWidth(w);
          self.cb.get('insert').setWidth(w);
        },
        insert_onclick:function() {
          self.opt.setText(self.input.getValue());
          self.onselectopt(self.opt);
        },
        input_onfocus:function() {
          if (self.input.getValue() == 'other')
            self.input.select();
        }
      })
    }
  }
}
/**
 * Anchor OptAnchor
 */
OptAnchor = {
  //
  create:function(/*Option*/opt) {
    var self = Html.AnchorNoFocus.create('OptAnchor');
    return self.aug({
      onselectopt:function(opt) {},
      //
      init:function() {
        self.opt = opt;
        self.setText(opt.uid);
        if (opt.selected) 
          self.addClass('sel');
      },
      onclick:function() {
        self.onselectopt(opt);
      }
    })
  }
}
/**
 * Tile MultiOptsTile
 *   Div cap
 *   Tile tile   
 *     TableCol table [OptCheck,..]
 *     Div others [OtherCheck,..]
 *   CmdBar cb
 */
MultiOptsTile = {
  create:function(into) {
    var self = Html.Tile.create(into, 'MultiOptsTile');
    return self.aug({
      onapplyopts:function(multis, others, customs) {},
      //
      init:function() {
        self.cap = Html.Div.create('Cap').into(self);
        self.tile = Html.Tile.create(self, 'MultiOpts').aug({
          scrollable:function(b) {
            this.addClassIf('scroll', b);
          },
          setMaxHeight:function(i) {
            if (this.getHeight() > i) {
              this.setHeight(i);
              this.scrollable(true);
            } 
          },
          reset:function() {
            this.scrollable(false);
            this.setHeight();
          }
        })
        self.table = MultiOptsTile.Table.create(self.tile);
        self.others = MultiOptsTile.Others.create(self.tile);
        self.cb = Html.CmdBar.create(self).button('Apply Checkmarked', self.apply_onclick, 'none', 'apply');
      },
      load:function(/*Opt[]*/multis, /*int*/cols, /*bool*/asTriChecks, /*Opt[]*/others, /*bool*/asIpcOthers, /*string*/cap) {
        self.multis = multis;
        self.cap.setText(self.removeHtml(cap));
        self.table.load(multis, cols, asTriChecks);
        self.others.load(others, asIpcOthers);
        self.setWidth();
        return self;
      },
      resize:function() {
        var w = self.getDim().width - 10;
        if (w > 0) {
          self.others.resize();
          self.setWidth(w);
        }
      },
      reset:function() {
        self.setWidth();
        self.tile.reset();
      },
      //
      setWidth:function(i) {
        self.cb.get('apply').setWidth(i);
      },
      setMaxHeight:function(i) {
        self.tile.setMaxHeight(i - self.cap.getHeight() - self.cb.height());
      },
      apply_onclick:function() {
        self.onapplyopts(self.multis, self.others.getOpts(), self.others.getSaveOpts());
      },
      removeHtml:function(d) {
        if (d) {
          d = d.replace(/<b>/g, "");
          d = d.replace(/<\/b>/g, "");
          d = d.replace(/<u>/g, "");
          d = d.replace(/<\/u>/g, "");
          return d;
        }
      }
    })
  },
  Table:{
    create:function(into) {
      var self = Html.TableVertical.create(into, 'table');
      return self.aug({
        load:function(opts, cols, asTriChecks) {
          var anchors = [];
          for (var i = 0; i < opts.length; i++) 
            anchors.push((asTriChecks) ? OptTriCheck.create(opts[i]) : OptCheck.create(opts[i]));
          self.vertload(cols, anchors);
          return self;
        }
      })
    }
  },
  Others:{
    create:function(into) {
      var self = Html.Div.create().into(into);
      return self.aug({
        load:function(opts, asIpc/*=false*/) {
          self.OtherCheck = asIpc ? OtherCheck_Ipc : OtherCheck;
          self.reset();
          self.opts = opts;
          self.loix = opts.end().index;
          opts.forEach(function(opt) {
            self.append(opt);
          })
        },
        getOpts:function() {
          return self.opts;
        },
        getSaveOpts:function() {
          var opts = [];
          Array.each(self.opts, function(opt) {
            opts.pushIfNotNull(opt._oc.getSave());
          })
          return opts;
        },
        resize:function() {
          Array.each(self.opts, function(opt) {
            opt._oc.resize();
          })
        },
        reset:function() {
          self.clean();
          self._ow = null;
        },
        //
        append:function(opt) {
          opt._oc = self.OtherCheck.create(self, opt).bubble('ontoggle', self.opt_ontoggle);
          return opt;
        },
        opt_ontoggle:function(opt) {
          if (opt.index == self.loix) { 
            self.loix++;
            self.opts.push(self.append(OptionOther.create(self.loix)));
          }
        }
      })
    }
  }
}
/**
 * Span OptCheck
 *   Check check
 *   Anchor anchor
 */
OptCheck = {
  /*
   * @arg Option opt
   */
  create:function(opt) {
    var self = Html.Span.create('OptCheck').setUnselectable();
    return self.aug({
      VALUES:{UNCHECKED:false,CHECKED:true},
      init:function() {
        self.check = Html.InputCheck.create().into(self)
          .bubble('onclick', self, 'toggle');
        self.anchor = Html.AnchorNoFocus.create().into(self).setText(self.getTextFrom(opt))
          .bubble('onclick', self, 'toggle');
        Html.Table.create(self).tbody().tr().td(self.check, 'check').td(self.anchor, 'anchor');
        self.setValue(self.getValueFrom(opt));
      },
      setValue:function(value) {
        self.value = value;
        opt.selected = value;
        if (value) {
          self.check.setCheck(true);
          self.anchor.setClass('sel');
        } else {
          self.check.setCheck(false);
          self.anchor.setClass('');
        }
      },
      getValue:function() {
        return self.value;
      },
      toggle:function() {
        self.setValue(! self.value);
      },
      //
      getTextFrom:function(opt) {
        return opt.uid;
      },
      getValueFrom:function(opt) {
        return opt.selected;
      }
    })
  }
}
/**
 * OptCheck OptTriCheck
 */
OptTriCheck = {
  VALUES:{UNCHECKED:0,CHECKED:1,STRIKETHRU:2},
  create:function(opt) {
    var self = OptCheck.create(opt);
    return self.aug({
      init:function() {
        self.setValue(self.getValueFrom(opt));
      },
      setValue:function(value) {
        self.value = value;
        switch (value) {
          case OptTriCheck.VALUES.UNCHECKED:
            opt.selected = false;
            opt.deleted = false;
            self.check.setCheck(false);
            self.anchor.setClass('');
            break;
          case OptTriCheck.VALUES.CHECKED: 
            opt.selected = true;
            opt.deleted = false;
            self.check.setCheck(true);
            self.anchor.setClass('sel');
            break;
          case OptTriCheck.VALUES.STRIKETHRU: 
            opt.selected = false;
            opt.deleted = true;
            self.check.setCheck(true);
            self.anchor.setClass('del');
            break;
        }
      },
      toggle:function() {
        var value = self.value + 1;
        if (value > OptTriCheck.VALUES.STRIKETHRU)
          value = 0;
        self.setValue(value);
      },
      //
      getValueFrom:function(opt) {
        if (opt.deleted)
          return OptTriCheck.VALUES.STRIKETHRU;
        else
          return (opt.selected) ? OptTriCheck.VALUES.CHECKED : OptTriCheck.VALUES.UNCHECKED;
      }
    })
  }
}
/**
 * Div OtherCheck
 *   Check check
 *   InputText input
 */
OtherCheck = {
  //
  create:function(/*<e>*/into, /*Option*/opt, /*[Proto]*/Input) {
    var My = this;
    var self = Html.Div.create('OtherCheck').setUnselectable().into(into);
    if (Input == null)
      Input = My.Input;
    return self.aug({
      ontoggle:function(opt) {},
      //
      init:function() {
        self.check = Html.InputCheck.create().into(self)
          .bubble('onclick', self.check_onclick);
        self.input = Input.create(self, opt)
          .bubble('onclick', self.input_onclick);
        self.save = My.SaveAnchor.create(self, opt);
        self.setValue(opt.selected);
        if (into._ow) 
          self.setWidth(into._ow);
      },
      setValue:function(value) {
        self.value = value;
        opt.selected = value;
        if (value) {
          self.check.setCheck(true);
          self.input.setClass('sel');
        } else {
          self.check.setCheck(false);
          self.input.setClass('');
        }
      },
      getValue:/*bool*/function() {
        return self.value;
      },
      getSave:/*Option*/function() {
        if (self.save.enabled)
          return opt;
      },
      resize:function() {
        var w = into.getDim().width - 50;
        if (w > 0)   
          self.setWidth(into._ow = w);
        else 
          into._ow = null
      },
      setWidth:function(w) {
        self.input.setWidth(w);
      },
      //
      toggle:function() {
        self.setValue(! self.value);
        if (self.value) { 
          self.input.setFocus().select();
          if (! self.save.showing)
            self.save.enable();
          self.ontoggle(opt);
        }
      },
      check_onclick:function() {
        self.toggle();
      },
      input_onclick:function() {
        if (! self.value) 
          self.toggle();
      }
    })
  },
  SaveAnchor:{
    create:function(container, opt) {
      return Html.Anchor.create('isave').into(container).extend(function(self) {
        return {
          init:function() {
            self.invisible();
            if (opt.saved)
              self.enable();
            else if (opt.text != 'other')
              self.disable();
          },
          enable:function() {
            self.showing = 1;
            self.enabled = 1;
            self.setClass('isave').visible();
            opt.nosave = 0;
          },
          disable:function() {
            self.enabled = 0;
            self.setClass('isaveo').visible();
            opt.nosave = 1;
          },
          onclick:function() {
            if (self.enabled)
              self.disable();
            else
              self.enable();
          }
        }
      })
    }
  },
  Input:{
    create:function(container, opt) {
      return Html.InputText.create().setValue(opt.text).noSelectFocus().into(container).extend(function(self) {
        return {
          onchange:function() {
            opt.setText(self.getValue());
          }
        }
      })
    }
  }
}
OtherCheck_Ipc = {
  //
  create:function(/*<e>*/into, /*Option*/opt, /*[Proto]*/Input) {
    var My = this;
    return OtherCheck.create(into, opt, My.Input);
  },
  Input:{
    create:function(container, opt) {
      return IpcPicker.create().into(container).extend(function(self) {
        return {
          init:function() {
            if (opt.text && opt.cpt) 
              self.setValueText(opt.cpt, opt.text);
          },
          onset:function(rec) {
            opt.setFromIpc(rec);
          }
        }
      })
    }
  }
}
/**
 * QPopFree
 */
QPopFree = {
  create:function() {
    return Html.Pop.create().extend(QPopFree, function(self) {
      return {
        onupdate:function(q) {},
        //
        pop:function(q, onupdate, pos) {
          self.POP_POS = pos || Pop.POS_CURSOR;
          self.load(q);
          self.show();
          if (onupdate)
            self.onupdate = onupdate;
          return self;
        },
        load:function(q) {
          self.q = q;
          self.setCaption(q.desc);
          self.opt = q.Opts.getSingle();
          self.loadFromOpt();
        },
        update:function() {
          self.updateOpt();
          self.q.setByValue(self.opt.text);
          self.close();
          self.onupdate(self.q);
        },
        //
        loadFromOpt:function() {},
        updateOpt:function() {}
      }
    })
  }
}
/**
 * QPopFree QPopFreeText
 */
QPopFreeText = QPop.aug({
  create:function() {
    return QPopFree.create().extend(QPopFreeText, function(self) {
      return {
        init:function() {
          self.content.addClass('PopFree');
          self.input = this.TextArea.create(self.content).bubble('onkeypresscr', self.update);
          self.cb = Html.CmdBar.create(self.content).button('OK', self.update, 'ok').cancel(self.close);
        },
        onshow:function() {
          async(function() {
            self.input.setFocus();
            if (self.opt.blank)
              self.input.select();
          })
        },
        loadFromOpt:function() {
          self.input.setValue(self.opt.getText());
        },
        updateOpt:function() {
          self.opt.setText(self.input.getValue());
        },
        TextArea:{
          create:function(into) {
            return Html.TextArea.create().setRows(5).into(into);
          }
        }
      }
    })
  }
})
/**
 * QPopFree QPopCalc
 */
QPopCalc = {
  pop:function(q, onupdate, pos) {
    var p = QPopCalc;
    if (q.uid == '#Weight')
      p = QPopCalcLbs;
/*    else if (q.uid == '#Height')
      p = QPopCalcFt;*/
    return Html.Pop.singleton_pop.apply(p, arguments);
  },
  create:function() {
    return QPopFree.create().extend(QPopCalc, function(self) {
      return {
        init:function() {
          self.content.addClass('PopCalc');
          self.display = this.Display.create(self.content);
          self.keypad = this.KeyPad.create(self.content)
            .bubble('onkey', self.keypad_onkey);
          self.cb = Html.CmdBar.create(self.content)
            .button('OK', self.update, 'ok', 'ok')
            .cancel(self.close);
          self.ok = self.cb.get('ok');
        },
        onshow:function() {
          self.ok.focus();
        },
        onkeydown:function() {
          switch (event.keyCode) {
            case 48:self.keypad_onkey('0');break;
            case 49:self.keypad_onkey('1');break;
            case 50:self.keypad_onkey('2');break;
            case 51:self.keypad_onkey('3');break;
            case 52:self.keypad_onkey('4');break;
            case 53:self.keypad_onkey('5');break;
            case 54:self.keypad_onkey('6');break;
            case 55:self.keypad_onkey('7');break;
            case 56:self.keypad_onkey('8');break;
            case 57:self.keypad_onkey('9');break;
            case 96:self.keypad_onkey('0');break;
            case 97:self.keypad_onkey('1');break;
            case 98:self.keypad_onkey('2');break;
            case 99:self.keypad_onkey('3');break;
            case 100:self.keypad_onkey('4');break;
            case 101:self.keypad_onkey('5');break;
            case 102:self.keypad_onkey('6');break;
            case 103:self.keypad_onkey('7');break;
            case 104:self.keypad_onkey('8');break;
            case 105:self.keypad_onkey('9');break;
            case 110:self.keypad_onkey('.');break;
            case 8:self.keypad_onkey('B');break;
            case 189:self.keypad_onkey('+/-');break;
            case 190:self.keypad_onkey('.');break;
            case 13:
              event.keyCode = 0;
              Html.Window.cancelBubble();
              self.update();
              return false;
              break;
            case 78:self.keypad_onkey('N/A');break;
            case 67:self.keypad_onkey('C');break;
          }
          return false;
        },
        loadFromOpt:function() {
          self.display.load(self.opt.getText());
        },
        updateOpt:function() {
          self.opt.setText(self.display.getValue());
        },
        keypad_onkey:function(text) {
          self.display.press(text);
          Html.Window.cancelBubble();
        },
        //
        Display:{
          create:function(into) {
            var self = Html.Tile.create(into, 'Display');
            return self.aug({
              load:function(value) {
                if (value == 'N/A') {
                  self.value = '';
                } else {
                  self.value = String.denull(value).replace(/[^\d.-]/g, '');
                  if (self.value.length == 0) 
                    self.value = '0';
                }
                self.refresh();
                self.preset = true;
              },
              getValue:function() {
                return self.innerText;
              },
              refresh:function() {
                if (self.value.length == 0) 
                  self.setText('N/A');
                else 
                  self.setText(self.formatCommas(self.value));
              },
              formatCommas:function(n) {
                var x = n.split('.');
                var x1 = x[0];
                var x2 = x.length > 1 ? '.' + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) 
                  x1 = x1.replace(rgx, '$1' + ',' + '$2');
                return x1 + x2;
              },
              press:function(v) {
                if (v == 'C') {
                  self.value = '0';
                } else if (v == 'N/A') {
                  self.value = '';
                } else if (v == '+/-') {
                  if (self.value.substring(0, 1) == '-') 
                    self.value = self.value.substring(1);
                  else
                    self.value = '-' + self.value;
                } else if (v == 'B') {
                  self.value = self.value.substring(0, self.value.length - 1);
                  if (self.value.length == 0) 
                    self.value = '0';
                } else {
                  if (self.value.length < 10) {
                    if (self.preset) 
                      self.value = '0';
                    if (v == '.') {
                      if (self.value.indexOf('.') == -1) 
                        self.value += v;
                    } else {
                      if (self.value == '0')
                        self.value = v;
                      else if (self.value == '-0')
                        self.value = '-' + v;
                      else 
                        self.value += v;
                    }
                  }
                }
                self.preset = false;
                self.refresh();
              }
            })
          }
        },
        KeyPad:{
          create:function(into) {
            var self = Html.Tile.create(into, 'KeyPad');
            return self.aug({
              onkey:function(text) {},
              //
              init:function() {
                self.tbody = Html.Table.create(self).tbody();
                self.tbody.tr().td(this.key('7')).td(this.key('8')).td(this.key('9')).th().td(this.keyback());
                self.tbody.tr().td(this.key('4')).td(this.key('5')).td(this.key('6')).th().td(this.key('C', 'Fn'));
                self.tbody.tr().td(this.key('1')).td(this.key('2')).td(this.key('3')).th().td(this.key('N/A', 'Fn'));
                self.tbody.tr().td(this.key('0', 'Zero')).colspan(2).td(self.keyDec = this.key('.')).th().td(this.key('+/-', 'Fn'));
              },
              key_onclick:function(text) {
                self.onkey(text);
              },
              keyback_onclick:function() {
                self.onkey('B');
              },
              //
              key:function(text, cls) {
                return Html.AnchorNoFocus.create(cls, text).aug({
                  onclick:function() {
                    self.key_onclick(text);
                  }
                  /*
                  ondblclick:function() {
                    self.key_onclick(text);
                  }
                  */
                });
              },
              keyback:function() {
                return Html.AnchorNoFocus.create('Fn KeyBack').html('&#217').aug({
                  onclick:function() {
                    self.keyback_onclick();
                  }
                  /*
                  ondblclick:function() {
                    self.keyback_onclick();
                  }
                  */
                })
              }
            })
          }
        }
      }
    })
  }
}
/**
 * QPopCalc QPopCalcLbs
 */
QPopCalcLbs = QPop.aug({
  create:function() {
    return QPopCalc.create().extend(QPopCalcLbs, function(self) {
      return {
        init:function() {
          self.display = this.Display.apply(self.display).aug({
            onrefresh:function(format, text) {
              var key = self.keypad.keyDec;
              if (self.display.isDecimal()) 
                key.visible().setText('.');
              else
                if (text.indexOf('.') > -1)
                  key.invisible();
                else
                  key.visible().setText('lb.oz');
            }
          })
        },
        Display:{
          apply:function(self) {
            return self.aug({
              onrefresh:function(format, text) {},
              //
              init:function() {
                self.formatter = self.Formatter.create(self).bubble('onselect', self.formatter_onselect);
              },
              refresh:function() {
                if (self.value.length == 0) 
                  self.setText('N/A');
                else if (self.formatter.isLbs())  
                  self.setText(self.formatLbs(self.value));
                else
                  self.setText(self.formatCommas(self.value));
                self.onrefresh(self.formatter.getValue(), self.value);
              },
              formatLbs:function(n) {
                var x = n.split('.');
                var x2 = '';
                if (x.length > 1) 
                  x2 = String.toInt(x[1]) + 'oz';
                return x[0] + 'lbs ' + x2;
              },
              formatter_onselect:function(format) {
                if (self.value != '0') {
                  var x = self.value.split('.');
                  var x1 = String.toInt(x[0]);
                  var x2 = (x.length > 1) ? String.toInt(x[1]) : 0;
                  if (format == self.Formatter.VALUES.LBS) 
                    self.value = x1 + '.' + (String.toFloat('.' + x2) * 16);
                  else
                    self.value = '' + (x1 + (x2 / 16));
                }
                self.refresh();
              },
              getValue:function() {
                if (self.formatter.isLbs()) 
                  self.formatter.setValue(self.Formatter.VALUES.DECIMAL);
                return self.innerText;
              },
              isDecimal:function() {
                return self.formatter.isDecimal();
              },
              //
              Formatter:{
                VALUES:{'DECIMAL':'0','LBS':'1'},
                create:function(display) {
                  var self = Html.Div.create('Formatter').before(display);
                  return self.aug({
                    onselect:function(value) {},
                    //
                    init:function() {
                      display.into(self);
                      self.radios = Html.LabelRadios.create({0:'Decimal',1:'Lbs/Oz'}).into(self).bubble('onselect', self)
                    },
                    getValue:function() {
                      return self.radios.getValue();
                    },
                    setValue:function(value) {
                      if (value != self.radios.getValue()) {
                        self.radios.setValue(value);
                        self.onselect(value);
                      }
                    },
                    isLbs:function() {
                      return self.getValue() == 1;
                    },
                    isDecimal:function() {
                      return self.getValue() == 0;
                    }
                  })
                }
              }
            }) 
          }
        }
      }
    })
  }
})
/**
 * QPopCalc QPopCalcFt
 */
QPopCalcFt = QPop.aug({
  create:function() {
    return QPopCalc.create().extend(QPopCalcFt, function(self) {
      return {
        init:function() {
          self.display = this.Display.apply(self.display).aug({
            onrefresh:function(format, text) {
              var key = self.keypad.keyDec;
              if (self.display.isDecimal()) 
                key.visible().setText('.');
              else
                if (text.indexOf('.') > -1)
                  key.invisible();
                else
                  key.visible().setText('ft.in');
            }
          })
        },
        Display:{
          onrefresh:function(format, text) {},
          //
          apply:function(self) {
            return self.aug({
              init:function() {
                self.formatter = self.Formatter.create(self).bubble('onselect', self.formatter_onselect);
              },
              refresh:function() {
                if (self.value.length == 0) 
                  self.setText('N/A');
                else if (self.formatter.isFt())  
                  self.setText(self.formatFt(self.value));
                else
                  self.setText(self.formatCommas(self.value));
                self.onrefresh(self.formatter.getValue(), self.value);
              },
              formatFt:function(n) {
                // TODO not working
                var x = n.split('.');
                var x2 = '';
                if (x.length > 1) 
                  x2 = String.toInt(x[1]) + 'in';
                return x[0] + 'ft ' + x2;
              },
              formatter_onselect:function(format) {
                if (self.value != '0') {
                  var x = self.value.split('.');
                  var x1 = String.toInt(x[0]);
                  var x2 = (x.length > 1) ? String.toInt(x[1]) : 0;
                  if (format == self.Formatter.VALUES.FT) 
                    //self.value = x1 + '.' + (String.toFloat('.' + x2) * 12);
                    self.value = x1 * 12 + x2;
                  else
                    //self.value = '' + (x1 + Math.roundTo((x2 / 12), 2));
                    self.value = '' + (x1 * 12 + x2);
                }
                self.refresh();
              },
              getValue:function() {
                if (self.formatter.isFt()) 
                  self.formatter.setValue(self.Formatter.VALUES.DECIMAL);
                return self.innerText;
              },
              isDecimal:function() {
                return self.formatter.isDecimal();
              },
              //
              Formatter:{
                VALUES:{'DECIMAL':'0','FT':'1'},
                create:function(display) {
                  var self = Html.Div.create('Formatter').before(display);
                  return self.aug({
                    onselect:function(value) {},
                    //
                    init:function() {
                      display.into(self);
                      self.radios = Html.LabelRadios.create({0:'Decimal',1:'Ft/In'}).into(self).bubble('onselect', self)
                    },
                    getValue:function() {
                      return self.radios.getValue();
                    },
                    setValue:function(value) {
                      if (value != self.radios.getValue()) {
                        self.radios.setValue(value);
                        self.onselect(value);
                      }
                    },
                    isFt:function() {
                      return self.getValue() == 1;
                    },
                    isDecimal:function() {
                      return self.getValue() == 0;
                    }
                  })
                }
              }
            }) 
          }
        }
      }
    })
  }
})
/**
 * QPopFree QPopCalendar
 */
QPopCalendar = QPop.aug({
  FORMAT:2,  // '14-Dec-2014'
  setFormatSentence:function() {
    QPopCalendar.FORMAT = 1;
  },
  create:function() {
    return QPopFree.create().extend(QPopCalendar, function(self) {
      return {
        init:function() {
          self.cal = CalendarTile.create(self.content).bubble('onselect', self.cal_onselect);
        },
        loadFromOpt:function() {
          self.value = new DateValue(self.opt.getText());
          self.cal.setValue(self.value);
        },
        cal_onselect:function(value) {
          self.value = value;
          self.update();
        },
        updateOpt:function() {
          self.opt.setText(self.value.toString(QPopCalendar.FORMAT));
        }
      }
    })
  }
})
/**
 * Tile CalendarTile
 */
CalendarTile = {
  create:function(into) {
    var self = Html.Tile.create(into, 'CalendarTile');
    return self.aug({
      onselect:function(value) {},
      //
      init:function() {
        self.combos = CalendarTile.Combos.create(self).bubble('onselect', self.combos_onselect);
        self.calendar = CalendarTile.Calendar.create(self).bubble('onpage', self.calendar_onpage).bubble('onselect', self);
        self.approx = CalendarTile.Approx.create(self).bubble('onselect', self);
      },
      /*
       * @arg DateValue value
       */
      setValue:function(value) {
        if (! value.isNull()) {
          self.calendar.set(value);
          self.approx.set(value);
        } else {
          value = DateValue.now();
        }
        self.calendar.page(value.getMonth(), value.getYear());
      },
      //
      combos_onselect:function(month, year) {
        self.calendar.page(month, year);
      },
      calendar_onpage:function(month, year) {
        self.combos.page(month, year);
        self.approx.page(month, year);
      }
    })
  },
  Combos:{
    create:function(into) {
      var self = Html.Tile.create(into, 'Combos');
      return self.aug({
        onselect:function(month, year) {},
        //
        init:function() {
          self.months = Html.Select.create(Map.from(DateUi.MONTH_NAMES)).into(self).bubble('onchange', self);
          self.years = Html.Select.create().into(self).bubble('onchange', self);
          for (var y = 1910, last = DateValue.now().getYear() + 10; y <= last; y++)
            self.years.addOption(y);
        },
        page:function(month, year) {
          self.months.setValue(month);
          self.years.setValue(year);
        },
        //
        onchange:function() {
          self.onselect(self.months.getValue(), self.years.getValue());
        }
      })
    }
  },
  Calendar:{
    create:function(into) {
      var self = Html.Tile.create(into, 'Calendar');
      return self.aug({
        onselect:function(value) {},
        onpage:function(month, year) {},
        //
        init:function() {
          self.table = this.Table.create(self).bubble('onnav', self.page).bubble('onselect', self);
        },
        set:function(value) {
          self.table.set(value);
        },
        page:function(month, year) {
          self.table.page(month, year);
          self.onpage(month, year);
        },
        onmousewheel:function() {
          if (event)
            if (event.wheelDelta > 0)
              self.table.head.next();
            else
              self.table.head.prev();
        },
        //
        Table:{
          create:function(into) {
            var container = Html.Tile.create(into, 'CalendarTable');
            var self = Html.Table.create(container);
            return self.aug({
              onnav:function(month, year) {},
              onselect:function(value) {},
              //
              init:function() {
                self.head = self.Head.into(self).bubble('onnav', self);
                self.body = self.Body.into(self).bubble('onselect', self);
              },
              set:function(value) {
                self.body.set(value);
              },
              page:function(month, year) {
                self.head.page(month, year);
                self.body.page(month, year);
              },
              //
              Body:{
                into:function(table) {
                  var self = table.tbody();
                  return self.aug({
                    onselect:function(value) {},
                    //
                    init:function() {
                      self.days = [];
                      for (var r = 0; r < 6; r++) {
                        var tr = self.tr();
                        for (var c = 0; c < 7; c++) {
                          var day = Html.AnchorNoFocus.create().aug({
                            weekend:(c == 0 || c == 6),
                            onclick:function() {
                              self.day_onclick(this);
                            }
                          }) 
                          self.days.push(day);
                          tr.td(day);
                        }
                      } 
                    },
                    day_onclick:function(day) {
                      if (day.val)
                        self.onselect(DateValue.fromTime(day.val));
                    },
                    set:function(value) {
                      self.value = value;
                    },
                    page:function(month, year) {
                      var stepper = self.DateStepper.reset(month, year, self.value);
                      Array.forEach(self.days, function(day) {
                        if (stepper.isMonth(month)) {
                          day.setText(stepper.getDate());
                          day.val = stepper.valueOf();
                          if (stepper.isValue())
                            day.setClass('Setting');
                          else if (stepper.isToday())
                            day.setClass('Today');
                          else 
                            day.setClass(day.weekend ? 'Weekend' : 'Day');
                        } else {
                          day.html('&nbsp;').setClass('Off');
                          day.val = null;
                        }
                        stepper.next();
                      })
                    },
                    DateStepper:Object.augment(new Date(), {
                      reset:function(month, year, value) {
                        var dFirstOfMonth = new Date(year, month, 1);
                        this.setTime(dFirstOfMonth.valueOf() - dFirstOfMonth.getDay() * 86400000);
                        this.now = new Date();
                        this.value = (value && ! value.date.approx) ? value.date.toDateString() : null;
                        return this;
                      },
                      next:function() {
                        this.setDate(this.getDate() + 1);
                      },
                      isToday:function() {
                        return this.valueOf() == this.now.valueOf();  
                      },
                      isValue:function() {
                        return this.value && this.toDateString() == this.value;
                      },
                      isMonth:function(month) {
                        return this.getMonth() == month; 
                      }
                    })
                  })
                }
              },
              Head:{
                into:function(table) {
                  var self = table.thead();
                  return self.aug({
                    onnav:function(month, year) {},
                    //
                    init:function() {
                      self.prevbox = Html.AnchorNoFocus.create('Prev').noFocus().bubble('onclick', self.prev);
                      self.nextbox = Html.AnchorNoFocus.create('Next').noFocus().bubble('onclick', self.next);
                      self.titlebox = Html.Div.create('Title');
                      self.tr('NavBar').td(self.prevbox).th(self.titlebox).colspan(5).td(self.nextbox);
                      self.tr('DowBar').td('S').td('M').td('T').td('W').td('T').td('F').td('S');
                    },
                    page:function(month, year) {
                      self.month = month;
                      self.year = year;
                      self.titlebox.setText(DateUi.getMonthName(month) + ' ' + year);
                    },
                    prev:function() {
                      self.month--;
                      if (self.month < 0) {
                        self.month = 11;
                        self.prevYr();
                      } else {
                        self.onnav(self.month, self.year);
                      }
                    },
                    next:function() {
                      self.month++;
                      if (self.month > 11) {
                        self.month = 0;
                        self.nextYr();
                      } else {
                        self.onnav(self.month, self.year);
                      }
                    },
                    prevYr:function() {
                      self.year--;
                      self.onnav(self.month, self.year);
                    },
                    nextYr:function() {
                      self.year++;
                      self.onnav(self.month, self.year);
                    }
                  })
                }
              }
            })
          }
        }
      })
    }
  },
  Approx:{
    create:function(into) {
      var self = Html.Tile.create(into, 'Approx');
      return self.aug({
        onselect:function(value) {},
        //
        init:function() {
          self.monthbox = Html.AnchorNoFocus.create('OptAnchor').setWidth(112).bubble('onclick', self.month_onclick);
          self.yearbox = Html.AnchorNoFocus.create('OptAnchor').setWidth(40).bubble('onclick', self.year_onclick);
          self.unknownbox = Html.AnchorNoFocus.create('OptAnchor').setText('Date Unknown').bubble('onclick', self.unknown_onclick);
          Html.TableCol.create(self, [self.monthbox, self.yearbox, self.unknownbox]);
        },
        page:function(month, year) {
          self.month = month;
          self.year = year;
          self.monthbox.setText(DateUi.getMonthName(month) + ' of ' + year);
          self.yearbox.setText(year);
        },
        set:function(value) {
          self.monthbox.addClassIf('sel', value.date.approx == DateValue.APPROX_MONTH_YEAR);
          self.yearbox.addClassIf('sel', value.date.approx == DateValue.APPROX_YEAR);
          self.unknownbox.addClassIf('sel', value.date.approx == DateValue.APPROX_UNKNOWN);
        },
        month_onclick:function() {
          self.onselect(new DateValue([self.year, self.month]));
        },
        year_onclick:function() {
          self.onselect(new DateValue([self.year]));
        },
        unknown_onclick:function() {
          self.onselect(DateValue.asUnknown());
        }
      })
    }
  }
}
/**
 * QPopLegacyMed
 */
QPopLegacyMed = {
  pop:function(q, onupdate, pos) {
    Html.IncludedSourcePop.pop.apply(QPopLegacyMed, arguments);
  },
  create:function(callback) {
    Html.IncludedSourcePop.create_asSingleton(callback, 'LegacyMedPop', 'popMedLegacy', function(self) {
      return {
        init:function() {
          QPopLegacyMed.$ = self;
          self.name = Html.InputText.$('medName');
          Html.InputText.$('medAmt');
          Html.InputText.$('medFreq');
          Html.InputText.$('medRoute');
          Html.InputText.$('medLength');
          Html.InputText.$('medDisp');
        },
        onpop:function(q, onupdate, pos) {
          self.POP_POS = pos || Pop.POS_CURSOR;
        },
        onshow:function(q, onupdate) {
          self.q = q;
          self.onupdate = onupdate;
          self.showMedHistory();
          self.name.setFocus();
        },
        //
        medShow:function(id, sel) {
          _$(id).select();
          for (var i = 0; i <= 4; i++) {
            var m = _$('m' + i);
            m.style.display = (sel == i) ? 'block' : 'none';
          }
        },
        medOk:function() {
          var name = self.name.getValue();
          if (name == '') {
            Pop.Msg.showCritical('Cannot save without specifying medication name.');
            return;
          }
          Pop.close();
          var m = {
              'name':name,
              'amt':_$('medAmt').getValue(),
              'freq':_$('medFreq').getValue(),
              'route':_$('medRoute').getValue(),
              'length':_$('medLength').getValue(),
              'asNeeded':_$('medAsNeed').checked,
              'meals':_$('medMeals').checked,
              'disp':_$('medDisp').getValue()};
          m.text = self.medBuildText(m.amt, m.freq, m.route, m.length, m.asNeeded, m.withMeals, m.disp);
          self.q.med = m;
          self.onupdate(self.q);
          /*
          if (medQCallback) {
            medQ.med = m;
            if (medQ.medNameOnly) 
              medQ.med.text = null;
            medQCallback(medQ);    
          } else {
            medOkCallback(m);
          }
          */ 
        },
        showMedHistory:function() {
          _$('medListTitle').hide();
          _$('medListNone').hide();
          _$('medListTitle').html('');
          var ul = _$(medListUl);
          ul.clean();
          ul.active = MedList.create(medListUl, 'Active');
          ul.inactive = MedList.create(medListUl, 'Inactive', 'medpad');
          var medHist = QPopLegacyMed.medHist;
          if (medHist)
            Array.each(medHist, function(m, i) {
              var m = medHist[i];
              if (m.active)
                ul.active.add(m);
              else
                ul.inactive.add(m);
            })
        },
        medHistSel:function(m) {
          Html.InputText.$('medName').setValue(m.name);
          Html.InputText.$('medAmt').setValue(m.amt);
          Html.InputText.$('medFreq').setValue(m.freq);
          Html.InputCheck.$('medAsNeed').setCheck(m.asNeeded);
          Html.InputCheck.$('medMeals').setCheck(m.meals);
          Html.InputText.$('medRoute').setValue(m.route);
          Html.InputText.$('medLength').setValue(m.length);
          Html.InputText.$('medDisp').setValue(m.disp);
          self.searchResults();
        },
        search_onclick:function() {
          _$('medName').focus();
          self.searchResults();
        },
        name_onkeypress:function() {
          if (event.keyCode == 13)
            self.searchResults();
          else if (event.keyCode > 31)
            self.clearResults();
        },
        disp_onfocus:function() {
          var e = Html.InputText.$('medDisp');
          if (e.getValue() != '') 
            return;
          e.setValue(self.calcMedDisp(_$('medAmt').getValue(), _$('medFreq').getValue(), _$('medLength').getValue()));
          e.setFocus();
        },
        searchResults:function() {
          var text = QPopLegacyMed.justMedName(value('medName'));
          if (text != '') {
            self.clearResults();
            _$('medListTitle').html('Searching for: <b>' + text + '</b>');
            _$('medListTitle').html('Searching for: <b>' + text + '</b>');
            Ajax.get('Json', 'searchMeds', text, function(o) {
              if (o.meds == null) {
                _$('medListNone').setText('No matching medications found.');
                return;
              }
              var m;
              var h = '';
              var limit = o.meds.length;
              if (limit > 100) {
                limit = 100;
                _$('medListFoot').innerText = 'Limit exceeded, please narrow your search.';
              }
              for (var i = 0; i < limit; i++) {
                m = o.meds[i];
                h += '<li><a href="javascript:" onclick="QPopLegacyMed.$.upMedName(this)">' + m.name;
                if (m.dosage != ' ' && m.dosage != '') {
                  h += ' (' + m.dosage + ')';
                }
                h += '</a></li>';
              }
              _$('medListUl').html(h);
            })
          }
        },
        clearResults:function() {
          _$('medListNone').setText('');
          _$('medListUl').html('');
          _$('medListFoot').setText('');
          _$('medListTitle').html('<i>Type in a partial name and hit ENTER for results.</i>');
        },
        upMedName:function(a) {
          Html.InputText.$('medName').setValue(a.innerText);
          _$('medAmt').setFocus();
        },
        upMedAmt:function(a) {
          Html.InputText.$('medAmt').setValue(a.innerText);
          _$('medFreq').setFocus();
        },
        upMedFreq:function(a) {
          Html.InputText.$('medFreq').setValue(self.noBr(a.innerText));
          _$('medRoute').setFocus();
        },
        noBr:function(s) {
          return s.split('<br>')[0];
        },
        upMedRoute:function(a) {
          Html.InputText.$('medRoute').setValue(a.innerText);
          _$('medLength').setFocus();
        },
        upMedLength:function(a) {
          Html.InputText.$('medLength').setValue(a.innerText);
          _$('medName').setFocus();
        },
        medBuildText:function(amt, freq, route, length, asNeeded, withMeals, disp) {
          var t = '';
          if (! isBlank(amt)) {
            t += ' ' + amt;
          }
          if (! isBlank(freq)) {
            t += ' ' + freq;
          }
          if (! isBlank(route)) {
            t += ' ' + route;
          }
          if (asNeeded && asNeeded != '0') {
            t += ' as needed';
          }
          if (withMeals && withMeals != '0') {
            t += ' with meals';
          }
          if (! isBlank(length)) {
            t += ' for ' + length;
          }
          //if (! isBlank(disp)) {
          //  t += ' (Disp: ' + disp + ')';
          //}
          return String.trim(t);
        },
        calcMedDisp:function(amt, freq, length) {
          var a = amt;
          var i = 0.;
          var u = '';
          var cf = 5;
          // Determine period
          var l = length;
          var days = 30;  // assume 30 days
          if (l == '1 day') {
            days = 1;
          } else if (l == '2 days') {
            days = 2;
          } else if (l == '3 days') {
            days = 3;
          } else if (l == '4 days') {
            days = 4;
          } else if (l == '5 days') {
            days = 5;
          } else if (l == '6 days') {
            days = 6;
          } else if (l == '7 days') {
            days = 7;
          } else if (l == '10 days') {
            days = 10;
          } else if (l == '12 days') {
            days = 12;
          } else if (l == '14 days') {
            days = 14;
          } else if (l == '21 days') {
            days = 21;
          } else if (l == '28 days') {
            days = 28;
          } else if (l == '30 days') {
            days = 30;
          } else if (l == '60 days') {
            days = 60;
          } else if (l == '90 days') {
            days = 90;
          }
          if (a == '1/4') {
            i = 1/4;
          } else if (a == '1/3') {
            i = 1/3;
          } else if (a == '1/2') {
            i = 1/2;
          } else if (a == '1') {
            i = 1;
          } else if (a == '1 1/2') {
            i = 1.5;
          } else if (a == '2') {
            i = 2;
          } else if (a == '3') {
            i = 3;
          } else if (a == '4') {
            i = 4;
          } else if (a == '5') {
            i = 5;
          } else if (a == '6') {
            i = 6;
          } else if (a == '7') {
            i = 7;
          } else if (a == '8') {
            i = 8;
          } else {
            u = ' ml';
            if (a == '1/4 tsp') {
              i = 1/4 * cf;
            } else if (a == '1/3 tsp') {
              i = 1/3 * cf;
            } else if (a == '1/2 tsp') {
              i = 1/2 * cf;
            } else if (a == '3/4 tsp') {
              i = 3/4 * cf;
            } else if (a == '1 tsp') {
              i = cf;
            } else if (a == '1 1/4 tsp') {
              i = 5/4 * cf;
            } else if (a == '1 1/3 tsp') {
              i = 5/3 * cf;
            } else if (a == '1 1/2 tsp') {
              i = 3/2 * cf;
            } else if (a == '1 3/4 tsp') {
              i = 7/4 * cf;
            } else if (a == '2 tsp') {
              i = 2 * cf;
            } else if (a == '3 tsp') {
              i = 3 * cf;
            } else if (a == '0.4 ml') {
              i = 0.4;
            } else if (a == '0.5 ml') {
              i = 0.5;
            } else if (a == '0.8 ml') {
              i = 0.8;
            } else if (a == '1 ml') {
              i = 1;
            } else if (a == '1.2 ml') {
              i = 1.2;
            } else if (a == '1 1/2 ml') {
              i = 1.5;
            } else if (a == '1.6 ml') {
              i = 1.6;
            } else if (a == '2 ml') {
              i = 2;
            } else if (a == '2 1/2 ml') {
              i = 2.5;
            } else if (a == '3 ml') {
              i = 3;
            } else if (a == '3 1/2 ml') {
              i = 3.5;
            } else if (a == '4 ml') {
              i = 4;
            } else if (a == '4 1/2 ml') {
              i = 4.5;
            } else if (a == '5 ml') {
              i = 5;
            } else {
              return 'QS x ' + plural(days, 'day');
            }
          }
          // Calculate 1-day amt
          var f = freq;
          var m = 0.;
          if (f == 'every hour') {
            m = i * 24;
          } else if (f == 'every 2 hours') {
            m = i * 12; 
          } else if (f == 'every 3 hours') {
            m = i * 8; 
          } else if (f == 'every 4 hours') {
            m = i * 6; 
          } else if (f == 'every 6 hours') {
            m = i * 4; 
          } else if (f == 'every 8 hours') {
            m = i * 3; 
          } else if (f == 'every 12 hours') {
            m = i * 2; 
          } else if (f == 'daily') {
            m = i; 
          } else if (f == 'BID') {
            m = i * 2; 
          } else if (f == 'TID') {
            m = i * 3; 
          } else if (f == 'QID') {
            m = i * 4; 
          } else if (f == 'five times daily') {
            m = i * 5; 
          } else if (f == 'QAM') {
            m = i; 
          } else if (f == 'QHS') {
            m = i; 
          } else if (f == 'every 2 days') {
            m = i * 1/2; 
          } else if (f == 'every 3 days') {
            m = i * 1/3; 
          } else if (f == 'Mon/Thur') {
            m = i * 8/30; 
          } else if (f == 'MWF') {
            m = i * 12/30; 
          } else if (f == 'once weekly') {
            m = i * 4/30; 
          } else if (f == 'once monthly') {
            m = i * 1/30; 
          } else if (f == 'every 2 weeks') {
            m = i * 2/30; 
          } else if (f == 'every 10 days') {
            m = i * 3/30; 
          } else {
            return 'QS x ' + plural(days, 'day');
          }
          // Multiply by period
          m = m * days;
          m = Math.round(m * 100) / 100;
          return m + u;
        }
      }
    })
  },
  justMedName:function(name) {
    name = name.toUpperCase();
    if (name.indexOf('(') >= 0)
      return String.trim(name.toUpperCase().split(' (')[0]);
    words = name.split(' ');
    for (var i = 0, j = words.length; i < j; i++) {
      if (! isNaN(words[i].substr(0, 1)))
        break;
    }
    if (i < j)
      words.splice(i, j - i);
    return words.join(' ');
  },
  loadMedHistory:function(meds) {
    if (meds) {
      var h = {};
      meds.each(function(med) {
        var name = med.name;
        if (name) 
          if (med.active || ! h[name])
            h[name] = med;
      })
      this.medHist = h;
    }
  }
}
var MedList = {
  create:function(container, title, cls) {
    return Html.Tile.create(container).extend(function(self) {
      return {
        init:function() {
          self.list = Html.Ul.create().into(self);
          self.titlebox = Html.Div.create(cls).setText(title).into(self.list.li()).hide();
        },
        add:function(m) {
          Html.Anchor.create(null, m.name, QPopLegacyMed.$.medHistSel.curry(m)).into(self.list.li());
          self.titlebox.show();
        }
      }
    })
  }
}
/**
 * TextAnchor QuestionEntry 
 */
QuestionEntry = {
  create:function(q, anchorCls, inputSize) {
    var self = Html.TextAnchor.create(anchorCls || 'mglass', inputSize || 26);
    return self.aug({
      getValue:function() {
        return self.getText();
      },
      setValue:function(value) {
        self.setText(value);
      },
      //
      onclick_anchor:function(text) {
        q.setByValue(text).pop(function() {
          self.setText(q.getValue());
          self.setFocus();
        })
      }
    })
  }
}
QuestionDateEntry = {
  create:function() {
    return QuestionEntry.create(QuestionDate.asDummy(), 'dical', 8);
  }
}
