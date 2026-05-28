-- 升级脚本：修复openid唯一约束问题
-- 将openid和unionid改为允许NULL，避免手机号注册时冲突

USE `diancan`;

ALTER TABLE `dc_member` 
  MODIFY COLUMN `openid` varchar(100) DEFAULT NULL,
  MODIFY COLUMN `unionid` varchar(100) DEFAULT NULL;

-- 清除已有的空字符串openid记录，改为NULL
UPDATE `dc_member` SET `openid` = NULL WHERE `openid` = '';
UPDATE `dc_member` SET `unionid` = NULL WHERE `unionid` = '';
