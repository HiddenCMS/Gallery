<?php

namespace HiddenCMS\Gallery\Seeders;

use HB\HiddenCMS\Addons\Seeder;

class InstallDefaults implements Seeder
{
	public function run($db)
	{
		if (!$db->from('gallery_categories')->count())
		{
			$category_id = $db->insert_checked('gallery_categories', ['name' => 'general']);
			$db->insert_checked('gallery_categories_lang', [
				'category_id' => $category_id,
				'lang'        => 'en',
				'title'       => 'General'
			]);
		}

		$menu_id = $db->select('menu_id')->from('menus')->where('name', 'main')->row();

		if ($menu_id && !$db->from('menus_items')->where('menu_id', $menu_id)->where('url', 'gallery')->count())
		{
			$position = (int)$db->select('MAX(position)')->from('menus_items')->where('menu_id', $menu_id)->row();
			$db->insert_checked('menus_items', [
				'menu_id'   => $menu_id,
				'parent_id' => NULL,
				'title'     => 'Galleries',
				'url'       => 'gallery',
				'target'    => '_parent',
				'position'  => $position + 1,
				'enabled'   => TRUE
			]);
		}
	}
}
