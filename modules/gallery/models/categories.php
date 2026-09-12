<?php

namespace HB\Modules\Gallery\Models;

use HB\HiddenCMS\Loadables\Model;

class Categories extends Model
{
	public function all()
	{
		return $this->db->select('c.category_id', 'c.name', 'cl.title', 'COUNT(g.gallery_id) as galleries')
			->from('gallery_categories c')
			->join('gallery_categories_lang cl', 'cl.category_id = c.category_id')
			->join('gallery g', 'g.category_id = c.category_id', 'LEFT')
			->where('cl.lang', $this->config->lang->info()->name)
			->group_by('c.category_id')
			->order_by('cl.title')
			->get();
	}

	public function choices()
	{
		$choices = [];

		foreach ($this->all() as $category)
		{
			$choices[$category['category_id']] = $category['title'];
		}

		return $choices;
	}

	public function check($category_id, $name)
	{
		return $this->db->select('c.category_id', 'c.name', 'cl.title')
			->from('gallery_categories c')
			->join('gallery_categories_lang cl', 'cl.category_id = c.category_id')
			->where('c.category_id', (int)$category_id)
			->where('c.name', $name)
			->where('cl.lang', $this->config->lang->info()->name)
			->row();
	}

	public function by_name($name)
	{
		return $this->db->select('c.category_id', 'c.name', 'cl.title')
			->from('gallery_categories c')
			->join('gallery_categories_lang cl', 'cl.category_id = c.category_id')
			->where('c.name', $name)
			->where('cl.lang', $this->config->lang->info()->name)
			->row();
	}

	public function add($title)
	{
		$category_id = $this->db->insert('gallery_categories', ['name' => $this->unique_name($title)]);
		$this->db->insert('gallery_categories_lang', [
			'category_id' => $category_id,
			'lang'        => $this->config->lang->info()->name,
			'title'       => $title
		]);
	}

	public function edit($category_id, $title)
	{
		$name = $this->unique_name($title, $category_id);

		$this->db->where('category_id', (int)$category_id)->update('gallery_categories', [
			'name' => $name
		]);
		$this->db->where('category_id', (int)$category_id)->where('lang', $this->config->lang->info()->name)->update('gallery_categories_lang', [
			'title' => $title
		]);
	}

	public function delete($category_id)
	{
		$this->db->where('category_id', (int)$category_id)->delete('gallery_categories');
	}

	private function unique_name($title, $category_id = 0)
	{
		$base = url_title($title) ?: 'categorie';
		$name = $base;
		$i = 2;

		do
		{
			$this->db->from('gallery_categories')->where('name', $name);

			if ($category_id)
			{
				$this->db->where('category_id <>', (int)$category_id);
			}

			$exists = !$this->db->empty();
			$name = $exists ? $base.'-'.$i++ : $name;
		}
		while ($exists);

		return $name;
	}
}
