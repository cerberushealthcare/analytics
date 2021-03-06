VerifyPasswordPop = {
  pop:function(onok) {
    if (VerifyPasswordPop.verified)
      onok && onok();
    else
      return Html.Pop.singleton_pop.apply(VerifyPasswordPop, arguments);
  },
  create:function() {
    return Html.Pop.create('Clicktate').withFrame('Verify password to continue.').extend(function(self) {
      return {
        init:function() {
          self.Pass = Html.InputPassword.create().into(self.frame)
            .bubble('onkeypresscr', self.Ok_onclick);
          Html.CmdBar.create(self.content)
            .ok(self.Ok_onclick)
            .cancel(self.close);
        },
        onshow:function(onok) {
          self.onok = onok;
          self.Pass.clean().setFocus();
        },
        //
        Ok_onclick:function() {
          Ajax.Profile.verifyPassword(self.Pass.getValue(), function(good) {
            if (good) {
              VerifyPasswordPop.verified = true;
              self.close();
              self.onok && self.onok();
            } else {
              Pop.Msg.showCritical('Entry is incorrect. Please retry.');
            }
          })
        }
      }
    })
  }
}
/**
 * Pop ChangePasswordPop
 */
ChangePasswordPop = {
  pop:function(id) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    var My = this;
    return Html.Pop.create('Change Password', 560).withErrorBox().extend(function(self) {
      return {
        init:function() {
          self.oldpw = OldPasswordTile.create(self.content);
          self.newpw = NewPasswordTile.create(self.content).addClass('mt10');
          Html.CmdBar.create(self.content).save(self.save_onclick, 'Set Password and Continue >').cancel(self.close);
          My.InfoBox.create(self, PasswordStrength);
          self.setOnkeypresscr(self.save_onclick);
        },
        reset:function() {
          self.oldpw.reset();
          self.newpw.reset();
        },
        onshow:function(id) {
          self.reset();
          self.id = id;
          self.setFocus();
        },
        setFocus:function() {
          self.oldpw.setFocus();
        },
        onerror:self.onerror.append(function() {
          self.oldpw.setFocus();
        }),
        save_onclick:function() {
          self.save();
        },
        save:function() {
          if (self.validate()) {
            self.working(true);
            Ajax.Profile.changePassword(self.id, self.oldpw.getValue(), self.newpw.getValue(), function() {
              self.working(false);
              self.close();
              Pop.Msg.showInfo('Password changed.');
            }, self.onerror);
          }
        },
        validate:function() {
          self.errorbox.hide();
          if (self.oldpw.isBlank()) {
            self.errorbox.show('Old password is required.');
            self.oldpw.setFocus();
            return;
          }
          if (self.newpw.isBlank()) {
            self.errorbox.show('New password is required.');
            self.newpw.setFocus();
            return;
          }
          if (self.newpw.isBlankRepeat()) {
            self.errorbox.show('New password (repeat) is required.');
            self.newpw.setFocusRepeat();
            return;
          }
          if (! self.newpw.isStrong()) {
            self.errorbox.show('New password does not meet formatting requirements.');
            self.newpw.setFocus();
            return;
          }
          if (! self.newpw.isEqual()) { 
            self.errorbox.show('New and repeat password do not match.');
            self.newpw.setFocusRepeat();
            return;
          }
          return true;
        }
      }
    })
  },
  InfoBox:{
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
}
ChangePasswordPop_Expired = {
  pop:function(id, tablet) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    var My = this;
    return ChangePasswordPop.create().extend(function(self) {
      return {
        init:function() {
          Html.Div.create('ExpiredBox').before(self.oldpw).html('A new password is required.<br/>Please choose a new password to continue with your login.');
          self.setCaption('Set Password');
          self.oldpw.hide();
        },
        onshow:function(id, tablet) {
          self.reset();
          self.id = id;
          self.tablet = tablet;
          self.setFocus();
        },
        setFocus:function() {
          self.newpw.setFocus();
        },
        save:function() {
          self.oldpw.setValue('any');
          if (self.validate()) {
            self.working(true);
            Ajax.Profile.setPassword(self.id, self.newpw.getValue(), self.tablet, function() {
              Page.Nav.goWelcome();
            })
          }
        }
      }
    })
  }
}
OldPasswordTile = {
  create:function(container) {
    return Html.Tile.create(container, 'PasswordTile').extend(function(self) {
      return {
        init:function() {
          self.label = Html.Label.create().into(self).setText('Old Password');
          self.input = Html.InputPassword.create().into(self);
        },
        reset:function() {
          self.input.setValue();
        },
        getValue:function() {
          return self.input.getValue();
        },
        setValue:function(value) {
          self.input.setValue(value);
          return self;
        },
        isBlank:function() {
          return String.isBlank(self.input.getValue());
        },
        setFocus:function() {
          self.input.setFocus();
        }
      }
    })
  } 
}
/**
 * Tile NewPasswordTile
 *   PassEntry pass
 *   PassEntry repeat
 */
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
