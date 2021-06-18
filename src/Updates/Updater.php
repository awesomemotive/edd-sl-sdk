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

use EDD_SL_SDK\Models\Product;
use EDD_SL_SDK\SDK;
use EDD_SL_SDK\StoreApi;

abstract class Updater {

	/**
	 * Product type
	 *
	 * @since 1.0
	 * @var string
	 */
	protected $type;

	/**
	 * Initialize hooks
	 *
	 * @since 1.0
	 * @return void
	 */
	public function init() {
		add_filter( 'pre_set_site_transient_update_' . $this->type . 's', array( $this, 'checkUpdates' ) );
	}

	/**
	 * Checks for product updates. This does one API request per store.
	 *
	 * @todo  Caching?
	 *
	 * @param object $transientData
	 *
	 * @since 1.0
	 * @return object
	 */
	public function checkUpdates( $transientData ) {
		if ( ! is_object( $transientData ) ) {
			$transientData = new \stdClass();
		}

		// Get latest versions for each store.
		foreach ( SDK::instance()->storeRegistry->getItems() as $store ) {
			$api           = new StoreApi( $store );
			$storeProducts = $store->getProducts( array(
				'type' => $this->type
			) );

			if ( empty( $storeProducts ) ) {
				continue;
			}

			try {
				$latestVersions = $api->checkVersions( $storeProducts );
			} catch ( \Exception $e ) {
				continue;
			}

			$transientData = $this->maybeAddVersionDetails( $transientData, $latestVersions, $storeProducts );
		}

		return $transientData;
	}

	/**
	 * Updates the provided transient object with new version information for all supplied products.
	 *
	 * @param object    $transientData
	 * @param array     $latestVersions
	 * @param Product[] $storeProducts
	 *
	 * @since 1.0
	 * @return object
	 */
	protected function maybeAddVersionDetails( $transientData, $latestVersions, $storeProducts ) {
		foreach ( $storeProducts as $product ) {
			if ( ! isset( $latestVersions[ $product->id ]['new_version'] ) ) {
				continue;
			}

			$update_data = $latestVersions[ $product->id ];
			$new_version = $update_data['new_version'];

			if ( 'theme' === $this->type ) {
				$update_data['theme'] = $product->slug;
			} else {
				// Plugins expect an object.
				$update_data         = json_decode( json_encode( $update_data ) );
				$update_data->plugin = $product->id;
				$update_data->id     = $product->id;

				// Make sure the slug is set to the current one.
				$update_data->slug = $product->slug;
			}

			if ( ! empty( $new_version ) && version_compare( $product->version, $new_version, '<' ) ) {
				$transientData->response[ $product->id ] = $update_data;
			} else {
				$transientData->no_update[ $product->id ] = $update_data;
			}
		}

		return $transientData;
	}

}
