<?php
/**
 * Plugin_Updater.php
 *
 * @todo      Base Updater class for both plugin & theme?
 *
 * @package   EDD_SL_SDK\Updates
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Updates;

use EDD_SL_SDK\SDK;
use EDD_SL_SDK\Store;
use EDD_SL_SDK\Store_API;
use EDD_SL_SDK\Traits\Singleton;

class Plugin_Updater extends Updater {

	use Singleton;

	/**
	 * Product type
	 *
	 * @since 1.0
	 * @var string
	 */
	private $type = 'plugin';

	/**
	 * Initializes hooks.
	 */
	public function init() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'show_version_details' ), 10, 3 );
	}

	/**
	 * Checks for plugin updates. This does one API request per store.
	 *
	 * @todo  Caching?
	 *
	 * @param object $transient_data
	 *
	 * @since 1.0
	 * @return object
	 */
	public function check_update( $transient_data ) {
		if ( ! is_object( $transient_data ) ) {
			$transient_data = new \stdClass();
		}

		// Get latest versions for each store.
		foreach ( SDK::instance()->store_registry->get_items() as $store_id => $store ) {
			/**
			 * @var Store $store
			 */

			$api           = new Store_API( $store );
			$store_plugins = $store->get_products( array(
				'type' => 'plugin'
			) );

			if ( empty( $store_plugins ) ) {
				continue;
			}

			try {
				$latest_versions = $api->check_versions( $store_plugins );
			} catch ( \Exception $e ) {
				continue;
			}

			$transient_data = $this->maybe_add_version_details( $transient_data, $latest_versions, $store_plugins );
		}

		return $transient_data;
	}

	/**
	 * Updates information on the "View version x.x details" page with custom data.
	 *
	 * @todo Caching
	 *
	 * @param object      $data
	 * @param string      $action
	 * @param object|null $args
	 *
	 * @since 1.0
	 * @return object
	 */
	public function show_version_details( $data, $action = '', $args = null ) {
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) ) {
			return $data;
		}

		foreach ( SDK::instance()->store_registry->get_items() as $store_id => $store ) {
			/**
			 * @var Store $store
			 */

			$api           = new Store_API( $store );
			$store_plugins = $store->get_products( array(
				'type' => 'plugin',
				'slug' => $args->slug
			) );

			if ( empty( $store_plugins ) ) {
				continue;
			}

			try {
				$latest_versions = $api->check_versions( $store_plugins );
			} catch ( \Exception $e ) {
				continue;
			}

			$data = (object) reset( $latest_versions );
		}

		return $data;
	}

}
