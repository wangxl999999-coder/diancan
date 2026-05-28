var app = getApp();

Page({
  data: {
    orderType: 2,
    categories: [],
    dishes: [],
    activeCategory: 0,
    cartCount: 0,
    cartTotal: '0.00',
    contactName: '',
    contactPhone: '',
    address: ''
  },

  onLoad: function () {
    this.loadCategories();
  },

  onShow: function () {
    this.updateCartInfo();
  },

  switchType: function (e) {
    this.setData({ orderType: e.currentTarget.dataset.type });
  },

  loadCategories: function () {
    var that = this;
    app.request({ url: '/category/list' }).then(function (res) {
      if (res.code === 0) {
        that.setData({ categories: res.data });
        if (res.data.length > 0) that.loadDishes(res.data[0].id, 0);
      }
    });
  },

  loadDishes: function (categoryId, idx) {
    var that = this;
    app.request({ url: '/dish/list?category_id=' + categoryId + '&page_size=50' }).then(function (res) {
      if (res.code === 0) that.setData({ dishes: res.data.list, activeCategory: idx });
    });
  },

  switchCategory: function (e) {
    var idx = e.currentTarget.dataset.index;
    this.loadDishes(this.data.categories[idx].id, idx);
  },

  quickAdd: function (e) {
    var dish = e.currentTarget.dataset.dish;
    var cartKey = 'dish_' + dish.id + '_takeout_default';
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
    wx.showToast({ title: '已加入', icon: 'success', duration: 600 });
  },

  updateCartInfo: function () {
    var info = app.getCartTotal();
    this.setData({ cartCount: info.count, cartTotal: info.total });
  },

  goDetail: function (e) {
    wx.navigateTo({ url: '/pages/dish-detail/dish-detail?id=' + e.currentTarget.dataset.id });
  },

  onContactNameInput: function (e) {
    this.setData({ contactName: e.detail.value });
  },

  onContactPhoneInput: function (e) {
    this.setData({ contactPhone: e.detail.value });
  },

  onAddressInput: function (e) {
    this.setData({ address: e.detail.value });
  },

  submitOrder: function () {
    if (this.data.cartCount === 0) {
      wx.showToast({ title: '请选择菜品', icon: 'none' }); return;
    }
    if (!this.data.contactName || !this.data.contactPhone) {
      wx.showToast({ title: '请填写联系信息', icon: 'none' }); return;
    }
    if (this.data.orderType === 2 && !this.data.address) {
      wx.showToast({ title: '请填写配送地址', icon: 'none' }); return;
    }
    wx.navigateTo({ url: '/pages/cart/cart?orderType=' + this.data.orderType + '&contactName=' + encodeURIComponent(this.data.contactName) + '&contactPhone=' + this.data.contactPhone + '&address=' + encodeURIComponent(this.data.address) });
  }
});
