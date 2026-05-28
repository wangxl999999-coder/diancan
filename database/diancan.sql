CREATE DATABASE IF NOT EXISTS `diancan` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `diancan`;

-- 管理员表
CREATE TABLE `dc_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `realname` varchar(50) NOT NULL DEFAULT '',
  `role` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0普通管理员 1超级管理员',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_time` datetime DEFAULT NULL,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 菜品分类表
CREATE TABLE `dc_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `icon` varchar(255) NOT NULL DEFAULT '',
  `sort` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1启用 0禁用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 菜品表
CREATE TABLE `dc_dish` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(11) unsigned NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text,
  `ingredients` varchar(500) NOT NULL DEFAULT '' COMMENT '配料',
  `spiciness` tinyint(1) NOT NULL DEFAULT 0 COMMENT '辣度 0不辣 1微辣 2中辣 3重辣',
  `monthly_sales` int(11) NOT NULL DEFAULT 0 COMMENT '月销量',
  `tags` varchar(100) NOT NULL DEFAULT '' COMMENT '标签: hot_sale,recommend,limited_price 多个逗号分隔',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1上架 0下架',
  `sort` int(11) NOT NULL DEFAULT 0,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 菜品规格组表（大中小份、辣度、温度等）
CREATE TABLE `dc_dish_spec_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `dish_id` int(11) unsigned NOT NULL DEFAULT 0,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '规格组名如：份量、辣度、温度',
  `is_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否必选',
  `sort` int(11) NOT NULL DEFAULT 0,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dish` (`dish_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 菜品规格选项表
CREATE TABLE `dc_dish_spec_option` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL DEFAULT 0,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '选项名如：大份、中份、小份',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '加价金额',
  `sort` int(11) NOT NULL DEFAULT 0,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_group` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 加料表
CREATE TABLE `dc_dish_addon` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `dish_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '0表示全局加料',
  `name` varchar(50) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dish` (`dish_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 桌位表
CREATE TABLE `dc_table` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `table_no` varchar(20) NOT NULL DEFAULT '' COMMENT '桌号',
  `area` varchar(50) NOT NULL DEFAULT '' COMMENT '区域',
  `seats` int(11) NOT NULL DEFAULT 4 COMMENT '座位数',
  `qrcode` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码图片路径',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0空闲 1已开台 2已预约',
  `current_order_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '当前进行中的订单ID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_table_no` (`table_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 订单表
CREATE TABLE `dc_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '订单号',
  `table_id` int(11) unsigned NOT NULL DEFAULT 0,
  `table_no` varchar(20) NOT NULL DEFAULT '',
  `order_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1堂食 2外卖 3自提',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0待支付 1已支付 2制作中 3已完成 4已取消',
  `pay_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未支付 1微信支付 2先吃后付',
  `pay_mode` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0单笔支付 1AA付款 2代付',
  `pay_time` datetime DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '订单总金额',
  `pay_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '实付金额',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '优惠金额',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '订单备注',
  `member_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '主下单人',
  `is_merged` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否合并订单',
  `contact_name` varchar(50) NOT NULL DEFAULT '' COMMENT '联系人（外卖/自提）',
  `contact_phone` varchar(20) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '配送地址',
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deliver_time` datetime DEFAULT NULL COMMENT '送达/自提时间',
  `kitchen_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0待接单 1制作中 2已完成',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_table` (`table_id`),
  KEY `idx_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 订单子表（多人合并时每人一个子订单）
CREATE TABLE `dc_order_sub` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '主订单ID',
  `member_id` int(11) unsigned NOT NULL DEFAULT 0,
  `sub_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pay_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未支付 1已支付',
  `pay_time` datetime DEFAULT NULL,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 订单详情表
CREATE TABLE `dc_order_item` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) unsigned NOT NULL DEFAULT 0,
  `sub_order_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '子订单ID，0为主订单直接项',
  `dish_id` int(11) unsigned NOT NULL DEFAULT 0,
  `dish_name` varchar(100) NOT NULL DEFAULT '',
  `dish_image` varchar(255) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `spec_info` varchar(500) NOT NULL DEFAULT '' COMMENT '规格信息JSON',
  `addon_info` varchar(500) NOT NULL DEFAULT '' COMMENT '加料信息JSON',
  `addon_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '加料总价',
  `remark` varchar(200) NOT NULL DEFAULT '' COMMENT '单品备注',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0待制作 1制作中 2已完成 3已退',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 会员表
CREATE TABLE `dc_member` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(100) DEFAULT NULL,
  `unionid` varchar(100) DEFAULT NULL,
  `nickname` varchar(100) NOT NULL DEFAULT '',
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `points` int(11) NOT NULL DEFAULT 0 COMMENT '积分',
  `level` tinyint(1) NOT NULL DEFAULT 1 COMMENT '会员等级',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_openid` (`openid`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 积分记录表
CREATE TABLE `dc_points_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(11) unsigned NOT NULL DEFAULT 0,
  `order_id` int(11) unsigned NOT NULL DEFAULT 0,
  `points` int(11) NOT NULL DEFAULT 0 COMMENT '变动积分 正增负减',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1消费获得 2签到 3兑换扣除 4管理员调整',
  `remark` varchar(200) NOT NULL DEFAULT '',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 优惠券模板表
CREATE TABLE `dc_coupon` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1满减 2折扣 3立减',
  `value` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '满减金额/折扣率(如0.85)/立减金额',
  `min_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '最低消费金额',
  `total_count` int(11) NOT NULL DEFAULT 0 COMMENT '发放总数 0不限',
  `used_count` int(11) NOT NULL DEFAULT 0,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 会员优惠券表
CREATE TABLE `dc_member_coupon` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) unsigned NOT NULL DEFAULT 0,
  `member_id` int(11) unsigned NOT NULL DEFAULT 0,
  `order_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '使用时关联的订单',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0未使用 1已使用 2已过期',
  `use_time` datetime DEFAULT NULL,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 评价表
CREATE TABLE `dc_review` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) unsigned NOT NULL DEFAULT 0,
  `member_id` int(11) unsigned NOT NULL DEFAULT 0,
  `rating` tinyint(1) NOT NULL DEFAULT 5 COMMENT '评分1-5',
  `content` text,
  `images` varchar(1000) NOT NULL DEFAULT '' COMMENT '评价图片，逗号分隔',
  `reply` text COMMENT '商家回复',
  `reply_time` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1显示 0隐藏',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 支付记录表
