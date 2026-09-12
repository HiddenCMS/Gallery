<?php

namespace HiddenCMS\Gallery\Migrations;

use HB\HiddenCMS\Addons\Migration;

class CreateGalleryTables implements Migration
{
	public function up($db)
	{
		$db->execute_checked('CREATE TABLE IF NOT EXISTS `gallery_categories` (
			`category_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(100) NOT NULL,
			PRIMARY KEY (`category_id`),
			UNIQUE KEY `name` (`name`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

		$db->execute_checked('CREATE TABLE IF NOT EXISTS `gallery_categories_lang` (
			`category_id` int(11) unsigned NOT NULL,
			`lang` varchar(5) NOT NULL,
			`title` varchar(100) NOT NULL,
			PRIMARY KEY (`category_id`, `lang`),
			CONSTRAINT `gallery_categories_lang_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `gallery_categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

		$db->execute_checked('CREATE TABLE IF NOT EXISTS `gallery` (
			`gallery_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`category_id` int(11) unsigned NOT NULL,
			`directory` varchar(255) NOT NULL,
			`published` enum("0", "1") NOT NULL DEFAULT "0",
			`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`gallery_id`),
			KEY `category_id` (`category_id`),
			CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `gallery_categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

		$db->execute_checked('CREATE TABLE IF NOT EXISTS `gallery_lang` (
			`gallery_id` int(11) unsigned NOT NULL,
			`lang` varchar(5) NOT NULL,
			`title` varchar(150) NOT NULL,
			`slug` varchar(180) NOT NULL,
			`content_before` text NOT NULL,
			`content_after` text NOT NULL,
			PRIMARY KEY (`gallery_id`, `lang`),
			KEY `slug` (`slug`),
			CONSTRAINT `gallery_lang_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`gallery_id`) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
	}

	public function down($db)
	{
		$db->where('url', 'gallery')->delete('menus_items');
		$db->execute_checked('DROP TABLE IF EXISTS `gallery_lang`');
		$db->execute_checked('DROP TABLE IF EXISTS `gallery`');
		$db->execute_checked('DROP TABLE IF EXISTS `gallery_categories_lang`');
		$db->execute_checked('DROP TABLE IF EXISTS `gallery_categories`');
	}
}
