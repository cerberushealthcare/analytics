/**
 * Page LoginPage
 */
LoginPage = PortalPage.extend(function(self) {
  return {
    init:function() {
      self.loginbox = _$('login');
      self.authbox =_$('auth')
      self.passbox = _$('pass');
      self.tosbox = _$('tos');
      self.id = Html.InputText.$('id').setFocus().aug(Events.onkeypresscr)
        .bubble('onkeypresscr', self.login_onclick);
      self.pw = Html.InputText.$('pw').aug(Events.onkeypresscr)
        .bubble('onkeypresscr', self.login_onclick);
      self.anchor = _$('alog').aug(Html.Anchor._proto);
    },
    login_onclick:function() {
      var id = String.nullify(self.id.value);
      var pw = String.nullify(self.pw.value);
      if (id && pw) {
        self.anchor.working(true);
        Ajax.Login.login(id, pw, self.sess_onupdate);
      }
    },
    sess_onupdate:function(sess) {
      self.loginbox.hide();
      if (sess.needsChallenge()) {
        self.authbox.show();
        self.passbox.hide();
        self.tosbox.hide();
        AuthTile.create().load(sess);
      } else if (sess.needsPassword()) {
        self.passbox.show();
        self.authbox.hide();
        self.tosbox.hide();
        PassTile.create().load(sess);
      } else if (sess.needsTos()) {
        self.tosbox.show();
        self.authbox.hide();
        self.passbox.hide();
        TosTile.create();
      } else {
        self.go('welcome.php'); 
      }
    }
  }
})
AuthTile = {
  create:function() {
    var container = _$('auth-tile').clean();
    var My = this;
    return Html.Tile.create(container).extend(function(self) {
      return {
        load:function(sess) {
          self.questions = My.QuestionBoxes.create(self).load(sess.cqs).setFocus()
            .bubble('onkeypresscr', self.submit_onclick);
          self.submit = Html.Anchor.create('big', 'Submit').into(self)
            .bubble('onclick', self.submit_onclick);
        },
        //
        submit_onclick:function(a) {
          var responses = self.questions.getResponses();
          self.submit.working(true);
          Ajax.Login.respond(responses, function(sess) {
            self.submit.working();
            LoginPage.sess_onupdate(sess);
          })
        }
      }
    })
  },
  QuestionBoxes:{
    create:function(container, cqs) {
      var My = this;
      return Html.Tile.create(container).aug(Events.onkeypresscr).extend(function(self) {
        return {
          onkeypresscr:function() {},
          //
          init:function() {
            self.boxes = [];
          },
          load:function(cqs) {
            self.boxes.push(My.QuestionBox.create_asSsn(self));
            cqs.forEach(function(cq) {
              self.boxes.push(My.QuestionBox.create(self, cq));
            })
            return self;
          },
          getResponses:function() {
            var responses = [];
            self.boxes.forEach(function(box) {
              responses.push(box.getValue());
            })
            return responses;
          },
          setFocus:function() {
            self.boxes[0].input.setFocus();
            return self;
          }
        }
      })
    },
    QuestionBox:{
      create:function(container, cq) {
        return Html.Tile.create(container).extend(function(self) {
          return {
            init:function() {
              Html.Label.create(null, cq).into(self);
              self.input = Html.InputText.create().into(self).setSize(25);
            },
            getValue:function() {
              return String.nullify(self.input.getValue());
            }
          }
        })
      },
      create_asSsn:function(container) {
        return this.create(container, 'What are the last four digits of your social security number?');
      }
    }
  }
}
TosTile = {
  create:function() {
    var container = _$('tos');
    var My = this;
    return Html.Tile.create(container).extend(function(self) {
      return {
        init:function() {
          self.cb = Html.CmdBar.create(_$('tos-cb'))
            .button('Yes, I accept the terms', self.yes_onclick, 'ok')
            .button('No, I do not accept', self.no_onclick, 'suspend');
        },
        //
        yes_onclick:function() {
          self.working(true);
          Ajax.Login.acceptTerms(function(sess) {
            self.working();
            LoginPage.sess_onupdate(sess);
          })
        },
        no_onclick:function() {
          LoginPage.goLogout();
        }
      }
    })
  }
}
PassTile = {
  create:function() {
    var container = _$('pass-tile').clean();
    var My = this;
    return Html.Tile.create(container).extend(function(self) {
      return {
        load:function(sess) {
          self.clean();
          var qs = ['New Password', 'New Password (repeat)'];
          self.questions = My.QuestionBoxes.create(self).load(qs).setFocus()
            .bubble('onkeypresscr', self.submit_onclick);
          self.submit = Html.Anchor.create('big', 'Submit').into(self)
            .bubble('onclick', self.submit_onclick);
          InfoBox.create(self, PasswordStrength);
        },
        //
        submit_onclick:function(a) {
          var responses = self.questions.getResponses();
          var strength = self.questions.boxes[0].input.isStrong() ? 1 : 0;
          responses.push(strength);
          self.submit.working(true);
          Ajax.Login.setPassword(responses, function(sess) {
            self.submit.working();
            LoginPage.sess_onupdate(sess);
          })
        }
      }
    })
  },
  QuestionBoxes:{
    create:function(container, qs) {
      var My = this;
      return AuthTile.QuestionBoxes.create(container, qs).extend(function(self) {
        return {
          load:function(cqs) {
            cqs.forEach(function(cq) {
              self.boxes.push(My.QuestionBox.create(self, cq));
            })
            return self;
          }
        }
      })
    },
    QuestionBox:{
      create:function(container, cq) {
        return Html.Tile.create(container).extend(function(self) {
          return {
            init:function() {
              //Html.Label.create(null, cq).into(self);
              //self.input = Html.InputPassword.create().into(self).setSize(25);
              var strength = PasswordStrength;
              self.input = NewPasswordTile.PassEntry.create(self, strength, cq);
            },
            getValue:function() {
              return String.nullify(self.input.getValue());
            },
            isBlank:function() {
              return String.isBlank(self.input.getValue());
            }
          }
        })
      }
    }
  }
}
InfoBox = {
  create:function(container, strength) {
    return Html.Tile.create(container, 'InfoBox').extend(function(self) {
      return {
        init:function() {
          self.html(strength.tips());
        }
      }
    })
  }
}

