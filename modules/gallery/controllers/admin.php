<?php

namespace HB\Modules\Gallery\Controllers;

use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;

class Admin extends Controller_Module
{
	public function index($galleries)
	{
		$this->title($this->lang('Galeries'));

		$gallery_table = $this->table()->add_columns([
			[
				'content' => function($data){
					return $data['published'] ? icon('fas fa-circle text-success', 'Publiée') : icon('far fa-circle text-muted', 'Non publiée');
				},
				'size' => TRUE
			],
			[
				'title'   => $this->lang('Titre'),
				'content' => function($data){
					return '<a href="'.url('admin/gallery/'.$data['gallery_id'].'/'.$data['slug']).'">'.utf8_htmlentities($data['title']).'</a>';
				},
				'search' => function($data){ return $data['title']; },
				'sort'   => function($data){ return $data['title']; }
			],
			[
				'title'   => $this->lang('Catégorie'),
				'content' => function($data){ return utf8_htmlentities($data['category_title']); },
				'search'  => function($data){ return $data['category_title']; },
				'sort'    => function($data){ return $data['category_title']; }
			],
			[
				'title'   => $this->lang('Dossier'),
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
		])->data($galleries)->no_data($this->lang('Aucune galerie'))->display();

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
		])->pagination(FALSE)->data($this->model('categories')->all())->no_data($this->lang('Aucune catégorie'))->display();

		return $this->row(
			$this->col($this->panel()->heading($this->lang('Catégories'), 'far fa-folder-open')->body($category_table)->footer_if($this->is_authorized('add_gallery_category'), $this->button_create('admin/gallery/categories/add', $this->lang('Créer une catégorie')))->size('col-12 col-lg-4')),
			$this->col($this->panel()->heading($this->lang('Galeries'), 'far fa-images')->body($gallery_table)->footer_if($this->is_authorized('add_gallery'), $this->button_create('admin/gallery/add', $this->lang('Créer une galerie')))->size('col-12 col-lg-8'))
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
		$this->form()->confirm_deletion($this->lang('Supprimer la galerie'), $this->lang('Êtes-vous sûr(e) de vouloir supprimer la galerie <b>%s</b> ? Les images resteront dans la médiathèque.', $title));

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
		$this->form()->confirm_deletion($this->lang('Supprimer la catégorie'), $this->lang('Êtes-vous sûr(e) de vouloir supprimer la catégorie <b>%s</b> et toutes ses galeries ? Les images resteront dans la médiathèque.', $title));

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
			->rule($this->form_text('title')->title('Titre')->value(isset($gallery['title']) ? $gallery['title'] : '')->required())
			->rule($this->form_select('category')->title('Catégorie')->data($this->model('categories')->choices())->value(isset($gallery['category_id']) ? $gallery['category_id'] : '')->search(0)->required())
			->rule($this->form_text('directory')->value(isset($gallery['directory']) ? $gallery['directory'] : '')->required()->size('hidden'))
			->info($this->module('files')->picker_directory_field('directory', isset($gallery['directory']) ? $gallery['directory'] : '', 'Dossier de la médiathèque', 'gallery-image'))
			->rule($this->form_editor('content_before')->title('Contenu avant les images')->value(isset($gallery['content_before']) ? $gallery['content_before'] : ''))
			->rule($this->form_editor('content_after')->title('Contenu après les images')->value(isset($gallery['content_after']) ? $gallery['content_after'] : ''))
			->rule($this->form_checkbox('published')->size('hb-switch-field')->data(['1' => 'Galerie publiée'])->value(!isset($gallery['published']) || $gallery['published'] ? ['1'] : []))
			->submit($editing ? 'Enregistrer' : 'Créer la galerie')
			->back('admin/gallery')
			->success(function($data) use ($editing, $gallery){
				$data['published'] = !empty($data['published']);

				if ($editing)
				{
					$this->model()->edit($gallery['gallery_id'], $data);
					notify('Galerie modifiée avec succès');
				}
				else
				{
					$this->model()->add($data);
					notify('Galerie créée avec succès');
				}

				redirect('admin/gallery');
			});

		$this->subtitle($editing ? $this->lang('Modifier la galerie') : $this->lang('Créer une galerie'));

		if (!$this->model('categories')->choices())
		{
			return $this->panel()->heading($this->lang('Galerie'), 'far fa-images')->body('<div class="ui warning message">'.$this->lang('Créez d’abord une catégorie avant d’ajouter une galerie.').'</div>'.$this->button_create('admin/gallery/categories/add', $this->lang('Créer une catégorie')));
		}

		return $form->panel();
	}

	private function category_form($category = [])
	{
		$editing = !empty($category['category_id']);
		$this->subtitle($editing ? $this->lang('Modifier la catégorie') : $this->lang('Créer une catégorie'));

		return $this->form2()
			->rule($this->form_text('title')->title('Nom')->value(isset($category['title']) ? $category['title'] : '')->required())
			->submit($editing ? 'Enregistrer' : 'Créer la catégorie')
			->back('admin/gallery')
			->success(function($data) use ($editing, $category){
				if ($editing)
				{
					$this->model('categories')->edit($category['category_id'], $data['title']);
					notify('Catégorie modifiée avec succès');
				}
				else
				{
					$this->model('categories')->add($data['title']);
					notify('Catégorie créée avec succès');
				}

				redirect('admin/gallery');
			})
			->panel();
	}
}
