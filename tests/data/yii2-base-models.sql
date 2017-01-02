-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2017-01-02 16:35:16
-- 服务器版本： 5.7.17
-- PHP Version: 7.0.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yii2-base-models`
--
CREATE DATABASE IF NOT EXISTS `yii2-base-models` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `yii2-base-models`;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--
-- 创建时间： 2017-01-01 08:50:49
-- 最后更新： 2017-01-02 08:31:12
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `guid` varbinary(16) NOT NULL,
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `pass_hash` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `ip` varbinary(16) NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `auth_key` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `access_token` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password_reset_token` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`guid`),
  UNIQUE KEY `access_token_unique` (`access_token`) USING BTREE,
  UNIQUE KEY `auth_key_unique` (`auth_key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 表的关联 `user`:
--

--
-- 插入之前先把表清空（truncate） `user`
--

TRUNCATE TABLE `user`;
-- --------------------------------------------------------

--
-- 表的结构 `user_additional_account`
--
-- 创建时间： 2017-01-02 08:27:57
--

DROP TABLE IF EXISTS `user_additional_account`;
CREATE TABLE IF NOT EXISTS `user_additional_account` (
  `guid` varbinary(16) NOT NULL,
  `user_guid` varbinary(16) NOT NULL,
  `id` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `pass_hash` varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `enable_login` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `content` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `source` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'User source',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varbinary(16) NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `confirmed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `confirmed_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  PRIMARY KEY (`guid`),
  KEY `user_guid` (`user_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 表的关联 `user_additional_account`:
--   `user_guid`
--       `user` -> `guid`
--

--
-- 插入之前先把表清空（truncate） `user_additional_account`
--

TRUNCATE TABLE `user_additional_account`;
-- --------------------------------------------------------

--
-- 表的结构 `user_email`
--
-- 创建时间： 2017-01-02 08:25:33
--

DROP TABLE IF EXISTS `user_email`;
CREATE TABLE IF NOT EXISTS `user_email` (
  `guid` varbinary(16) NOT NULL,
  `user_guid` varbinary(16) NOT NULL,
  `id` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `confirmed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `confirmed_at` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `confirm_code` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`guid`),
  UNIQUE KEY `user_email_id_unique` (`user_guid`,`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 表的关联 `user_email`:
--   `user_guid`
--       `user` -> `guid`
--

--
-- 插入之前先把表清空（truncate） `user_email`
--

TRUNCATE TABLE `user_email`;
--
-- 限制导出的表
--

--
-- 限制表 `user_additional_account`
--
ALTER TABLE `user_additional_account`
  ADD CONSTRAINT `user_additional_account_ibfk_1` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_email`
--
ALTER TABLE `user_email`
  ADD CONSTRAINT `user_email_ibfk_1` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
