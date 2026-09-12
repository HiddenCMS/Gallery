(function(){
	'use strict';

	document.querySelectorAll('[data-gallery]').forEach(function(gallery){
		var items = Array.prototype.slice.call(gallery.querySelectorAll('[data-gallery-image]'));
		var dialog = gallery.querySelector('[data-gallery-lightbox]');
		var masonry = gallery.querySelector('.gallery-masonry');
		var columnCount = 0;

		var arrange = function(){
			if (!masonry){ return; }
			var width = masonry.clientWidth;
			var count = window.innerWidth >= 1024 ? 4 : Math.max(1, Math.min(4, Math.floor((width + 14) / (window.innerWidth <= 640 ? 148 : 234))));
			if (count === columnCount){ return; }
			columnCount = count;
			var columns = [];
			masonry.replaceChildren();
			masonry.classList.add('gallery-masonry-arranged');
			for (var i = 0; i < count; i++){
				var column = document.createElement('div');
				column.className = 'gallery-masonry-column';
				masonry.appendChild(column);
				columns.push(column);
			}
			items.forEach(function(item, index){ columns[index % count].appendChild(item); });
		};
		arrange();
		if (masonry){ new ResizeObserver(arrange).observe(masonry); }

		if (!dialog || !items.length){
			return;
		}

		var image = dialog.querySelector('[data-gallery-large]');
		var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
		var animation;
		var current = 0;

		var show = function(index){
			var direction = index < current ? -1 : 1;
			current = (index + items.length) % items.length;
			var thumbnail = items[current].querySelector('img');
			dialog.style.setProperty('--gallery-image-ratio', (thumbnail.naturalWidth || thumbnail.width) / (thumbnail.naturalHeight || thumbnail.height) || 1);
			if (animation){ animation.cancel(); }
			image.onload = function(){
				dialog.style.setProperty('--gallery-image-ratio', image.naturalWidth / image.naturalHeight || 1);
				if (!reducedMotion.matches && dialog.open){
					animation = image.animate([{opacity: 0, transform: 'translateX('+(direction * 18)+'px)'}, {opacity: 1, transform: 'translateX(0)'}], {duration: 240, easing: 'ease-out'});
				}
			};
			image.src = items[current].getAttribute('data-src');
			image.alt = items[current].getAttribute('data-caption') || '';
		};

		items.forEach(function(item, index){
			item.addEventListener('click', function(){
				show(index);
				dialog.showModal();
				if (!reducedMotion.matches){
					dialog.animate([{opacity: 0}, {opacity: 1}], {duration: 220, easing: 'ease-out'});
				}
			});
		});

		dialog.querySelector('[data-gallery-close]').addEventListener('click', function(){ dialog.close(); });
		dialog.querySelector('[data-gallery-prev]').addEventListener('click', function(){ show(current - 1); });
		dialog.querySelector('[data-gallery-next]').addEventListener('click', function(){ show(current + 1); });
		dialog.addEventListener('click', function(event){
			if (event.target === dialog){ dialog.close(); }
		});
		dialog.addEventListener('keydown', function(event){
			if (event.key === 'ArrowLeft'){ show(current - 1); }
			if (event.key === 'ArrowRight'){ show(current + 1); }
		});
	});
})();
