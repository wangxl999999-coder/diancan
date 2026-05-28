Page({
  data: {
    tableNo: '',
    tableId: 0,
    hasOrder: false,
    orderInfo: null
  },

  onLoad: function (options) {
    if (options.scene) {
      var scene = decodeURIComponent(options.scene);
      this.parseScene(scene);
    }
    if (options.table_no) {
      this.scanResult(options.table_no);
    }
  },

  onShow: function () {
    var app = getApp();
    if (app.globalData.tableNo) {
      this.setData({
        tableNo: app.globalData.tableNo,
        tableId: app.globalData.tableId
      });
    }
  },

  parseScene: function (scene) {
    var parts = scene.split('_');
    if (parts.length >= 2 && parts[0] === 'T') {
      this.scanResult(parts[1]);
    }
  },

  scanCode: function () {
    var that = this;
    wx.scanCode({
      onlyFromCamera: true,
      scanType: ['qrCode'],
      success: function (res) {
        var result = res.result;
        var tableNo = '';
        if (result.indexOf('table_no=') > -1) {
          var match = result.match(/table_no=([^&]+)/);
          if (match) tableNo = match[1];
        } else {
          tableNo = result;
        }
        if (tableNo) {
          that.scanResult(tableNo);
        } else {
          wx.showToast({ title: '无法识别桌号', icon: 'none' });
        }
      }
    });
  },

  scanResult: function (tableNo) {
    var that = this;
    var app = getApp();
    app.request({
      url: '/table/scan?table_no=' + tableNo
    }).then(function (res) {
      if (res.code === 0) {
        var table = res.data.table;
        app.setTableInfo(table.id, table.table_no);
        that.setData({
          tableNo: table.table_no,
          tableId: table.id,
          hasOrder: !!res.data.order,
          orderInfo: res.data.order
        });
        wx.switchTab({ url: '/pages/menu/menu' });
      } else {
        wx.showToast({ title: res.msg, icon: 'none' });
      }
    });
  },

  startOrder: function () {
    wx.switchTab({ url: '/pages/menu/menu' });
  },

  goTakeout: function () {
    wx.navigateTo({ url: '/pages/takeout/takeout' });
  }
});
