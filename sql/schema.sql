-- Adminer 4.2.3 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';

CREATE TABLE `block` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Block ID',
  `background_color` varchar(50) COLLATE utf8_czech_ci NOT NULL COMMENT 'Content background color',
  `color` varchar(50) COLLATE utf8_czech_ci NOT NULL COMMENT 'Font color',
  `width` varchar(50) COLLATE utf8_czech_ci NOT NULL COMMENT 'Width of block',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `block_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID of record',
  `block_id` int(11) NOT NULL COMMENT 'ID of block',
  `lang` varchar(5) COLLATE utf8_czech_ci NOT NULL COMMENT 'Lang of content',
  `content` longtext COLLATE utf8_czech_ci NOT NULL COMMENT 'Text content',
  PRIMARY KEY (`id`),
  UNIQUE KEY `block_id_lang` (`block_id`,`lang`),
  CONSTRAINT `block_content_ibfk_1` FOREIGN KEY (`block_id`) REFERENCES `block` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `block_pic` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `path` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Path to picture',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `footer_pic` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id',
  `path` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Cesta k souboru',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `menu_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID of record (needed in subitems)',
  `lang` varchar(5) COLLATE utf8_czech_ci NOT NULL COMMENT 'Language shortcut',
  `link` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Link to web',
  `title` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Frontend title',
  `alt` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Alt on hover',
  `level` int(11) NOT NULL COMMENT 'Level nesting',
  `order` int(11) NOT NULL COMMENT 'Order in menu',
  `submenu` int(11) NOT NULL COMMENT 'ID of this menu item',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang_link` (`lang`,`link`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `page_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `menu_item_id` int(11) NOT NULL COMMENT 'ID of men item',
  `block_id` int(11) NOT NULL COMMENT 'ID of block',
  `order` int(11) NOT NULL COMMENT 'Order of item in',
  PRIMARY KEY (`id`),
  KEY `menu_item_id` (`menu_item_id`),
  KEY `block_id` (`block_id`),
  CONSTRAINT `page_content_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_item` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `slider_pic` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `path` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Cesta k souboru',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `slider_setting` (
  `id` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'ID položky (inputu)',
  `value` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Uložená hodnota',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `email` varchar(255) NOT NULL COMMENT 'Přihlašovací jméno (email)',
  `password` char(255) NOT NULL COMMENT 'Heslo',
  `role` int(2) NOT NULL COMMENT 'Role v číselném vyjádření',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Je uživatel aktivní?',
  `register_timestamp` datetime NOT NULL COMMENT 'Kdy by uživatel registrován',
  `last_login` datetime NOT NULL COMMENT 'Poslední přihlášení',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `web_config` (
  `id` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Identifikace položky (název inputu)',
  `lang` varchar(5) COLLATE utf8_czech_ci NOT NULL COMMENT 'Identifikace jazyka',
  `value` text COLLATE utf8_czech_ci NOT NULL COMMENT 'Uložená hodnota',
  UNIQUE KEY `lang_id` (`lang`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

CREATE TABLE `header_pic` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id',
  `path` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Cesta k souboru',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


-- 2016-06-30 13:20:58