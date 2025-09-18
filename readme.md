# EDD Software Licensing SDK

A drop-in solution for WordPress plugin and theme developers to quickly integrate Easy Digital Downloads Software Licensing into their products without complex setup or custom admin interfaces.

## Overview

The EDD Software Licensing SDK streamlines the process of adding licensing functionality to your WordPress plugins and themes. Instead of building custom settings pages and handling license validation manually, this SDK provides a complete licensing solution that seamlessly integrates with existing WordPress admin interfaces.

### Key Features

- **Zero-configuration licensing** - Add licensing support with just a few lines of code
- **Native WordPress integration** - License fields appear directly in plugin action links and theme admin menus
- **Automatic updates** - Handles secure update delivery for licensed products
- **No custom admin pages** - Uses WordPress's existing interface patterns
- **Flexible deployment** - Works as a standalone plugin or Composer package
- **Developer-friendly** - Minimal code required, maximum functionality provided

### How It Works

For **plugins**, the SDK adds a "Manage License" link directly in the plugin list on the Plugins admin screen. Clicking this link opens a modal where users can enter and activate their license key.

For **themes**, a "Theme License" menu item is automatically added to the Appearance menu, providing easy access to license management via the modal.

The SDK handles all the complex licensing logic behind the scenes:
- License key validation and activation
- Automatic update notifications and delivery
- License status tracking and renewal reminders
- Secure communication with your EDD store

### Perfect For

- Plugin developers who want to focus on features, not licensing infrastructure
- Theme authors looking for a professional licensing solution
- Developers transitioning from other licensing systems
- Anyone who wants licensing integration without reinventing the wheel

## Installation

You can run the SDK as a standalone plugin on your site, or install it as a Composer package in your theme or plugin:

```
{
  "name": "edd/edd-sample-plugin",
  "license": "GPL-2.0-or-later",
  "repositories": {
    "edd-sl-sdk": {
      "type": "vcs",
      "url": "git@github.com:awesomemotive/edd-sl-sdk.git"
    }
  },
  "require": {
    "easy-digital-downloads/edd-sl-sdk": "1.0.0"
  }
}

### Example Usage

Plugin:
```php
add_action(
	'edd_sl_sdk_registry',
	function ( $init ) {
		$init->register(
			array(
				'id'      => 'edd-sample-plugin', // The plugin slug.
				'url'     => 'https://edd.test', // The URL of the site with EDD installed.
				'item_id' => 83, // The download ID of the product in Easy Digital Downloads.
				'version' => '1.0.0', // The version of the product.
				'file'    => __FILE__, // The path to the main plugin file.
			)
		);
	}
);

if ( file_exists( __DIR__ . '/vendor/easy-digital-downloads/edd-sl-sdk/edd-sl-sdk.php' ) ) {
	require_once __DIR__ . '/vendor/easy-digital-downloads/edd-sl-sdk/edd-sl-sdk.php';
}
```

Theme:
```
add_action(
	'edd_sl_sdk_registry',
	function ( $init ) {
		$init->register(
			array(
				'id'      => 'edd-sample-theme',
				'url'     => 'https://easydigitaldownloads.com',
				'item_id' => 123,
				'version' => '1.0.0',
				'type'    => 'theme',
			)
		);
	}
);

if ( file_exists( __DIR__ . '/vendor/easy-digital-downloads/edd-sl-sdk/edd-sl-sdk.php' ) ) {
	require_once __DIR__ . '/vendor/easy-digital-downloads/edd-sl-sdk/edd-sl-sdk.php';
}
```

### Arguments

- `id` - Plugin/theme slug.
- `url` - The store URL.
- `item_id` - The item ID (on your store).
- `version` - The current version number.
- `file` - The main plugin file. Not needed for themes.
- `type` - `plugin` or `theme`. Not needed for plugins.
- `weekly_check` - Optional: whether to make a weekly request to confirm the license status. Defaults to true.

## Admin Notices

The SDK includes a `Notices` class for displaying admin notices. The registry automatically handles instantiation, so you can use the static `add()` method directly.

### Adding Notices

You can add notices statically from anywhere in your code before the `admin_notices` hook fires at priority 100:

```php
use EasyDigitalDownloads\Updater\Admin\Notices;

// Basic usage - can be called from anywhere
Notices::add( array(
    'id'      => 'my-notice-id',
    'type'    => 'success', // 'success', 'error', 'warning', 'info'
    'message' => 'Your license has been activated successfully!',
    'classes' => array( 'my-custom-class' ) // Optional additional CSS classes
) );

// Example: Add notice from an early hook (this works)
add_action( 'admin_init', function() {
    Notices::add( array(
        'id'      => 'early-notice',
        'type'    => 'info',
        'message' => 'Notice added during admin_init',
    ) );
} );

// Example: Add notice from admin_notices at lower priority (this works)
add_action( 'admin_notices', function() {
    Notices::add( array(
        'id'      => 'priority-notice',
        'type'    => 'warning',
        'message' => 'Notice added at default priority (10)',
    ) );
}, 10 ); // Priority 10 runs before our render at priority 100

// This would NOT work - priority 200 runs after our render at priority 100
add_action( 'admin_notices', function() {
    Notices::add( array(
        'id'      => 'too-late-notice',
        'type'    => 'error',
        'message' => 'This notice will not display!',
    ) );
}, 200 ); // Too late - render already happened at priority 100
```

The notices will be automatically displayed on admin pages. The registry takes care of instantiating the `Notices` class, and the `Notices` class handles rendering and styling according to WordPress admin notice standards.
