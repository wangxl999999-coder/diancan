var app = getApp();

Page({
  data: {
    orderId: 0,
    rating: 5,
    content: '',
    images: []
  },

  onLoad: function (options) {
    this.setData({ orderId: options.order_id });
  },

  onRatingChange: function (e) {
    this.setData({ rating: e.detail });
  },

  onContentInput: function (e) {
    this.setData({ content: e.detail.value });
  },

  chooseImage: function () {
    var that = this;
    wx.chooseImage({
      count: 3 - that.data.images.length,
      success: function (res) {
        that.setData({ images: that.data.images.concat(res.tempFilePaths) });
      }
    });
  },

  removeImage: function (e) {
    var idx = e.currentTarget.dataset.index;
    var images = this.data.images.slice();
    images.splice(idx, 1);
    this.setData({ images: images });
  },

  submitReview: function () {
    var that = this;
    app.request({
      url: '/review/create',
      method: 'POST',
      data: {
        order_id: this.data.orderId,
        rating: this.data.rating,
        content: this.data.content,
        images: this.data.images.join(',')
      }
    }).then(function (res) {
      if (res.code === 0) {
        wx.showToast({ title: '评价成功', icon: 'success' });
        setTimeout(function () { wx.navigateBack(); }, 800);
      } else {
        wx.showToast({ title: res.msg, icon: 'none' });
      }
    });
  }
});
