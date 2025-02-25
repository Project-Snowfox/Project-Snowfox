-- phpMyAdmin SQL Dump
-- version 2.11.5
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2008 年 07 月 06 日 18:00
-- 服务器版本: 5.0.51
-- PHP 版本: 5.2.5

--
-- 数据库: `Snowfox`
--

-- --------------------------------------------------------

--
-- 表的结构 `Snowfox_comments`
--

CREATE TABLE `Snowfox_comments` (
  `coid` int(10) unsigned NOT NULL auto_increment,
  `cid` int(10) unsigned default '0',
  `created` int(10) unsigned default '0',
  `author` varchar(150) default NULL,
  `authorId` int(10) unsigned default '0',
  `ownerId` int(10) unsigned default '0',
  `mail` varchar(150) default NULL,
  `url` varchar(255) default NULL,
  `ip` varchar(64) default NULL,
  `agent` varchar(511) default NULL,
  `text` text,
  `type` varchar(16) default 'comment',
  `status` varchar(16) default 'approved',
  `parent` int(10) unsigned default '0',
  PRIMARY KEY  (`coid`),
  KEY `cid` (`cid`),
  KEY `created` (`created`)
) ENGINE=%engine%  DEFAULT CHARSET=%charset%;

-- --------------------------------------------------------

--
-- 表的结构 `Snowfox_contents`
--

CREATE TABLE `Snowfox_contents` (
  `cid` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(150) default NULL,
  `slug` varchar(150) default NULL,
  `created` int(10) unsigned default '0',
  `modified` int(10) unsigned default '0',
  `text` longtext,
  `order` int(10) unsigned default '0',
  `authorId` int(10) unsigned default '0',
  `template` varchar(32) default NULL,
  `type` varchar(16) default 'post',
  `status` varchar(16) default 'publish',
  `password` varchar(32) default NULL,
  `commentsNum` int(10) unsigned default '0',
  `allowComment` char(1) default '0',
  `allowPing` char(1) default '0',
  `allowFeed` char(1) default '0',
  `parent` int(10) unsigned default '0',
  PRIMARY KEY  (`cid`),
  UNIQUE KEY `slug` (`slug`),
  KEY `created` (`created`)
) ENGINE=%engine%  DEFAULT CHARSET=%charset%;

-- --------------------------------------------------------

--
-- 表的结构 `Snowfox_fields`
--

CREATE TABLE `Snowfox_fields` (
  `cid` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` varchar(8) default 'str',
  `str_value` text,
  `int_value` int(10) default '0',
  `float_value` float default '0',
  PRIMARY KEY  (`cid`,`name`),
  KEY `int_value` (`int_value`),
  KEY `float_value` (`float_value`)
) ENGINE=%engine%  DEFAULT CHARSET=%charset%;

-- --------------------------------------------------------

--
-- 表的结构 `Snowfox_metas`
--

CREATE TABLE `Snowfox_metas` (
  `mid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(150) default NULL,
  `slug` varchar(150) default NULL,
  `type` varchar(32) NOT NULL,
  `description` varchar(150) default NULL,
  `count` int(10) unsigned default '0',
  `order` int(10) unsigned default '0',
  `parent` int(10) unsigned default '0',
  PRIMARY KEY  (`mid`),
  KEY `slug` (`slug`)
) ENGINE=%engine%  DEFAULT CHARSET=%charset%;

-- --------------------------------------------------------

--
-- 表的结构 `Snowfox_options`
--

CREATE TABLE `Snowfox_options` (
  `name` varchar(32) NOT NULL,
  `user` int(10) unsigned NOT NULL default '0',
  `value` text,
  PRIMARY KEY  (`name`,`user`)
) ENGINE=%engine% DEFAULT CHARSET=%charset%;

-- --------------------------------------------------------

--
-- 表的结构 `Snowfox_relationships`
--

CREATE TABLE `Snowfox_relationships` (
  `cid` int(10) unsigned NOT NULL,
  `mid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`cid`,`mid`)
) ENGINE=%engine% DEFAULT CHARSET=%charset%;

-- --------------------------------------------------------

--
-- 表的结构 `Snowfox_users`
--

CREATE TABLE `Snowfox_users` (
  `uid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) default NULL,
  `password` varchar(64) default NULL,
  `mail` varchar(150) default NULL,
  `url` varchar(150) default NULL,
  `screenName` varchar(32) default NULL,
  `created` int(10) unsigned default '0',
  `activated` int(10) unsigned default '0',
  `logged` int(10) unsigned default '0',
  `group` varchar(16) default 'visitor',
  `authCode` varchar(64) default NULL,
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `mail` (`mail`)
) ENGINE=%engine%  DEFAULT CHARSET=%charset%;
