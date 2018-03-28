(function (body) {
  var ele = function (selector, parent) {
    if (!selector) return selector;
    if (typeof selector !== 'string' && selector instanceof Element) {
      return factory(selector)
    }
    var element;
    parent = parent || document;
    if (selector.split('#').length == 2) {
      element = document.getElementById(selector.split('#')[1]);
    } else {
      element = parent.querySelectorAll(selector);
    }
    function factory(el) {
      if (el.isEle) return el;
      el.isEle = true;
      var proto = {
        prototype: Element,
        val: function (value) {
          return typeof value === 'string' ? (this.value = value) : this.value;
        },
        html: function (html) {
          return typeof html === 'string' ? (this.innerHTML = html) : this.innerHTML
        },
        next: function () {
          var nextElement = this.nextSibling;
          while (nextElement instanceof Text) {
            nextElement = nextElement.nextSibling;
          }
          return ele(nextElement);
        },
        addClass: function (klass) {
          var classArray = this.className.split(' ');
          classArray.indexOf(klass) == -1 && (this.className += ' ' + klass);
          return this;
        },
        removeClass: function (klass) {
          var classArray = this.className.split(' ');
          if (classArray.indexOf(klass) == -1) return;
          classArray.splice(classArray.indexOf(klass), 1);
          this.className = classArray.join(' ');
          return this;
        },
        hasClass: function (klass) {
          var classArray = this.className.split(' ');
          return classArray.indexOf(klass) > -1;
        },
        ele: function (selector) {
          return ele(selector, this)
        },
        getInput: function (name) {
          return this.ele('input[name=' + name + ']')[0];
        },
        addEvent: function (type, cb) {
          if (this.addEventListener) {
            this.addEventListener(type, cb);
          } else if (this.attachEvent) {
            this.attachEvent('on' + type, function (e) {
              e = e || window.event;
              e.target = e.target || e.srcElement;
              cb(e);
            });
          }
          return this;
        },
        target: function (type) {
          if (typeof type === "string") {
            if (document.dispatchEvent) {
              var event = document.createEvent('HTMLEvents');
              event.initEvent(type, true, true);
              this.dispatchEvent(event);
            } else if (document.attachEvent) {
              this.fireEvent('on' + type)
            }
          }
          return this;
        },
        // removeSelf
      }
      if (el.__proto__) {
        proto.__proto__ = el.__proto__;
        el.__proto__ = proto;
      } else {
        for (var i in proto) {
          el[i] = proto[i];
        }
      }
      return el;
    }
    if (element instanceof Element) {
      element = factory(element);
    } else if (element) {
      for (var i = 0; i < element.length; i++) {
        factory(element[i]);
      }
      element.call = function (name, arg1, arg2) {
        for (var i = 0; i < element.length; i++) {
          element[i][name](arg1, arg2);
        }
      }
    } else {
      console.error('unknow ele type ', selector)
    }

    return element;
  }
  var Router = function (routes, views) {
    this.routes = routes;
    this.views = views;
  }
  Router.prototype = {
    constructor: Router,
    run: function () {
      var self = this;
      this._action();
      this._bindEvent();
      window.addEventListener('hashchange', function () {
        self._action();
      });
      return this;
    },
    _action: function () {
      var routeName = location.hash.split('#')[1];
      var routes = this.routes;
      routes._init && routes._init();
      if (routes.hasOwnProperty(routeName)) {
        this.views[routeName].removeClass('none');
        routes[routeName](this);
      } else {
        routes.other && routes.other(this);
      }
    },
    _bindEvent: function () {
      var self = this;
      for (var i in self.views) {
        var el = self.views[i];
        el.ele('.redirect-back').call('addEvent', 'click', function () {
          self.back();
        })
        for (var routeName in self.views) {
          (function (routeName) {
            el.ele('.redirect-' + routeName).call('addEvent', 'click', function () {
              self.redirect(routeName);
            });
          })(routeName);
        }
      }
      return this;
    },
    back: function () {
      history.back();
      return this;
    },
    redirect: function (routeName) {
      location.hash = '#' + routeName;
    }
  }
  var Helper = function (options) {
    var self = this;
    this.options = options || {};
    this.views = {
      login: ele('.m-dz-login')[0],
      smslogin: ele('.m-dz-sms-login')[0],
      register: ele('.m-dz-register')[0],
      forgetword: ele('.m-dz-forgetword')[0],
      resetpwd: ele('.m-dz-resetpwd')[0],
      bindphone: ele('.m-dz-bind-phone')[0]
    };
    var routes = {
      _init: function () {
        body.innerHTML = '';
      },
      login: function (router) {
        body.appendChild(self.views.login);
        self.run_login();
      },
      smslogin: function(router) {
        body.appendChild(self.views.smslogin);
        self.run_sms_login();
      },
      register: function (router) {
        body.appendChild(self.views.register);
        self.run_register();
      },
      forgetword: function (router) {
        body.appendChild(self.views.forgetword);
        self.run_forgetpwd();
      },
      resetpwd: function (router) {
        body.appendChild(self.views.resetpwd);
        self.run_resetpwd();
      },
      bindphone: function (router) {
        body.appendChild(self.views.bindphone);
        self.run_bindphone();
      },
      other: function (router) {
        router.redirect('login')
      }
    }
    this.router = new Router(routes, self.views).run();
  }
  Helper.prototype = {
    constructor: Helper,
    ajax: function (options) {
      var url = this.options.site_url + options.url,
        callback = options.success,
        errorCb = options.error,
        type = options.type || 'GET',
        data = options.data || null,
        xmlHttp;
      function createxmlHttpRequest() {
        if (window.ActiveXObject) {
          xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
        } else if (window.XMLHttpRequest) {
          xmlHttp = new XMLHttpRequest();
        }
      }
      function postDataFormat(obj) {
        if (typeof obj != "object") {
          return obj;
        }
        if (typeof FormData == "function") {
          var data = new FormData();
          for (var attr in obj) {
            data.append(attr, obj[attr]);
          }
          return data;
        } else {
          var arr = new Array();
          var i = 0;
          for (var attr in obj) {
            arr[i] = encodeURIComponent(attr) + "=" + encodeURIComponent(obj[attr]);
            i++;
          }
          return arr.join("&");
        }
      }
      createxmlHttpRequest();
      xmlHttp.open(type, url);
      if (typeof FormData == "undefined") {
        xmlHttp.setRequestHeader("Content-Type", "application/json; charset=utf-8");
      }
      xmlHttp.send(postDataFormat(data));
      xmlHttp.onreadystatechange = function (result) {
        if ((xmlHttp.readyState === 4)) {
          var data = JSON.parse(xmlHttp.responseText);
          if (xmlHttp.status === 200 && data.status === 200) {
            callback && callback(data);
          } else {
            errorCb && errorCb(data);
          }
        }
      }
    },
    isPhone: function (phone) {
      return phone.trim().length > 5 
      var myreg = /^[1][3,4,5,7,8][0-9]{9}$/;
      if (!myreg.test(phone)) {
        return false;
      } else {
        return true;
      }
    },
    initVaptcha: function (options) {
      var self = this;
      var form = options.form;
      var successCallback = options.success;
      options.scene = options.scene || '';
      var _v = new function () {
        this.isPass = false;
        this.vaptcha = null;
        this.refresh = function () {
          if (this.isPass) {
            this.vaptcha.destroy();
            self.initVaptcha(options);
          }
        }
      }()
      var init = function () {
        self.ajax({
          url: '/plugin.php?id=phone_auth&action=getChallenge&scene=' + options.scene + '&t=' + (new Date()).getTime(),
          success: function (data) {
            data = data.data;
            var config = {
              vid: data.vid,
              challenge: data.challenge,
              container: options.element,
              type: options.type || 'popup',
              style: self.options.vaptcha_style || 'dark',
              https: options.https || false,
              color: self.options.vaptcha_color || '#3c8aff',
              lang: 'zh-CN',
              outage: '/plugin.php?id=phone_auth&action=downtime',
              success: function (token, challenge) {
                if (form) {
                  var inputs = form.getElementsByTagName('input');
                  inputs['vaptcha_challenge'].value = challenge;
                  inputs['vaptcha_token'].value = token;
                }
                _v.isPass = true;
                successCallback && successCallback(token, challenge);
              }
            }
            window.vaptcha(config, function (obj) {
              if (form) {
                var inputs = form.getElementsByTagName('input');
                inputs['vaptcha_challenge'].value = '';
                inputs['vaptcha_token'].value = '';
              }
              _v.vaptcha = obj;
              _v.vaptcha.init();
            });
          }
        })
      }
      var script = document.getElementById('vaptcha_v_js');
      if (script) {
        init();
      } else {
        script = document.createElement('script');
        protocol = 'https';//options.https ? 'https' : 'http';
        script.src = protocol + '://cdn.vaptcha.com/v.js';
        script.id = 'vaptcha_v_js';
        script.onload = script.onreadystatechange = function () {
          if (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete') {
            init();
            script.onload = script.onreadystatechange = null;
          }
        };
        document.getElementsByTagName("head")[0].appendChild(script);
      }
      return _v;
    },
    formValidate: function (rules, form) {
      /* 
      rule => {
          name: 'username',
          validate: Function
      }
       */
      rules.forEach(function (rule) {
        var el = form.getElementsByTagName('input')[rule.name];
        el.addEventListener('input', function () {
          rule.validator(el.value, el);
        })
        el.addEventListener('focus', function () {
          el.removeClass('error');
        })
        el.addEventListener('blur', function () {
          rule.validator(el.value, el);
        })
      })
    },
    buttonCountDown: function (sendCodeBtn, time) {
      var self = this;
      time = time || 120;
      sendCodeBtn.setAttribute('disabled', 'disabled');
      (function countDown() {
        if (time == 0) {
          sendCodeBtn.removeAttribute('disabled');
          sendCodeBtn.innerText = self.options.lang.send_code;
          return;
        }
        sendCodeBtn.innerText = time + 's';
        time--;
        setTimeout(countDown, 1000)
      })()
    },
    getFormData: function (form) {
      var inputs = form.ele('input');
      var res = {}
      for (var i = 0; i < inputs.length; i++) {
        inputs[i].name && (res[inputs[i].name] = inputs[i].value);
      }
      return res;
    },
    passwordLevel: function (oPass, oLevel) {
      oPass.addEvent('input', function (e) {
        e = e || window.event;
        var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
        var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
        var enoughRegex = new RegExp("(?=.{6,}).*", "g");
        var allWordsRegex = new RegExp('[a-zA-Z]');
        if (oPass.value.length >= 6) {
          if (strongRegex.test(oPass.value)) {
            oLevel.removeClass('pw-medium');
            oLevel.removeClass('pw-weak');
            oLevel.addClass('pw-strong');
            canBeSubmit = true;
          } else if (mediumRegex.test(oPass.value) || allWordsRegex.test(oPass.value)) {
            oLevel.removeClass('pw-strong');
            oLevel.removeClass('pw-weak');
            oLevel.addClass('pw-medium');
            canBeSubmit = true;
          } else {
            oLevel.removeClass('pw-strong');
            oLevel.removeClass('pw-medium');
            oLevel.addClass('pw-weak');
            canBeSubmit = false;
          }
        } else if (oPass.value.length > 0 && oPass.value.length < 6) {
          oLevel.removeClass('pw-strong');
          oLevel.removeClass('pw-medium');
          oLevel.addClass('pw-weak');
          canBeSubmit = false;
        } else {
          oLevel.removeClass('pw-strong');
          oLevel.removeClass('pw-medium');
          oLevel.removeClass('pw-weak');
          canBeSubmit = false;
        }
        if (e.keyCode == 13) {
          signUp()
        }
      })
    },
    showMsg: function (msg, success) {
      // this.modalTimer && clearTimeout(this.modalTimer);
      var modal = document.createElement('div');
      modal.className = 'dz-m-tip ' + (success ? 'dz-m-tip-success' : '');
      modal.innerHTML = '<div class="dz-m-cont"><span class="dz-tip-icon"></span><span class="dz-tip-text">' + msg + '</span></div>';
      document.body.appendChild(modal);
      var self = this;
      (function () {
        self.modalTimer = setTimeout(function () {
          modal && modal.parentNode.removeChild(modal);
        }, 1500)
      })(modal)
    },
    initCountryCode: function (form) {
      var menu = form.ele('.dropdown-menu')[0];
      form = form.ele('#phonePrefix');
      var btn = form.ele('.btn-down')[0];
      var show = function () {
        menu.style.display = 'block';
        btn.addClass('open');
      }
      var hide = function () {
        menu.style.display = 'none';
        btn.removeClass('open');
      }
      form.getInput('country_code').addEvent('focus', show)
      form.ele('.btn-down')[0].addEvent('click', function() {
        menu.style.display = menu.style.display == 'block' ? hide() : show();
      })
      ele('body')[0].addEvent('click', function (e) {
        var elem = e.target;
        !form.contains(elem) && hide();
      })
      menu.ele('.dropdown-item').call('addEvent', 'click', function (e) {
        hide();
        form.getInput('country_code').value = e.target.innerHTML.split('+')[1].trim();
      })
    },
    loging_loaded: false,
    run_login: function () {
      if (this.loging_loaded) return;
      this.loging_loaded = true;
      var self = this,
        form = ele('.m-dz-login')[0],
        vaptcha = form.ele('.vaptcha_container')[0],
        loginBtn = form.ele('.login-btn')[0],
        inputs = form.ele('input');
      var validate = function () {
        var user = form.getInput('user');
        var password = form.getInput('password');
        if (user.val() && password.val().length > 5) {
          loginBtn.removeAttribute('disabled');
        } else {
          loginBtn.setAttribute('disabled', 'disabled');
        }
      }
      var _vaptcha;
      this.options.login_captcha && (_vaptcha = self.initVaptcha({
        scene: '01',
        form: form,
        element: vaptcha,
        success: validate
      }));
      self.formValidate([
        {
          name: 'user', validator: function (value, el) {
            validate();
            value ? el.removeClass('error') : el.addClass('error');
          }
        },
        {
          name: 'password', validator: function (value, el) {
            validate();
            6 > value.length ? el.addClass('error') : el.removeClass('error')
          }
        }
      ], form)
      loginBtn.addEvent('click', function () {
        self.ajax({
          url: '/plugin.php?id=phone_auth&mod=logging&action=login&loginsubmit=yes',
          type: 'POST',
          data: self.getFormData(form),
          success: function (data) {
            window.location.href = self.options.site_url + '/forum.php?mobile=yes';
          },
          error: function (data) {
            if (['user', 'password', 'vaptcha'].indexOf(data.error_pos) >= 0) {
              form.getInput(data.error_pos) && form.getInput(data.error_pos).addClass('error');
              self.showMsg(data.msg);
            }
            if (data.error_pos === 'bind_phone') {
              self.router.redirect('bindphone');
            }
            self.options.login_captcha && _vaptcha.refresh();
            loginBtn.setAttribute('disabled', 'disabled');
          }
        })
      })
    },
    smslogin_loaded: false,
    run_sms_login: function() {
      if (this.smslogin_loaded) return;
      this.smslogin_loaded = true;
      var self = this,
        form = ele('.m-dz-sms-login')[0],
        vaptcha = form.ele('.vaptcha_container')[0],
        sendCodeBtn = form.ele('.dz-btn-code')[0],
        loginBtn = form.ele('.login-btn')[0],
        inputs = form.ele('input');
      var validate = function() {
        var data = self.getFormData(form);
        if (self.isPhone(data.phone) && /^\d{6}$/.test(data.code)) {
          loginBtn.removeAttribute('disabled');
        } else {
          loginBtn.setAttribute('disabled', 'disabled');
        }
      }
      var _vaptcha = self.initVaptcha({
        scene: '01',
        form: form,
        element: vaptcha,
        success: function () {
          validate();
          sendCodeBtn.click();
          form.ele('.send-code-group')[0].removeClass('none');
        }
      });
      form.getInput('phone').addEvent('input', function (e) {
        var it = e.target;
        !Number(it.value) && (it.value = parseInt(it.value) ? parseInt(it.value) : '');
      })
      var isSending = false;
      sendCodeBtn.addEvent('click', function () {
        if (isSending) return;
        validate();
        isSending = true;
        self.ajax({
          url: '/plugin.php?id=phone_auth&action=sendlogincode',
          type: 'POST',
          data: {
            'phone': form.getInput('phone').value,
            'vaptcha_token': form.getInput('vaptcha_token').value,
            'vaptcha_challenge': form.getInput('vaptcha_challenge').value
          },
          success: function (data) {
            isSending = false;
            self.showMsg(data.msg, true);
            self.buttonCountDown(sendCodeBtn);
          },
          error: function (data) {
            isSending = false;
            if (data.error_pos === 'vaptcha') {
              _vaptcha.refresh();
            }
            if (data.error_pos === 'phone') {
              form.getInput('phone').addClass('error')
            }
            if (data.status === 301) {
              self.buttonCountDown(sendCodeBtn, data.msg);
            } else {
              self.showMsg(data.msg);
            }
          }
        })
      })
      inputs.call('addEvent', 'input', validate)
      inputs.call('addEvent', 'blur', validate)
      inputs.call('addEvent', 'focus', function (e) {
        e.target.removeClass('error');
      })
      loginBtn.addEvent('click', function () {
        self.ajax({
          url: '/plugin.php?id=phone_auth&mod=logging&action=smslogin&loginsubmit=yes',
          type: 'POST',
          data: self.getFormData(form),
          success: function (data) {
            window.location.href = self.options.site_url + '/forum.php?mobile=yes';
          },
          error: function (data) {
            if (['phone', 'code'].indexOf(data.error_pos) >= 0) {
              form.getInput(data.error_pos) && form.getInput(data.error_pos).addClass('error');
              self.showMsg(data.msg);
            }
            data.error_pos == 'vaptcha' && _vaptcha.refresh();
            loginBtn.setAttribute('disabled', 'disabled');
          }
        })
      })
    },
    forgetpwd_loaded: false,
    run_forgetpwd: function () {
      var self = this,
        form = ele('.m-dz-forgetword')[0],
        inputs = form.ele('input'),
        sendCodeBtn = form.ele('.dz-btn-code')[0];
      if (this.forgetpwd_loaded) {
        inputs.call('val', '');
        return;
      }
      this.forgetpwd_loaded = true;
      var validate = function () {
        self.isPhone(form.getInput('phone').value) && form.getInput('phone').removeClass('error');
        /^\d{6}$/.test(form.getInput('code').value) && form.getInput('code').removeClass('error');
        if (self.isPhone(form.getInput('phone').value) && /^\d{6}$/.test(form.getInput('code').value)) {
          form.ele('.next-step')[0].removeAttribute('disabled');
        } else {
          form.ele('.next-step')[0].setAttribute('disabled', 'disabled');
        }
      }
      form.getInput('phone').addEvent('input', function (e) {
        var it = e.target;
        !Number(it.value) && (it.value = parseInt(it.value) ? parseInt(it.value) : '');
      })
      var _vaptcha = self.initVaptcha({
        element: form.ele('.vaptcha_container')[0],
        form: form,
        success: function () {
          validate();
          sendCodeBtn.click();
          form.ele('.send-code-group')[0].removeClass('none');
        }
      })
      var isSending = false;
      sendCodeBtn.addEvent('click', function () {
        if (isSending) return;
        validate();
        isSending = true;
        self.ajax({
          url: '/plugin.php?id=phone_auth&action=sendcode',
          type: 'POST',
          data: {
            'phone': form.getInput('phone').value,
            'vaptcha_token': form.getInput('vaptcha_token').value,
            'vaptcha_challenge': form.getInput('vaptcha_challenge').value
          },
          success: function (data) {
            isSending = false;
            self.showMsg(data.msg, true);
            self.buttonCountDown(sendCodeBtn);
          },
          error: function (data) {
            isSending = false;
            if (data.error_pos === 'vaptcha') {
              _vaptcha.refresh();
            }
            if (data.error_pos === 'phone') {
              form.getInput('phone').addClass('error')
            }
            if (data.status === 301) {
              self.buttonCountDown(sendCodeBtn, data.msg);
            } else {
              self.showMsg(data.msg);
            }
          }
        })
      })
      inputs.call('addEvent', 'input', validate)
      inputs.call('addEvent', 'blur', validate)
      inputs.call('addEvent', 'focus', function (e) {
        e.target.removeClass('error');
      })
      form.ele('.next-step')[0].addEvent('click', function () {
        self.ajax({
          url: '/plugin.php?id=phone_auth&action=verifyCode',
          type: 'POST',
          data: {
            'phone': form.getInput('phone').value,
            'code': form.getInput('code').value
          },
          success: function (data) {
            form.addClass('none');
            self.router.redirect('resetpwd');
          },
          error: function (data) {
            form.ele('.next-step')[0].setAttribute('disabled', 'disabled');
            if (['phone', 'code'].indexOf(data.error_pos) >= 0) {
              form.getInput(data.error_pos).addClass('error');
            }
            self.showMsg(data.msg);
          }
        })
      })
    },
    resetpwd_loaded: false,
    run_resetpwd: function () {
      var self = this;
      form = ele('.m-dz-resetpwd')[0],
        inputs = form.ele('input'),
        oPass = form.getInput('new_password');
      self.passwordLevel(form.getInput('new_password'), form.ele('.pw-strength')[0]);
      if (this.resetpwd_loaded) {
        inputs.call('val', '');
        return;
      }
      this.resetpwd_loaded = true;
      inputs.call('addEvent', 'input', function () {
        if (form.getInput('new_password').value == form.getInput('verify_password').value &&
          form.getInput('new_password').value.length > 5 && form.getInput('new_password').value.length < 21) {
          form.ele('.submit-btn')[0].removeAttribute('disabled');
        } else {
          form.ele('.submit-btn')[0].setAttribute('disabled', 'disabled');
        }
      })
      form.getInput('verify_password').addEvent('blur', function () {
        if (form.getInput('new_password').value != form.getInput('verify_password').value) {
          self.showMsg(self.options.lang.password_not_match);
        }
      })
      form.ele('.submit-btn')[0].addEvent('click', function () {
        self.ajax({
          url: '/plugin.php?id=phone_auth&action=resetPassword',
          type: 'POST',
          data: {
            'new_password': form.getInput('new_password').value
          },
          success: function (data) {
            self.showMsg(data.msg, true);
            setTimeout(function () {
              self.router.redirect('login');
            }, 1000)
          },
          error: function (data) {
            self.showMsg(data.msg);
          }
        })
      })
    },
    register_loaded: false,
    run_register: function () {
      if (this.register_loaded) return;
      this.register_loaded = true;
      var self = this,
        options = self.options.register,
        inputsValidate = {
          username: false,
          email: options.has_email == 0,
          password: false,
          phone: false,
          vaptcha: false,
          code: false
        },
        form = ele('.m-dz-register')[0],
        sendCodeBtn = form.ele('.dz-btn-code')[0];
      function formValidate() {
        var isTrue = inputsValidate.username && inputsValidate.email && inputsValidate.password && inputsValidate.phone;
        (isTrue && inputsValidate.code) ?
          ele('#register_btn').removeAttribute('disabled') : ele('#register_btn').setAttribute('disabled', 'disabled');
        return isTrue;
      }
      if (this.options.enable_inter) {
        this.initCountryCode(form);
        form.getInput('country_code').addEvent('input', function (e) {
          var it = e.target;
          !Number(it.value) && (it.value = parseInt(it.value) ? parseInt(it.value) : '');
          inputsValidate.phone = self.isPhone(it.value);
        })
      }
      var _vaptcha = self.initVaptcha({
        element: form.ele('.vaptcha_container')[0],
        form: form,
        success: function () {
          inputsValidate.vaptcha = true;
          form.ele('.dz-code-group')[0].removeClass('none');
          sendCodeBtn.target('click');
          formValidate();
        }
      })
      self.passwordLevel(form.getInput(options.password), form.ele('.pw-strength')[0]);
      form.getInput(options.password).addEvent('input', function (e) {
        var it = e.target;
        inputsValidate.password = it.value.length >= 6 && it.value.length <= 20;
      })
      form.getInput(options.password).addEvent('focus', function (e) {
        form.ele('.pw-strength')[0].removeClass('error');
      })
      form.getInput(options.password).addEvent('blur', function (e) {
        var it = e.target;
        if (it.value.length < 6 || it.value.length > 20) {
          inputsValidate.password = false;
          form.ele('.pw-strength')[0].addClass('error');
        } else {
          inputsValidate.password = true;
        }
      })
      form.getInput(options.username).addEvent('input', function (e) {
        var it = e.target;
        it.value = it.value.trim();
        inputsValidate.username = (it.value.length < 3 || it.value.length > 15) ? false : true;
      })
      form.getInput(options.username).addEvent('blur', function (e) {
        var it = e.target;
        if (!inputsValidate.username) {
          inputsValidate.username = false;
          it.addClass('error')
        } else {

        }
      })
      if (options.has_email) {
        form.getInput(options.email).addEvent('input', function (e) {
          var it = e.target;
          it.value = it.value.trim();
          inputsValidate.email = /^(\w)+(\.\w+)*@(\w)+((\.\w{2,3}){1,3})$/.test(it.value);
        })
        form.getInput(options.email).addEvent('blur', function (e) {
          var it = e.target;
          if (!inputsValidate.email) {
            it.addClass('error');
          }
        })
      }
      form.getInput('phone').addEvent('input', function (e) {
        var it = e.target;
        !Number(it.value) && (it.value = parseInt(it.value) ? parseInt(it.value) : '');
        inputsValidate.phone = self.isPhone(it.value);
      })
      form.getInput('phone').addEvent('blur', function (e) {
        var it = e.target;
        it.target('input');
        if (!inputsValidate.phone) {
          it.addClass('error');
          self.showMsg('&#35831;&#36755;&#20837;&#27491;&#30830;&#30340;&#25163;&#26426;&#21495;');
        }
      })
      form.getInput('code').addEvent('input', function (e) {
        var it = e.target;
        it.value = it.value.trim();
        inputsValidate.code = /^\d{6}$/.test(it.value)
      })
      form.getInput('code').addEvent('blur', function (e) {
        var it = e.target;
        it.target('input');
        !inputsValidate.code && it.addClass('error');
      })
      var isSending = false;
      sendCodeBtn.addEvent('click', function () {
        form.getInput('phone').target('blur');
        if (!inputsValidate.phone || isSending) { return false; }
        isSending = true;
        self.ajax({
          url: '/plugin.php?id=phone_auth&action=sendRegisterCode',
          type: 'POST',
          data: {
            'phone': form.getInput('phone').value,
            'country_code': form.getInput('country_code').value.trim(),
            'vaptcha_token': form.getInput('vaptcha_token').value,
            'vaptcha_challenge': form.getInput('vaptcha_challenge').value
          },
          success: function (data) {
            isSending = false;
            self.buttonCountDown(sendCodeBtn);
          },
          error: function (data) {
            isSending = false;
            if (data.error_pos === 'vaptcha') {
              _vaptcha.refresh();
            }
            if (['phone', 'code'].indexOf(data.error_pos) >= 0) {
              form.getInput(data.error_pos).addClass('error')
            }
            if (data.status === 301) {
              self.buttonCountDown(sendCodeBtn, data.msg);
            } else {
              self.showMsg(data.msg);
            }
          }
        })
      })
      ele('#register_btn').addEvent('click', function () {
        var data = self.getFormData(form);
        data.agreebbrule = options.agreebbrule;
        self.ajax({
          url: '/plugin.php?id=phone_auth&action=register',
          data: data,
          type: 'POST',
          success: function (data) {
            if (data.status === 200) {
              location.href = self.options.site_url + '/forum.php?mobile=yes';
            }
          },
          error: function (data) {
            var username = form.getInput(options.username);
            var email = form.getInput(options.email);
            self.showMsg(data.msg)
            if (['username', 'email'].indexOf(data.error_pos) >= 0) {
              form.getInput(options[data.error_pos]).addClass('error');
            }
          }
        })
      })
      form.ele('input').call('addEvent', 'input', formValidate);
      form.ele('input').call('addEvent', 'focus', function (e) {
        e.target.removeClass('error');
      });
    },
    bindphone_loaded: false,
    run_bindphone: function () {
      var self = this,
        form = ele('.m-dz-bind-phone')[0],
        inputs = form.ele('input'),
        sendCodeBtn = form.ele('.dz-btn-code')[0];;
      if (this.bindphone_loaded) {
        inputs.call('val', '');
        return;
      }
      this.bindphone_loaded = true;
      var validate = function () {
        self.isPhone(form.getInput('phone').value) && form.getInput('phone').removeClass('error');
        /^\d{6}$/.test(form.getInput('code').value) && form.getInput('code').removeClass('error');
        if (self.isPhone(form.getInput('phone').value) && /^\d{6}$/.test(form.getInput('code').value)) {
          form.ele('.next-step')[0].removeAttribute('disabled');
        } else {
          form.ele('.next-step')[0].setAttribute('disabled', 'disabled');
        }
      }
      if (this.options.enable_inter) {
        this.initCountryCode(form);
        form.getInput('country_code').addEvent('input', function (e) {
          var it = e.target;
          !Number(it.value) && (it.value = parseInt(it.value) ? parseInt(it.value) : '');
          inputsValidate.phone = self.isPhone(it.value);
        })
      }
      var _vaptcha = self.initVaptcha({
        element: form.ele('.vaptcha_container')[0],
        form: form,
        success: function () {
          validate();
          sendCodeBtn.click();
          form.ele('.send-code-group')[0].removeClass('none');
        }
      })
      var isSending = false;
      sendCodeBtn.addEvent('click', function () {
        if (isSending) return;
        validate();
        isSending = true;
        self.ajax({
          url: '/plugin.php?id=phone_auth&action=bindphonecode',
          type: 'POST',
          data: {
            'phone': form.getInput('phone').value,
            'country_code': form.getInput('country_code').value.trim(),
            'vaptcha_token': form.getInput('vaptcha_token').value,
            'vaptcha_challenge': form.getInput('vaptcha_challenge').value
          },
          success: function (data) {
            isSending = false;
            self.showMsg(data.msg, true);
            self.buttonCountDown(sendCodeBtn);
          },
          error: function (data) {
            isSending = false;
            if (data.error_pos === 'vaptcha') {
              _vaptcha.refresh();
            }
            if (data.error_pos === 'phone') {
              form.getInput('phone').addClass('error')
            }
            if (data.status === 301) {
              self.buttonCountDown(sendCodeBtn, data.msg);
            } else {
              self.showMsg(data.msg);
            }
          }
        })
      })
      inputs.call('addEvent', 'input', validate)
      inputs.call('addEvent', 'focus', function (e) {
        e.target.removeClass('error');
      })
      form.ele('.next-step')[0].addEvent('click', function () {
        self.ajax({
          url: '/plugin.php?id=phone_auth&action=bindphone',
          type: 'POST',
          data: {
            'phone': form.getInput('phone').value,
            'code': form.getInput('code').value
          },
          success: function (data) {
            form.addClass('none');
            window.location.href = self.options.site_url + '/forum.php?mobile=yes';
          },
          error: function (data) {
            form.ele('.next-step')[0].setAttribute('disabled', 'disabled');
            if (['phone', 'code'].indexOf(data.error_pos) >= 0) {
              form.getInput(data.error_pos).addClass('error');
            }
            self.showMsg(data.msg);
          }
        })
      })
    }
  }
  window.helper = Helper;
})(document.getElementById('app'))