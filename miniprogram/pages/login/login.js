var app = getApp();

Page({
  data: {
    phone: '',
    code: '',
    codeText: '获取验证码',
    counting: false
  },

  wxLogin: function () {
    var that = this;
    wx.login({
      success: function (res) {
        if (res.code) {
          app.request({
            url: '/member/wx-login',
            method: 'POST',
            data: { code: res.code }
          }).then(function (result) {
            if (result.code === 0) {
              app.globalData.token = result.data.token;
              app.globalData.userInfo = result.data.member;
              wx.setStorageSync('token', result.data.token);
              wx.showToast({ title: '登录成功', icon: 'success' });
              setTimeout(function () { that.redirectAfterLogin(); }, 800);
            } else {
              wx.showToast({ title: result.msg, icon: 'none' });
            }
          });
        }
      }
    });
  },

  onPhoneInput: function (e) { this.setData({ phone: e.detail.value }); },
  onCodeInput: function (e) { this.setData({ code: e.detail.value }); },

  sendCode: function () {
    if (this.data.counting) return;
    if (!this.data.phone || this.data.phone.length !== 11) {
      wx.showToast({ title: '请输入正确手机号', icon: 'none' });
      return;
    }
    var that = this;
    var seconds = 60;
    that.setData({ counting: true, codeText: seconds + 's' });
    var timer = setInterval(function () {
      seconds--;
      if (seconds <= 0) {
        clearInterval(timer);
        that.setData({ counting: false, codeText: '获取验证码' });
      } else {
        that.setData({ codeText: seconds + 's' });
      }
    }, 1000);
    wx.showToast({ title: '验证码已发送', icon: 'success' });
  },

  phoneLogin: function () {
    var that = this;
    if (!this.data.phone || !this.data.code) {
      wx.showToast({ title: '请填写完整信息', icon: 'none' });
      return;
    }
    app.request({
      url: '/member/phone-login',
      method: 'POST',
      data: { phone: this.data.phone, code: this.data.code }
    }).then(function (result) {
      if (result.code === 0) {
        app.globalData.token = result.data.token;
        app.globalData.userInfo = result.data.member;
        wx.setStorageSync('token', result.data.token);
        wx.showToast({ title: '登录成功', icon: 'success' });
        setTimeout(function () { that.redirectAfterLogin(); }, 800);
      } else {
        wx.showToast({ title: result.msg, icon: 'none' });
      }
    });
  },

  redirectAfterLogin: function () {
    var pages = getCurrentPages();
    if (pages.length > 1) {
      wx.navigateBack();
    } else {
      wx.switchTab({ url: '/pages/menu/menu' });
    }
  }
});
