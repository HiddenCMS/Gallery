<?php

namespace HB\Modules\Gallery\Models;

use HB\HiddenCMS\Loadables\Model;

class Gallery extends Model
{
	public function all($category_id = 0)
	{
		$this->db->select('g.gallery_id', 'g.category_id', 'g.directory', 'g.published', 'g.date', 'gl.title', 'gl.slug', 'gl.content_before', 'gl.content_after', 'c.name as category_name', 'cl.title as category_title')
			->from('gallery g')
			->join('gallery_lang gl', 'gl.gallery_id = g.gallery_id')
			->join('gallery_categories c', 'c.category_id = g.category_id')
			->join('gallery_categories_lang cl', 'cl.category_id = c.category_id')
			->where('gl.lang', $this->config->lang->info()->name)
			->where('cl.lang', $this->config->lang->info()->name)
			->order_by('g.date DESC');

		if ($category_id)
		{
			$this->db->where('g.category_id', (int)$category_id);
		}

		if (!$this->url->admin)
		{
			$this->db->where('g.published', TRUE);
		}

		return $this->db->get();
	}

	public function check($gallery_id, $slug = '')
	{
		$this->db->select('g.gallery_id', 'g.category_id', 'g.directory', 'g.published', 'g.date', 'gl.title', 'gl.slug', 'gl.content_before', 'gl.content_after', 'c.name as category_name', 'cl.title as category_title')
			->from('gallery g')
			->join('gallery_lang gl', 'gl.gallery_id = g.gallery_id')
			->join('gallery_categories c', 'c.category_id = g.category_id')
			->join('gallery_categories_lang cl', 'cl.category_id = c.category_id')
			->where('g.gallery_id', (int)$gallery_id)
			->where('gl.lang', $this->config->lang->info()->name)
			->where('cl.lang', $this->config->lang->info()->name);

		if (!$this->url->admin)
		{
			$this->db->where('g.published', TRUE);
		}

		$gallery = $this->db->row();

		return $gallery && (!$slug || $gallery['slug'] === $slug) ? $gallery : FALSE;
	}

	public function by_route($category, $slug)
	{
		foreach ($this->all() as $gallery)
		{
			if ($gallery['category_name'] === $category && $gallery['slug'] === $slug)
			{
				return $gallery;
			}
		}

		return FALSE;
	}

	public function add($data)
	{
		$gallery_id = $this->db->insert('gallery', [
			'category_id' => (int)$data['category'],
			'directory'   => $this->directory($data['directory']),
			'published'   => !empty($data['published'])
		]);

		$this->db->insert('gallery_lang', [
			'gallery_id'     => $gallery_id,
			'lang'           => $this->config->lang->info()->name,
			'title'          => $data['title'],
			'slug'           => $this->unique_slug($data['title'], (int)$data['category']),
			'content_before' => $data['content_before'],
			'content_after'  => $data['content_after']
		]);
	}

	public function edit($gallery_id, $data)
	{
		$slug = $this->unique_slug($data['title'], (int)$data['category'], $gallery_id);

		$this->db->where('gallery_id', (int)$gallery_id)->update('gallery', [
			'category_id' => (int)$data['category'],
			'directory'   => $this->directory($data['directory']),
			'published'   => !empty($data['published'])
		]);

		$this->db->where('gallery_id', (int)$gallery_id)->where('lang', $this->config->lang->info()->name)->update('gallery_lang', [
			'title'          => $data['title'],
			'slug'           => $slug,
			'content_before' => $data['content_before'],
			'content_after'  => $data['content_after']
		]);
	}

	public function delete($gallery_id)
	{
		$this->db->where('gallery_id', (int)$gallery_id)->delete('gallery');
	}

	public function images($directory)
	{
		$directory = $this->directory($directory);
		$prefix = 'upload/files/'.($directory !== '' ? $directory.'/' : '');
		$images = [];

		foreach ($this->db->select('id', 'name', 'path')->from('file')->order_by('name')->get(FALSE) as $file)
		{
			$path = trim(str_replace('\\', '/', $file['path']), '/');
			$relative = strpos($path, $prefix) === 0 ? substr($path, strlen($prefix)) : NULL;

			if ($relative === NULL || strpos($relative, '/') !== FALSE || !in_array(strtolower(extension($path)), ['jpeg', 'jpg', 'png'], TRUE) || !is_file(HIDDENCMS_CMS.'/'.$path))
			{
				continue;
			}

			if (!$this->access('files', 'read_file', (int)$file['id']))
			{
				continue;
			}

			$size = @getimagesize(HIDDENCMS_CMS.'/'.$path);
			if (!$size || !in_array($size[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], TRUE))
			{
				continue;
			}
			$images[] = [
				'id'     => (int)$file['id'],
				'name'   => $file['name'],
				'url'    => url($path),
				'width'  => $size ? (int)$size[0] : 0,
				'height' => $size ? (int)$size[1] : 0
			];
		}

		return $images;
	}

	private function directory($directory)
	{
		$directory = trim(str_replace('\\', '/', utf8_html_entity_decode((string)$directory, ENT_QUOTES)), '/');

		if ($directory === '' || strpos('/'.$directory.'/', '/../') === FALSE)
		{
			return $directory;
		}

		return '';
	}

	private function unique_slug($title, $category_id, $gallery_id = 0)
	{
		$base = url_title($title) ?: 'galerie';
		$slug = $base;
		$i = 2;

		do
		{
			$this->db->from('gallery_lang gl')->join('gallery g', 'g.gallery_id = gl.gallery_id')->where('gl.lang', $this->config->lang->info()->name)->where('gl.slug', $slug)->where('g.category_id', $category_id);

			if ($gallery_id)
			{
				$this->db->where('g.gallery_id <>', (int)$gallery_id);
			}

			$exists = !$this->db->empty();
			$slug = $exists ? $base.'-'.$i++ : $slug;
		}
		while ($exists);

		return $slug;
	}
}
