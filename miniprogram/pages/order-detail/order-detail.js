var app = getApp();

Page({
  data: {
    order: null,
    statusMap: ['待支付', '已支付', '制作中', '已完成', '已取消'],
    typeMap: ['', '堂食', '外卖', '自提']
  },

  onLoad: function (options) {
    this.loadOrder(options.id);
  },

  loadOrder: function (id) {
    var that = this;
    app.request({ url: '/order/detail/' + id }).then(function (res) {
      if (res.code === 0) {
        that.setData({ order: res.data });
      }
    });
  },

  payNow: function () {
    var that = this;
    var order = this.data.order;
    app.request({
      url: '/order/pay',
      method: 'POST',
      data: { order_id: order.id, pay_type: 1, pay_mode: 0 }
    }).then(function (res) {
      if (res.code === 0 && res.data) {
        wx.requestPayment({
          timeStamp: res.data.timeStamp,
          nonceStr: res.data.nonceStr,
          package: res.data.package,
          signType: res.data.signType,
          paySign: res.data.paySign,
          success: function () { that.loadOrder(order.id); },
          fail: function () {}
        });
      }
    });
  },

  aaPay: function () {
    var that = this;
    var order = this.data.order;
    wx.showModal({
      title: 'AA付款',
      content: '您的份额为¥' + (order.pay_amount / (order.sub_orders.length || 1)).toFixed(2),
      success: function (res) {
        if (res.confirm) {
          app.request({
            url: '/order/aa-pay',
            method: 'POST',
            data: { order_id: order.id, amount: order.pay_amount / (order.sub_orders.length || 1) }
          }).then(function (res2) {
            if (res2.code === 0 && res2.data) {
              wx.requestPayment({
                timeStamp: res2.data.timeStamp,
                nonceStr: res2.data.nonceStr,
                package: res2.data.package,
                signType: res2.data.signType,
                paySign: res2.data.paySign,
                success: function () { that.loadOrder(order.id); }
              });
            }
          });
        }
      }
    });
  },

  confirmReceive: function () {
    var that = this;
    app.request({
      url: '/order/confirm',
      method: 'POST',
      data: { order_id: this.data.order.id }
    }).then(function (res) {
      if (res.code === 0) {
        wx.showToast({ title: '已确认收货', icon: 'success' });
        that.loadOrder(that.data.order.id);
      }
    });
  },

  goReview: function () {
    wx.navigateTo({ url: '/pages/review/review?order_id=' + this.data.order.id });
  }
});
