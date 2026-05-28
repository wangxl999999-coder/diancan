var app = getApp();

Page({
  data: {
    pointsList: [],
    points: 0
  },

  onLoad: function () {
    this.loadPoints();
  },

  loadPoints: function () {
    var that = this;
    app.request({ url: '/member/info' }).then(function (res) {
      if (res.code === 0) that.setData({ points: res.data.points });
    });
    app.request({ url: '/member/points?page_size=50' }).then(function (res) {
      if (res.code === 0) that.setData({ pointsList: res.data.list });
    });
  }
});
