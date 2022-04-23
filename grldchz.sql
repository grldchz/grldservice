USE grldchz;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
CREATE TABLE IF NOT EXISTS `contents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(20) NOT NULL,
  `comment` text NOT NULL,
  `create_date_time` datetime NOT NULL DEFAULT '2009-12-31 15:00:00',
  `modify_date_time` datetime NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `share_id` int(11) DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `legacy_id` int(10) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `num_photos` int(4) NOT NULL DEFAULT '0',
  `num_videos` int(4) NOT NULL DEFAULT '0',
  `open_public` tinyint(1) NOT NULL,
  `image_title` varchar(200) DEFAULT NULL
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------


--
-- Table structure for table `media`
--
DROP TABLE IF EXISTS `media`;

CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `file` varchar(200) NOT NULL,
  `title` varchar(200) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `num_hits` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------

--
-- Table structure for table `skillet`
--
DROP TABLE IF EXISTS `skillet`;

CREATE TABLE IF NOT EXISTS `skillet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(4) DEFAULT NULL,
  `friend_id` int(4) DEFAULT NULL,
  `hidden` tinyint(1) DEFAULT '0',
  `accepted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;
INSERT INTO `skillet` (`user_id`, `friend_id`, `hidden`, `accepted`) VALUES (1, 1, 0, 0);
INSERT INTO `skillet` (`user_id`, `friend_id`, `hidden`, `accepted`) VALUES (2, 2, 0, 0);
INSERT INTO `skillet` (`user_id`, `friend_id`, `hidden`, `accepted`) VALUES (1, 2, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `ip` varchar(14) NOT NULL,
  `create_date_time` datetime NOT NULL,
  `relationship` varchar(300) NOT NULL,
  `img_file` varchar(100) DEFAULT NULL,
  `img_caption` varchar(150) DEFAULT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `first_name` varchar(32) DEFAULT NULL,
  `last_name` varchar(32) DEFAULT NULL,
  `terms_accepted` tinyint(1) NOT NULL DEFAULT '0',
  `banner_img` varchar(100) DEFAULT NULL,
  `banner_margin_top` decimal(10,0) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `img_width` decimal(10,0) DEFAULT NULL,
  `img_height` decimal(10,0) DEFAULT NULL,
  `img_margin_left` decimal(10,0) DEFAULT NULL,
  `img_margin_top` decimal(10,0) DEFAULT NULL,
  `img_json` varchar(200) DEFAULT NULL,
  `banner_json` varchar(200) DEFAULT NULL,
  `device` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;
INSERT INTO `users` (`name`, `password`, `email`, `ip`, `create_date_time`, `img_file`, `img_caption`, `description`, `first_name`, `last_name`, `terms_accepted`, `banner_img`, `banner_margin_top`, `last_login`, `img_width`, `img_height`, `img_margin_left`, `img_margin_top`, `img_json`, `banner_json`) VALUES
('admin', '$2y$10$PkNA9Snt/vqdXbrEnJtNYOFoonE.fE6./3x2tahNw9xS80axH4WE6', 'admin@yourdomain.com', '127.0.0.1', '2016-09-21 19:18:00', '', NULL, 'Administration Account', 'Administration', 'Account', 0, NULL, '0', '2016-09-21 19:18:00', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `users` (`name`, `password`, `email`, `ip`, `create_date_time`, `img_file`, `img_caption`, `description`, `first_name`, `last_name`, `terms_accepted`, `banner_img`, `banner_margin_top`, `last_login`, `img_width`, `img_height`, `img_margin_left`, `img_margin_top`, `img_json`, `banner_json`) VALUES
('guest', '$2y$10$PkNA9Snt/vqdXbrEnJtNYOFoonE.fE6./3x2tahNw9xS80axH4WE6', 'guest@yourdomain.com', '127.0.0.1', '2016-09-21 19:18:00', '', NULL, 'Guest Account', 'Guest', 'Account', 1, NULL, '0', '2016-09-21 19:18:00', NULL, NULL, NULL, NULL, NULL, NULL);
-- the password is changeme
-- change it using genpass.php
-- copy and paste the result into the insert above
-- cmd> php genpass.php <newpassword>

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
