var app = getApp();

Page({
  data: {
    tabs: ['全部', '待支付', '进行中', '已完成'],
    activeTab: 0,
    orders: [],
    page: 1,
    hasMore: true
  },

  onLoad: function () {
    this.loadOrders();
  },

  onShow: function () {
    this.setData({ page: 1, orders: [], hasMore: true });
    this.loadOrders();
  },

  switchTab: function (e) {
    var idx = e.currentTarget.dataset.index;
    this.setData({ activeTab: idx, page: 1, orders: [], hasMore: true });
    this.loadOrders();
  },

  loadOrders: function () {
    var that = this;
    var status = '';
    if (this.data.activeTab === 1) status = 0;
    else if (this.data.activeTab === 2) status = '1,2';
    else if (this.data.activeTab === 3) status = 3;

    app.request({
      url: '/order/list?page=' + this.data.page + '&page_size=10' + (status ? '&status=' + status : '')
    }).then(function (res) {
      if (res.code === 0) {
        var orders = that.data.page === 1 ? res.data.list : that.data.orders.concat(res.data.list);
        that.setData({
          orders: orders,
          hasMore: orders.length < res.data.total
        });
      }
    });
  },

  loadMore: function () {
    if (!this.data.hasMore) return;
    this.setData({ page: this.data.page + 1 });
    this.loadOrders();
  },

  goDetail: function (e) {
    var id = e.currentTarget.dataset.id;
    wx.navigateTo({ url: '/pages/order-detail/order-detail?id=' + id });
  },

  statusText: function (s) {
    return ['待支付', '已支付', '制作中', '已完成', '已取消'][s] || '';
  },

  orderTypeText: function (t) {
    return ['', '堂食', '外卖', '自提'][t] || '';
  }
});
