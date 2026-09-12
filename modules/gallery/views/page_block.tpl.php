<?php $gallery_module = $this->module('gallery'); ?>
<div class="gallery-album-grid gallery-page-block gallery-page-block-<?php echo $display ?>">
	<?php foreach ($galleries as $gallery): ?>
	<a class="gallery-album" href="<?php echo url($gallery_module->gallery_path($gallery['category_name'], $gallery['slug'])) ?>">
		<span class="gallery-album-cover">
			<?php if ($gallery['images']): ?>
			<img src="<?php echo utf8_htmlentities($gallery['images'][0]['url']) ?>" alt="" loading="lazy" />
			<?php else: ?>
			<span class="gallery-album-empty"><?php echo icon('far fa-image') ?></span>
			<?php endif ?>
		</span>
		<span class="gallery-album-copy"><strong><?php echo utf8_htmlentities($gallery['title']) ?></strong><span><?php echo (int)count($gallery['images']).' '.$this->lang(count($gallery['images']) > 1 ? 'photos' : 'photo') ?></span></span>
	</a>
	<?php endforeach ?>
	<?php if (!$galleries): ?>
	<p class="gallery-empty"><?php echo $this->lang('Aucune galerie publiée pour le moment.') ?></p>
	<?php endif ?>
</div>
