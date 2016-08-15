/**
 * QuestionPop 
 */
QuestionPop = {
  pop:function(q) {
    switch (q.type) {
      case Question.TYPES.COMBO:
        return QPopCombo.pop(q);
      case Question.TYPES.FREE:
        return QPopFree.pop(q);
      case Question.TYPES.CALC:
        return QPopCalc.pop(q);
      case Question.TYPES.DATE:
        return QPopCalendar.pop(q);
      //case Question.TYPES.ALLERGY:
      //case Question.TYPES.MED:
      default:
        return (q.multi) ? QPopMulti.pop(q) : QPopStandard.pop(q);
    }
  }
}
/**
 * Pop QPopStandard
 *   SingleOptsTile singleTile
 */
QPopStandard = {
  pop:function(q) {
    return QPopStandard = this.create().pop(q);
  },
  create:function() {
    return Html.extend(Html.Pop.create(), this._protof);
  },
  _protof:function(self) {
    return {
      onupdate:function(q) {},
      //
      init:function() {
        self.singleTile = SingleOptsTile.create(self.content).bubble('onselectopt', self, 'singleTile_onselect');
      }, 
      pop:function(q) {
        self.load(q);
        self.invisible().showPosCursor().resize();
        return self.visible();
      },
      load:function(q) {
        self.q = q;
        self.setCaption(q.desc);
        self.sopts = q.opts.getSingles();
        self.design = self.getDesign();
        self.loadSingleTile();
      },
      resize:function() {
        self.singleTile.resize();
      },
      //
      singleTile_onselect:function(opt) {
        self.q.updateSingle(opt);
        self.close();
        self.onupdate(self.q);
      },
      loadSingleTile:function() {
        self.singleTile.load(self.sopts, self.design.scols, self.q.opts.getSingleOther());
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
  }
}
/**
 * QPopStandard QPopMulti
 *   SingleOptsTile singleTile
 *   MultiOptsTile multiTile
 */
QPopMulti = {
  pop:function(q) {
    return QPopMulti = this.create().pop(q);
  },
  create:function() {
    return Html.extend(Html.Pop.create(), this._protof);
  },
  _protof:function(self) {
    var parent = QPopStandard._protof(self);
    self.aug(parent);
    return {
      onupdate:function(q) {},
      //
      init:function() {
        self.multiTile = MultiOptsTile.create(self.content).bubble('onapplyopts', self, 'multiTile_onapply'); 
      }, 
      load:function(q) {
        self.mopts = q.opts.getMultis();
        parent.load(q);
        self.loadMultiTile();
      },
      resize:function() {
        self.singleTile.resize();
        self.multiTile.resize();
      },
      //
      multiTile_onapply:function(multis, others) {
        self.q.updateMultis(multis, others);
        self.close();
        self.onupdate(self.q);
      },
      loadSingleTile:function() {
        if (Array.isEmpty(self.sopts)) {
          self.singleTile.hide();
        } else {
          parent.loadSingleTile();
          self.singleTile.show();
        }
      },
      loadMultiTile:function() {
        if (Array.isEmpty(self.mopts))
          self.multiTile.hide();
        else
          self.multiTile.load(self.mopts, self.design.mcols, self.design.tristate, self.q.opts.getMultiOthers(), self.q.mc).show();
      },
      getDesign:function() {
        var s = self.sopts.length, m = self.mopts.length;
        var scols = 3, mcols = 3, tristate;
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
          'tristate':(self.q.qt.btmu != null)}
      }
    }
  }
}
/**
 * QPopMulti QPopCombo
 *   Tile page1
 *     SingleOptsTile singleTile
 *   Tile page2
 *     MultiOptsTile multiTile
 */
QPopCombo = {
  pop:function(q) {
    return QPopCombo = this.create().pop(q);
  },
  create:function() {
    return Html.extend(Html.Pop.create(), this._protof);
  },
  _protof:function(self) {
    var parent = QPopMulti._protof(self);
    self.aug(parent);
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
        self.sopt = q.opts.getSingleSel();
        self.mopts = q.opts.getMultis();
        parent.load(q);
        self.loadMultiTile();
        self.showPage(q.opts.isSel() ? 2 : 1);
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
        Html.Anchor.create('back', 'Back').bubble('onclick', self.back_onclick).into(div);
        return cap;
      },
      back_onclick:function() {
        self.showPage(1);
      }
    }
  }
}
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
      /*
       * @arg Opt[] opts
       * @arg int cols (0=scrolling)
       * @arg Opt other (optional)
       */
      load:function(opts, cols, other) {
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
          self._resize();
          return self;
        },
        resize:function() {
          var w = self.getDim().width - 20;
          if (w > 0)
            self._resize(w);
        },
        _resize:function(w) {
          self.input.setWidth(w);
          self.cb.get('insert').setWidth(w);
        },
        //
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
  /*
   * @arg Option opt
   */
  create:function(opt) {
    var self = Html.Anchor.create('OptAnchor');
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
 *   TableCol table [OptCheck,..]
 *   Div others [OtherCheck,..]
 *   CmdBar cb
 */
MultiOptsTile = {
  create:function(into) {
    var self = Html.Tile.create(into, 'MultiOptsTile');
    return self.aug({
      onapplyopts:function(multis, others) {},
      //
      init:function() {
        self.cap = Html.Div.create('Cap').into(self);
        var div = Html.Tile.create(self, 'MultiOpts');
        self.table = MultiOptsTile.Table.create(div);
        self.others = MultiOptsTile.Others.create(div);
        self.cb = Html.CmdBar.create(div).button('Apply Checkmarked', self.apply_onclick, 'none', 'apply');
      },
      /*
       * @arg Opt[] multis
       * @arg int cols
       * @arg bool asTriChecks
       * @arg Opt[] others
       * @arg string caption (optional) 
       */
      load:function(multis, cols, asTriChecks, others, cap) {
        self.multis = multis;
        self.cap.setText(cap);
        self.table.load(multis, cols, asTriChecks);
        self.others.load(others);
        self._resize();
        return self;
      },
      resize:function() {
        var w = self.getDim().width - 10;
        if (w > 0) {
          self.others.resize();
          self._resize(w);
        }
      },
      _resize:function(w) {
        self.cb.get('apply').setWidth(w);
      },
      //
      apply_onclick:function() {
        self.onapplyopts(self.multis, self.others.getOpts());
      }
    })
  },
  Table:{
    create:function(into) {
      var self = Html.TableCol.create(into);
      return self.aug({
        load:function(opts, cols, asTriChecks) {
          self.reset(cols);
          opts.forEach(function(opt) {
            self.add((asTriChecks) ? OptTriCheck.create(opt) : OptCheck.create(opt)); 
          });
          return self;
        }
      })
    }
  },
  Others:{
    create:function(into) {
      var self = Html.Div.create().into(into);
      return self.aug({
        load:function(opts) {
          self.opts = opts;
          self.loix = opts.end().index;
          self.clean();
          opts.forEach(function(opt) {
            self.append(opt);
          })
        },
        getOpts:function() {
          return self.opts;
        },
        resize:function() {
          Array.forEach(self.opts, function(opt) {
            opt._oc.resize();
          })
        },
        //
        append:function(opt) {
          opt._oc = OtherCheck.create(self, opt).bubble('ontoggle', self.opt_ontoggle);
          return opt;
        },
        opt_ontoggle:function(opt) {
          if (opt.index == self.loix) { 
            self.loix++;
            self.opts.push(self.append(OptionNewOther.create(self.loix)));
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
        self.check = Html.InputCheck.create().into(self).bubble('onclick', self, 'toggle');
        self.anchor = Html.Anchor.create().setText(self.getTextFrom(opt)).into(self).bubble('onclick', self, 'toggle').bubble('ondblclick', self, 'toggle');
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
        return opt.text;
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
  /*
   * @arg <e> into
   * @arg Option opt
   */
  create:function(into, opt) {
    var self = Html.Div.create('OtherCheck').setUnselectable().into(into);
    return self.aug({
      ontoggle:function(opt) {},
      //
      init:function() {
        self.check = Html.InputCheck.create().into(self).bubble('onclick', self.check_onclick);
        self.input = Html.InputText.create().setValue(opt.text).noSelectFocus().into(self).bubble('onclick', self.input_onclick).bubble('onchange', self.input_onchange);
        self.setValue(opt.selected);
        if (into._ow) 
          self._resize(into._ow);
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
      getValue:function() {
        return self.value;
      },
      resize:function() {
        var w = into.getDim().width - 40;
        if (w > 0)   
          self._resize(into._ow = w);
        else 
          into._ow = null
      },
      _resize:function(w) {
        self.input.setWidth(w);
      },
      //
      toggle:function() {
        self.setValue(! self.value);
        if (self.value) { 
          self.input.setFocus().select();
          self.ontoggle(opt);
        }
      },
      check_onclick:function() {
        self.toggle();
      },
      input_onclick:function() {
        if (! self.value) 
          self.toggle();
      },
      input_onchange:function() {
        opt.setText(self.input.getValue());
      }
    })
  }
}
/**
 * Pop QPopFree
 */
QPopFree = {
  pop:function(q) {
    return QPopFree = this.create().pop(q);
  },
  create:function() {
    var self = Html.extend(Html.Pop.create(), this._protof);
    return self.aug(this._protof(self)).aug({
      init:function() {
        self.content.addClass('PopFree');
        self.input = this.TextArea.create(self.content).bubble('onkeypresscr', self.update);
        self.cb = Html.CmdBar.create(self.content).button('OK', self.update, 'ok').cancel(self.close);
      },
      onshow:function() {
        async(function(){self.input.setFocus()});
      },
      loadFromOpt:function() {
        self.input.setValue(self.opt.getText());
      },
      updateOpt:function() {
        self.opt.setText(self.input.getValue());
      },
      TextArea:{
        create:function(into) {
          return Html.TextArea.create().setRows(5).aug(Html.InputText._protof_onkeypresscr).into(into);
        }
      }
    })
  },
  _protof:function(self) {
    return {
      onupdate:function(q) {},
      //
      pop:function(q) {
        self.load(q);
        self.showPosCursor();
        return self;
      },
      load:function(q) {
        self.q = q;
        self.setCaption(q.desc);
        self.opt = q.opts.getSingle();
        self.loadFromOpt();
      },
      update:function() {
        self.updateOpt();
        self.q.updateSingle(self.opt);
        self.close();
        self.onupdate(self.q);
      },
      //
      loadFromOpt:function() {},
      updateOpt:function() {}
    }
  }
}
/**
 * QPopFree QPopCalc
 */
QPopCalc = {
  pop:function(q) {
    return QPopCalc = this.create().pop(q);
  },
  create:function() {
    return Html.extend(Html.Pop.create(), this._protof);
  },
  _protof:function(self) {
    var parent = QPopFree._protof(self);
    self.aug(parent);
    return {
      init:function() {
        self.content.addClass('PopCalc');
        self.display = this.Display.create(self.content);
        self.keypad = this.KeyPad.create(self.content).bubble('onkey', self.keypad_onkey);
        self.cb = Html.CmdBar.create(self.content).button('OK', self.update, 'ok').cancel(self.close);
      },
      onshow:function() {
        self.focus();
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
          case 13:self.update();break;
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
          });
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
              return Html.Anchor.create(cls, text).aug({
                onclick:function() {
                  self.key_onclick(text);
                },
                ondblclick:function() {
                  self.key_onclick(text);
                }
              });
            },
            keyback:function() {
              return Html.Anchor.create('Fn KeyBack').html('&#217').aug({
                onclick:function() {
                  self.keyback_onclick();
                },
                ondblclick:function() {
                  self.keyback_onclick();
                }
              });
            }
          })
        }
      }
    }
  }
}
/**
 * QPopCalc QPopCalcLbs
 */
QPopCalcLbs = {
  pop:function(q) {
    return QPopCalcLbs = this.create().pop(q);
  },
  create:function() {
    return Html.extend(Html.Pop.create(), this._protof);
  },
  _protof:function(self) {
    var parent = QPopCalc._protof(self);
    self.aug(parent);
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
  }
}
/**
 * QPopCalc QPopCalcFt
 */
QPopCalcFt = {
  pop:function(q) {
    return QPopCalcFt = this.create().pop(q);
  },
  create:function() {
    return Html.extend(Html.Pop.create(), this._protof);
  },
  _protof:function(self) {
    var parent = QPopCalc._protof(self);
    self.aug(parent);
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
                  self.value = x1 + '.' + (String.toFloat('.' + x2) * 12);
                else
                  self.value = '' + (x1 + Math.round((x2 / 12), 2));
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
  }
}
/**
 * QPopFree QPopCalendar
 */
QPopCalendar = {
  pop:function(q) {
    return QPopCalendar = this.create().pop(q);
  },
  create:function() {
    return Html.extend(Html.Pop.create(), this._protof);
  },
  _protof:function(self) {
    var parent = QPopFree._protof(self);
    self.aug(parent);
    return {
      init:function() {
        self.content.addClass('p5');
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
        self.opt.setText(self.value.toString());
      }
    }
  }
}
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
                          var day = Html.Anchor.create().aug({
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
                      self.prevbox = Html.Anchor.create('Prev').noFocus().bubble('onclick', self.prev);
                      self.nextbox = Html.Anchor.create('Next').noFocus().bubble('onclick', self.next);
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
          self.monthbox = Html.Anchor.create('OptAnchor').setWidth(112).bubble('onclick', self.month_onclick);
          self.yearbox = Html.Anchor.create('OptAnchor').setWidth(40).bubble('onclick', self.year_onclick);
          self.unknownbox = Html.Anchor.create('OptAnchor').setText('Date Unknown').bubble('onclick', self.unknown_onclick);
          Html.TableCol.create(self, 3, [self.monthbox, self.yearbox, self.unknownbox]);
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
 * TextAnchor QuestionDateEntry
 */
QuestionDateEntry = {
  create:function() {
    var self = Html.TextAnchor.create('dical', 8);
    return self.aug({
      init:function() {
        self.q = QuestionDate.asDummy();
      },
      getValue:function() {
        return self.getText();
      },
      setValue:function(value) {
        self.setText(value);
      },
      //
      onclick_anchor:function(text) {
        self.q.setByValue(text).pop().bubble('onupdate', function() {
          self.setText(self.q.getValue());
          self.setFocus();
        })
      }
    })
  }
}
