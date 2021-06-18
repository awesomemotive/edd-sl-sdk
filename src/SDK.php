<?php
/**
 * SDK.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 */

namespace EDD_SL_SDK;

use EDD_SL_SDK\Exceptions\ItemNotFoundException;
use EDD_SL_SDK\Models\Store;
use EDD_SL_SDK\Registry\StoreRegistry;
use EDD_SL_SDK\Updates;

class SDK {

	/**
	 * @var SDK
	 */
	private static $instance;

	/**
	 * @var StoreRegistry
	 */
	public $storeRegistry;

	/**
	 * Returns the SDK instance.
	 *
	 * @return SDK
	 */
	public static function instance() {
		if ( self::$instance instanceof SDK ) {
			return self::$instance;
		}

		self::$instance = new SDK;
		self::$instance->setupInstance();

		return self::$instance;
	}

	/**
	 * Sets up a new instance.
	 */
	private function setupInstance() {
		self::$instance->autoload();
		self::$instance->init();
	}

	/**
	 * Autoload our files.
	 *
	 * @since 1.0
	 */
	private function autoload() {
		spl_autoload_register( function ( $class_name ) {
			$class_parts = explode( '\\', $class_name );

			if ( __NAMESPACE__ !== $class_parts[0] ) {
				return false;
			}

			array_shift( $class_parts );

			$file_name = array_pop( $class_parts );
			$directory = implode( DIRECTORY_SEPARATOR, $class_parts );

			if ( ! empty( $directory ) ) {
				$directory = trailingslashit( $directory );
			}

			$file_path = trailingslashit( dirname( __FILE__ ) ) . $directory . $file_name . '.php';

			if ( file_exists( $file_path ) ) {
				require $file_path;

				return true;
			}

			return false;
		} );
	}

	/**
	 * Initializes classes.
	 *
	 * @since 1.0
	 */
	private function init() {
		self::$instance->storeRegistry = new StoreRegistry();

		Updates\PluginUpdater::instance()->init();
		Updates\ThemeUpdater::instance()->init();
	}

	/**
	 * Registers a new store.
	 *
	 * @param array $args     {
	 *                        Array of arguments.
	 *
	 * @type string $id       Optional unique ID. If omitted, an ID is generated from the API URL.
	 *                     Set this explicitly if you intend on using it.
	 * @type string $api_url  Required. Software Licensing API endpoint.
	 * @type string $author   Optional. Name of the store.
	 * @type array  $products Optional. Array of products.
	 *                     }
	 *
	 * @since 1.0
	 * @return Store
	 * @throws \InvalidArgumentException
	 */
	public function registerStore( $args ) {
		if ( empty( $args['api_url'] ) ) {
			throw new \InvalidArgumentException( __( 'Missing required api_url argument.' ) );
		}

		if ( empty( $args['id'] ) ) {
			$args['id'] = strtolower( sanitize_key( trailingslashit( $args['api_url'] ) ) );
		}

		// If the store already exists, add products to it.
		try {
			$store = $this->storeRegistry->get( $args['id'] );

			if ( ! empty( $args['products'] ) && is_array( $args['products'] ) ) {
				foreach ( $args['products'] as $product_args ) {
					$store->addProduct( $product_args );
				}
			}

			return $store;
		} catch ( ItemNotFoundException $e ) {
			return $this->storeRegistry->addItem( $args['id'], $args );
		}
	}

}
