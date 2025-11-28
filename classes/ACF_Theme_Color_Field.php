<?php

namespace BEAPI\Acf_Palette;

/**
 * ACF Field Class for Theme Color
 *
 * @package BEAPI\Acf_Palette
 */
class ACF_Theme_Color_Field extends \acf_field {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name        = 'theme_color';
		$this->label       = __( 'Color palette', 'beapi-acf-palette' );
		$this->description = __( 'Select a color from the theme color palette', 'beapi-acf-palette' );
		$this->category    = 'advanced';
		$this->defaults    = [
			'allow_null'     => 0,
			'default_value'  => '',
			'return_format'  => 'value',
			'exclude_colors' => [],
			'include_colors' => [],
			'color_filter'   => 'exclude', // 'exclude' or 'include'
		];

		parent::__construct();
	}

	/**
	 * Render the field input
	 *
	 * @param array $field The field settings
	 */
	public function render_field( $field ) {
		$colors         = Theme_Color_Field::get_theme_colors();
		$exclude_colors = ! empty($field['exclude_colors']) ? $field['exclude_colors'] : [];
		$include_colors = ! empty($field['include_colors']) ? $field['include_colors'] : [];
		$color_filter   = $field['color_filter'] ?? 'exclude';

		// Filter colors based on the selected method
		if ( 'include' === $color_filter && ! empty( $include_colors ) ) {
			// Only show included colors
			$available_colors = array_intersect_key( $colors, array_flip( $include_colors ) );
		} else {
			// Filter out excluded colors (default behavior)
			$available_colors = array_diff_key( $colors, array_flip( $exclude_colors ) );
		}

		if ( empty( $available_colors ) ) {
			echo '<p>' . esc_html__( 'No colors available for selection.', 'beapi-acf-palette' ) . '</p>';
			return;
		}

		$field_value = $field['value'];
		$field_name  = $field['name'];
		$field_id    = $field['id'];

		// Get first color slug for auto-selection
		$first_color_slug = array_key_first( $available_colors );

		// If no value is set, auto-select the first available option
		if ( empty( $field_value ) ) {
			if ( $field['allow_null'] ) {
				// If allow_null is true, select the "No color" option
				$field_value = '';
			} elseif ( ! empty( $available_colors ) ) {
				// If allow_null is false, select the first available color
				$field_value = $first_color_slug;
			}
		}

		?>
		<div class="acf-theme-color-field">
			<div class="acf-theme-color-options">
				<?php if ( $field['allow_null'] ) : ?>
					<label class="acf-theme-color-option acf-theme-color-none">
						<input
							type="radio"
							name="<?php echo esc_attr( $field_name ); ?>"
							value=""
							<?php checked( $field_value, '' ); ?>
							class="acf-theme-color-input"
						>
						<div class="acf-theme-color-circle acf-theme-color-none-circle"></div>
						<span class="acf-theme-color-label"><?php echo esc_html__( 'No color', 'beapi-acf-palette' ); ?></span>
					</label>
				<?php endif; ?>
				<?php foreach ( $available_colors as $slug => $color_data ) : ?>
					<label class="acf-theme-color-option">
						<input
							type="radio"
							name="<?php echo esc_attr( $field_name ); ?>"
							value="<?php echo esc_attr( $slug ); ?>"
							<?php checked( $field_value, $slug ); ?>
							class="acf-theme-color-input"
						>
						<div class="acf-theme-color-circle" style="background-color: <?php echo esc_attr( $color_data['color'] ); ?>"></div>
						<span class="acf-theme-color-label"><?php echo esc_html( $color_data['name'] ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
			<div class="acf-theme-color-preview" style="display: none;">
				<div class="color-preview"></div>
				<span class="color-name"></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Render field settings
	 *
	 * @param array $field The field settings
	 */
	public function render_field_settings( $field ) {
		// Allow null
		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Allow Null?', 'beapi-acf-palette' ),
				'instructions' => __( 'Allow the field to have no value?', 'beapi-acf-palette' ),
				'name'         => 'allow_null',
				'type'         => 'true_false',
				'ui'           => 1,
			]
		);

		// Default value
		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Default Value', 'beapi-acf-palette' ),
				'instructions' => __( 'Enter a default color slug (e.g., primary-orange). Leave empty to auto-select first color.', 'beapi-acf-palette' ),
				'name'         => 'default_value',
				'type'         => 'text',
			]
		);

		// Color Filter Method setting
		acf_render_field_setting(
			$field,
			[
				'label'         => __( 'Color Filter Method', 'beapi-acf-palette' ),
				'instructions'  => __( 'Choose how to filter the available colors', 'beapi-acf-palette' ),
				'name'          => 'color_filter',
				'type'          => 'radio',
				'choices'       => [
					'exclude' => __( 'Exclude specific colors', 'beapi-acf-palette' ),
					'include' => __( 'Include only specific colors', 'beapi-acf-palette' ),
				],
				'layout'        => 'horizontal',
				'default_value' => 'exclude',
			]
		);

		// Exclude Colors setting
		$colors        = Theme_Color_Field::get_theme_colors();
		$color_choices = [];
		foreach ( $colors as $slug => $color_data ) {
			$color_choices[ $slug ] = $color_data['name'] . ' (' . $color_data['color'] . ')';
		}

		acf_render_field_setting(
			$field,
			[
				'label'             => __( 'Exclude Colors', 'beapi-acf-palette' ),
				'instructions'      => __( 'Select colors to exclude from the selection', 'beapi-acf-palette' ),
				'name'              => 'exclude_colors',
				'type'              => 'select',
				'multiple'          => 1,
				'ui'                => 1,
				'ajax'              => 0,
				'choices'           => $color_choices,
				'placeholder'       => __( 'Select colors to exclude...', 'beapi-acf-palette' ),
				'conditional_logic' => [
					[
						'field'    => 'color_filter',
						'operator' => '==',
						'value'    => 'exclude',
					],
				],
			]
		);

		// Include Colors setting
		acf_render_field_setting(
			$field,
			[
				'label'             => __( 'Include Colors', 'beapi-acf-palette' ),
				'instructions'      => __( 'Select colors to include in the selection (only these colors will be available)', 'beapi-acf-palette' ),
				'name'              => 'include_colors',
				'type'              => 'select',
				'multiple'          => 1,
				'ui'                => 1,
				'ajax'              => 0,
				'choices'           => $color_choices,
				'placeholder'       => __( 'Select colors to include...', 'beapi-acf-palette' ),
				'conditional_logic' => [
					[
						'field'    => 'color_filter',
						'operator' => '==',
						'value'    => 'include',
					],
				],
			]
		);

		// Return format
		acf_render_field_setting(
			$field,
			[
				'label'        => __( 'Return Format', 'beapi-acf-palette' ),
				'instructions' => __( 'Specify the returned value format', 'beapi-acf-palette' ),
				'name'         => 'return_format',
				'type'         => 'radio',
				'choices'      => [
					'value' => __( 'Value (Slug)', 'beapi-acf-palette' ),
					'hex'   => __( 'Hex Color', 'beapi-acf-palette' ),
					'label' => __( 'Label', 'beapi-acf-palette' ),
					'array' => __( 'Both (Array)', 'beapi-acf-palette' ),
				],
				'layout'       => 'horizontal',
			]
		);
	}

	/**
	 * Format the value for output
	 *
	 * @param mixed $value   The field value
	 * @param int   $post_id The post ID
	 * @param array $field   The field settings
	 * @return mixed
	 */
	public function format_value( $value, $post_id, $field ) {
		if ( empty( $value ) ) {
			return $value;
		}

		$colors     = Theme_Color_Field::get_theme_colors();
		$color_data = $colors[ $value ] ?? null;

		if ( ! $color_data ) {
			return $value;
		}

		switch ( $field['return_format'] ) {
			case 'hex':
				return $color_data['color'];
			case 'label':
				return $color_data['name'];
			case 'array':
				return [
					'value' => $value,
					'label' => $color_data['name'],
					'color' => $color_data['color'],
				];
			default:
				return $value;
		}
	}

	/**
	 * Update the value
	 *
	 * @param mixed $value   The field value
	 * @param int   $post_id The post ID
	 * @param array $field   The field settings
	 * @return mixed
	 */
	public function update_value( $value, $post_id, $field ) {
		$colors = Theme_Color_Field::get_theme_colors();

		if ( ! empty( $value ) && ! isset( $colors[ $value ] ) ) {
			return '';
		}

		return $value;
	}

	/**
	 * Load the value
	 *
	 * @param mixed $value   The field value
	 * @param int   $post_id The post ID
	 * @param array $field   The field settings
	 * @return mixed
	 */
	public function load_value( $value, $post_id, $field ) {
		$colors         = Theme_Color_Field::get_theme_colors();
		$exclude_colors = ! empty($field['exclude_colors']) ? $field['exclude_colors'] : [];
		$include_colors = ! empty($field['include_colors']) ? $field['include_colors'] : [];
		$color_filter   = $field['color_filter'] ?? 'exclude';

		// Filter colors based on the selected method
		if ( 'include' === $color_filter && ! empty( $include_colors ) ) {
			// Only show included colors
			$available_colors = array_intersect_key( $colors, array_flip( $include_colors ) );
		} else {
			// Filter out excluded colors (default behavior)
			$available_colors = array_diff_key( $colors, array_flip( $exclude_colors ) );
		}

		// If no value is set
		if ( empty( $value ) ) {
			// If default value is configured, use it (only if available)
			if ( ! empty( $field['default_value'] ) && isset( $available_colors[ $field['default_value'] ] ) ) {
				return $field['default_value'];
			}

			// If no default value and allow_null is false, auto-select first available color
			if ( empty( $field['allow_null'] ) && ! empty( $available_colors ) ) {
				return array_key_first( $available_colors );
			}
		}

		return $value;
	}
}
