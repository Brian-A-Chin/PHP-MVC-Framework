-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 13, 2022 at 07:25 AM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 8.0.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `letsprovide`
--

-- --------------------------------------------------------

--
-- Table structure for table `AddressBook`
--

CREATE TABLE `AddressBook` (
  `ID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `FullName` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AddressOne` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AddressTwo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `City` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `State` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Zipcode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Country` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PrimaryAddress` tinyint(1) NOT NULL DEFAULT 0,
  `CompanyName` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Blacklist`
--

CREATE TABLE `Blacklist` (
  `ID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `Registration` tinyint(1) NOT NULL DEFAULT 0,
  `Login` tinyint(1) NOT NULL DEFAULT 0,
  `Payment` tinyint(1) NOT NULL DEFAULT 0,
  `Support` tinyint(1) NOT NULL DEFAULT 0,
  `IP` varchar(185) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Reason` varchar(185) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Expires` date NOT NULL,
  `Logged` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ContactRecords`
--

CREATE TABLE `ContactRecords` (
  `ID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `Identifier` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `FirstName` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LastName` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ErrorLogs`
--

CREATE TABLE `ErrorLogs` (
  `ID` int(6) NOT NULL,
  `Message` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `IP` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Posted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `LoginHistory`
--

CREATE TABLE `LoginHistory` (
  `ID` int(6) NOT NULL,
  `AccountID` int(6) NOT NULL,
  `IP` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Metadata` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Logged` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `PermissionGroups`
--

CREATE TABLE `PermissionGroups` (
  `ID` int(11) NOT NULL,
  `Name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Posted` timestamp NOT NULL DEFAULT current_timestamp(),
  `Permissions` longtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `RegistrationCodes`
--

CREATE TABLE `RegistrationCodes` (
  `ID` int(6) NOT NULL,
  `PermissionGroupID` int(6) NOT NULL,
  `Email` varchar(185) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Identity` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Posted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `RegistrationSettings`
--

CREATE TABLE `RegistrationSettings` (
  `ID` int(6) NOT NULL,
  `RequireRegistrationCode` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ResetLinks`
--

CREATE TABLE `ResetLinks` (
  `ID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `IP` varchar(185) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(185) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Posted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `SecurityLogs`
--

CREATE TABLE `SecurityLogs` (
  `ID` int(6) NOT NULL,
  `Subject` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `AccountID` int(6) DEFAULT NULL,
  `IP` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Posted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `VerificationCodes`
--

CREATE TABLE `VerificationCodes` (
  `ID` int(11) NOT NULL,
  `AccountID` int(11) NOT NULL,
  `IP` varchar(185) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(185) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Code` int(11) NOT NULL,
  `Posted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `AddressBook`
--
ALTER TABLE `AddressBook`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `AccountID` (`AccountID`);

--
-- Indexes for table `Blacklist`
--
ALTER TABLE `Blacklist`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `AccountID` (`AccountID`);

--
-- Indexes for table `ContactRecords`
--
ALTER TABLE `ContactRecords`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `AccountID` (`AccountID`);

--
-- Indexes for table `ErrorLogs`
--
ALTER TABLE `ErrorLogs`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `LoginHistory`
--
ALTER TABLE `LoginHistory`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `AccountID` (`AccountID`);

--
-- Indexes for table `PermissionGroups`
--
ALTER TABLE `PermissionGroups`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `RegistrationCodes`
--
ALTER TABLE `RegistrationCodes`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `Code` (`Code`),
  ADD KEY `PermissionGroupID` (`PermissionGroupID`);

--
-- Indexes for table `RegistrationSettings`
--
ALTER TABLE `RegistrationSettings`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `ResetLinks`
--
ALTER TABLE `ResetLinks`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Code` (`Code`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `SecurityLogs`
--
ALTER TABLE `SecurityLogs`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `VerificationCodes`
--
ALTER TABLE `VerificationCodes`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Code` (`Code`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `AddressBook`
--
ALTER TABLE `AddressBook`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Blacklist`
--
ALTER TABLE `Blacklist`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ContactRecords`
--
ALTER TABLE `ContactRecords`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ErrorLogs`
--
ALTER TABLE `ErrorLogs`
  MODIFY `ID` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `LoginHistory`
--
ALTER TABLE `LoginHistory`
  MODIFY `ID` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `PermissionGroups`
--
ALTER TABLE `PermissionGroups`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `RegistrationCodes`
--
ALTER TABLE `RegistrationCodes`
  MODIFY `ID` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `RegistrationSettings`
--
ALTER TABLE `RegistrationSettings`
  MODIFY `ID` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ResetLinks`
--
ALTER TABLE `ResetLinks`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `SecurityLogs`
--
ALTER TABLE `SecurityLogs`
  MODIFY `ID` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `VerificationCodes`
--
ALTER TABLE `VerificationCodes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `AddressBook`
--
ALTER TABLE `AddressBook`
  ADD CONSTRAINT `addressbook_ibfk_1` FOREIGN KEY (`AccountID`) REFERENCES `Accounts` (`AccountID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Blacklist`
--
ALTER TABLE `Blacklist`
  ADD CONSTRAINT `Blacklist_ibfk_1` FOREIGN KEY (`AccountID`) REFERENCES `Accounts` (`AccountID`);

--
-- Constraints for table `ContactRecords`
--
ALTER TABLE `ContactRecords`
  ADD CONSTRAINT `contactrecords_ibfk_1` FOREIGN KEY (`AccountID`) REFERENCES `Accounts` (`AccountID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `LoginHistory`
--
ALTER TABLE `LoginHistory`
  ADD CONSTRAINT `loginhistory_ibfk_1` FOREIGN KEY (`AccountID`) REFERENCES `Accounts` (`AccountID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `RegistrationCodes`
--
ALTER TABLE `RegistrationCodes`
  ADD CONSTRAINT `registrationcodes_ibfk_1` FOREIGN KEY (`PermissionGroupID`) REFERENCES `PermissionGroups` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
