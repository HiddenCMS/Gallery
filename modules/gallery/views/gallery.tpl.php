<article class="gallery-page" data-gallery>
	<?php $gallery_module = $this->module('gallery'); ?>
	<header class="gallery-page-header">
		<p class="gallery-page-category"><a href="<?php echo url($gallery_module->category_path($gallery['category_name'])) ?>"><?php echo utf8_htmlentities($gallery['category_title']) ?></a></p>
		<h1><?php echo utf8_htmlentities($gallery['title']) ?></h1>
		<p class="gallery-page-count"><?php echo (int)count($images).' '.$this->lang(count($images) > 1 ? 'photos' : 'photo') ?></p>
	</header>

	<?php if (trim($gallery['content_before']) !== ''): ?>
	<div class="gallery-content gallery-content-before"><?php echo $gallery['content_before'] ?></div>
	<?php endif ?>

	<?php if ($images): ?>
	<div class="gallery-masonry">
		<?php foreach ($images as $index => $image): ?>
		<button type="button" class="gallery-image" data-gallery-image data-index="<?php echo (int)$index ?>" data-src="<?php echo utf8_htmlentities($image['url']) ?>" data-caption="<?php echo utf8_htmlentities($image['name']) ?>" aria-label="<?php echo $this->lang('Agrandir %s', utf8_htmlentities($image['name'])) ?>">
			<img src="<?php echo utf8_htmlentities($image['url']) ?>" alt="<?php echo utf8_htmlentities($image['name']) ?>" loading="lazy"<?php if ($image['width'] && $image['height']): ?> width="<?php echo (int)$image['width'] ?>" height="<?php echo (int)$image['height'] ?>"<?php endif ?> />
		</button>
		<?php endforeach ?>
	</div>
	<?php else: ?>
	<div class="gallery-empty"><?php echo icon('far fa-images') ?><strong><?php echo $this->lang('Ce dossier ne contient aucune image JPEG ou PNG.') ?></strong></div>
	<?php endif ?>

	<?php if (trim($gallery['content_after']) !== ''): ?>
	<div class="gallery-content gallery-content-after"><?php echo $gallery['content_after'] ?></div>
	<?php endif ?>

	<dialog class="gallery-lightbox" data-gallery-lightbox aria-label="<?php echo $this->lang('Visionneuse') ?>">
		<button type="button" class="gallery-lightbox-close" data-gallery-close aria-label="<?php echo $this->lang('Fermer') ?>"><?php echo icon('fas fa-times') ?></button>
		<button type="button" class="gallery-lightbox-nav gallery-lightbox-prev" data-gallery-prev aria-label="<?php echo $this->lang('Image précédente') ?>"><?php echo icon('fas fa-chevron-left') ?></button>
		<figure><img data-gallery-large src="" alt="" /></figure>
		<button type="button" class="gallery-lightbox-nav gallery-lightbox-next" data-gallery-next aria-label="<?php echo $this->lang('Image suivante') ?>"><?php echo icon('fas fa-chevron-right') ?></button>
	</dialog>
</article>
