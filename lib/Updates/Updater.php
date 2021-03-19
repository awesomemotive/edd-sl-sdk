<?php
/**
 * Updater.php
 *
 * @package   EDD_SL_SDK\Updates
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Updates;

use EDD_SL_SDK\Product;
use EDD_SL_SDK\SDK;
use EDD_SL_SDK\Store;
use EDD_SL_SDK\Store_API;

abstract class Updater {

	/**
	 * Product type
	 *
	 * @since 1.0
	 * @var string
	 */
	private $type;

	/**
	 * Initialize hooks
	 *
	 * @since 1.0
	 * @return void
	 */
	abstract public function init();

	/**
	 * Checks for product updates. This does one API request per store.
	 *
	 * @todo  Caching?
	 *
	 * @param object $transient_data
	 *
	 * @since 1.0
	 * @return object
	 */
	public function check_updates( $transient_data ) {
		if ( ! is_object( $transient_data ) ) {
			$transient_data = new \stdClass();
		}

		// Get latest versions for each store.
		foreach ( SDK::instance()->store_registry->get_items() as $store_id => $store ) {
			/**
			 * @var Store $store
			 */

			$api           = new Store_API( $store );
			$store_products = $store->get_products( array(
				'type' => $this->type
			) );

			if ( empty( $store_products ) ) {
				continue;
			}

			try {
				$latest_versions = $api->check_versions( $store_products );
			} catch ( \Exception $e ) {
				continue;
			}

			$transient_data = $this->maybe_add_version_details( $transient_data, $latest_versions, $store_products );
		}

		return $transient_data;
	}

	/**
	 * Updates the provided transient object with new version information for all supplied products.
	 *
	 * @param object    $transient_data
	 * @param array     $latest_versions
	 * @param Product[] $store_products
	 *
	 * @since 1.0
	 * @return object
	 */
	protected function maybe_add_version_details( $transient_data, $latest_versions, $store_products ) {
		foreach ( $store_products as $product ) {
			if ( ! isset( $latest_versions[ $product->id ]['new_version'] ) ) {
				continue;
			}

			$update_data = $latest_versions[ $product->id ];
			$new_version = $update_data['new_version'];

			if ( 'theme' === $this->type ) {
				$update_data['theme'] = $product->slug;
			} else {
				// Plugins expect an object.
				$update_data         = (object) $update_data;
				$update_data->plugin = $product->id;
				$update_data->id     = $product->id;
			}

			if ( ! empty( $new_version ) && version_compare( $product->version, $new_version, '<' ) ) {
				$transient_data->response[ $product->id ] = $update_data;
			} else {
				$transient_data->no_update[ $product->id ] = $update_data;
			}
		}

		return $transient_data;
	}

}
