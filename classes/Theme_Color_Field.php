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
		wp_enqueue_script(
			'beapi-acf-theme-color-field',
			BEAPI_ACF_PALETTE_URL . 'assets/js/theme-color-field.js',
			[ 'acf-input' ],
			BEAPI_ACF_PALETTE_VERSION,
			true
		);

		wp_enqueue_style(
			'beapi-acf-theme-color-field',
			BEAPI_ACF_PALETTE_URL . 'assets/css/theme-color-field.css',
			[],
			BEAPI_ACF_PALETTE_VERSION
		);
	}

	/**
	 * Enqueue admin styles for field settings
	 */
	public function enqueue_admin_styles(): void {
		wp_enqueue_style(
			'beapi-acf-admin-field-settings',
			BEAPI_ACF_PALETTE_URL . 'assets/css/admin-field-settings.css',
			[],
			BEAPI_ACF_PALETTE_VERSION
		);

		wp_enqueue_script(
			'beapi-acf-admin-field-settings',
			BEAPI_ACF_PALETTE_URL . 'assets/js/admin-field-settings.js',
			[ 'jquery', 'select2' ],
			BEAPI_ACF_PALETTE_VERSION,
			true
		);
	}

	/**
	 * Get theme colors from theme.json
	 *
	 * @return array Array of color options
	 */
	public static function get_theme_colors(): array {
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

		$theme_json_content = file_get_contents( $theme_json_path );
		$theme_json = json_decode( $theme_json_content, true );

		if ( ! $theme_json || ! isset( $theme_json['settings']['color']['palette'] ) ) {
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
	 * Get color options for ACF field
	 *
	 * @return array Array of color options for ACF
	 */
	public static function get_color_options(): array {
		$colors = self::get_theme_colors();
		$options = [];

		foreach ( $colors as $slug => $color_data ) {
			$options[ $slug ] = $color_data['name'];
		}

		return $options;
	}
}
