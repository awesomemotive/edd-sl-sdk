# EDD Software Licensing SDK

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

## Example Usage

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
```

## Arguments

- `id` - Plugin/theme slug.
- `url` - The store URL.
- `item_id` - The item ID (on your store).
- `version` - The current version number.
- `file` - The main plugin file. Not needed for themes.
- `type` - `plugin` or `theme`. Not needed for plugins.
