var app = getApp();

Page({
  data: {
    tabs: ['未使用', '已使用', '已过期'],
    activeTab: 0,
    couponList: []
  },

  onLoad: function () {
    this.loadCoupons();
  },

  switchTab: function (e) {
    var idx = e.currentTarget.dataset.index;
    this.setData({ activeTab: idx });
    this.loadCoupons();
  },

  loadCoupons: function () {
    var that = this;
    var status = this.data.activeTab;
    app.request({ url: '/coupon/list?status=' + status }).then(function (res) {
      if (res.code === 0) {
        that.setData({ couponList: res.data });
      }
    });
  },

  receiveCoupon: function (e) {
    var id = e.currentTarget.dataset.id;
    var that = this;
    app.request({
      url: '/coupon/receive',
      method: 'POST',
      data: { coupon_id: id }
    }).then(function (res) {
      if (res.code === 0) {
        wx.showToast({ title: '领取成功', icon: 'success' });
      } else {
        wx.showToast({ title: res.msg, icon: 'none' });
      }
    });
  }
});
