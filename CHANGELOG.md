# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

Nothing yet.

## [1.1.0] - 2026-01-12

### Added

- New "Color Source" field setting to choose between three sources in theme.json:
  - `settings.color.palette` (default): Standard WordPress color palette
  - `custom.color`: Custom color definitions with slug as key and hex value (must be at root level)
  - `both`: Combines both settings and custom colors (custom takes priority on conflicts)
- Automatic name generation from slugs for custom colors
  - Example: `environnement-400` → "Environnement 400"
  - Example: `services-publics-900` → "Services Publics 900"
- Dynamic reloading of Include/Exclude color options via AJAX when source changes
- AJAX endpoint `acf_palette_get_colors` for fetching colors based on selected source

### Changed

- Simplified Return Format options: removed "Label" option
  - **Slug**: Returns only the color slug (e.g., `"primary-orange"`)
  - **Hex Color**: Returns only the hex value (e.g., `"#FF6745"`)
  - **Both (Array)**: Returns array with `name`, `slug`, and `color`
- Updated Array format structure for consistency:
  - Before: `['value' => '...', 'label' => '...', 'color' => '...']`
  - After: `['name' => '...', 'slug' => '...', 'color' => '...']`
- Split color retrieval logic into separate methods (`get_settings_colors()`, `get_custom_colors()`, `get_both_colors()`)
- Changed color source field layout from horizontal to vertical for better readability
- Updated `get_theme_colors()` method to accept a `$source` parameter
- All field methods now support the `color_source` parameter

### Fixed

- Fixed AJAX handler to use proper capability checks and custom nonce validation
- Fixed custom colors retrieval to correctly read from root-level `custom.color` in theme.json
- Improved security with `acf_palette_nonce` instead of generic `acf_nonce`

### Technical

- Added `get_settings_colors()` private method for `settings.color.palette`
- Added `get_custom_colors()` private method for `custom.color`
- Added `get_both_colors()` private method for combining both sources
- Added `format_slug_to_name()` helper method for slug-to-name conversion
- Added `ajax_get_colors()` method to handle AJAX requests
- Added JavaScript AJAX handler in `editor.js` to reload color options dynamically
- Added `updateSelectOptions()` JavaScript function to refresh Select2 dropdowns
- Added `wp_localize_script()` for passing AJAX URL and nonce to JavaScript
- Removed debug logs (`console.log` and `error_log`) from production code

## [1.0.6] - 2025-11-28

### Fixed

- Updated export-ignore list in `.gitattributes` for cleaner releases

## [1.0.5] - 2025-11-28

### Changed

- Updated PHP version requirement to 8.3

### Fixed

- Fixed potential undefined array key warnings

## [1.0.4] - 2025-11-27

### Changed

- Updated plugin metadata and autoloader configuration

## [1.0.3] - 2025-11-27

### Added

- Added autoload configuration

## [1.0.2] - 2025-11-27

### Fixed

- Updated package metadata in `composer.json`

## [1.0.1] - 2025-11-27

### Added

- Automated release workflow setup
- Added `composer.lock` file

### Changed

- Removed Node.js build step from workflow
- Removed obsolete workflow configurations
- Set first radio option as selected by default

## [1.0.0] - 2025-08-08

### Added

- Initial release of ACF Color Palette plugin
- Custom ACF field type "Theme Color" for selecting colors from theme.json
- Support for `settings.color.palette` colors from theme.json
- Color preview with color circle and hex code
- Select2 integration for searchable dropdown
- Field settings:
  - Allow Null: Option to allow no color selection
  - Default Value: Set a default color
  - Color Filter Method: Choose between "Exclude" or "Include" colors
  - Exclude Colors: Exclude specific colors from selection
  - Include Colors: Only include specific colors
  - Return Format: Value (Slug), Hex Color, Label, or Array
- Return format options:
  - Value: Returns color slug
  - Hex: Returns hex color code
  - Label: Returns color name
  - Array: Returns complete color data
- Support for multiple theme.json file locations
- Color filtering based on inclusion/exclusion rules
- Automatic color selection (first color or "No color" if null allowed)
