<?php

namespace HB\Modules\Gallery\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index($galleries)
	{
		$this->title($this->lang('Galleries'));

		$gallery_table = $this->table()->add_columns([
			[
				'content' => function($data){
					return $data['published'] ? icon('fas fa-circle text-success', (string)$this->lang('Published')) : icon('far fa-circle text-muted', (string)$this->lang('Unpublished'));
				},
				'size' => TRUE
			],
			[
				'title'   => $this->lang('Title'),
				'content' => function($data){
					return '<a href="'.url('admin/gallery/'.$data['gallery_id'].'/'.$data['slug']).'">'.utf8_htmlentities($data['title']).'</a>';
				},
				'search' => function($data){ return $data['title']; },
				'sort'   => function($data){ return $data['title']; }
			],
			[
				'title'   => $this->lang('Category'),
				'content' => function($data){ return utf8_htmlentities($data['category_title']); },
				'search'  => function($data){ return $data['category_title']; },
				'sort'    => function($data){ return $data['category_title']; }
			],
			[
				'title'   => $this->lang('Folder'),
				'content' => function($data){ return '<code>'.utf8_htmlentities($data['directory'] ?: '/').'</code>'; },
				'search'  => function($data){ return $data['directory']; }
			],
			[
				'content' => [
					function($data){ return $this->is_authorized('modify_gallery') ? $this->button_update('admin/gallery/'.$data['gallery_id'].'/'.$data['slug']) : ''; },
					function($data){ return $this->is_authorized('delete_gallery') ? $this->button_delete('admin/gallery/delete/'.$data['gallery_id'].'/'.$data['slug']) : ''; }
				],
				'size' => TRUE
			]
		])->data($galleries)->no_data($this->lang('No galleries'))->display();

		$category_table = $this->table()->add_columns([
			[
				'content' => function($data){ return '<a href="'.url('admin/gallery/categories/'.$data['category_id'].'/'.$data['name']).'">'.utf8_htmlentities($data['title']).'</a>'; },
				'search'  => function($data){ return $data['title']; },
				'sort'    => function($data){ return $data['title']; }
			],
			[
				'content' => function($data){ return '<span class="ui tiny label">'.(int)$data['galleries'].'</span>'; },
				'size'    => TRUE
			],
			[
				'content' => [
					function($data){ return $this->is_authorized('modify_gallery_category') ? $this->button_update('admin/gallery/categories/'.$data['category_id'].'/'.$data['name']) : ''; },
					function($data){ return $this->is_authorized('delete_gallery_category') ? $this->button_delete('admin/gallery/categories/delete/'.$data['category_id'].'/'.$data['name']) : ''; }
				],
				'size' => TRUE
			]
		])->pagination(FALSE)->data($this->model('categories')->all())->no_data($this->lang('No categories'))->display();

		return $this->row(
			$this->col($this->panel()->heading($this->lang('Categories'), 'far fa-folder-open')->body($category_table)->footer_if($this->is_authorized('add_gallery_category'), $this->button_create('admin/gallery/categories/add', $this->lang('Create a category')))->size('col-12 col-lg-4')),
			$this->col($this->panel()->heading($this->lang('Galleries'), 'far fa-images')->body($gallery_table)->footer_if($this->is_authorized('add_gallery'), $this->button_create('admin/gallery/add', $this->lang('Create a gallery')))->size('col-12 col-lg-8'))
		);
	}

	public function add()
	{
		return $this->gallery_form();
	}

	public function _edit($gallery_id, $category_id, $directory, $published, $date, $title, $slug, $content_before, $content_after, $category_name, $category_title)
	{
		return $this->gallery_form([
			'gallery_id'     => $gallery_id,
			'category_id'    => $category_id,
			'directory'      => $directory,
			'published'      => $published,
			'title'          => $title,
			'content_before' => $content_before,
			'content_after'  => $content_after
		]);
	}

	public function delete($gallery_id, $title)
	{
		$this->form()->confirm_deletion($this->lang('Delete gallery'), $this->lang('Are you sure you want to delete gallery <b>%s</b>? Images will remain in the media library.', $title));

		if ($this->form()->is_valid())
		{
			$this->model()->delete($gallery_id);
			return 'OK';
		}

		return $this->form()->display();
	}

	public function _categories_add()
	{
		return $this->category_form();
	}

	public function _categories_edit($category_id, $name, $title)
	{
		return $this->category_form(['category_id' => $category_id, 'title' => $title]);
	}

	public function _categories_delete($category_id, $title)
	{
		$this->form()->confirm_deletion($this->lang('Delete category'), $this->lang('Are you sure you want to delete category <b>%s</b> and all its galleries? Images will remain in the media library.', $title));

		if ($this->form()->is_valid())
		{
			$this->model('categories')->delete($category_id);
			return 'OK';
		}

		return $this->form()->display();
	}

	private function gallery_form($gallery = [])
	{
		$editing = !empty($gallery['gallery_id']);
		$form = $this->form2()
			->rule($this->form_text('title')->title('Title')->value(isset($gallery['title']) ? $gallery['title'] : '')->required())
			->rule($this->form_select('category')->title('Category')->data($this->model('categories')->choices())->value(isset($gallery['category_id']) ? $gallery['category_id'] : '')->search(0)->required())
			->rule($this->form_text('directory')->value(isset($gallery['directory']) ? $gallery['directory'] : '')->required()->size('gallery-directory-value'))
			->info('<style>.field.gallery-directory-value{display:none!important}</style>')
			->info($this->module('files')->picker_directory_field('directory', isset($gallery['directory']) ? $gallery['directory'] : '', (string)$this->lang('Media library folder'), 'gallery-image'))
			->rule($this->form_editor('content_before')->title((string)$this->lang('Content before images'))->value(isset($gallery['content_before']) ? $gallery['content_before'] : ''))
			->rule($this->form_editor('content_after')->title((string)$this->lang('Content after images'))->value(isset($gallery['content_after']) ? $gallery['content_after'] : ''))
			->rule($this->form_checkbox('published')->size('hb-switch-field')->data(['1' => (string)$this->lang('Publish')])->value(!isset($gallery['published']) || $gallery['published'] ? ['1'] : []))
			->submit($editing ? (string)$this->lang('Save') : (string)$this->lang('Create gallery'))
			->back('admin/gallery')
			->success(function($data) use ($editing, $gallery){
				$data['published'] = !empty($data['published']);

				if ($editing)
				{
					$this->model()->edit($gallery['gallery_id'], $data);
					notify((string)$this->lang('Gallery updated successfully'));
				}
				else
				{
					$this->model()->add($data);
					notify((string)$this->lang('Gallery created successfully'));
				}

				redirect('admin/gallery');
			});

		$this->subtitle($editing ? $this->lang('Edit gallery') : $this->lang('Create a gallery'));

		if (!$this->model('categories')->choices())
		{
			return $this->panel()->heading($this->lang('Gallery'), 'far fa-images')->body('<div class="ui warning message">'.$this->lang('Create a category before adding a gallery.').'</div>'.$this->button_create('admin/gallery/categories/add', $this->lang('Create a category')));
		}

		return $form->panel();
	}

	private function category_form($category = [])
	{
		$editing = !empty($category['category_id']);
		$this->subtitle($editing ? $this->lang('Edit category') : $this->lang('Create a category'));

		return $this->form2()
			->rule($this->form_text('title')->title('Name')->value(isset($category['title']) ? $category['title'] : '')->required())
			->submit($editing ? (string)$this->lang('Save') : (string)$this->lang('Create category'))
			->back('admin/gallery')
			->success(function($data) use ($editing, $category){
				if ($editing)
				{
					$this->model('categories')->edit($category['category_id'], $data['title']);
					notify((string)$this->lang('Category updated successfully'));
				}
				else
				{
					$this->model('categories')->add($data['title']);
					notify((string)$this->lang('Category created successfully'));
				}

				redirect('admin/gallery');
			})
			->panel();
	}
}
