
DROP TABLE IF EXISTS `meta`;
CREATE TABLE `meta` (
  `guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--
-- 创建时间： 2016-02-20 13:24:05
-- 最后更新： 2016-03-11 08:38:18
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `pass_hash` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `ip_1` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_2` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_3` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_4` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `auth_key` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `access_token` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password_reset_token` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `source` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_additional_account`
--
-- 创建时间： 2016-02-20 13:24:06
-- 最后更新： 2016-03-11 08:36:46
--

DROP TABLE IF EXISTS `user_additional_account`;
CREATE TABLE `user_additional_account` (
  `guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `id` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `pass_hash` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `enable_login` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `content` tinyint(3) UNSIGNED NOT NULL,
  `source` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'User source',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_1` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_2` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_3` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_4` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `confirmed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `confirm_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_comment`
--
-- 创建时间： 2016-02-20 13:24:06
-- 最后更新： 2016-03-11 08:37:12
--

DROP TABLE IF EXISTS `user_comment`;
CREATE TABLE `user_comment` (
  `guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `id` varchar(4) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `parent_guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `content` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `ip_1` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_2` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_3` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_4` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `confirmed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `confirm_code` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `confirm_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_email`
--
-- 创建时间： 2016-02-20 13:24:06
-- 最后更新： 2016-03-11 08:37:13
--

DROP TABLE IF EXISTS `user_email`;
CREATE TABLE `user_email` (
  `guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `id` varchar(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `confirmed` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `confirm_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `confirm_code` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_notification`
--
-- 创建时间： 2016-03-11 08:35:35
-- 最后更新： 2016-03-11 08:36:47
--

DROP TABLE IF EXISTS `user_notification`;
CREATE TABLE `user_notification` (
  `guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `id` varchar(4) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `content` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `range` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ip_1` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `ip_2` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `ip_3` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `ip_4` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `ip_type` tinyint(4) UNSIGNED NOT NULL DEFAULT '4',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_relation`
--
-- 创建时间： 2016-02-20 13:24:06
-- 最后更新： 2016-03-11 08:38:04
--

DROP TABLE IF EXISTS `user_relation`;
CREATE TABLE `user_relation` (
  `guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `id` varchar(4) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `other_guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `favorite` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ip_1` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_2` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_3` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_4` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `groups` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_relation_group`
--
-- 创建时间： 2016-02-20 13:24:06
-- 最后更新： 2016-03-11 08:38:04
--

DROP TABLE IF EXISTS `user_relation_group`;
CREATE TABLE `user_relation_group` (
  `guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `content` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'group name',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user_single_relation`
--
-- 创建时间： 2016-02-20 13:24:06
-- 最后更新： 2016-03-11 08:37:53
--

DROP TABLE IF EXISTS `user_single_relation`;
CREATE TABLE `user_single_relation` (
  `guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `id` varchar(4) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `other_guid` varchar(36) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `favorite` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `ip_1` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_2` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_3` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_4` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '4',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  `groups` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `meta`
--
ALTER TABLE `meta`
  ADD PRIMARY KEY (`guid`),
  ADD UNIQUE KEY `meta_key_unique` (`key`),
  ADD KEY `user_meta_fkey` (`user_guid`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`guid`),
  ADD UNIQUE KEY `user_id_unique` (`id`),
  ADD UNIQUE KEY `user_auth_key_unique` (`auth_key`) USING BTREE,
  ADD UNIQUE KEY `user_access_token_unique` (`access_token`);

--
-- Indexes for table `user_additional_account`
--
ALTER TABLE `user_additional_account`
  ADD PRIMARY KEY (`guid`),
  ADD UNIQUE KEY `user_email_id_unique` (`user_guid`,`id`);

--
-- Indexes for table `user_comment`
--
ALTER TABLE `user_comment`
  ADD PRIMARY KEY (`guid`),
  ADD UNIQUE KEY `user_coment_id_unique` (`id`,`user_guid`) USING BTREE,
  ADD KEY `user_comment_fkey` (`user_guid`);

--
-- Indexes for table `user_email`
--
ALTER TABLE `user_email`
  ADD PRIMARY KEY (`guid`),
  ADD UNIQUE KEY `user_email_id_unique` (`user_guid`,`id`);

--
-- Indexes for table `user_notification`
--
ALTER TABLE `user_notification`
  ADD PRIMARY KEY (`guid`),
  ADD UNIQUE KEY `user_notification_unique` (`id`,`user_guid`),
  ADD KEY `user_notification_fkey` (`user_guid`);

--
-- Indexes for table `user_relation`
--
ALTER TABLE `user_relation`
  ADD PRIMARY KEY (`guid`),
  ADD UNIQUE KEY `user_other_guid_unique` (`user_guid`,`other_guid`),
  ADD UNIQUE KEY `user_relation_id_unique` (`user_guid`,`id`) USING BTREE,
  ADD KEY `relation_other_guid_fkey` (`other_guid`);

--
-- Indexes for table `user_relation_group`
--
ALTER TABLE `user_relation_group`
  ADD PRIMARY KEY (`guid`),
  ADD KEY `relation_group_user_guid_fkey` (`user_guid`);

--
-- Indexes for table `user_single_relation`
--
ALTER TABLE `user_single_relation`
  ADD PRIMARY KEY (`guid`),
  ADD UNIQUE KEY `user_other_guid_unique` (`user_guid`,`other_guid`) USING BTREE,
  ADD UNIQUE KEY `user_single_relation_unique` (`user_guid`,`id`),
  ADD KEY `user_single_relation_ibfk_1` (`other_guid`);

--
-- 限制导出的表
--

--
-- 限制表 `meta`
--
ALTER TABLE `meta`
  ADD CONSTRAINT `user_meta_fkey` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_additional_account`
--
ALTER TABLE `user_additional_account`
  ADD CONSTRAINT `user_additional_account_ibfk_1` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_comment`
--
ALTER TABLE `user_comment`
  ADD CONSTRAINT `user_comment_fkey` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_email`
--
ALTER TABLE `user_email`
  ADD CONSTRAINT `email_user_guid_fkey` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_notification`
--
ALTER TABLE `user_notification`
  ADD CONSTRAINT `user_notification_fkey` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_relation`
--
ALTER TABLE `user_relation`
  ADD CONSTRAINT `relation_other_guid_fkey` FOREIGN KEY (`other_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `relation_user_guid_fkey` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_relation_group`
--
ALTER TABLE `user_relation_group`
  ADD CONSTRAINT `relation_group_user_guid_fkey` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `user_single_relation`
--
ALTER TABLE `user_single_relation`
  ADD CONSTRAINT `user_single_relation_ibfk_1` FOREIGN KEY (`other_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_single_relation_ibfk_2` FOREIGN KEY (`user_guid`) REFERENCES `user` (`guid`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;