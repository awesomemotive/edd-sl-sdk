<?php
/**
 * Plugin_Updater.php
 *
 * @todo Base Updater class for both plugin & theme?
 *
 * @package   EDD_SL_SDK\Updates
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 */

namespace EDD_SL_SDK\Updates;

use EDD_SL_SDK\SDK;
use EDD_SL_SDK\Store;
use EDD_SL_SDK\Store_API;
use EDD_SL_SDK\Traits\Singleton;

class Plugin_Updater {

	use Singleton;

	/**
	 * Initializes hooks.
	 */
	public function init() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
	}

	/**
	 * Checks for plugin updates. This does one API request per store.
	 *
	 * @param \stdClass $transient_data
	 *
	 * @since 1.0
	 * @return \stdClass
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

			$api = new Store_API( $store );

			try {
				$latest_versions = $api->check_version( $store->get_products( array(
					'type' => 'plugin'
				) ) );
			} catch ( \Exception $e ) {
				continue;
			}

			// @todo format
		}

		return $transient_data;
	}

	public function plugins_api_filter( $data, $action = '', $args = null ) {
		if ( 'plugin_information' !== $action ) {
			return $data;
		}

		return $data;
	}

}
