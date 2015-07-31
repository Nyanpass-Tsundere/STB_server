-- phpMyAdmin SQL Dump
-- version 4.4.11
-- http://www.phpmyadmin.net
--
-- 主機: localhost
-- 產生時間： 2015 年 07 月 31 日 16:53
-- 伺服器版本: 10.0.20-MariaDB-1~jessie-log
-- PHP 版本： 5.6.9-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `STB_datas`
--

-- --------------------------------------------------------

--
-- 資料表結構 `ChannelName`
--

CREATE TABLE IF NOT EXISTS `ChannelName` (
  `ChannelID` int(11) NOT NULL,
  `ChannelName` varchar(120) NOT NULL,
  `ChannelHolder` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `ChannelTable`
--

CREATE TABLE IF NOT EXISTS `ChannelTable` (
  `channel_num` int(11) NOT NULL,
  `channel_name` varchar(50) NOT NULL,
  `channel_uri` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `ProgramName`
--

CREATE TABLE IF NOT EXISTS `ProgramName` (
  `ProgramID` int(11) NOT NULL,
  `ProgramName` varchar(120) NOT NULL,
  `ProgramHolder` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `RealtimeComment`
--

CREATE TABLE IF NOT EXISTS `RealtimeComment` (
  `UserID` int(11) NOT NULL,
  `ProgramlID` int(11) NOT NULL,
  `programstarttime` datetime NOT NULL,
  `ChannelID` int(11) NOT NULL,
  `CommentTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Comment` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `RealtimeViews`
--

CREATE TABLE IF NOT EXISTS `RealtimeViews` (
  `UserID` int(11) NOT NULL,
  `ProgramlID` int(11) NOT NULL,
  `programstarttime` datetime NOT NULL,
  `ChannelID` int(11) NOT NULL,
  `Status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `StaticsChannel`
--

CREATE TABLE IF NOT EXISTS `StaticsChannel` (
  `ChannelID` int(11) NOT NULL,
  `week_view` int(11) NOT NULL,
  `week_like` int(11) NOT NULL,
  `month_view` int(11) NOT NULL,
  `month_like` int(11) NOT NULL,
  `year_view` int(11) NOT NULL,
  `year_like` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `StaticsProgram`
--

CREATE TABLE IF NOT EXISTS `StaticsProgram` (
  `ProgramID` int(11) NOT NULL,
  `week_view` int(11) NOT NULL,
  `week_like` int(11) NOT NULL,
  `month_view` int(11) NOT NULL,
  `month_like` int(11) NOT NULL,
  `year_view` int(11) NOT NULL,
  `year_like` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 已匯出資料表的索引
--

--
-- 資料表索引 `ChannelName`
--
ALTER TABLE `ChannelName`
  ADD PRIMARY KEY (`ChannelID`),
  ADD UNIQUE KEY `ChennelName` (`ChannelName`);

--
-- 資料表索引 `ChannelTable`
--
ALTER TABLE `ChannelTable`
  ADD PRIMARY KEY (`channel_num`);

--
-- 資料表索引 `ProgramName`
--
ALTER TABLE `ProgramName`
  ADD PRIMARY KEY (`ProgramID`);

--
-- 資料表索引 `RealtimeComment`
--
ALTER TABLE `RealtimeComment`
  ADD PRIMARY KEY (`UserID`,`ProgramlID`,`programstarttime`);

--
-- 資料表索引 `RealtimeViews`
--
ALTER TABLE `RealtimeViews`
  ADD PRIMARY KEY (`UserID`,`ProgramlID`,`programstarttime`);

--
-- 資料表索引 `StaticsChannel`
--
ALTER TABLE `StaticsChannel`
  ADD PRIMARY KEY (`ChannelID`);

--
-- 資料表索引 `StaticsProgram`
--
ALTER TABLE `StaticsProgram`
  ADD PRIMARY KEY (`ProgramID`);

--
-- 在匯出的資料表使用 AUTO_INCREMENT
--

--
-- 使用資料表 AUTO_INCREMENT `ChannelName`
--
ALTER TABLE `ChannelName`
  MODIFY `ChannelID` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `ProgramName`
--
ALTER TABLE `ProgramName`
  MODIFY `ProgramID` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

