var app = getApp();

Page({
  data: {
    cartList: [],
    cartTotal: '0.00',
    cartCount: 0,
    orderType: 1,
    tableId: 0,
    tableNo: '',
    remark: '',
    couponList: [],
    selectedCouponId: 0,
    couponDiscount: 0,
    payAmount: '0.00',
    contactName: '',
    contactPhone: '',
    address: '',
    deliveryFee: 0
  },

  onLoad: function (options) {
    var orderType = options.orderType ? parseInt(options.orderType) : 1;
    this.setData({
      cartList: app.globalData.cart,
      tableId: app.globalData.tableId,
      tableNo: app.globalData.tableNo,
      orderType: orderType,
      contactName: options.contactName || '',
      contactPhone: options.contactPhone || '',
      address: options.address || '',
      deliveryFee: orderType === 2 ? 5 : 0
    });
    this.calcTotal();
    this.loadCoupons();
  },

  calcTotal: function () {
    var info = app.getCartTotal();
    var discount = this.data.couponDiscount;
    var delivery = this.data.deliveryFee;
    var payAmount = Math.max(0, parseFloat(info.total) - discount + delivery);
    this.setData({
      cartTotal: info.total,
      cartCount: info.count,
      payAmount: payAmount.toFixed(2)
    });
  },

  loadCoupons: function () {
    var that = this;
    var info = app.getCartTotal();
    app.request({ url: '/coupon/available?amount=' + info.total }).then(function (res) {
      if (res.code === 0) {
        that.setData({ couponList: res.data });
      }
    });
  },

  changeQty: function (e) {
    var key = e.currentTarget.dataset.key;
    var type = e.currentTarget.dataset.type;
    var cart = app.globalData.cart;
    var idx = app.globalData.cartMap[key];
    if (idx === undefined) return;
    var item = cart[idx];
    var newQty = type === 'plus' ? item.quantity + 1 : item.quantity - 1;
    app.updateCartItem(key, newQty);
    this.setData({ cartList: app.globalData.cart });
    this.calcTotal();
  },

  onRemarkInput: function (e) {
    this.setData({ remark: e.detail.value });
  },

  switchOrderType: function (e) {
    var type = e.currentTarget.dataset.type;
    this.setData({ orderType: type });
    if (type == 2) {
      this.setData({ deliveryFee: 5 });
    } else {
      this.setData({ deliveryFee: 0 });
    }
    this.calcTotal();
  },

  selectCoupon: function (e) {
    var coupon = e.currentTarget.dataset.coupon;
    var discount = 0;
    var total = parseFloat(this.data.cartTotal);
    if (coupon.type == 1) {
      discount = parseFloat(coupon.value);
    } else if (coupon.type == 2) {
      discount = total * (1 - parseFloat(coupon.value));
    } else if (coupon.type == 3) {
      discount = parseFloat(coupon.value);
    }
    if (discount > total) discount = total;
    this.setData({
      selectedCouponId: coupon.id,
      couponDiscount: discount
    });
    this.calcTotal();
  },

  onContactNameInput: function (e) { this.setData({ contactName: e.detail.value }); },
  onContactPhoneInput: function (e) { this.setData({ contactPhone: e.detail.value }); },
  onAddressInput: function (e) { this.setData({ address: e.detail.value }); },

  submitOrder: function () {
    if (this.data.cartCount === 0) {
      wx.showToast({ title: '购物车为空', icon: 'none' });
      return;
    }
    if (this.data.orderType === 2 && (!this.data.contactName || !this.data.contactPhone || !this.data.address)) {
      wx.showToast({ title: '请填写配送信息', icon: 'none' });
      return;
    }
    if (this.data.orderType === 3 && (!this.data.contactName || !this.data.contactPhone)) {
      wx.showToast({ title: '请填写联系信息', icon: 'none' });
      return;
    }

    var that = this;
    var items = this.data.cartList.map(function (item) {
      return {
        dish_id: item.dishId,
        quantity: item.quantity,
        spec_info: item.specInfo,
        addon_info: item.addonInfo,
        remark: item.remark
      };
    });

    var orderData = {
      table_id: this.data.orderType === 1 ? this.data.tableId : 0,
      table_no: this.data.orderType === 1 ? this.data.tableNo : '',
      order_type: this.data.orderType,
      remark: this.data.remark,
      items: items,
      coupon_id: this.data.selectedCouponId,
      contact_name: this.data.contactName,
      contact_phone: this.data.contactPhone,
      address: this.data.address,
      delivery_fee: this.data.deliveryFee
    };

    app.request({
      url: '/order/create',
      method: 'POST',
      data: orderData
    }).then(function (res) {
      if (res.code === 0) {
        app.clearCart();
        wx.showModal({
          title: '下单成功',
          content: '订单金额：¥' + res.data.pay_amount,
          confirmText: '去支付',
          cancelText: '稍后付',
          success: function (modalRes) {
            if (modalRes.confirm) {
              that.payOrder(res.data.order_id);
            } else {
              wx.redirectTo({ url: '/pages/order-detail/order-detail?id=' + res.data.order_id });
            }
          }
        });
      } else {
        wx.showToast({ title: res.msg, icon: 'none' });
      }
    });
  },

  payOrder: function (orderId) {
    var that = this;
    app.request({
      url: '/order/pay',
      method: 'POST',
      data: { order_id: orderId, pay_type: 1, pay_mode: 0 }
    }).then(function (res) {
      if (res.code === 0 && res.data) {
        wx.requestPayment({
          timeStamp: res.data.timeStamp,
          nonceStr: res.data.nonceStr,
          package: res.data.package,
          signType: res.data.signType,
          paySign: res.data.paySign,
          success: function () {
            wx.redirectTo({ url: '/pages/order-detail/order-detail?id=' + orderId });
          },
          fail: function () {
            wx.redirectTo({ url: '/pages/order-detail/order-detail?id=' + orderId });
          }
        });
      } else {
        wx.showToast({ title: '支付发起失败', icon: 'none' });
      }
    });
  },

  payLater: function () {
    this.setData({ payType: 2 });
    this.submitOrder();
  }
});
