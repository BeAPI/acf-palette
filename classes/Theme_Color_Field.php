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
		// Try multiple possible paths for theme.json
		$possible_paths = [
			get_template_directory() . '/src/theme-json/settings-color.json',
			get_theme_file_path( 'theme.json' ),
			get_stylesheet_directory() . '/src/theme-json/settings-color.json',
		];

		$theme_json_path = null;
		foreach ( $possible_paths as $path ) {
			if ( is_readable( $path ) ) {
				$theme_json_path = $path;
				break;
			}
		}

		if ( ! $theme_json_path ) {
			return [];
		}

		$theme_json_content = file_get_contents( $theme_json_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$theme_json         = json_decode( $theme_json_content, true );

		if ( ! $theme_json ) {
			return [];
		}

		// Get colors based on source
		if ( 'custom' === $source ) {
			return self::get_custom_colors( $theme_json );
		}

		if ( 'both' === $source ) {
			return self::get_both_colors( $theme_json );
		}

		return self::get_settings_colors( $theme_json );
	}

	/**
	 * Get colors from settings.color.palette
	 *
	 * @param array $theme_json Decoded theme.json content
	 * @return array Array of color options
	 */
	private static function get_settings_colors( array $theme_json ): array {
		if ( ! isset( $theme_json['settings']['color']['palette'] ) ) {
			return [];
		}

		$colors = [];
		foreach ( $theme_json['settings']['color']['palette'] as $color ) {
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
	 * Get colors from custom.color
	 *
	 * @param array $theme_json Decoded theme.json content
	 * @return array Array of color options
	 */
	private static function get_custom_colors( array $theme_json ): array {
		if ( ! isset( $theme_json['custom']['color'] ) || ! is_array( $theme_json['custom']['color'] ) ) {
			return [];
		}

		$colors = [];
		foreach ( $theme_json['custom']['color'] as $slug => $hex_color ) {
			// Generate a readable name from the slug
			$name = self::format_slug_to_name( $slug );

			$colors[ $slug ] = [
				'name'  => $name,
				'slug'  => $slug,
				'color' => $hex_color,
			];
		}

		return $colors;
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
