-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 16, 2019 at 08:47 AM
-- Server version: 5.7.24
-- PHP Version: 7.3.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `asgn1_contacts`
--
CREATE DATABASE IF NOT EXISTS `asgn1_contacts` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `asgn1_contacts`;

CREATE USER 'api_user'@'localhost' IDENTIFIED WITH mysql_native_password AS 'api_password';
GRANT USAGE ON *.* TO 'api_user'@'localhost' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
GRANT ALL PRIVILEGES ON `asgn1_contacts`.* TO 'api_user'@'localhost';

-- --------------------------------------------------------

--
-- Table structure for table `lst_contact_number_types`
--

CREATE TABLE `lst_contact_number_types` (
                                            `id` int(11) NOT NULL COMMENT 'Contact Number Type ID',
                                            `label` varchar(255) NOT NULL COMMENT 'Contact Number Type Label'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='List of all Contact Number Types';

--
-- Dumping data for table `lst_contact_number_types`
--

INSERT INTO `lst_contact_number_types` (`id`, `label`) VALUES
(1, 'Mobile'),
(2, 'Home'),
(3, 'Work');

-- --------------------------------------------------------

--
-- Table structure for table `lst_countries`
--

CREATE TABLE `lst_countries` (
                                 `id` smallint(6) NOT NULL COMMENT 'Country ID',
                                 `label` varchar(255) NOT NULL COMMENT 'Country Label'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='List of Countries';

--
-- Dumping data for table `lst_countries`
--

INSERT INTO `lst_countries` (`id`, `label`) VALUES
(1, 'Afghanistan'),
(2, 'Albania'),
(3, 'Algeria'),
(4, 'America'),
(5, 'Andorra'),
(6, 'Angola'),
(7, 'Antigua'),
(8, 'Argentina'),
(9, 'Armenia'),
(10, 'Australia'),
(11, 'Austria'),
(12, 'Azerbaijan'),
(13, 'Bahamas'),
(14, 'Bahrain'),
(15, 'Bangladesh'),
(16, 'Barbados'),
(17, 'Belarus'),
(18, 'Belgium'),
(19, 'Belize'),
(20, 'Benin'),
(21, 'Bhutan'),
(22, 'Bissau'),
(23, 'Bolivia'),
(24, 'Bosnia'),
(25, 'Botswana'),
(26, 'Brazil'),
(27, 'British'),
(28, 'Brunei'),
(29, 'Bulgaria'),
(30, 'Burkina'),
(31, 'Burma'),
(32, 'Burundi'),
(33, 'Cambodia'),
(34, 'Cameroon'),
(35, 'Canada'),
(36, 'Cape Verde'),
(37, 'Central African Republic'),
(38, 'Chad'),
(39, 'Chile'),
(40, 'China'),
(41, 'Colombia'),
(42, 'Comoros'),
(43, 'Congo'),
(44, 'Costa Rica'),
(45, 'country debt'),
(46, 'Croatia'),
(47, 'Cuba'),
(48, 'Cyprus'),
(49, 'Czech'),
(50, 'Denmark'),
(51, 'Djibouti'),
(52, 'Dominica'),
(53, 'East Timor'),
(54, 'Ecuador'),
(55, 'Egypt'),
(56, 'El Salvador'),
(57, 'Emirate'),
(58, 'England'),
(59, 'Eritrea'),
(60, 'Estonia'),
(61, 'Ethiopia'),
(62, 'Fiji'),
(63, 'Finland'),
(64, 'France'),
(65, 'Gabon'),
(66, 'Gambia'),
(67, 'Georgia'),
(68, 'Germany'),
(69, 'Ghana'),
(70, 'Great Britain'),
(71, 'Greece'),
(72, 'Grenada'),
(73, 'Grenadines'),
(74, 'Guatemala'),
(75, 'Guinea'),
(76, 'Guyana'),
(77, 'Haiti'),
(78, 'Herzegovina'),
(79, 'Honduras'),
(80, 'Hungary'),
(81, 'Iceland'),
(82, 'in usa'),
(83, 'India'),
(84, 'Indonesia'),
(85, 'Iran'),
(86, 'Iraq'),
(87, 'Ireland'),
(88, 'Israel'),
(89, 'Italy'),
(90, 'Ivory Coast'),
(91, 'Jamaica'),
(92, 'Japan'),
(93, 'Jordan'),
(94, 'Kazakhstan'),
(95, 'Kenya'),
(96, 'Kiribati'),
(97, 'Korea'),
(98, 'Kosovo'),
(99, 'Kuwait'),
(100, 'Kyrgyzstan'),
(101, 'Laos'),
(102, 'Latvia'),
(103, 'Lebanon'),
(104, 'Lesotho'),
(105, 'Liberia'),
(106, 'Libya'),
(107, 'Liechtenstein'),
(108, 'Lithuania'),
(109, 'Luxembourg'),
(110, 'Macedonia'),
(111, 'Madagascar'),
(112, 'Malawi'),
(113, 'Malaysia'),
(114, 'Maldives'),
(115, 'Mali'),
(116, 'Malta'),
(117, 'Marshall'),
(118, 'Mauritania'),
(119, 'Mauritius'),
(120, 'Mexico'),
(121, 'Micronesia'),
(122, 'Moldova'),
(123, 'Monaco'),
(124, 'Mongolia'),
(125, 'Montenegro'),
(126, 'Morocco'),
(127, 'Mozambique'),
(128, 'Myanmar'),
(129, 'Namibia'),
(130, 'Nauru'),
(131, 'Nepal'),
(132, 'Netherlands'),
(133, 'New Zealand'),
(134, 'Nicaragua'),
(135, 'Niger'),
(136, 'Nigeria'),
(137, 'Norway'),
(138, 'Oman'),
(139, 'Pakistan'),
(140, 'Palau'),
(141, 'Panama'),
(142, 'Papua'),
(143, 'Paraguay'),
(144, 'Peru'),
(145, 'Philippines'),
(146, 'Poland'),
(147, 'Portugal'),
(148, 'Qatar'),
(149, 'Romania'),
(150, 'Russia'),
(151, 'Rwanda'),
(173, 'Saint Kitts'),
(152, 'Samoa'),
(153, 'San Marino'),
(174, 'Santa Lucia'),
(154, 'Sao Tome'),
(155, 'Saudi Arabia'),
(156, 'scotland'),
(157, 'scottish'),
(158, 'Senegal'),
(159, 'Serbia'),
(160, 'Seychelles'),
(161, 'Sierra Leone'),
(162, 'Singapore'),
(163, 'Slovakia'),
(164, 'Slovenia'),
(165, 'Solomon'),
(166, 'Somalia'),
(167, 'South Africa'),
(168, 'South Sudan'),
(169, 'Spain'),
(170, 'Sri Lanka'),
(171, 'St Kitts'),
(172, 'St Lucia'),
(175, 'Sudan'),
(176, 'Suriname'),
(177, 'Swaziland'),
(178, 'Sweden'),
(179, 'Switzerland'),
(180, 'Syria'),
(181, 'Taiwan'),
(182, 'Tajikistan'),
(183, 'Tanzania'),
(184, 'Thailand'),
(185, 'Tobago'),
(186, 'Togo'),
(187, 'Tonga'),
(188, 'Trinidad'),
(189, 'Tunisia'),
(190, 'Turkey'),
(191, 'Turkmenistan'),
(192, 'Tuvalu'),
(193, 'Uganda'),
(194, 'Ukraine'),
(195, 'United Kingdom'),
(196, 'United States'),
(197, 'Uruguay'),
(198, 'USA'),
(199, 'Uzbekistan'),
(200, 'Vanuatu'),
(201, 'Vatican'),
(202, 'Venezuela'),
(203, 'Vietnam'),
(204, 'wales'),
(205, 'welsh'),
(206, 'Yemen'),
(207, 'Zambia'),
(208, 'Zimbabwe');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
                        `id` bigint(20) NOT NULL COMMENT 'User ID',
                        `username` varchar(255) NOT NULL COMMENT 'Unique Username',
                        `email` varchar(320) NOT NULL COMMENT 'Unique Email Address',
                        `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Case Sensitive Password Hash',
                        `first_name` varchar(255) NOT NULL COMMENT 'First Name',
                        `last_name` varchar(255) NOT NULL COMMENT 'Last Name',
                        `date_of_birth` date NOT NULL COMMENT 'Date of Birth',
                        `gender` enum('Male','Female','Other') DEFAULT NULL COMMENT 'Gender',
                        `country_id` smallint(6) NOT NULL COMMENT 'Country ID',
                        `is_active` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Indicates if this User Account is Active or not',
                        `login_attempts` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Number of consecutive failed Login Attempts',
                        `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date the User record was created',
                        `date_last_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date the Record was last updated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The System Users';

-- --------------------------------------------------------

--
-- Table structure for table `user_contact_number`
--

CREATE TABLE `user_contact_number` (
                                       `id` bigint(20) NOT NULL COMMENT 'User-Contact-number ID',
                                       `user_id` bigint(20) NOT NULL COMMENT 'Link to the User table',
                                       `country_code` varchar(10) NOT NULL COMMENT 'Country Code of the related phone number',
                                       `number` varchar(20) NOT NULL COMMENT 'The Phone Number',
                                       `type` tinyint(4) NOT NULL COMMENT 'The phone number Type',
                                       `is_primary` tinyint(4) NOT NULL COMMENT 'Indicates if this record is the primary number'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User Contact Numbers';

-- --------------------------------------------------------

--
-- Table structure for table `user_session`
--

CREATE TABLE `user_session` (
                                `id` bigint(20) NOT NULL COMMENT 'Session ID',
                                `user_id` bigint(20) NOT NULL COMMENT 'User ID',
                                `access_token` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Access Token',
                                `access_token_expiry` datetime NOT NULL COMMENT 'Access Token Expiry Date/Time',
                                `refresh_token` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Refresh Token',
                                `refresh_token_expiry` datetime NOT NULL COMMENT 'Refresh Token Expiry Date/Time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lst_contact_number_types`
--
ALTER TABLE `lst_contact_number_types`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lst_countries`
--
ALTER TABLE `lst_countries`
    ADD PRIMARY KEY (`id`),
    ADD KEY `idx_country_label` (`label`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `unq_username` (`username`),
    ADD UNIQUE KEY `unq_email` (`email`),
    ADD KEY `idx_password` (`password`),
    ADD KEY `idx_is_active` (`is_active`),
    ADD KEY `fk_user_country_id` (`country_id`);

--
-- Indexes for table `user_contact_number`
--
ALTER TABLE `user_contact_number`
    ADD PRIMARY KEY (`id`),
    ADD KEY `idx_contact_number_user_id` (`user_id`),
    ADD KEY `idx_contact_number_number` (`number`),
    ADD KEY `idx_contact_number_country_code` (`country_code`),
    ADD KEY `idx_countact_number_type` (`type`),
    ADD KEY `idx_is_primary_number` (`is_primary`);

--
-- Indexes for table `user_session`
--
ALTER TABLE `user_session`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `access_token` (`access_token`),
    ADD UNIQUE KEY `refresh_token` (`refresh_token`),
    ADD KEY `fk_user_session_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lst_contact_number_types`
--
ALTER TABLE `lst_contact_number_types`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Contact Number Type ID', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lst_countries`
--
ALTER TABLE `lst_countries`
    MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT 'Country ID', AUTO_INCREMENT=209;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
    MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'User ID', AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `user_contact_number`
--
ALTER TABLE `user_contact_number`
    MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'User-Contact-number ID', AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `user_session`
--
ALTER TABLE `user_session`
    MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Session ID', AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user`
--
ALTER TABLE `user`
    ADD CONSTRAINT `fk_user_country_id` FOREIGN KEY (`country_id`) REFERENCES `lst_countries` (`id`);

--
-- Constraints for table `user_contact_number`
--
ALTER TABLE `user_contact_number`
    ADD CONSTRAINT `fk_user_phone_number` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_session`
--
ALTER TABLE `user_session`
    ADD CONSTRAINT `fk_user_session_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
