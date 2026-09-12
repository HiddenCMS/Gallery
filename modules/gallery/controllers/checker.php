<?php

namespace HB\Modules\Gallery\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module_Checker;

class Checker extends Module_Checker
{
	public function index()
	{
		return [$this->model()->all()];
	}

	public function _category($name)
	{
		if ($category = $this->model('categories')->by_name($name))
		{
			return [$category, $this->model()->all($category['category_id'])];
		}
	}

	public function _gallery($category, $slug)
	{
		if ($gallery = $this->model()->by_route($category, $slug))
		{
			return [$gallery, $this->model()->images($gallery['directory'])];
		}
	}
}
