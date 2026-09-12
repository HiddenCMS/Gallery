<section class="gallery-index" aria-labelledby="gallery-index-title">
	<?php $gallery_module = $this->module('gallery'); ?>
	<header class="gallery-index-header">
		<span class="gallery-index-icon"><?php echo icon('far fa-images') ?></span>
		<div>
			<h1 id="gallery-index-title"><?php echo isset($category) ? utf8_htmlentities($category['title']) : $this->lang('Galeries') ?></h1>
			<p><?php echo $this->lang('Parcourez les albums photo.') ?></p>
		</div>
	</header>

	<?php if ($galleries): ?>
	<div class="gallery-album-grid">
		<?php foreach ($galleries as $gallery): ?>
		<?php $images = $gallery['images']; ?>
		<a class="gallery-album" href="<?php echo url($gallery_module->gallery_path($gallery['category_name'], $gallery['slug'])) ?>">
			<span class="gallery-album-cover">
				<?php if ($images): ?>
				<img src="<?php echo utf8_htmlentities($images[0]['url']) ?>" alt="" loading="lazy" />
				<?php else: ?>
				<span class="gallery-album-empty"><?php echo icon('far fa-image') ?></span>
				<?php endif ?>
			</span>
			<span class="gallery-album-copy">
				<strong><?php echo utf8_htmlentities($gallery['title']) ?></strong>
				<span><?php echo (int)count($images).' '.$this->lang(count($images) > 1 ? 'photos' : 'photo') ?></span>
			</span>
		</a>
		<?php endforeach ?>
	</div>
	<?php else: ?>
	<div class="gallery-empty"><?php echo icon('far fa-images') ?><strong><?php echo $this->lang('Aucune galerie publiée pour le moment.') ?></strong></div>
	<?php endif ?>
</section>
