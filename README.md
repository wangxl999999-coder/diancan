# 智慧点餐系统

一款功能完整的点餐类微信小程序，支持扫码点餐、在线支付、外卖配送、会员管理等功能。

## 项目结构

```
diancan/
├── database/                    # 数据库文件
│   └── diancan.sql             # 数据库初始化脚本
├── server/                      # PHP后端管理系统
│   ├── api.php                 # API入口文件
│   ├── config.php              # 配置文件
│   ├── DB.php                  # 数据库操作类
│   ├── Auth.php                # 认证类
│   ├── Response.php            # 响应类
│   ├── Upload.php              # 上传类
│   ├── WxPay.php               # 微信支付类
│   ├── nginx.conf              # Nginx配置示例
│   ├── .htaccess               # Apache重写规则
│   ├── controller/             # 控制器目录
│   │   ├── AdminController.php
│   │   ├── CategoryController.php
│   │   ├── DishController.php
│   │   ├── TableController.php
│   │   ├── OrderController.php
│   │   ├── MemberController.php
│   │   ├── CouponController.php
│   │   ├── ReviewController.php
│   │   ├── UploadController.php
│   │   ├── WxController.php
│   │   ├── SettingController.php
│   │   └── StatsController.php
│   └── admin/                  # 管理后台
│       └── index.html          # 管理后台单页应用
└── miniprogram/                 # 微信小程序前端
    ├── app.js                  # 小程序入口
    ├── app.json                # 小程序配置
    ├── app.wxss                # 全局样式
    ├── sitemap.json
    ├── project.config.json
    ├── pages/                  # 页面目录
    │   ├── index/              # 首页（扫码入口）
    │   ├── menu/               # 菜单页
    │   ├── dish-detail/        # 菜品详情页
    │   ├── cart/               # 购物车/确认订单页
    │   ├── order/              # 订单列表页
    │   ├── order-detail/       # 订单详情页
    │   ├── mine/               # 个人中心
    │   ├── login/              # 登录页
    │   ├── coupon/             # 优惠券页
    │   ├── points/             # 积分明细页
    │   ├── review/             # 评价页
    │   └── takeout/            # 外卖/自提页
    └── images/                 # 图片资源目录
```

## 功能特性

### 🍽️ 用户端（微信小程序）

#### 扫码点餐
- ✅ 扫描桌上二维码，自动识别桌号
- ✅ 桌位显示当前状态（空闲/已开台/有进行中订单）
- ✅ 同一桌多人扫码，点餐自动合并到同一订单

#### 菜单浏览
- ✅ 菜品按分类导航展示（热菜、凉菜、主食、饮品、套餐等）
- ✅ 菜品展示图片、名称、价格、月销量
- ✅ 人气热销、店长推荐、限时特价标签
- ✅ 按菜品名搜索、价格区间搜索

#### 菜品详情
- ✅ 查看菜品描述、配料、辣度口味
- ✅ 规格设置：大/中/小份、辣度选择、温度选择
- ✅ 加料选项
- ✅ 单品备注
- ✅ 一键清空购物车

#### 购物车与下单
- ✅ 实时显示菜品、数量、总价
- ✅ 订单整体备注
- ✅ 一键下单，订单自动推送到后厨和收银台

#### 支付方式
- ✅ 微信在线支付
- ✅ 先吃后付模式
- ✅ 多人AA付款
- ✅ 代付功能

#### 订单管理
- ✅ 个人订单列表（按分类展示）
- ✅ 订单详情（状态、明细、总金额）
- ✅ 外卖配送状态跟踪
- ✅ 订单确认收货

#### 外卖与自提
- ✅ 支持外卖配送
- ✅ 支持到店自提
- ✅ 配送地址管理

#### 会员系统
- ✅ 微信一键登录
- ✅ 手机号登录
- ✅ 会员积分累计
- ✅ 优惠券领取与使用
- ✅ 订单评价评分（1-5星）
- ✅ 商家回复展示

### 🛠️ 管理后台（PHP + Vue3）

- ✅ 数据概览仪表盘（今日订单、营收、热销菜品等）
- ✅ 菜品分类管理
- ✅ 菜品管理（上架/下架、规格设置、加料设置、标签设置）
- ✅ 桌位管理（开台/关台、桌位信息）
- ✅ 订单管理（状态流转、订单详情）
- ✅ 会员管理
- ✅ 优惠券管理
- ✅ 评价管理（商家回复、评价审核）
- ✅ 系统设置
- ✅ 管理员管理

## 数据库表结构

| 表名 | 说明 |
|------|------|
| dc_admin | 管理员表 |
| dc_category | 菜品分类表 |
| dc_dish | 菜品表 |
| dc_dish_spec_group | 菜品规格组表 |
| dc_dish_spec_option | 菜品规格选项表 |
| dc_dish_addon | 加料表 |
| dc_table | 桌位表 |
| dc_order | 订单表 |
| dc_order_sub | 订单子表（AA付款） |
| dc_order_item | 订单详情表 |
| dc_member | 会员表 |
| dc_points_log | 积分记录表 |
| dc_coupon | 优惠券模板表 |
| dc_member_coupon | 会员优惠券表 |
| dc_review | 评价表 |
| dc_payment | 支付记录表 |
| dc_setting | 系统设置表 |

## 快速部署

### 1. 环境要求

- PHP >= 7.3
- MySQL >= 5.7
- Nginx / Apache
- PDO PHP 扩展
- 微信小程序开发者工具
- 微信支付商户号（如需在线支付）

### 2. 数据库部署

```bash
mysql -u root -p
source database/diancan.sql
```

