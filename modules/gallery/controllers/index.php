<?php

namespace HB\Modules\Gallery\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Index extends Controller_Module
{
	public function page_block($block = 'index', $settings = [])
	{
		$value = $this->module->page_block($block, $settings);
		$settings = $value['settings'];
		$category_id = strpos($settings['block'], 'category:') === 0 ? (int)substr($settings['block'], 9) : 0;
		$galleries = array_values(array_filter($this->model()->all($category_id), function($gallery){ return !empty($gallery['published']); }));
		$this->css('gallery')->css('page-blocks');
		return $this->view('page_block', [
			'galleries' => $this->with_images(array_slice($galleries, 0, $settings['limit'])),
			'display' => $settings['display']
		]);
	}

	public function index($galleries)
	{
		$this->title($this->lang('Galeries'));
		$this->css('gallery');
		$galleries = $this->with_images($galleries);

		return $this->view('index', ['galleries' => $galleries]);
	}

	public function _category($category, $galleries)
	{
		$this->title($category['title'])->breadcrumb($category['title']);
		$this->css('gallery');
		$galleries = $this->with_images($galleries);

		return $this->view('index', [
			'category'  => $category,
			'galleries' => $galleries
		]);
	}

	public function _gallery($gallery, $images)
	{
		$this->title($gallery['title'])
			->breadcrumb($gallery['category_title'], $this->module->category_path($gallery['category_name']))
			->breadcrumb($gallery['title']);
		$this->css('gallery')->js('gallery');

		return $this->view('gallery', [
			'gallery' => $gallery,
			'images'  => $images
		]);
	}

	private function with_images($galleries)
	{
		foreach ($galleries as &$gallery)
		{
			$gallery['images'] = $this->model()->images($gallery['directory']);
		}
		unset($gallery);

		return $galleries;
	}
}
