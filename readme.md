# EDD Software Licensing SDK

## Example Usage

```php
use EDD_SL_SDK\SDK;

require_once 'edd-sl-sdk-main/src/Loader.php';

add_action( 'edd_sl_sdk_loaded', function ( SDK $sdk ) {
	try {
		$sdk->registerStore( array(
			'id'       => 'sandhillsdev.com',
			'api_url'  => 'https://sandhillsdev.com/wp-json/edd-sl/v2',
			'author'   => 'Sandhills Development, LLC',
			'products' => array(
				/* Plugin Example */
				[
					'type'       => 'plugin',
					'product_id' => 123,
					'file'       => __FILE__,
					'version'    => '1.0',
					'beta'       => false,

					/*
					 * Optionally have an admin menu managed for you. 
					 * Accepts all the same arguments as `add_submenu_page()`
					 * @link https://developer.wordpress.org/reference/functions/add_submenu_page/
					 */
					'menu'       => [
						'parent_slug' => 'options-general.php',
						'page_title'  => 'My Plugin License',
						'menu_title'  => 'My Plugin License',
						'menu_slug'   => 'my-plugin-license',
					],
				],
				/* Theme Example */
				[
					'type'       => 'theme',
					'product_id' => 125,
					'beta'       => false
				]
			)
		) );
	} catch ( \Exception $e ) {
		// Optionally do something error messages.
	}
} );
```

## Store Arguments

- `id` - Optional. Unique ID for your store. If omitted, it's generated from your API URL.
- `api_url` - **Required.** API endpoint. Should be `https://yoursite.com/wp-json/edd-sl/v2`
- `author` - Optional. Plugin author name.
- `products` - Optional. Array of product registrations.
- `update_cache_duration` - Optional. Length of time, in seconds, to cache update API responses. Default is `180` (3 hours). Set to `0` to disable caching. If disabled, a fresh API request is made every time WordPress core checks for plugin/theme updates.

## Product Arguments

- `type` - **Required.** Either `plugin` or `theme`
- `product_id` - **Required.** ID of the product in your Software Licensing store.
- `file` - **Required for plugins.** Path to the main plugin file.
- `version` - **Required for plugins.** Current version number. If omitted for a theme, the version will be parsed from the stylesheet.
- `slug` - Optional. Name of the theme or plugin directory.
- `beta` - Optional. Whether to receive beta versions.
- `menu` - Optional. If set, the SDK will handle rendering an admin page UI, which does license activation and deactivation. This can be set to `true` for all default arguments, or can accept an array of any arguments used by [add_submenu_page()](https://developer.wordpress.org/reference/functions/add_submenu_page/).
- `license_option_name` - Optional. Name of the option used for saving the license key. By default, it's built using this format: `sl_{type}_{slug}_license`.
- `license_object_option_name` - Optional. Name of the option used for saving license data. This is the response data from the API when activating/checking a license key. By default, it's built using this format: `sl_{type}_{slug}_license_object`.
- `license_getter` - Optional. Closure used for retrieving the license key. This can be set if you do not want to save the license key in the options table (such as if you're using a custom table).
- `license_setter` - Optional. Closure used for setting the license key.
- `i18n` - Optional. Array of translation-ready strings. See transaction section for available strings.

### Custom getter & setter

Here's an example of how to use a custom getter and setter. In this example, the reason for using a getter and setter is that we're using custom `edd_get/update_option()` functions instead of the ones from WordPress core.

```php 
[
    'license_getter' => static function() {
        return edd_get_option( 'my_license_key' );
    },
    'license_setter' => static function ( $newLicense, $previousLicense ) {
        edd_update_option( 'my_license_key', sanitize_text_field( $newLicense ) );
    }
]
```

### Main plugin with add-ons example

If you sell "add-on" plugins to a main plugin (such as Easy Digital Downloads with its various extensions) then you can optionally only register your store in the main plugin file. Then each add-on adds a product to that existing store.

The benefit of this is that you only need to declare your API URL once in the "parent plugin".

Here's how that would look:

The parent plugin would register the store like this:

```php
add_action( 'edd_sl_sdk_loaded', function ( \EDD_SL_SDK\SDK $sdk ) {
	try {
		$sdk->registerStore( [
			// ID: Replace `yoursite.com` with the domain name of the site that has Software Licensing installed.
			'id'      => 'yoursite.com',
			// API URL: Replace `yoursite.com` with the domain of the site that has Software Licensing installed.
			'api_url' => 'https://yoursite.com/wp-json/edd-sl/v2',
			// Author: Your company's name.
			'author'  => 'Sandhills Development, LLC',
		] );
	} catch ( \Exception $e ) {

	}
} );
```

Note that if your parent plugin is also a product in itself and not hosted in the .org repo, you'll also need to register the parent plugin's product like so:

```php 
add_action( 'edd_sl_sdk_loaded', function ( \EDD_SL_SDK\SDK $sdk ) {
	try {
		$sdk->registerStore( [
			// ID: Replace `yoursite.com` with the domain name of the site that has Software Licensing installed.
			'id'       => 'yoursite.com',
			// API URL: Replace `yoursite.com` with the domain of the site that has Software Licensing installed.
			'api_url'  => 'https://yoursite.com/wp-json/edd-sl/v2',
			// Author: Your company's name.
			'author'   => 'Sandhills Development, LLC',
			'products' => [
				[
					'type'       => 'plugin',
					'product_id' => 123, // @todo replace
					'file'       => __FILE__,
					'version'    => '1.0', // @todo replace
				]
			]
		] );
	} catch ( \Exception $e ) {

	}
} );
```

Then, each add-on can skip the store registration and piggyback off the parent, like this:

```php 
add_action( 'edd_sl_after_store_registered', function ( \EDD_SL_SDK\Models\Store $store ) {
	if ( 'yoursite.com' === $store->id ) {
		try {
			$store->addProduct( [
				'type'       => 'plugin',
				'product_id' => 123, // @todo replace
				'file'       => __FILE__,
				'version'    => '1.0', // @todo replace
			] );
		} catch ( \Exception $e ) {

		}
	}
} );
```

This product will be registered to the store that was created in the parent plugin and use the same API URL.

## Strings

If using an admin menu, all the strings used for displaying statuses and response messages can be customized or made translation-ready.

The list of strings can be found here: https://github.com/easydigitaldownloads/edd-sl-sdk/blob/main/src/Helpers/Strings.php#L15-L30 Any strings in the array can be overridden via the `i18n` array when you register your product.

Example:

```php
[
    // Other options here.
    'i18n' => [
        'activate_license' => __( 'Activate License', 'my-plugin-text-domain' ),
        // More strings below, if desired.
    ] 
];
```
