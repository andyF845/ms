CREATE DATABASE `sms` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `sms`;

CREATE TABLE IF NOT EXISTS `data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `memo` tinytext,
  `date` datetime DEFAULT NULL,
  `ip` tinytext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;