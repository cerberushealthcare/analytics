/**
 * TabAnchor UserSelector
 */
UserAnchorTab = {
  create:function(cls, cols, text) {
    return Html.AnchorTabSelector.create(cls, cols, text).extend(function(self) {
      return {
        onset:function() {},
        //
        load:function() {
          self.radios(C_Users);
          return self;
        },
        /*
         * @arg User rec (optional) 
         */
        set:function(rec) {
          self.setText((rec) ? rec.name : text || '(Select)');
        },
        set_byId:function(id) {
          self.setValue(id);
        },
        /*
         * @arg int userId
         */
        setValueText:function(userId) {
          self.setValue(userId);
        },
        //
        onchange:function(value) {
          self.onset(null);
        }
      }
    })
  }
}
DoctorAnchorTab = {
  create:function() {
    return UserAnchorTab.create('action acct1', 1).extend(function(self) {
      return {
        init:function() {
          self.radios(C_Docs);
        }
      }
    })
  }
}
Html.Selector = {
  //
  create:function(Anchor_Rec, SelectorPop) {
    var My = this;
    return Html.Span.create().extend(function(self) {
      return {
        onupdate:function(rec) {},
        //
        init:function() {
          self.Anchor_Select = Anchor_Rec.asSelect ? Anchor_Rec : Html.AnchorAction;
        },
        load:function(recs, value/*=null*/) {
          self.recs = recs;
          if (value) 
            self.setValue(self.getRecFrom(value));
          return self;
        },
        setValue:function(rec, text/*=null*/) {
          self.clean();
          self.rec = rec;
          self.append(Object.is(rec) ? Anchor_Rec.create(rec, self.pop) : self.Anchor_Select.asSelect(text || '(Select)', self.pop));
          return self;
        },
        getValue:function() {
          return self.rec ? self.getValueFrom(self.rec) : null;
        },
        reset:function() {
          self.recs = null;
          self.clean();
        },
        //
        getRecFrom:function(value) {
          if (self.recs) {
            for (var i = 0; i < self.recs.length; i++) {
              if (self.getValueFrom(self.recs[i]) == value)
                return self.recs[i];
            }
          }
        },
        getValueFrom:function(rec) {
          return rec;
        },
        pop:function() {
          SelectorPop.pop(self.recs)
            .bubble('onselect', self.Pop_onselect);
        },
        Pop_onselect:function(rec) {
          if (rec != self.rec) {
            self.setValue(rec);
            self.onupdate(rec);
          }
        }
      }
    })
  },
  Pop:{
    create:function(caption, width, Table) {
      Table = Table || Html.TableLoader;
      return Html.Pop.create(caption, width).withFrame().extend(function(self) {
        return {
          POP_POS:Pop.POS_CURSOR,
          onselect:function(rec) {},
          //
          init:function() {
            self.Table = Table.create(self.frame).bubble('onselect', self.Table_onselect);
          },
          //
          Table_onselect:function(rec) {
            self.onselect(rec);
            self.close();
          }
        }
      })
    }
  }
}
UserSelector = {
  create:function() {
    return Html.Selector.create(AnchorUser, UserSelector.Pop).extend(function(self) {
      return {
        getValueFrom:function(rec) {
          return rec.userId;
        }
      }
    })
  },
  Pop:Html.SingletonPop.aug({
    create:function() {
      return Html.Selector.Pop.create('User Selector', 350, UserSelector.Table).extend(function(self) {
        return {
          //
          onpop:function(recs) {
            if (self.recs == null) 
              self.load(recs);
          },
          load:function(recs) {
            self.recs = recs;
            self.Table.load(recs, function(rec, tr) {
              tr.select(AnchorUser);
            })
          }
        }
      })
    }
  }),
  Table:{
    create:function(container) {
      return Html.TableVertical.create(container, 'UVTable').extend(function(self) {
        return {
          onselect:function(rec) {},
          //
          load:function(recs) {
            if (self.recs == null) {
              self.recs = [];
              recs.each(function(rec) {
                self.recs.push(AnchorUser.create(rec, self.onselect));
              })
              var cols = Math.ceil(self.recs.length / 10) || 1;
              self.vertload(cols, self.recs);
            }
          }
        }
      })
    }
  }
}
PracticeSelector = {
  create:function() {
    return Html.Selector.create(AnchorPractice, PracticeSelector.Pop).extend(function(self) {
      return {
        onset:function() {},
        //
        init:function() {
          self.setValue(null);
          self.text = null;
        },
        setValue:self.setValue.append(function() {
          self.onset(self.rec);
        }),
        set:function(rec) {
          self.setValue(rec);
        },
        setValueText:function(value, text) {
          self.text = text;
          self.setValue(value, text);
        },
        getValueFrom:function(rec) {
          return Object.is(rec) ? rec.userGroupId : rec;
        },
        getText:function() {
          if (Object.is(self.rec))
            return self.rec.name;
          else
            return self.text;
        },
        load:function() {
          self.recs = C_Children;
          return self;
        }
      }
    })
  },
  Pop:Html.SingletonPop.aug({
    create:function() {
      return Html.Selector.Pop.create('Practice Selector', 400, PracticeSelector.Table).extend(function(self) {
        return {
          //
          onpop:function(recs) {
            if (self.recs == null)
              self.load(recs);
          },
          load:function(recs) {
            self.recs = recs;
            self.Table.load(recs, function(rec, tr) {
              tr.select(AnchorPractice);
            })
          }
        }
      })
    }
  }),
  Table:{
    create:function(container) {
      return Html.ScrollTable.create(container).extend(function(self) {
        return {
          onselect:function(rec) {},
          //
          load:function(recs) {
            if (self.recs == null) {
              self.recs = recs;
              recs.each(function(rec) {
                self.tbody().tr().td(AnchorPractice.create(rec, self.onselect));
              })
            }
          }
        }
      })
    }
  }
}
AnchorPractice = {
  create:function(rec, onclick) {
    return Html.AnchorRec.from(Html.AnchorAction.asPractice(rec.name), rec, onclick);
  },
  asSelect:function(text, url) {
    return Html.AnchorAction.asPractice(text, url);
  }
}
