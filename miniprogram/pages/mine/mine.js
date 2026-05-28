var app = getApp();

Page({
  data: {
    userInfo: null,
    isLogin: false
  },

  onShow: function () {
    if (app.globalData.token) {
      this.setData({ isLogin: true });
      this.loadUserInfo();
    } else {
      this.setData({ isLogin: false, userInfo: null });
    }
  },

  loadUserInfo: function () {
    var that = this;
    app.request({ url: '/member/info' }).then(function (res) {
      if (res.code === 0) {
        that.setData({ userInfo: res.data, isLogin: true });
      }
    });
  },

  goLogin: function () {
    wx.navigateTo({ url: '/pages/login/login' });
  },

  goPoints: function () {
    wx.navigateTo({ url: '/pages/points/points' });
  },

  goCoupon: function () {
    wx.navigateTo({ url: '/pages/coupon/coupon' });
  },

  scanCode: function () {
    wx.switchTab({ url: '/pages/index/index' });
  }
});