NewPasswordTile = {
  create:function(container) {
    var My = this;
    return Html.Tile.create(container, 'PasswordTile').extend(function(self) {
      return {
        init:function() {
          var strength = PasswordStrength;
          self.pass = My.PassEntry.create(self, strength, 'Password');
          self.repeat = My.PassEntry.create(self, strength, 'Password (Repeat)');
        },
        reset:function() {
          self.pass.reset();
          self.repeat.reset();
        },
        setFocus:function() {
          self.pass.setFocus();
        },
        setFocusRepeat:function() {
          self.repeat.setFocus();
        },
        isBlank:function() {
          return String.isBlank(self.pass.getValue());
        },
        isBlankRepeat:function() {
          return String.isBlank(self.repeat.getValue());
        },
        isStrong:function() {
          return self.pass.isStrong();
        },
        isEqual:function() {
          return self.pass.equals(self.repeat);
        },
        getValue:function() {
          return self.pass.getValue();
        }
      }
    })
  },
  /**
   * Tile PassEntry
   *   InputPassword input
   *   StrengthMeter meter
   */
  PassEntry:{
    create:function(container, strength, label) {
      var My = this;
      return Html.Tile.create(container, 'PassEntry').extend(function(self) {
        return {
          init:function() {
            self.label = Html.Label.create().into(self).setText(label);
            self.input = Html.InputPassword.create().into(self).bubble('onkeyup', self.input_onkeypress);
            self.meter = My.StrengthMeter.create(self, strength);
          },
          reset:function() {
            self.input.setValue();
            self.meter.reset();
          },
          setFocus:function() {
            self.input.setFocus();
          },
          isStrong:function() {
            return self.meter.isStrong();
          },
          equals:function(that) {
            return self.input.getValue() == that.input.getValue();
          },
          getValue:function() {
            return self.input.getValue();
          },
          //
          input_onkeypress:function() {
            self.meter.load(self.input.getValue());
          }
        }
      })
    },
    StrengthMeter:{
      create:function(container, strength) {
        return Html.Span.create().into(container).extend(function(self) {
          return {
            on:function() {
              self.visible();
            },
            off:function() {
              self.invisible();
            },
            reset:function() {
              self.load();
            },
            load:function(pw) {
              self.pw = pw;
              self.test();
              self.draw();
            },
            isStrong:function() {
              return self.result && self.result.cls == 'ok';
            },
            //
            draw:function() {
              self.setText(self.result.text);
              self.setClass(self.result.cls);
            },
            test:function() {
              var pw = self.pw;
              self.result = {'text':'','cls':''};
              if (String.isBlank(pw) || pw.length < 4)
                return;
              self.result.text = strength.test(pw);
              if (self.result.text) {
                self.result.cls = 'warn';
              } else {
                self.result.cls = 'ok';
                self.result.text = 'OK';
              }
            }
          }
        })
      }
    }
  }
}
/**
 * Rec PasswordStrength
 */
