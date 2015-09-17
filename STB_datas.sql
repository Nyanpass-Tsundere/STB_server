-- phpMyAdmin SQL Dump
-- version 4.4.11
-- http://www.phpmyadmin.net
--
-- 主機: localhost
-- 產生時間： 2015 年 09 月 18 日 01:22
-- 伺服器版本: 10.0.20-MariaDB-1~jessie-log
-- PHP 版本： 5.6.9-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- 資料庫： `STB_datas`
--

-- --------------------------------------------------------

--
-- 資料表結構 `AdsName`
--

CREATE TABLE IF NOT EXISTS `AdsName` (
  `ADID` int(11) NOT NULL,
  `ADName` varchar(120) NOT NULL,
  `ADHolder` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
-- 資料表結構 `ProgramName`
--

CREATE TABLE IF NOT EXISTS `ProgramName` (
  `ProgramID` int(11) NOT NULL,
  `ProgramName` varchar(120) NOT NULL,
  `ProgramHolder` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `RealtimeViewADs`
--

CREATE TABLE IF NOT EXISTS `RealtimeViewADs` (
  `UserID` int(11) NOT NULL,
  `ADID` int(11) NOT NULL,
  `ADTime` datetime NOT NULL,
  `ChannelID` int(11) NOT NULL,
  `Status` tinyint(4) NOT NULL,
  `Favorite` tinyint(1) NOT NULL
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
  `Status` tinyint(4) NOT NULL,
  `Favorite` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 已匯出資料表的索引
--

--
-- 資料表索引 `AdsName`
--
ALTER TABLE `AdsName`
  ADD PRIMARY KEY (`ADID`),
  ADD UNIQUE KEY `ChennelName` (`ADName`);

--
-- 資料表索引 `ChannelName`
--
ALTER TABLE `ChannelName`
  ADD PRIMARY KEY (`ChannelID`),
  ADD UNIQUE KEY `ChennelName` (`ChannelName`);

--
-- 資料表索引 `ProgramName`
--
ALTER TABLE `ProgramName`
  ADD PRIMARY KEY (`ProgramID`);

--
-- 資料表索引 `RealtimeViewADs`
--
ALTER TABLE `RealtimeViewADs`
  ADD PRIMARY KEY (`UserID`,`ADID`),
  ADD KEY `ProgramlID` (`ADID`),
  ADD KEY `ChannelID` (`ChannelID`),
  ADD KEY `ChannelID_2` (`ChannelID`);

--
-- 資料表索引 `RealtimeViews`
--
ALTER TABLE `RealtimeViews`
  ADD PRIMARY KEY (`UserID`,`ProgramlID`,`programstarttime`),
  ADD KEY `ProgramlID` (`ProgramlID`),
  ADD KEY `ChannelID` (`ChannelID`),
  ADD KEY `ChannelID_2` (`ChannelID`);

--
-- 在匯出的資料表使用 AUTO_INCREMENT
--

--
-- 使用資料表 AUTO_INCREMENT `AdsName`
--
ALTER TABLE `AdsName`
  MODIFY `ADID` int(11) NOT NULL AUTO_INCREMENT;
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
--
-- 已匯出資料表的限制(Constraint)
--

--
-- 資料表的 Constraints `AdsName`
--
ALTER TABLE `AdsName`
  ADD CONSTRAINT `AdsName_ibfk_1` FOREIGN KEY (`ADID`) REFERENCES `RealtimeViewADs` (`ADID`);

--
-- 資料表的 Constraints `RealtimeViewADs`
--
ALTER TABLE `RealtimeViewADs`
  ADD CONSTRAINT `RealtimeViewADs_ibfk_1` FOREIGN KEY (`ADID`) REFERENCES `AdsName` (`ADID`),
  ADD CONSTRAINT `RealtimeViewADs_ibfk_2` FOREIGN KEY (`ChannelID`) REFERENCES `ChannelName` (`ChannelID`);

--
-- 資料表的 Constraints `RealtimeViews`
--
ALTER TABLE `RealtimeViews`
  ADD CONSTRAINT `RealtimeViews_ibfk_1` FOREIGN KEY (`ProgramlID`) REFERENCES `ProgramName` (`ProgramID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `RealtimeViews_ibfk_2` FOREIGN KEY (`ChannelID`) REFERENCES `ChannelName` (`ChannelID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

