-- USE grldchz;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


-- --------------------------------------------------------

--
-- Table structure for table `contents`
--
DROP TABLE IF EXISTS `contents`;
CREATE TABLE `contents` (
  `id` int(11) NOT NULL,
  `user_name` varchar(20) NOT NULL,
  `comment` text NOT NULL,
  `create_date_time` datetime NOT NULL DEFAULT '2009-12-31 15:00:00',
  `modify_date_time` datetime NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `image` varchar(100) DEFAULT NULL,
  `legacy_id` int(10) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `num_photos` int(4) NOT NULL DEFAULT '0',
  `num_videos` int(4) NOT NULL DEFAULT '0',
  `share_id` int(11) DEFAULT NULL,
  `open_public` tinyint(1) NOT NULL DEFAULT '0',
  `image_title` varchar(200) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------


--
-- Table structure for table `media`
--
DROP TABLE IF EXISTS `media`;

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `file` varchar(200) NOT NULL,
  `title` varchar(200) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `num_hits` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `skillet`
--
DROP TABLE IF EXISTS `skillet`;

DROP TABLE IF EXISTS `skillet`;
CREATE TABLE `skillet` (
  `id` int(11) NOT NULL,
  `user_id` int(4) DEFAULT NULL,
  `friend_id` int(4) DEFAULT NULL,
  `hidden` tinyint(1) DEFAULT '0',
  `accepted` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(4) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `ip` varchar(14) NOT NULL,
  `device` varchar(200) DEFAULT NULL,
  `create_date_time` datetime NOT NULL,
  `relationship` varchar(300) DEFAULT NULL,
  `img_file` varchar(100) DEFAULT NULL,
  `img_caption` varchar(150) DEFAULT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `first_name` varchar(32) DEFAULT NULL,
  `last_name` varchar(32) DEFAULT NULL,
  `terms_accepted` tinyint(1) NOT NULL DEFAULT '1',
  `banner_img` varchar(100) DEFAULT NULL,
  `banner_margin_top` decimal(10,0) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `img_width` decimal(10,0) DEFAULT NULL,
  `img_height` decimal(10,0) DEFAULT NULL,
  `img_margin_left` decimal(10,0) DEFAULT NULL,
  `img_margin_top` decimal(10,0) DEFAULT NULL,
  `img_json` varchar(200) DEFAULT NULL,
  `banner_json` varchar(200) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `contents`
--
ALTER TABLE `contents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skillet`
--
ALTER TABLE `skillet`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contents`
--
ALTER TABLE `contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `skillet`
--
ALTER TABLE `skillet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;

INSERT INTO `users` (`name`, `password`, `email`, `ip`, `create_date_time`, `img_file`, `img_caption`, `description`, `first_name`, `last_name`, `terms_accepted`, `banner_img`, `banner_margin_top`, `last_login`, `img_width`, `img_height`, `img_margin_left`, `img_margin_top`, `img_json`, `banner_json`) VALUES
('admin', '$2y$10$PkNA9Snt/vqdXbrEnJtNYOFoonE.fE6./3x2tahNw9xS80axH4WE6', 'admin@yourdomain.com', '127.0.0.1', '2016-09-21 19:18:00', '', NULL, 'Administration Account', 'Administration', 'Account', 0, NULL, '0', '2016-09-21 19:18:00', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`name`, `password`, `email`, `ip`, `create_date_time`, `img_file`, `img_caption`, `description`, `first_name`, `last_name`, `terms_accepted`, `banner_img`, `banner_margin_top`, `last_login`, `img_width`, `img_height`, `img_margin_left`, `img_margin_top`, `img_json`, `banner_json`) VALUES
('guest', '$2y$10$PkNA9Snt/vqdXbrEnJtNYOFoonE.fE6./3x2tahNw9xS80axH4WE6', 'guest@yourdomain.com', '127.0.0.1', '2016-09-21 19:18:00', '', NULL, 'Guest Account', 'Guest', 'Account', 1, NULL, '0', '2016-09-21 19:18:00', NULL, NULL, NULL, NULL, NULL, NULL);
-- the password is changeme
-- change it using genpass.php
-- copy and paste the result into the insert above
-- cmd> php genpass.php <newpassword>
INSERT INTO `skillet` (`user_id`, `friend_id`, `hidden`, `accepted`) VALUES (1, 1, 0, 0);
INSERT INTO `skillet` (`user_id`, `friend_id`, `hidden`, `accepted`) VALUES (2, 2, 0, 0);
INSERT INTO `skillet` (`user_id`, `friend_id`, `hidden`, `accepted`) VALUES (1, 2, 0, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
