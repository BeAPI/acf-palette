/* global jQuery, acf, MutationObserver, acfPaletteAdmin, Option */
import './editor.scss';

( function ( $ ) {
	'use strict';

	function addColorPreviews() {
		// Attendre que Select2 soit complètement chargé
		setTimeout( function () {
			// Ajouter les couleurs aux options de la dropdown
			$( '.select2-results__option' ).each( function () {
				const $option = $( this );
				const text = $option.text();

				// Vérifier si c'est une option de couleur (contient #)
				if (
					text.indexOf( '#' ) !== -1 &&
					! $option.find( '.color-preview' ).length
				) {
					const colorMatch = text.match( /\(#([a-fA-F0-9]{6})\)/ );
					if ( colorMatch ) {
						const colorValue = '#' + colorMatch[ 1 ];
						const colorPreview = $(
							'<span class="color-preview" style="display: inline-block; width: 16px; height: 16px; border-radius: 50%; background-color: ' +
								colorValue +
								'; margin-right: 8px; border: 1px solid #ddd; vertical-align: middle;"></span>'
						);
						$option.prepend( colorPreview );
					}
				}
			} );

			// Ajouter les couleurs aux tags sélectionnés
			$( '.select2-selection__choice' ).each( function () {
				const $choice = $( this );
				const text = $choice.text();

				if (
					text.indexOf( '#' ) !== -1 &&
					! $choice.find( '.color-preview' ).length
				) {
					const colorMatch = text.match( /\(#([a-fA-F0-9]{6})\)/ );
					if ( colorMatch ) {
						const colorValue = '#' + colorMatch[ 1 ];
						const colorPreview = $(
							'<span class="color-preview" style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: ' +
								colorValue +
								'; margin-right: 4px; border: 1px solid #ddd; vertical-align: middle;"></span>'
						);
						$choice.prepend( colorPreview );
					}
				}
			} );
		}, 200 );
	}

	// Initialiser au chargement de la page
	$( document ).ready( function () {
		addColorPreviews();
	} );

	// Initialiser quand ACF ajoute des champs
	$( document ).on( 'acf/setup_fields', function () {
		addColorPreviews();
	} );

	// Réinitialiser quand Select2 s'ouvre
	$( document ).on( 'select2:open', function () {
		addColorPreviews();
	} );

	// Réinitialiser quand Select2 se ferme et se rouvre
	$( document ).on( 'select2:close', function () {
		setTimeout( function () {
			addColorPreviews();
		}, 100 );
	} );

	// Observer les changements dans le DOM pour les nouveaux éléments Select2
	const observer = new MutationObserver( function ( mutations ) {
		mutations.forEach( function ( mutation ) {
			if (
				mutation.type === 'childList' &&
				mutation.addedNodes.length > 0
			) {
				$( mutation.addedNodes ).each( function () {
					if (
						$( this ).hasClass( 'select2-results__options' ) ||
						$( this ).hasClass( 'select2-selection__choice' )
					) {
						addColorPreviews();
					}
				} );
			}
		} );
	} );

	// Démarrer l'observation
	$( document ).ready( function () {
		observer.observe( document.body, {
			childList: true,
			subtree: true,
		} );
	} );

	/**
	 * Reset and reload Include/Exclude color fields when color source changes
	 *
	 * @param {Object} $field      - jQuery field object
	 * @param {string} colorSource - Selected color source (settings, custom, both)
	 */
	function resetColorFilters( $field, colorSource ) {
		// Find the exclude_colors and include_colors fields within this field group
		const $excludeField = $field
			.closest( '.acf-field-object' )
			.find( '[data-name="exclude_colors"]' );
		const $includeField = $field
			.closest( '.acf-field-object' )
			.find( '[data-name="include_colors"]' );

		if ( ! $excludeField.length && ! $includeField.length ) {
			return;
		}

		// Make AJAX request to get new colors
		$.ajax( {
			url: acfPaletteAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'acf_palette_get_colors',
				nonce: acfPaletteAdmin.nonce,
				source: colorSource,
			},
			success( response ) {
				if ( response.success && response.data.colors ) {
					// Update exclude_colors field
					if ( $excludeField.length ) {
						updateSelectOptions(
							$excludeField,
							response.data.colors
						);
					}

					// Update include_colors field
					if ( $includeField.length ) {
						updateSelectOptions(
							$includeField,
							response.data.colors
						);
					}
				}
			},
		} );
	}

	/**
	 * Update Select2 options
	 *
	 * @param {Object} $field  - jQuery field wrapper
	 * @param {Array}  options - New options array
	 */
	function updateSelectOptions( $field, options ) {
		const $select = $field.find( 'select' );
		if ( ! $select.length ) {
			return;
		}

		// Clear current selection
		$select.val( null );

		// Clear current options
		$select.empty();

		// Add new options
		options.forEach( function ( option ) {
			const newOption = new Option(
				option.text,
				option.id,
				false,
				false
			);
			$select.append( newOption );
		} );

		// Trigger change to update Select2
		$select.trigger( 'change' );

		// Reinitialize color previews
		addColorPreviews();
	}

	// Listen for color_source field changes
	$( document ).on(
		'change',
		'[data-name="color_source"] input[type="radio"]',
		function () {
			const $field = $( this ).closest( '[data-name="color_source"]' );
			const colorSource = $( this ).val();
			resetColorFilters( $field, colorSource );
		}
	);

	// ACF hook for field changes
	if ( typeof acf !== 'undefined' ) {
		acf.addAction( 'change_field_type=color_source', function ( $field ) {
			const colorSource = $field.find( 'input:checked' ).val();
			resetColorFilters( $field, colorSource );
		} );
	}
} )( jQuery );
