(function($) {
	'use strict';

	function addColorPreviews() {
		// Attendre que Select2 soit complètement chargé
		setTimeout(function() {
			// Ajouter les couleurs aux options de la dropdown
			$('.select2-results__option').each(function() {
				var $option = $(this);
				var text = $option.text();

				// Vérifier si c'est une option de couleur (contient #)
				if (text.indexOf('#') !== -1 && !$option.find('.color-preview').length) {
					var colorMatch = text.match(/\(#([a-fA-F0-9]{6})\)/);
					if (colorMatch) {
						var colorValue = '#' + colorMatch[1];
						var colorPreview = $('<span class="color-preview" style="display: inline-block; width: 16px; height: 16px; border-radius: 50%; background-color: ' + colorValue + '; margin-right: 8px; border: 1px solid #ddd; vertical-align: middle;"></span>');
						$option.prepend(colorPreview);
					}
				}
			});

			// Ajouter les couleurs aux tags sélectionnés
			$('.select2-selection__choice').each(function() {
				var $choice = $(this);
				var text = $choice.text();

				if (text.indexOf('#') !== -1 && !$choice.find('.color-preview').length) {
					var colorMatch = text.match(/\(#([a-fA-F0-9]{6})\)/);
					if (colorMatch) {
						var colorValue = '#' + colorMatch[1];
						var colorPreview = $('<span class="color-preview" style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: ' + colorValue + '; margin-right: 4px; border: 1px solid #ddd; vertical-align: middle;"></span>');
						$choice.prepend(colorPreview);
					}
				}
			});
		}, 200);
	}

	// Initialiser au chargement de la page
	$(document).ready(function() {
		addColorPreviews();
	});

	// Initialiser quand ACF ajoute des champs
	$(document).on('acf/setup_fields', function() {
		addColorPreviews();
	});

	// Réinitialiser quand Select2 s'ouvre
	$(document).on('select2:open', function() {
		addColorPreviews();
	});

	// Réinitialiser quand Select2 se ferme et se rouvre
	$(document).on('select2:close', function() {
		setTimeout(function() {
			addColorPreviews();
		}, 100);
	});

	// Observer les changements dans le DOM pour les nouveaux éléments Select2
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
				$(mutation.addedNodes).each(function() {
					if ($(this).hasClass('select2-results__options') || $(this).hasClass('select2-selection__choice')) {
						addColorPreviews();
					}
				});
			}
		});
	});

	// Démarrer l'observation
	$(document).ready(function() {
		observer.observe(document.body, {
			childList: true,
			subtree: true
		});
	});

})(jQuery);
