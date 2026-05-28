var app = getApp();

Page({
  data: {
    dish: null,
    quantity: 1,
    selectedSpecs: {},
    selectedAddons: [],
    remark: '',
    totalPrice: 0,
    spicinessMap: ['不辣', '微辣', '中辣', '重辣']
  },

  onLoad: function (options) {
    this.loadDish(options.id);
  },

  loadDish: function (id) {
    var that = this;
    app.request({ url: '/dish/detail/' + id }).then(function (res) {
      if (res.code === 0) {
        that.setData({ dish: res.data });
        that.calcPrice();
      }
    });
  },

  selectSpecOption: function (e) {
    var groupId = e.currentTarget.dataset.group;
    var optionId = e.currentTarget.dataset.option;
    var optionName = e.currentTarget.dataset.name;
    var optionPrice = parseFloat(e.currentTarget.dataset.price);
    var specs = this.data.selectedSpecs;
    specs[groupId] = { id: optionId, name: optionName, price: optionPrice };
    this.setData({ selectedSpecs: specs });
    this.calcPrice();
  },

  toggleAddon: function (e) {
    var addon = e.currentTarget.dataset.addon;
    var addons = this.data.selectedAddons.slice();
    var idx = -1;
    for (var i = 0; i < addons.length; i++) {
      if (addons[i].id === addon.id) { idx = i; break; }
    }
    if (idx > -1) {
      addons.splice(idx, 1);
    } else {
      addons.push({ id: addon.id, name: addon.name, price: parseFloat(addon.price) });
    }
    this.setData({ selectedAddons: addons });
    this.calcPrice();
  },

  isAddonSelected: function (addonId) {
    var addons = this.data.selectedAddons;
    for (var i = 0; i < addons.length; i++) {
      if (addons[i].id === addonId) return true;
    }
    return false;
  },

  onRemarkInput: function (e) {
    this.setData({ remark: e.detail.value });
  },

  changeQuantity: function (e) {
    var type = e.currentTarget.dataset.type;
    var qty = this.data.quantity;
    if (type === 'plus') qty++;
    else if (type === 'minus' && qty > 1) qty--;
    this.setData({ quantity: qty });
    this.calcPrice();
  },

  calcPrice: function () {
    var dish = this.data.dish;
    if (!dish) return;
    var basePrice = parseFloat(dish.price);
    var specPrice = 0;
    var specs = this.data.selectedSpecs;
    Object.keys(specs).forEach(function (k) {
      specPrice += specs[k].price;
    });
    var addonPrice = 0;
    this.data.selectedAddons.forEach(function (a) {
      addonPrice += a.price;
    });
    var total = (basePrice + specPrice + addonPrice) * this.data.quantity;
    this.setData({ totalPrice: total.toFixed(2) });
  },

  addToCart: function () {
    var dish = this.data.dish;
    var specs = this.data.selectedSpecs;
    var specInfo = [];
    Object.keys(specs).forEach(function (k) {
      specInfo.push(specs[k]);
    });
    var cartKey = 'dish_' + dish.id + '_' + JSON.stringify(specInfo) + '_' + JSON.stringify(this.data.selectedAddons.map(function (a) { return a.id; }));
    var item = {
      cartKey: cartKey,
      dishId: dish.id,
      dishName: dish.name,
      dishImage: dish.image,
      price: parseFloat(dish.price),
      specPrice: 0,
      addonPrice: 0,
      totalPrice: (parseFloat(dish.price) + specInfo.reduce(function (s, i) { return s + i.price; }, 0) + this.data.selectedAddons.reduce(function (s, i) { return s + i.price; }, 0)),
      specInfo: JSON.stringify(specInfo),
      addonInfo: JSON.stringify(this.data.selectedAddons),
      quantity: this.data.quantity,
      remark: this.data.remark
    };
    app.addToCart(item);
    wx.showToast({ title: '已加入购物车', icon: 'success' });
    setTimeout(function () {
      wx.navigateBack();
    }, 800);
  }
});
