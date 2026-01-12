<?php
/*
Plugin Name: Be API - ACF Color Palette
Version: 1.1.1
Version Boilerplate: 3.5.0
Plugin URI: https://beapi.fr
Description: Add a new theme color palette selector field for Advanced Custom Fields.
Author: Be API Technical team
Author URI: https://beapi.fr
Domain Path: languages
Text Domain: beapi-acf-palette

----

Copyright 2021 Be API Technical team (human@beapi.fr)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Plugin constants
define( 'BEAPI_ACF_PALETTE_VERSION', '1.1.1' );
define( 'BEAPI_ACF_PALETTE_VIEWS_FOLDER_NAME', 'beapi-acf-palette' );

// Plugin URL and PATH
define( 'BEAPI_ACF_PALETTE_URL', plugin_dir_url( __FILE__ ) );
define( 'BEAPI_ACF_PALETTE_DIR', plugin_dir_path( __FILE__ ) );
define( 'BEAPI_ACF_PALETTE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Require vendor
if ( file_exists( BEAPI_ACF_PALETTE_DIR . '/vendor/autoload.php' ) ) {
	require BEAPI_ACF_PALETTE_DIR . '/vendor/autoload.php';
}

add_action( 'plugins_loaded', 'init_beapi_acf_palette_plugin' );
/**
 * Init the plugin
 */
function init_beapi_acf_palette_plugin(): void {
	// Client
	\BEAPI\Acf_Palette\Main::get_instance();

	// Theme Color Field
	\BEAPI\Acf_Palette\Theme_Color_Field::get_instance();
}
