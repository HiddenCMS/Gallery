<?php

namespace HB\Modules\Gallery;

use HB\HiddenCMS\Addons\Module;

class Gallery extends Module
{
	protected function __info()
	{
		return [
			'title'          => $this->lang('Galeries'),
			'description'    => $this->lang('Galeries d’images alimentées par la médiathèque.'),
			'icon'           => 'far fa-images',
			'author'         => 'HiddenCMS',
			'license'        => 'GPLv3',
			'admin'          => TRUE,
			'front'          => TRUE,
			'version'        => '1.0',
			'reserved_route' => 'gallery',
			'routes'         => [
				'admin/categories/add'                     => '_categories_add',
				'admin/categories/delete/{id}/{url_title}' => '_categories_delete',
				'admin/categories/{id}/{url_title}'        => '_categories_edit',
				'admin/delete/{id}/{url_title}'            => 'delete',
				'admin/add'                                => 'add',
				'admin/{id}/{url_title}'                   => '_edit',
				'admin'                                    => 'index',
				'{url_title}/{url_title}'                  => '_gallery',
				'{url_title}'                              => '_category',
				''                                         => 'index'
			]
		];
	}

	public function permissions()
	{
		return [
			'default' => [
				'access' => [
					[
						'title'  => 'Galeries',
						'icon'   => 'far fa-images',
						'access' => [
							'add_gallery'    => ['title' => 'Ajouter', 'icon' => 'fas fa-plus', 'admin' => TRUE],
							'modify_gallery' => ['title' => 'Modifier', 'icon' => 'fas fa-edit', 'admin' => TRUE],
							'delete_gallery' => ['title' => 'Supprimer', 'icon' => 'far fa-trash-alt', 'admin' => TRUE]
						]
					],
					[
						'title'  => 'Catégories',
						'icon'   => 'far fa-folder-open',
						'access' => [
							'add_gallery_category'    => ['title' => 'Ajouter', 'icon' => 'fas fa-plus', 'admin' => TRUE],
							'modify_gallery_category' => ['title' => 'Modifier', 'icon' => 'fas fa-edit', 'admin' => TRUE],
							'delete_gallery_category' => ['title' => 'Supprimer', 'icon' => 'far fa-trash-alt', 'admin' => TRUE]
						]
					]
				]
			]
		];
	}

	public function gallery_path($category, $slug)
	{
		return $this->base_path().trim($category, '/').'/'.trim($slug, '/');
	}

	public function menu_links()
	{
		$links = [];
		foreach ($this->model('categories')->all() as $category)
		{
			$links[$this->category_path($category['name'])] = '[Catégorie de galeries] '.$category['title'];
		}
		foreach ($this->model()->all() as $gallery)
		{
			if ($gallery['published'])
			{
				$links[$this->gallery_path($gallery['category_name'], $gallery['slug'])] = '[Galerie] '.$gallery['title'];
			}
		}
		return $links;
	}

	public function category_path($category)
	{
		return $this->base_path().trim($category, '/');
	}

	public function index_path()
	{
		return rtrim($this->base_path(), '/');
	}

	private function base_path()
	{
		return trim($this->info()->reserved_route ?: $this->info()->name, '/').'/';
	}
}
