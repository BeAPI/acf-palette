# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- New "Color Source" field setting to choose between three sources in theme.json:
  - `settings.color.palette` (default): Standard WordPress color palette
  - `custom.color`: Custom color definitions with slug as key and hex value
  - `both`: Combines both settings and custom colors (custom takes priority on conflicts)
- Automatic name generation from slugs for custom colors
  - Example: `environnement-400` → "Environnement 400"
  - Example: `services-publics-900` → "Services Publics 900"

### Changed

- Updated `get_theme_colors()` method to accept a `$source` parameter
- Split color retrieval logic into separate methods for better maintainability
- Updated README with documentation for the new color sources
- Include/Exclude color fields are now automatically reloaded with correct colors when source changes (via AJAX)

### Fixed

- Fixed AJAX handler to use proper capability checks and custom nonce validation
- Fixed custom colors retrieval to correctly read from root-level `custom.color` in theme.json
- Clarified documentation: `custom` section must be at root level of theme.json (same level as `settings`, `styles`)

### Technical

- Added `get_settings_colors()` private method for settings.color.palette
- Added `get_custom_colors()` private method for custom.color
- Added `get_both_colors()` private method for combining both sources
- Added `format_slug_to_name()` helper method for name generation
- Added `ajax_get_colors()` method to handle AJAX requests for dynamic color loading
- Added JavaScript AJAX handler to reload Include/Exclude color options on source change
- Added `wp_localize_script()` for passing AJAX URL and nonce to JavaScript
- All field methods now support the color_source parameter ('settings', 'custom', 'both')
- Changed color source field layout from horizontal to vertical for better readability
- Removed debug logs (console.log and error_log) from production code

## [1.0.0] - Previous release

- Initial release with settings.color.palette support
