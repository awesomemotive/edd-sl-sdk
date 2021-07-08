# EDD Software Licensing SDK

## Example Usage

```php
use EDD_SL_SDK\SDK;

require_once 'sdk/Loader.php';

add_action( 'edd_sl_sdk_loaded', function ( SDK $sdk ) {
	try {
		$sdk->registerStore( array(
			'id' => 'sandhillsdev.com',
			'api_url' => 'https://sandhillsdev.com/wp-json/edd-sl/v2',
			'author'    => 'Sandhills Development, LLC',
			'products'  => array(
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
					'menu' => [
					    'parent_slug' => 'options-general.php',
					    'page_title'  => 'My Plugin License',
					    'menu_title'  => 'My Plugin License',
					    'menu_slug'   => 'my-plugin-license',
                    ],
                ],
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
