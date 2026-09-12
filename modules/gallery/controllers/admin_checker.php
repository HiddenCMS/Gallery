<?php

namespace HB\Modules\Gallery\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module_Checker;

class Admin_Checker extends Module_Checker
{
	public function index()
	{
		return [$this->model()->all()];
	}

	public function add()
	{
		$this->authorize('add_gallery');
		return [];
	}

	public function _edit($gallery_id, $slug)
	{
		$this->authorize('modify_gallery');
		return ($gallery = $this->model()->check($gallery_id, $slug)) ? $gallery : NULL;
	}

	public function delete($gallery_id, $slug)
	{
		$this->authorize('delete_gallery', TRUE);
		return ($gallery = $this->model()->check($gallery_id, $slug)) ? [$gallery['gallery_id'], $gallery['title']] : NULL;
	}

	public function _categories_add()
	{
		$this->authorize('add_gallery_category');
		return [];
	}

	public function _categories_edit($category_id, $name)
	{
		$this->authorize('modify_gallery_category');
		return $this->model('categories')->check($category_id, $name);
	}

	public function _categories_delete($category_id, $name)
	{
		$this->authorize('delete_gallery_category', TRUE);
		return ($category = $this->model('categories')->check($category_id, $name)) ? [$category['category_id'], $category['title']] : NULL;
	}

	private function authorize($permission, $ajax = FALSE)
	{
		if (!$this->is_authorized($permission))
		{
			$this->error->unauthorized();
		}

		if ($ajax)
		{
			$this->ajax();
		}

	}
}
