App({
  globalData: {
    baseUrl: 'http://localhost/api',
    userInfo: null,
    token: '',
    tableId: 0,
    tableNo: '',
    currentOrderId: 0,
    cart: [],
    cartMap: {}
  },

  onLaunch: function () {
    var token = wx.getStorageSync('token');
    if (token) {
      this.globalData.token = token;
      this.checkLogin();
    } else {
      this.navigateToLogin();
    }
    var cart = wx.getStorageSync('cart');
    if (cart) {
      this.globalData.cart = cart;
      this._buildCartMap();
    }
    var tableInfo = wx.getStorageSync('tableInfo');
    if (tableInfo) {
      this.globalData.tableId = tableInfo.tableId;
      this.globalData.tableNo = tableInfo.tableNo;
    }
  },

  checkLogin: function () {
    var that = this;
    this.request({
      url: '/member/info'
    }).then(function (res) {
      if (res.code === 0) {
        that.globalData.userInfo = res.data;
      } else {
        that.navigateToLogin();
      }
    }).catch(function () {
      that.navigateToLogin();
    });
  },

  navigateToLogin: function () {
    var pages = getCurrentPages();
    var currentPage = pages.length > 0 ? pages[pages.length - 1].route : '';
    if (currentPage !== 'pages/login/login') {
      wx.redirectTo({ url: '/pages/login/login' });
    }
  },

  isLoggedIn: function () {
    return !!this.globalData.token && !!this.globalData.userInfo;
  },

  _buildCartMap: function () {
    var map = {};
    this.globalData.cart.forEach(function (item, idx) {
      var key = item.cartKey;
      map[key] = idx;
    });
    this.globalData.cartMap = map;
  },

  request: function (options) {
    var that = this;
    var header = { 'Content-Type': 'application/json' };
    if (this.globalData.token) {
      header['Authorization'] = 'Bearer ' + this.globalData.token;
    }
    return new Promise(function (resolve, reject) {
      wx.request({
        url: that.globalData.baseUrl + options.url,
        method: options.method || 'GET',
        data: options.data || {},
        header: header,
        success: function (res) {
          if (res.data.code === 401) {
            that.globalData.token = '';
            wx.removeStorageSync('token');
            wx.navigateTo({ url: '/pages/login/login' });
            reject(res.data);
            return;
          }
          resolve(res.data);
        },
        fail: function (err) {
          wx.showToast({ title: '网络错误', icon: 'none' });
          reject(err);
        }
      });
    });
  },

  addToCart: function (item) {
    var key = item.cartKey;
    var cartMap = this.globalData.cartMap;
    var cart = this.globalData.cart;
    if (cartMap.hasOwnProperty(key)) {
      cart[cartMap[key]].quantity += item.quantity;
    } else {
      cart.push(item);
      this._buildCartMap();
    }
    this._saveCart();
  },

  updateCartItem: function (key, quantity) {
    var cartMap = this.globalData.cartMap;
    var cart = this.globalData.cart;
    if (cartMap.hasOwnProperty(key)) {
      if (quantity <= 0) {
        cart.splice(cartMap[key], 1);
        this._buildCartMap();
      } else {
        cart[cartMap[key]].quantity = quantity;
      }
      this._saveCart();
    }
  },

  clearCart: function () {
    this.globalData.cart = [];
    this.globalData.cartMap = {};
    this._saveCart();
  },

  getCartTotal: function () {
    var total = 0;
    var count = 0;
    this.globalData.cart.forEach(function (item) {
      total += item.totalPrice * item.quantity;
      count += item.quantity;
    });
    return { total: total.toFixed(2), count: count };
  },

  _saveCart: function () {
    wx.setStorageSync('cart', this.globalData.cart);
  },

  setTableInfo: function (tableId, tableNo) {
    this.globalData.tableId = tableId;
    this.globalData.tableNo = tableNo;
    wx.setStorageSync('tableInfo', { tableId: tableId, tableNo: tableNo });
  }
});