### 3. 后端配置

修改 `server/config.php`：

```php
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'diancan',
        'user' => 'root',
        'pass' => 'your_password',
        'charset' => 'utf8mb4',
        'prefix' => 'dc_'
    ],
    'wx' => [
        'appid' => 'your_wx_appid',
        'secret' => 'your_wx_secret',
        'mch_id' => 'your_mch_id',
        'mch_key' => 'your_mch_key',
        'notify_url' => 'https://your-domain.com/api/order/notify'
    ]
];
```

### 4. 配置重写规则

#### Nginx

参考 `server/nginx.conf`：

```nginx
location /api/ {
    rewrite ^/api/(.*)$ /api.php/$1 last;
}

location /api.php/ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root/api.php;
    include fastcgi_params;
}
```

#### Apache

已包含 `.htaccess` 文件。

### 5. 小程序配置

1. 打开微信开发者工具，导入 `miniprogram` 目录
2. 修改 `miniprogram/project.config.json` 中的 `appid` 为你的小程序 AppID
3. 修改 `miniprogram/app.js` 中的 `baseUrl` 为你的后端接口地址

```javascript
globalData: {
    baseUrl: 'https://your-domain.com/api',
    // ...
}
```

### 6. 初始账号

管理后台默认账号：
- 用户名：`admin`
- 密码：`admin123`

登录后请立即修改密码！

## API 接口说明

### 公共响应格式

```json
{
    "code": 0,
    "msg": "success",
    "data": {}
}
```

- `code = 0` 表示成功，非 0 表示失败
- `code = 401` 表示未授权，需重新登录

### 主要接口列表

| 接口 | 方法 | 说明 |
|------|------|------|
| /api/admin/login | POST | 管理员登录 |
| /api/category/list | GET | 分类列表（前台） |
| /api/category/all | GET | 分类列表（后台） |
| /api/dish/list | GET | 菜品列表 |
| /api/dish/detail/:id | GET | 菜品详情 |
| /api/dish/create | POST | 新增菜品 |
| /api/table/scan | GET | 扫码识别桌位 |
| /api/order/create | POST | 创建订单 |
| /api/order/list | GET | 订单列表 |
| /api/order/detail/:id | GET | 订单详情 |
| /api/order/pay | POST | 发起支付 |
| /api/order/notify | POST | 支付回调 |
| /api/member/wx-login | POST | 微信登录 |
| /api/member/phone-login | POST | 手机号登录 |
| /api/member/info | GET | 获取会员信息 |
| /api/coupon/list | GET | 优惠券列表 |
| /api/coupon/available | GET | 可用优惠券 |
| /api/review/create | POST | 提交评价 |
| /api/stats/dashboard | GET | 数据概览 |

## 核心技术点

### 📦 订单合并机制

多人扫描同一桌位二维码时，系统会：
1. 识别桌位号，查询是否存在进行中的订单
2. 如果存在进行中的订单，新订单自动合并到原有订单
3. 每个用户有独立的子订单记录，支持AA付款

### 💳 多支付模式

1. **微信支付**：JSAPI 模式，调用微信支付统一下单
2. **先吃后付**：标记为赊账模式，后厨正常出单
3. **AA付款**：订单拆分子订单，每人支付自己的份额

### 🏷️ 灵活的菜品规则系统

- 规格组：份量（大/中/小份）、辣度、温度等
- 每个规格组可设置多个选项，每个选项可单独加价
- 加料系统：支持单菜品加料和全局加料
- 标签系统：人气热销、店长推荐、限时特价

## 开发说明

### 目录规范

- 所有控制器放在 `server/controller/` 目录
- 控制器类名必须是 `XxxController`，文件名对应 `XxxController.php`
- 方法名可自动识别 `get`/`post`/`put`/`delete` 前缀，或者直接使用方法名

### 新增API示例

1. 创建 `server/controller/ExampleController.php`

```php
<?php
class ExampleController
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public function getList()
    {
        // GET /api/example/list
        $list = $this->db->fetchAll("SELECT * FROM " . $this->db->table('example'));
        Response::success($list);
    }

    public function postCreate()
    {
        // POST /api/example/create
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $this->db->insert('example', $data);
        Response::success(['id' => $id]);
    }

    public function putUpdate($id)
    {
        // PUT /api/example/update/{id}
        $data = json_decode(file_get_contents('php://input'), true);
        $this->db->update('example', $data, 'id = :id', [':id' => $id]);
        Response::success();
    }

    public function deleteDelete($id)
    {
        // DELETE /api/example/delete/{id}
        $this->db->delete('example', 'id = :id', [':id' => $id]);
        Response::success();
    }
}
```

2. 在 `server/api.php` 的 `$controllerMap` 中添加映射

## 安全建议

1. 修改默认管理员密码
2. 配置文件 `config.php` 禁止外部访问
3. 微信支付密钥、小程序密钥妥善保管
4. 开启 HTTPS
5. 上传目录设置适当的权限，禁止执行 PHP
6. 定期备份数据库

## 常见问题

**Q: 小程序无法登录？**
A: 检查 `baseUrl` 是否正确，后端服务是否正常运行，域名是否配置在小程序后台的合法域名列表中。

**Q: 微信支付无法调起？**
A: 检查支付配置是否正确，商户号是否已开通 JSAPI 支付，支付目录是否配置正确。

**Q: 扫码识别不到桌号？**
A: 确保二维码链接中包含 `table_no` 参数，且桌号已在 `dc_table` 表中录入。

## License

MIT

## 技术支持

如有问题，请联系开发团队。
