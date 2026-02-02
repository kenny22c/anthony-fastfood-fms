-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2026 at 05:49 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fastfood_klc`
--

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

CREATE TABLE `availability` (
  `availabilityID` int(11) NOT NULL,
  `dateTimeFrom` datetime DEFAULT NULL,
  `dateTimeTo` datetime DEFAULT NULL,
  `staffID` int(11) NOT NULL,
  `rosterID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `availability`
--

INSERT INTO `availability` (`availabilityID`, `dateTimeFrom`, `dateTimeTo`, `staffID`, `rosterID`) VALUES
(8, NULL, NULL, 1, 1),
(9, NULL, NULL, 1, 2),
(16, NULL, NULL, 3, 1),
(17, NULL, NULL, 3, 2),
(18, NULL, NULL, 3, 15),
(19, NULL, NULL, 3, 16),
(20, NULL, NULL, 3, 17),
(21, NULL, NULL, 5, 2),
(22, NULL, NULL, 5, 9),
(23, NULL, NULL, 5, 3);

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `roleID` int(11) NOT NULL,
  `name` varchar(15) NOT NULL,
  `description` varchar(30) NOT NULL,
  `ratehour` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`roleID`, `name`, `description`, `ratehour`) VALUES
(1, 'cook', 'works in kitchen', 50.50),
(2, 'waiter', 'front desk', 40.50),
(3, 'supervisor', 'of branch', 70.50),
(4, 'manager', 'of area', 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `roster`
--

CREATE TABLE `roster` (
  `rosterID` int(11) NOT NULL,
  `dateTimeFrom` datetime NOT NULL,
  `dateTimeTo` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roster`
--

INSERT INTO `roster` (`rosterID`, `dateTimeFrom`, `dateTimeTo`) VALUES
(1, '2023-05-01 08:00:00', '2023-05-01 17:00:00'),
(2, '2023-05-01 17:00:00', '2023-05-01 22:00:00'),
(3, '2023-05-02 08:00:00', '2023-05-02 17:00:00'),
(4, '2023-05-02 17:00:00', '2023-05-02 22:00:00'),
(5, '2023-05-03 08:00:00', '2023-05-03 17:00:00'),
(6, '2023-05-03 17:00:00', '2023-05-03 22:00:00'),
(7, '2023-05-04 08:00:00', '2023-05-04 17:00:00'),
(8, '2023-05-04 17:00:00', '2023-05-04 22:00:00'),
(9, '2023-05-02 08:00:00', '2023-05-02 12:00:00'),
(10, '2023-05-02 12:00:00', '2023-05-02 17:00:00'),
(11, '2023-05-03 08:00:00', '2023-05-03 17:00:00'),
(12, '2023-05-04 17:00:00', '2023-05-04 23:00:00'),
(13, '2023-05-05 08:00:00', '2023-05-05 14:00:00'),
(14, '2023-05-05 14:00:00', '2023-05-05 22:00:00'),
(15, '2025-12-07 17:00:00', '2025-12-07 22:00:00'),
(16, '2025-12-07 17:00:00', '2025-12-07 22:00:00'),
(17, '2025-12-07 17:00:00', '2025-12-07 22:00:00'),
(18, '2025-12-04 18:01:00', '2025-12-04 22:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `rosterrole`
--

CREATE TABLE `rosterrole` (
  `rosterRoleID` int(11) NOT NULL,
  `qty` int(3) NOT NULL,
  `rosterID` int(11) NOT NULL,
  `roleID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rosterrole`
--

INSERT INTO `rosterrole` (`rosterRoleID`, `qty`, `rosterID`, `roleID`) VALUES
(1, 5, 1, 1),
(2, 6, 1, 2),
(3, 5, 2, 1),
(4, 1, 2, 3),
(5, 3, 2, 2),
(6, 1, 1, 4),
(7, 5, 3, 1),
(8, 5, 4, 1),
(9, 5, 5, 1),
(10, 5, 6, 1),
(11, 5, 7, 1),
(12, 5, 8, 1),
(13, 5, 9, 1),
(14, 5, 10, 1),
(15, 5, 11, 1),
(16, 5, 12, 1),
(17, 5, 13, 1),
(18, 5, 14, 1),
(19, 5, 3, 2),
(20, 5, 4, 2),
(21, 5, 5, 2),
(22, 5, 6, 2),
(23, 5, 3, 3),
(24, 5, 4, 3),
(25, 5, 5, 3),
(26, 5, 6, 3);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staffID` int(11) NOT NULL,
  `firstName` varchar(50) DEFAULT NULL,
  `lastName` varchar(50) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `address` varchar(120) DEFAULT NULL,
  `dateOfBirth` date DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mob` varchar(15) DEFAULT NULL,
  `passwordHash` varchar(255) NOT NULL DEFAULT '',
  `roleID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staffID`, `firstName`, `lastName`, `name`, `address`, `dateOfBirth`, `email`, `mob`, `passwordHash`, `roleID`) VALUES
(1, 'Lionel', 'Messi', 'Lionel Messi', 'address1', '2000-01-01', 'email1@test.com', '01234567', '', 1),
(2, 'Serena', 'Williams', 'Serena Williams', 'address2', '2000-02-01', 'email2@test.com', '12345678', '', 1),
(3, 'Chris', 'Hemsworth', 'Chris Hemsworth', 'address3', '2000-03-01', 'staff3@test.com', '111111111', 'staff123', 1),
(4, 'Emma', 'Watson', 'Emma Watson', 'address4', '2000-04-01', 'email4@test.com', '34567891', '', 2),
(5, 'Robert', 'Downey Jr', 'Robert Downey Jr', 'address5', '2000-05-01', 'supervisor@test.com', '45678912', 'super123', 3),
(6, 'Taylor', 'Swift', 'Taylor Swift', 'address6', '2000-06-01', 'email6@test.com', '56789123', '', 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `availability`
--
ALTER TABLE `availability`
  ADD PRIMARY KEY (`availabilityID`),
  ADD UNIQUE KEY `ux_availability` (`staffID`,`rosterID`),
  ADD KEY `fk_availability_roster` (`rosterID`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`roleID`);

--
-- Indexes for table `roster`
--
ALTER TABLE `roster`
  ADD PRIMARY KEY (`rosterID`);

--
-- Indexes for table `rosterrole`
--
ALTER TABLE `rosterrole`
  ADD PRIMARY KEY (`rosterRoleID`),
  ADD UNIQUE KEY `ux_rosterrole` (`roleID`,`rosterID`),
  ADD KEY `fk_rosterrole_roster` (`rosterID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staffID`),
  ADD UNIQUE KEY `ux_staff_email` (`email`),
  ADD KEY `idx_staff_email` (`email`),
  ADD KEY `fk_staff_role` (`roleID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `availability`
--
ALTER TABLE `availability`
  MODIFY `availabilityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `roleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roster`
--
ALTER TABLE `roster`
  MODIFY `rosterID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `rosterrole`
--
ALTER TABLE `rosterrole`
  MODIFY `rosterRoleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staffID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `availability`
--
ALTER TABLE `availability`
  ADD CONSTRAINT `fk_availability_roster` FOREIGN KEY (`rosterID`) REFERENCES `roster` (`rosterID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_availability_staff` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rosterrole`
--
ALTER TABLE `rosterrole`
  ADD CONSTRAINT `fk_rosterrole_role` FOREIGN KEY (`roleID`) REFERENCES `role` (`roleID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rosterrole_roster` FOREIGN KEY (`rosterID`) REFERENCES `roster` (`rosterID`) ON UPDATE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `fk_staff_role` FOREIGN KEY (`roleID`) REFERENCES `role` (`roleID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
