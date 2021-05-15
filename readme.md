# EDD Software Licensing SDK

## Example Usage

```php
use EDD_SL_SDK\SDK;

require_once 'sdk/Loader.php';

add_action( 'edd_sl_sdk_loaded', function ( SDK $sdk ) {
	try {
		$sdk->register_store( array(
			'id' => 'sandhillsdev.com',
			'api_url' => 'https://sandhillsdev.com/wp-json/edd-sl/v2',
			'author'    => 'Sandhills Development, LLC',
			'products'  => array(
				array(
					'type'      => 'plugin',
					'item_name' => 'My Product',
					'item_id'   => 123,
					'file'      => __FILE__,
					'version'   => '1.0',
					'license'   => '',
					'beta'      => false
				)
			)
		) );
	} catch ( \Exception $e ) {
		// Optionally do something error messages.
	}
} );
```
