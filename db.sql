/*
 Navicat Premium Data Transfer

 Source Server         : 本地-root@localhost
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : localhost:3306
 Source Schema         : tp-ue-qn-db

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 10/06/2019 11:42:19
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for db_bucket
-- ----------------------------
DROP TABLE IF EXISTS `db_bucket`;
CREATE TABLE `db_bucket`  (
  `bucket_name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'bucket名称',
  `bucket_domain` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'bucket对应的domain',
  `bucket_description` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '文字描述',
  `bucket_default` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '默认，0为否，1为是',
  `bucket_style_thumb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '缩略图样式名',
  `bucket_style_original` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '原图样式名',
  `bucket_style_water` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '原图打水印样式名',
  `bucket_style_fixwidth` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '限制宽度样式名',
  `bucket_style_fixheight` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '限制高度样式名',
  PRIMARY KEY (`bucket_name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for db_picture
-- ----------------------------
DROP TABLE IF EXISTS `db_picture`;
CREATE TABLE `db_picture`  (
  `picture_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '图片唯一ID',
  `picture_key` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '云存储文件名',
  `bucket_name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '存储仓库',
  `picture_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '本机文件描述名',
  `picture_description` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '图片描述',
  `picture_protected` tinyint(4) NULL DEFAULT NULL COMMENT '是否保护，0为不保护，1为保护',
  `admin_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '上传者管理员ID，后台上传时保存',
  `user_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '上传者用户ID，用户上传时保存',
  `create_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '编辑时间',
  PRIMARY KEY (`picture_id`) USING BTREE,
  INDEX `bucket_name`(`bucket_name`) USING BTREE,
  CONSTRAINT `db_picture_ibfk_1` FOREIGN KEY (`bucket_name`) REFERENCES `db_bucket` (`bucket_name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