CREATE TABLE `dc_payment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) unsigned NOT NULL DEFAULT 0,
  `sub_order_id` int(11) unsigned NOT NULL DEFAULT 0,
  `member_id` int(11) unsigned NOT NULL DEFAULT 0,
  `transaction_id` varchar(64) NOT NULL DEFAULT '' COMMENT '微信支付交易号',
  `pay_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pay_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1微信支付 2余额',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0待支付 1成功 2失败 3已退款',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 系统设置表
CREATE TABLE `dc_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(50) NOT NULL DEFAULT '' COMMENT '设置分组',
  `key` varchar(100) NOT NULL DEFAULT '',
  `value` text,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_group_key` (`group`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 初始管理员
INSERT INTO `dc_admin` (`username`, `password`, `realname`, `role`) VALUES ('admin', MD5('admin123'), '超级管理员', 1);

-- 初始分类
INSERT INTO `dc_category` (`name`, `icon`, `sort`) VALUES ('热菜', 'fire', 1);
INSERT INTO `dc_category` (`name`, `icon`, `sort`) VALUES ('凉菜', 'snow', 2);
INSERT INTO `dc_category` (`name`, `icon`, `sort`) VALUES ('主食', 'bowl', 3);
INSERT INTO `dc_category` (`name`, `icon`, `sort`) VALUES ('饮品', 'cup', 4);
INSERT INTO `dc_category` (`name`, `icon`, `sort`) VALUES ('套餐', 'combo', 5);
