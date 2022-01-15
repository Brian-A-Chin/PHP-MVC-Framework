CREATE TABLE `PermissionGroups` (
`ID` int(6) NOT NULL AUTO_INCREMENT,
`Name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
`Permissions` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
`Posted` timestamp NOT NULL DEFAULT current_timestamp(),
PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `Accounts` (
`AccountID` int(6) NOT NULL AUTO_INCREMENT,
`Email` varchar(185) COLLATE utf8mb4_unicode_ci UNIQUE NOT NULL,
`Phone` varchar(20) COLLATE utf8mb4_unicode_ci UNIQUE NOT NULL,
`Password` varchar(185) COLLATE utf8mb4_unicode_ci NOT NULL,
`Complete` tinyint(1) NOT NULL DEFAULT 0,
`Status` tinyint(1) NOT NULL DEFAULT 1,
`Verified` tinyint(1) NOT NULL DEFAULT 0,
`PermissionGroupID` int(6) NOT NULL DEFAULT 1,
`JoinDate` timestamp NOT NULL DEFAULT current_timestamp(),
PRIMARY KEY (`AccountID`),
FOREIGN KEY (`PermissionGroupID`)
    REFERENCES PermissionGroups(`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE Accounts AUTO_INCREMENT=100;

CREATE TABLE `ContactRecords` (
`ID` int(6) NOT NULL AUTO_INCREMENT,
`AccountID` int(6) UNIQUE NOT NULL,
`Identifier` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
`FirstName` varchar(100) COLLATE utf8mb4_unicode_ci NULL,
`LastName` varchar(100) COLLATE utf8mb4_unicode_ci NULL,
PRIMARY KEY (`ID`),
FOREIGN KEY (`AccountID`)
  REFERENCES Accounts(`AccountID`)
  ON DELETE CASCADE
  ON Update CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `AccountSettings` (
`ID` int(6) NOT NULL AUTO_INCREMENT,
`AccountID` int(6) UNIQUE NOT NULL,
`2FAMethod` varchar(10) NOT NULL default 'Email',
PRIMARY KEY (`ID`),
FOREIGN KEY (`AccountID`)
  REFERENCES Accounts(`AccountID`)
  ON DELETE CASCADE
  ON Update CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ErrorLogs` (
`ID` int(6) NOT NULL AUTO_INCREMENT,
`Message` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
`Exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
`IP` varchar(50) COLLATE utf8mb4_unicode_ci NULL,
`Posted` timestamp NOT NULL DEFAULT current_timestamp(),
PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `SecurityLogs` (
`ID` int(6) NOT NULL AUTO_INCREMENT,
`Subject` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
`Message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
`AccountID` int(6) NULL,
`IP` varchar(50) COLLATE utf8mb4_unicode_ci NULL,
`Posted` timestamp NOT NULL DEFAULT current_timestamp(),
PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `LoginHistory` (
`ID` int(6) NOT NULL AUTO_INCREMENT,
`AccountID` int(6) NOT NULL,
`IP` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
`Metadata` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
`Logged` timestamp NOT NULL DEFAULT current_timestamp(),
PRIMARY KEY (`ID`,`IP`),
FOREIGN KEY (`AccountID`)
    REFERENCES Accounts(`AccountID`)
    ON DELETE CASCADE
    ON Update CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `RegistrationCodes` (
 `ID` int(6) NOT NULL AUTO_INCREMENT,
 `PermissionGroupID` int(6) NOT NULL,
 `Email` varchar(185) COLLATE utf8mb4_unicode_ci UNIQUE NOT NULL,
`Identity` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
 `Code` varchar(64) COLLATE utf8mb4_unicode_ci UNIQUE NOT NULL,
 `Posted` timestamp NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`ID`),
 FOREIGN KEY (`PermissionGroupID`)
     REFERENCES PermissionGroups(`ID`)
     ON DELETE CASCADE
     ON Update CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ResetLinks` (
`ID` int(6) NOT NULL AUTO_INCREMENT,
`AccountID` int(6) NOT NULL,
`IP` varchar(185) COLLATE utf8mb4_unicode_ci NOT NULL,
`Email` varchar(185) COLLATE utf8mb4_unicode_ci UNIQUE NOT NULL,
`Code` varchar(32) COLLATE utf8mb4_unicode_ci UNIQUE NOT NULL,
`Posted` timestamp NOT NULL DEFAULT current_timestamp(),
PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `VerificationCodes` (
`ID` int(6) NOT NULL AUTO_INCREMENT,
`AccountID` int(6) NOT NULL,
`IP` varchar(185) COLLATE utf8mb4_unicode_ci NOT NULL,
`Email` varchar(185) COLLATE utf8mb4_unicode_ci UNIQUE NOT NULL,
`Code` int(6) UNIQUE NOT NULL,
`Posted` timestamp NOT NULL DEFAULT current_timestamp(),
PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `AddressBook` (
`ID` int(6) NOT NULL AUTO_INCREMENT,
`AccountID` int(6) NOT NULL,
`CompanyName` varchar(150) COLLATE utf8mb4_unicode_ci NULL,
`FullName` varchar(150) COLLATE utf8mb4_unicode_ci NULL,
`AddressOne` varchar(150) COLLATE utf8mb4_unicode_ci NULL,
`AddressTwo` varchar(150) COLLATE utf8mb4_unicode_ci NULL,
`City` varchar(50) COLLATE utf8mb4_unicode_ci NULL,
`State` varchar(50) COLLATE utf8mb4_unicode_ci NULL,
`Zipcode` varchar(10) COLLATE utf8mb4_unicode_ci NULL,
`Phone` varchar(20) COLLATE utf8mb4_unicode_ci NULL,
`Country` varchar(50) COLLATE utf8mb4_unicode_ci NULL,
`PrimaryAddress` tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (`ID`),
FOREIGN KEY (`AccountID`)
  REFERENCES Accounts(`AccountID`)
  ON DELETE CASCADE
  ON Update CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `Blacklist` (
 `ID` int(6) NOT NULL AUTO_INCREMENT,
 `AccountID` int(6) NOT NULL,
 `Registration` tinyint(1) NOT NULL DEFAULT 0,
 `Login` tinyint(1) NOT NULL DEFAULT 0,
 `Payment` tinyint(1) NOT NULL DEFAULT 0,
 `Support` tinyint(1) NOT NULL DEFAULT 0,
 `IP` varchar(185) COLLATE utf8mb4_unicode_ci NOT NULL,
 `Reason` varchar(185) COLLATE utf8mb4_unicode_ci NULL,
 `Expires` date NOT NULL,
 `Logged` timestamp NOT NULL DEFAULT current_timestamp(),
 PRIMARY KEY (`ID`),
 FOREIGN KEY (`AccountID`)
     REFERENCES Accounts(`AccountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `RegistrationSettings` (
 `ID` int(6) NOT NULL AUTO_INCREMENT,
 `RequireRegistrationCode` boolean default false,
 PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;