<?php

namespace HB\Modules\Gallery;

use HB\HiddenCMS\Addons\Module;

class Gallery extends Module
{
	protected function __info()
	{
		return [
			'title'          => $this->lang('Galleries'),
			'description'    => $this->lang('Galleries populated with images from the media library.'),
			'icon'           => 'far fa-images',
			'author'         => 'HiddenCMS',
			'license'        => 'GPLv3',
			'admin'          => TRUE,
			'front'          => TRUE,
			'page_blocks'    => TRUE,
			'version'        => '0.2.1',
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
						'title'  => (string)$this->lang('Galleries'),
						'icon'   => 'far fa-images',
						'access' => [
							'add_gallery'    => ['title' => (string)$this->lang('Add'), 'icon' => 'fas fa-plus', 'admin' => TRUE],
							'modify_gallery' => ['title' => (string)$this->lang('Edit'), 'icon' => 'fas fa-edit', 'admin' => TRUE],
							'delete_gallery' => ['title' => (string)$this->lang('Delete'), 'icon' => 'far fa-trash-alt', 'admin' => TRUE]
						]
					],
					[
						'title'  => (string)$this->lang('Categories'),
						'icon'   => 'far fa-folder-open',
						'access' => [
							'add_gallery_category'    => ['title' => (string)$this->lang('Add'), 'icon' => 'fas fa-plus', 'admin' => TRUE],
							'modify_gallery_category' => ['title' => (string)$this->lang('Edit'), 'icon' => 'fas fa-edit', 'admin' => TRUE],
							'delete_gallery_category' => ['title' => (string)$this->lang('Delete'), 'icon' => 'far fa-trash-alt', 'admin' => TRUE]
						]
					]
				]
			]
		];
	}

	public function page_blocks()
	{
		$blocks = ['index' => ['title' => (string)$this->lang('All galleries'), 'icon' => 'far fa-images']];
		foreach ($this->model('categories')->all() as $category)
		{
			$blocks['category:'.$category['category_id']] = ['title' => utf8_html_entity_decode($category['title'], ENT_QUOTES), 'icon' => 'far fa-folder-open'];
		}
		foreach ($blocks as &$block)
		{
			$block['displays'] = [
				'cards' => ['title' => (string)$this->lang('Gallery cards'), 'icon' => 'fas fa-th-large'],
				'list' => ['title' => (string)$this->lang('Vertical list'), 'icon' => 'fas fa-list']
			];
			$block['fields'] = ['limit' => ['label' => (string)$this->lang('Number of visible galleries'), 'type' => 'number', 'default' => 6, 'min' => 1, 'max' => 24, 'step' => 1]];
		}
		unset($block);
		return $blocks;
	}

	public function page_block($block = 'index', $settings = [])
	{
		$blocks = $this->page_blocks();
		$block = isset($blocks[$block]) ? $block : 'index';
		return ['route' => '', 'settings' => [
			'block' => $block,
			'display' => isset($settings['display']) && $settings['display'] === 'list' ? 'list' : 'cards',
			'limit' => min(24, max(1, (int)($settings['limit'] ?? 6)))
		]];
	}

	public function page_block_form_value($block)
	{
		$settings = isset($block['settings']) && is_array($block['settings']) ? $block['settings'] : [];
		$value = $this->page_block($settings['block'] ?? 'index', $settings);
		return ['type' => 'module', 'module' => $this->info()->name, 'block' => $value['settings']['block'], 'settings' => $value['settings']];
	}

	public function page_block_content($block = 'index', $settings = [])
	{
		return $this->controller('index')->page_block($block, $settings);
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
			$links[$this->category_path($category['name'])] = (string)$this->lang('[Gallery category] %s', $category['title']);
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
