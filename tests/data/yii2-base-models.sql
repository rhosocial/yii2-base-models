-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2017-05-09 14:09:13
-- 服务器版本： 8.0.0-dmr
-- PHP Version: 7.1.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yii2-base-models`
--
CREATE DATABASE IF NOT EXISTS `yii2-base-models` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `yii2-base-models`;

-- --------------------------------------------------------

--
-- 表的结构 `entity`
--
-- 创建时间： 2017-05-09 06:03:41
--

DROP TABLE IF EXISTS `entity`;
CREATE TABLE IF NOT EXISTS `entity` (
  `guid` varbinary(16) NOT NULL,
  `id` varchar(16) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `content` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `ip` varbinary(16) NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `expired_after` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`guid`),
  UNIQUE KEY `entity_id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `entity_ai`
--
-- 创建时间： 2017-05-09 06:04:43
--

DROP TABLE IF EXISTS `entity_ai`;
CREATE TABLE IF NOT EXISTS `entity_ai` (
  `guid` varbinary(16) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `ip` varbinary(16) NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  PRIMARY KEY (`guid`),
  UNIQUE KEY `entity_ai_id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `operator_entity`
--
-- 创建时间： 2017-05-09 06:01:30
--

DROP TABLE IF EXISTS `operator_entity`;
CREATE TABLE IF NOT EXISTS `operator_entity` (
  `guid` varbinary(16) NOT NULL,
  `id` varchar(16) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `content` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `ip` varbinary(16) NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `expired_after` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `operator_guid` varbinary(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`guid`),
  UNIQUE KEY `operator_entity_id_unique` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='For Operator.';

-- --------------------------------------------------------

--
-- 表的结构 `user`
--
-- 创建时间： 2017-01-10 05:51:10
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `guid` varbinary(16) NOT NULL,
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `pass_hash` varchar(80) COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `expired_after` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip` varbinary(16) NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `auth_key` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `access_token` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_reset_token` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `source` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`guid`),
  UNIQUE KEY `user_id_unique` (`id`) USING BTREE,
  UNIQUE KEY `user_access_token_unique` (`access_token`) USING BTREE,
  UNIQUE KEY `user_auth_key_unique` (`auth_key`) USING BTREE,
  UNIQUE KEY `user_password_reset_token_unique` (`password_reset_token`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_additional_account`
--
-- 创建时间： 2017-01-24 14:07:01
--

DROP TABLE IF EXISTS `user_additional_account`;
CREATE TABLE IF NOT EXISTS `user_additional_account` (
  `guid` varbinary(16) NOT NULL,
  `user_guid` varbinary(16) NOT NULL,
  `id` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `pass_hash` varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `separate_login` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `content` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `source` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'User source',
  `description` text COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `ip` varbinary(16) NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `confirmed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `confirmed_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  PRIMARY KEY (`guid`),
  KEY `user_guid` (`user_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_comment`
--
-- 创建时间： 2017-01-23 03:40:35
--

DROP TABLE IF EXISTS `user_comment`;
CREATE TABLE IF NOT EXISTS `user_comment` (
  `guid` varbinary(16) NOT NULL,
  `id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `parent_guid` varbinary(16) NOT NULL DEFAULT '',
  `user_guid` varbinary(16) NOT NULL,
  `post_guid` varbinary(16) NOT NULL,
  `content` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `ip` varbinary(16) NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `confirmed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `confirmed_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `confirm_code` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`guid`),
  UNIQUE KEY `user_comment_id_unique` (`id`,`user_guid`) USING BTREE,
  KEY `user_guid` (`user_guid`),
  KEY `post_guid` (`post_guid`),
  KEY `parent_guid` (`parent_guid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_email`
--
-- 创建时间： 2017-01-14 07:44:00
--

DROP TABLE IF EXISTS `user_email`;
CREATE TABLE IF NOT EXISTS `user_email` (
  `guid` varbinary(16) NOT NULL,
  `user_guid` varbinary(16) NOT NULL,
  `id` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `confirmed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `confirmed_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `confirm_code` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`guid`),
  UNIQUE KEY `user_email_id_unique` (`user_guid`,`id`) USING BTREE,
  KEY `user_email_normal` (`email`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_meta`
--
-- 创建时间： 2017-01-07 07:39:45
--

DROP TABLE IF EXISTS `user_meta`;
CREATE TABLE IF NOT EXISTS `user_meta` (
  `guid` varbinary(16) NOT NULL,
  `user_guid` varbinary(16) NOT NULL,
  `key` varchar(190) COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '', -- key length should be less than 767 bytes.
  `value` text COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`guid`),
  UNIQUE KEY `meta__key_unique` (`user_guid`,`key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_post`
--
-- 创建时间： 2017-05-09 06:09:04
--

DROP TABLE IF EXISTS `user_post`;
CREATE TABLE IF NOT EXISTS `user_post` (
  `guid` varbinary(16) NOT NULL,
  `user_guid` varbinary(16) NOT NULL,
  `id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `content` text COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `ip` varbinary(16) NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  PRIMARY KEY (`guid`),
  UNIQUE KEY `post_id_unique` (`id`) USING BTREE,
  KEY `user_post_guid_fkey` (`user_guid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_relation`
--
-- 创建时间： 2017-01-07 07:39:45
--

DROP TABLE IF EXISTS `user_relation`;
CREATE TABLE IF NOT EXISTS `user_relation` (
  `guid` varbinary(16) NOT NULL,
  `id` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_guid` varbinary(16) NOT NULL,
  `remark` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `other_guid` varbinary(16) NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `favorite` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ip` varbinary(16) NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `groups` varbinary(800) NOT NULL DEFAULT '' COMMENT 'Group GUID array.',
  `description` text COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`guid`),
  UNIQUE KEY `user_other_unique` (`user_guid`,`other_guid`) USING BTREE,
  UNIQUE KEY `user_relation_id_unique` (`id`,`user_guid`) USING BTREE,
  KEY `relation_other_guid_fkey` (`other_guid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_relation_group`
--
-- 创建时间： 2017-01-31 01:05:16
--

DROP TABLE IF EXISTS `user_relation_group`;
CREATE TABLE IF NOT EXISTS `user_relation_group` (
  `guid` varbinary(16) NOT NULL,
  `id` varchar(4) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_guid` varbinary(16) NOT NULL,
  `content` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `description` text COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`guid`),
  UNIQUE KEY `user_relation_group_id_unique` (`id`,`user_guid`) USING BTREE,
  KEY `user_guid` (`user_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_single_relation`
--
-- 创建时间： 2017-01-07 07:39:45
--

DROP TABLE IF EXISTS `user_single_relation`;
CREATE TABLE IF NOT EXISTS `user_single_relation` (
  `guid` varbinary(16) NOT NULL,
  `id` varchar(8) COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `user_guid` varbinary(16) NOT NULL,
  `remark` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `other_guid` varbinary(16) NOT NULL,
  `favorite` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ip` varbinary(16) NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `groups` varbinary(800) NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`guid`),
  UNIQUE KEY `user_other_unique` (`user_guid`,`other_guid`) USING BTREE,
  UNIQUE KEY `user_single_relation_unique` (`id`,`user_guid`) USING BTREE,
  KEY `user_single_relation_other_guid_fkey` (`other_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- 限制导出的表
--

--
-- 限制表 `user_additional_account`
--
ALTER TABLE `user_additional_account`
  ADD CONSTRAINT `user_additional_account_ibfk_1` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_comment`
--
ALTER TABLE `user_comment`
  ADD CONSTRAINT `user_comment_ibfk_1` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_comment_ibfk_2` FOREIGN KEY (`post_guid`) REFERENCES `user_post` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_email`
--
ALTER TABLE `user_email`
  ADD CONSTRAINT `user_email_ibfk_1` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_meta`
--
ALTER TABLE `user_meta`
  ADD CONSTRAINT `user_meta_fkey` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_post`
--
ALTER TABLE `user_post`
  ADD CONSTRAINT `user_post_guid_fkey` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_relation`
--
ALTER TABLE `user_relation`
  ADD CONSTRAINT `user_relation_other_guid_fkey` FOREIGN KEY (`other_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_relation_user_guid_fkey` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_relation_group`
--
ALTER TABLE `user_relation_group`
  ADD CONSTRAINT `user_relation_group_ibfk_1` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_single_relation`
--
ALTER TABLE `user_single_relation`
  ADD CONSTRAINT `user_single_relation_other_guid_fkey` FOREIGN KEY (`other_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_single_relation_user_guid_fkey` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