PasswordStrength = {
  'minLength':8,
  'maxLength':18,
  'requireUpper':1,
  'requireLower':1,
  'requireNumeric':1,
  'requireSpecial':1,
  'allowSpace':0,
  'maxRepeat':2,
  'maxConsec':2,
  //
  test:function(pw) {
    if (pw.length < this.minLength) 
      return 'Too short';
    if (pw.length > this.maxLength) 
      return 'Too long';
    if (this.requireUpper)
      if (! (/^(?=.*[A-Z])/g).test(pw))
        return 'An uppercase letter required';
    if (this.requireLower)
      if (! (/^(?=.*[a-z])/g).test(pw))
        return 'A lowercase letter required';
    if (this.requireNumeric)
      if (! (/^(?=.*[0-9])/g).test(pw))
        return 'A numeric digit required';
    if (this.requireSpecial)
      if (! (/^(?=.*[?!@#$%&*+=<>])/g).test(pw))
        return 'A special symbol required';
    if (! this.allowSpace) {
      if ((/^(?=.*[ ])/g).test(pw))
        return 'No spaces allowed';
    }
    if (this.maxRepeat || this.maxConsec) {
      var chars = this.chars(pw);
      if (this.maxRepeat) 
        if (chars.max(0) > this.maxRepeat) 
          return 'Too many repeated';
      if (this.maxConsec) 
        if (chars.max(1) > this.maxConsec) 
          return 'Too many consecutive';
    }
  },
  tips:function() {
    var lis = [];
    lis.push('Must be between ' + this.minLength + ' and ' + this.maxLength + ' characters in length.');
    lis.push('Must contain at least one of: ' + this.tipscontain());
    if (! this.allowSpace)
      lis.push('No spaces.');
    if (this.maxRepeat)
      lis.push('No more than ' + this.maxRepeat + ' repeating characters allowed: <em><span>' + '111111'.substr(0, this.maxRepeat + 1) + '</span></em>');
    if (this.maxConsec)
      lis.push('No more than ' + this.maxConsec + ' consecutive characters allowed: <em><span>' + '123456'.substr(0, this.maxConsec + 1) + '</span></em>');
    return '<div>Password Rules</div><ol><li>' + lis.join('</li><li>') + '</li></ol>';
  },
  tipscontain:function() {
    var lis = [];
    if (this.requireUpper)
      lis.push('uppercase letter: A-Z');
    if (this.requireLower)
      lis.push('lowercase letter: a-z');
    if (this.requireNumeric)
      lis.push('numeric digit: 0-9');
    if (this.requireSpecial)
      lis.push('special symbol: !?@#$%&*+<=>');
    return '<ul><li>' + lis.join('</li><li>') + '</li></ul>';
  },
  chars:function(pw) {
    var c, arr = [];
    for (var i = 0; i < pw.length; i++) 
      arr.push({'code':pw.charCodeAt(i),'last':i && arr[i - 1].code});
    arr.max = function(offset) {
      var max = 0, ct = 1;
      this.forEach(function(c) {
        if (c.code == c.last + offset) { 
          ct++;
        } else {
          max = (ct > max) ? ct : max;
          ct = 1;
        }
      })
      return max;
    }
    return arr;
  }
}
