<?php
/**
 * SDK.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 */

namespace EDD_SL_SDK;

use EDD_SL_SDK\Updates;

class SDK {

	/**
	 * @var SDK
	 */
	private static $instance;

	/**
	 * @var Store_Registry
	 */
	public $store_registry;

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
		self::$instance->setup_instance();

		return self::$instance;
	}

	/**
	 * Sets up a new instance.
	 */
	private function setup_instance() {
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

			$file_path = trailingslashit( dirname( __FILE__ ) ) . 'lib/' . $directory . $file_name . '.php';

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
		self::$instance->store_registry = new Store_Registry();

		Updates\Plugin_Updater::instance()->init();
	}

	/**
	 * Registers a new store.
	 *
	 * @param array  $args Array of arguments.
	 * @param string $id   Optional unique ID. If omitted, an ID is generated from the URL. Set this explicitly
	 *                     if you intend on using it.
	 *
	 * @since 1.0
	 */
	public function register_store( $args, $id = '' ) {
		if ( empty( $args['store_url'] ) ) {
			throw new \InvalidArgumentException( __( 'Missing required store_url argument.' ) );
		}

		if ( empty( $id ) ) {
			$id = sanitize_key( $args['store_url'] );
		}

		$this->store_registry->add_item( $id, $args );
	}

}
