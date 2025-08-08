/**
 * Theme Color Field JavaScript
 */

(function($) {
	'use strict';

	/**
	 * Initialize theme color field functionality
	 */
	function initThemeColorField() {
		$('.acf-theme-color-field').each(function() {
			var $container = $(this);
			var $options = $container.find('.acf-theme-color-option');
			var $preview = $container.find('.acf-theme-color-preview');
			var $colorPreview = $preview.find('.color-preview');
			var $colorName = $preview.find('.color-name');

			// Handle radio button changes
			$options.find('input[type="radio"]').on('change', function() {
				var $selectedOption = $(this);
				var $option = $selectedOption.closest('.acf-theme-color-option');
				var colorValue = $option.find('.acf-theme-color-circle').css('background-color');
				var colorName = $option.find('.acf-theme-color-label').text();

				// Update preview
				if (colorValue && colorName) {
					$colorPreview.css('background-color', colorValue);
					$colorName.text(colorName);
					$preview.show();
				} else {
					$preview.hide();
				}

				// Update visual states
				$options.removeClass('selected');
				$option.addClass('selected');
			});

			// Handle option clicks (for better UX)
			$options.on('click', function(e) {
				// Don't trigger if clicking on the radio input itself
				if (!$(e.target).is('input[type="radio"]')) {
					$(this).find('input[type="radio"]').prop('checked', true).trigger('change');
				}
			});

			// Initialize selected state on load
			var $checkedInput = $options.find('input[type="radio"]:checked');
			if ($checkedInput.length) {
				$checkedInput.trigger('change');
			}

			// Add keyboard navigation
			$options.on('keydown', function(e) {
				var $currentOption = $(this);
				var $allOptions = $options;
				var currentIndex = $allOptions.index($currentOption);

				switch (e.keyCode) {
					case 37: // Left arrow
						e.preventDefault();
						var prevIndex = currentIndex > 0 ? currentIndex - 1 : $allOptions.length - 1;
						$allOptions.eq(prevIndex).find('input[type="radio"]').focus();
						break;
					case 39: // Right arrow
						e.preventDefault();
						var nextIndex = currentIndex < $allOptions.length - 1 ? currentIndex + 1 : 0;
						$allOptions.eq(nextIndex).find('input[type="radio"]').focus();
						break;
					case 32: // Space
					case 13: // Enter
						e.preventDefault();
						$currentOption.find('input[type="radio"]').prop('checked', true).trigger('change');
						break;
				}
			});
		});
	}

	// Initialize on document ready
	$(document).ready(function() {
		initThemeColorField();
	});

	// Initialize on ACF field load (for dynamic fields)
	$(document).on('acf/setup_fields', function() {
		initThemeColorField();
	});

})(jQuery);
