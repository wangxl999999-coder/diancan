var app = getApp();

Page({
  data: {
    categories: [],
    activeCategory: 0,
    dishes: [],
    allDishes: [],
    searchMode: false,
    keyword: '',
    priceMin: '',
    priceMax: '',
    cartCount: 0,
    cartTotal: '0.00',
    showCartPopup: false,
    cartList: [],
    tableNo: ''
  },

  onLoad: function () {
    this.loadCategories();
    var tableNo = app.globalData.tableNo;
    if (tableNo) {
      this.setData({ tableNo: tableNo });
    }
  },

  onShow: function () {
    this.updateCartInfo();
    var tableNo = app.globalData.tableNo;
    if (tableNo !== this.data.tableNo) {
      this.setData({ tableNo: tableNo });
    }
  },

  loadCategories: function () {
    var that = this;
    app.request({ url: '/category/list' }).then(function (res) {
      if (res.code === 0) {
        that.setData({ categories: res.data });
        if (res.data.length > 0) {
          that.loadDishes(res.data[0].id, 0);
        }
      }
    });
  },

  loadDishes: function (categoryId, idx) {
    var that = this;
    app.request({
      url: '/dish/list?category_id=' + categoryId + '&page_size=50'
    }).then(function (res) {
      if (res.code === 0) {
        that.setData({
          dishes: res.data.list,
          activeCategory: idx
        });
      }
    });
  },

  switchCategory: function (e) {
    var idx = e.currentTarget.dataset.index;
    var cat = this.data.categories[idx];
    if (cat) {
      this.loadDishes(cat.id, idx);
    }
  },

  onSearchFocus: function () {
    this.setData({ searchMode: true });
  },

  onSearchBlur: function () {
    if (!this.data.keyword && !this.data.priceMin && !this.data.priceMax) {
      this.setData({ searchMode: false });
    }
  },

  onSearchInput: function (e) {
    this.setData({ keyword: e.detail.value });
  },

  onPriceMinInput: function (e) {
    this.setData({ priceMin: e.detail.value });
  },

  onPriceMaxInput: function (e) {
    this.setData({ priceMax: e.detail.value });
  },

  doSearch: function () {
    var that = this;
    var params = 'page_size=50';
    if (this.data.keyword) params += '&keyword=' + encodeURIComponent(this.data.keyword);
    if (this.data.priceMin) params += '&price_min=' + this.data.priceMin;
    if (this.data.priceMax) params += '&price_max=' + this.data.priceMax;
    app.request({ url: '/dish/list?' + params }).then(function (res) {
      if (res.code === 0) {
        that.setData({
          dishes: res.data.list,
          searchMode: true
        });
      }
    });
  },

  clearSearch: function () {
    this.setData({
      keyword: '',
      priceMin: '',
      priceMax: '',
      searchMode: false
    });
    if (this.data.categories.length > 0) {
      this.loadDishes(this.data.categories[0].id, 0);
    }
  },

  goDishDetail: function (e) {
    var id = e.currentTarget.dataset.id;
    wx.navigateTo({ url: '/pages/dish-detail/dish-detail?id=' + id });
  },

  quickAdd: function (e) {
    var dish = e.currentTarget.dataset.dish;
    var cartKey = 'dish_' + dish.id + '_default';
    var item = {
      cartKey: cartKey,
      dishId: dish.id,
      dishName: dish.name,
      dishImage: dish.image,
      price: parseFloat(dish.price),
      specPrice: 0,
      addonPrice: 0,
      totalPrice: parseFloat(dish.price),
      specInfo: '',
      addonInfo: '',
      quantity: 1,
      remark: ''
    };
    app.addToCart(item);
    this.updateCartInfo();
    wx.showToast({ title: '已加入购物车', icon: 'success', duration: 800 });
  },

  updateCartInfo: function () {
    var info = app.getCartTotal();
    this.setData({
      cartCount: info.count,
      cartTotal: info.total
    });
  },

  toggleCartPopup: function () {
    if (this.data.cartCount === 0) return;
    var cart = app.globalData.cart;
    this.setData({
      showCartPopup: !this.data.showCartPopup,
      cartList: cart
    });
  },

  hideCartPopup: function () {
    this.setData({ showCartPopup: false });
  },

  changeCartQty: function (e) {
    var key = e.currentTarget.dataset.key;
    var type = e.currentTarget.dataset.type;
    var cart = app.globalData.cart;
    var idx = app.globalData.cartMap[key];
    if (idx === undefined) return;
    var item = cart[idx];
    var newQty = type === 'plus' ? item.quantity + 1 : item.quantity - 1;
    app.updateCartItem(key, newQty);
    this.updateCartInfo();
    this.setData({ cartList: app.globalData.cart });
  },

  clearCart: function () {
    var that = this;
    wx.showModal({
      title: '提示',
      content: '确定清空购物车吗？',
      success: function (res) {
        if (res.confirm) {
          app.clearCart();
          that.updateCartInfo();
          that.setData({ showCartPopup: false, cartList: [] });
        }
      }
    });
  },

  goCheckout: function () {
    if (this.data.cartCount === 0) {
      wx.showToast({ title: '请先选择菜品', icon: 'none' });
      return;
    }
    wx.navigateTo({ url: '/pages/cart/cart' });
  }
});
