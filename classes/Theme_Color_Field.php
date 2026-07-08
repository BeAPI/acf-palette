<?php

namespace BEAPI\Acf_Palette;

/**
 * Custom ACF Field for Theme Colors
 *
 * This field retrieves colors from the current theme's theme.json file
 * and provides them as selectable options in ACF.
 *
 * @package BEAPI\Acf_Palette
 */
class Theme_Color_Field {

	/**
	 * Use the trait
	 */
	use Singleton;

	/**
	 * Initialize the theme color field functionality
	 */
	protected function init(): void {
		add_action( 'acf/include_field_types', [ $this, 'include_field' ] );
		add_action( 'acf/input/admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
		add_action( 'wp_ajax_acf_palette_get_colors', [ $this, 'ajax_get_colors' ] );
	}

	/**
	 * Include the field type
	 */
	public function include_field(): void {
		// Register the ACF field type
		acf_register_field_type( 'BEAPI\Acf_Palette\ACF_Theme_Color_Field' );
	}

	/**
	 * Enqueue scripts for the field
	 */
	public function enqueue_scripts(): void {
		$asset_file = BEAPI_ACF_PALETTE_DIR . 'build/index.asset.php';
		$asset      = file_exists( $asset_file ) ? require $asset_file : [
			'dependencies' => [],
			'version'      => BEAPI_ACF_PALETTE_VERSION,
		];

		wp_enqueue_style(
			'beapi-acf-theme-color-field',
			BEAPI_ACF_PALETTE_URL . 'build/style-index.css',
			[ 'acf-input', 'select2' ],
			$asset['version']
		);

		wp_enqueue_script(
			'beapi-acf-theme-color-field',
			BEAPI_ACF_PALETTE_URL . 'build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);
	}

	/**
	 * Enqueue admin styles for field settings
	 */
	public function enqueue_admin_styles(): void {
		$asset_file = BEAPI_ACF_PALETTE_DIR . 'build/editor.asset.php';
		$asset      = file_exists( $asset_file ) ? require $asset_file : [
			'dependencies' => [],
			'version'      => BEAPI_ACF_PALETTE_VERSION,
		];
		wp_enqueue_style(
			'beapi-acf-admin-field-settings',
			BEAPI_ACF_PALETTE_URL . 'build/editor.css',
			[],
			$asset['version']
		);

		wp_enqueue_script(
			'beapi-acf-admin-field-settings',
			BEAPI_ACF_PALETTE_URL . 'build/editor.js',
			array_merge( $asset['dependencies'], [ 'jquery' ] ),
			$asset['version'],
			true
		);

		// Localize script for AJAX
		wp_localize_script(
			'beapi-acf-admin-field-settings',
			'acfPaletteAdmin',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'acf_palette_nonce' ),
			]
		);
	}

	/**
	 * Get theme colors from theme.json
	 *
	 * @param string $source Source of colors: 'settings', 'custom', or 'both'
	 * @return array Array of color options
	 */
	public static function get_theme_colors( string $source = 'settings' ): array {
		$theme_json = self::load_theme_json_file();

		if ( ! $theme_json ) {
			return [];
		}

		if ( 'custom' === $source ) {
			return self::get_custom_colors( $theme_json );
		}

		if ( 'both' === $source ) {
			return self::get_both_colors( $theme_json );
		}

		return self::get_settings_colors( $theme_json );
	}

