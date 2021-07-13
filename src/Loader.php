<?php
/**
 * Loader.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 */

namespace EDD_SL_SDK;

if ( ! class_exists( '\\EDD_SL_SDK\\Loader' ) ) {
	class Loader {
		/**
		 * @var Loader
		 */
		private static $instance;

		/**
		 * Contains all registered SDKs.
		 *
		 * @var array
		 */
		private $registeredSdks = array();

		/**
		 * Contains information about the latest version of the SDK (version and path to file).
		 *
		 * @var array
		 */
		private $latestSdk = array();

		/**
		 * Returns an instance of Loader.
		 *
		 * @since 1.0
		 * @return Loader
		 */
		public static function instance() {
			if ( self::$instance instanceof Loader ) {
				return self::$instance;
			}

			self::$instance = new Loader;
			self::$instance->hooks();

			return self::$instance;
		}

		/**
		 * Registered hooks.
		 *
		 * @since 1.0
		 */
		public function hooks() {
			add_action( 'after_setup_theme', array( $this, 'setAndLoadLatest' ), 99999 );
		}

		/**
		 * Determines the latest version of the SDK and loads it.
		 *
		 * @since 1.0
		 */
		public function setAndLoadLatest() {
			foreach ( $this->registeredSdks as $registered_sdk ) {
				if ( $this->isLaterVersion( $registered_sdk ) ) {
					$this->latestSdk = $registered_sdk;
				}
			}

			if ( ! empty( $this->latestSdk['path'] ) && file_exists( $this->latestSdk['path'] ) ) {
				require_once $this->latestSdk['path'];

				if ( class_exists( '\\EDD_SL_SDK\\SDK' ) && ! did_action( 'edd_sl_sdk_loaded' ) ) {
					/**
					 * Triggers after the SDK has been loaded.
					 *
					 * @param SDK $sdk
					 *
					 * @since 1.0
					 */
					do_action( 'edd_sl_sdk_loaded', SDK::instance() );
				}
			}
		}

		/**
		 * Determines whether or not the provided SDK is later than the currently set.
		 *
		 * @param array $sdk
		 *
		 * @since 1.0
		 * @return bool
		 */
		private function isLaterVersion( $sdk ) {
			if ( empty( $sdk['version'] ) || empty( $sdk['path'] ) ) {
				return false;
			}

			if ( empty( $this->latestSdk ) ) {
				return true;
			}

			return version_compare( $sdk['version'], $this->latestSdk['version'], '>' );
		}

		/**
		 * Registers a version of the SDK.
		 *
		 * @param array $args    {
		 *                       SDK arguments.
		 *
		 * @type string $version Version of the SDK being registered.
		 * @type string $path    Path to the `SDK` class file.
		 *                    }
		 *
		 * @since 1.0
		 * @return Loader
		 */
		public function registerSdk( $args ) {
			$this->registeredSdks[] = $args;

			return $this;
		}
	}
}

Loader::instance()
	->registerSdk( array(
		'version' => '1.0',
		'path'    => dirname( __FILE__ ) . '/SDK.php'
	) );