	/**
	 * Load the active theme theme.json file.
	 *
	 * @return array|null Decoded theme.json content
	 */
	private static function load_theme_json_file(): ?array {
		$theme_json_path = get_theme_file_path( 'theme.json' );

		if ( ! is_readable( $theme_json_path ) ) {
			return null;
		}

		$theme_json_content = file_get_contents( $theme_json_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$theme_json         = json_decode( $theme_json_content, true );

		return is_array( $theme_json ) ? $theme_json : null;
	}

	/**
	 * Extract palette colors from theme.json across supported versions.
	 *
	 * @param array $theme_json Decoded theme.json content.
	 * @return array Palette definition from theme.json.
	 */
	private static function extract_palette_from_theme_json( array $theme_json ): array {
		$palette = $theme_json['settings']['color']['palette']
			?? $theme_json['settings']['palette']
			?? $theme_json['color']['palette']
			?? [];

		return is_array( $palette ) ? $palette : [];
	}

	/**
	 * Extract custom color map from theme.json across supported versions.
	 *
	 * @param array $theme_json Decoded theme.json content.
	 * @return array Custom color map from theme.json.
	 */
	private static function extract_custom_color_map_from_theme_json( array $theme_json ): array {
		$custom_color_sources = [
			$theme_json['settings']['custom']['color'] ?? null,
			$theme_json['custom']['color'] ?? null,
			$theme_json['settings']['color']['custom'] ?? null,
		];

		foreach ( $custom_color_sources as $custom_colors ) {
			if ( ! is_array( $custom_colors ) || [] === $custom_colors ) {
				continue;
			}

			$normalized_colors = self::normalize_custom_color_map( $custom_colors );

			if ( [] !== $normalized_colors ) {
				return $normalized_colors;
			}
		}

		return [];
	}

	/**
	 * Normalize custom color values from theme.json.
	 *
	 * Supports string values and palette-like objects.
	 *
	 * @param array $custom_colors Custom color map from theme.json.
	 * @return array<string, string> Normalized slug => color map.
	 */
	private static function normalize_custom_color_map( array $custom_colors ): array {
		$normalized = [];

		foreach ( $custom_colors as $slug => $value ) {
			if ( ! is_string( $slug ) ) {
				continue;
			}

			$color = self::extract_color_value( $value );

			if ( null !== $color ) {
				$normalized[ $slug ] = $color;
			}
		}

		return $normalized;
	}

	/**
	 * Extract a color value from theme.json custom color definitions.
	 *
	 * @param mixed $value Color value from theme.json.
	 * @return string|null
	 */
	private static function extract_color_value( mixed $value ): ?string {
		if ( is_string( $value ) && '' !== $value ) {
			return $value;
		}

		if ( is_array( $value ) && isset( $value['color'] ) && is_string( $value['color'] ) && '' !== $value['color'] ) {
			return $value['color'];
		}

		return null;
	}

	/**
	 * Format palette colors from theme.json settings.color.palette.
	 *
	 * @param array $palette Palette definition from theme.json.
	 * @return array Array of color options
	 */
	private static function format_palette_colors( array $palette ): array {
		$colors = [];

		foreach ( $palette as $color ) {
			if ( isset( $color['name'], $color['slug'], $color['color'] ) ) {
				$colors[ $color['slug'] ] = [
					'name'  => $color['name'],
					'slug'  => $color['slug'],
					'color' => $color['color'],
				];
			}
		}

		return $colors;
	}

	/**
	 * Format custom colors from theme.json settings.custom.color.
	 *
	 * @param array $custom_colors Custom color map from theme.json.
	 * @return array Array of color options
	 */
	private static function format_custom_color_map( array $custom_colors ): array {
		$colors = [];

		foreach ( self::normalize_custom_color_map( $custom_colors ) as $slug => $hex_color ) {
			$colors[ $slug ] = [
				'name'  => self::format_slug_to_name( $slug ),
				'slug'  => $slug,
				'color' => $hex_color,
			];
		}

		return $colors;
	}

	/**
	 * Get colors from settings.color.palette
	 *
	 * @param array $theme_json Decoded theme.json content
	 * @return array Array of color options
	 */
	private static function get_settings_colors( array $theme_json ): array {
		return self::format_palette_colors( self::extract_palette_from_theme_json( $theme_json ) );
	}

	/**
	 * Get colors from settings.custom.color
	 *
	 * @param array $theme_json Decoded theme.json content
	 * @return array Array of color options
	 */
	private static function get_custom_colors( array $theme_json ): array {
		return self::format_custom_color_map( self::extract_custom_color_map_from_theme_json( $theme_json ) );
	}

	/**
	 * Get colors from both settings.color.palette and custom.color
	 *
	 * @param array $theme_json Decoded theme.json content
	 * @return array Array of color options
	 */
	private static function get_both_colors( array $theme_json ): array {
		$settings_colors = self::get_settings_colors( $theme_json );
		$custom_colors   = self::get_custom_colors( $theme_json );

		// Merge both arrays, custom colors will override settings colors if same slug
		return array_merge( $settings_colors, $custom_colors );
	}

	/**
	 * Format a slug to a readable name
	 * Converts 'environnement-400' to 'Environnement 400'
	 *
	 * @param string $slug The color slug
	 * @return string Formatted name
	 */
	private static function format_slug_to_name( string $slug ): string {
		// Replace hyphens and underscores with spaces
		$name = str_replace( [ '-', '_' ], ' ', $slug );

		// Capitalize first letter of each word
		$name = ucwords( $name );

		return $name;
	}

	/**
	 * Get color options for ACF field
	 *
	 * @return array Array of color options for ACF
	 */
	public static function get_color_options(): array {
		$colors  = self::get_theme_colors();
		$options = [];

		foreach ( $colors as $slug => $color_data ) {
			$options[ $slug ] = $color_data['name'];
		}

		return $options;
	}

	/**
	 * AJAX handler to get colors based on source
	 */
	public function ajax_get_colors(): void {
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions' ] );
			return;
		}

		// Verify nonce if provided
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'acf_palette_nonce' ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
			return;
		}

		// Get source from request
		$source = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : 'settings';

		// Validate source
		if ( ! in_array( $source, [ 'settings', 'custom', 'both' ], true ) ) {
			wp_send_json_error( [ 'message' => 'Invalid source' ] );
			return;
		}

		// Get colors
		$colors = self::get_theme_colors( $source );

		// Format for Select2
		$options = [];
		foreach ( $colors as $slug => $color_data ) {
			$options[] = [
				'id'   => $slug,
				'text' => $color_data['name'] . ' (' . $color_data['color'] . ')',
			];
		}

		wp_send_json_success( [ 'colors' => $options ] );
	}
}
