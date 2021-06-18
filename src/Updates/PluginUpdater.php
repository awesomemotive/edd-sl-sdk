<?php
/**
 * PluginUpdater.php
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
use EDD_SL_SDK\StoreApi;
use EDD_SL_SDK\Traits\Singleton;

class PluginUpdater extends Updater {

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
		parent::init();
		
		add_filter( 'plugins_api', array( $this, 'showVersionDetails' ), 10, 3 );
	}

	/**
	 * Updates information on the "View version x.x details" page with custom data.
	 *
	 * @todo  Caching
	 *
	 * @param object      $data
	 * @param string      $action
	 * @param object|null $args
	 *
	 * @since 1.0
	 * @return object
	 */
	public function showVersionDetails( $data, $action = '', $args = null ) {
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) ) {
			return $data;
		}

		foreach ( SDK::instance()->storeRegistry->getItems() as $store_id => $store ) {
			$api           = new StoreApi( $store );
			$store_plugins = $store->getProducts( array(
				'type' => 'plugin',
				'slug' => $args->slug
			) );

			if ( empty( $store_plugins ) ) {
				continue;
			}

			try {
				$latest_versions = $api->checkVersions( $store_plugins );
			} catch ( \Exception $e ) {
				continue;
			}

			return $this->formatVersionDetails( reset( $latest_versions ) );
		}

		return $data;
	}

	/**
	 * Formats an input array in the way WordPress core expects.
	 * The main return value is an object, but certain elements within that object
	 * need to be arrays.
	 *
	 * @since 1.0
	 *
	 * @param array $data
	 *
	 * @return object
	 */
	private function formatVersionDetails( $data ) {
		/*
		 * Overall we want to return an object, but these properties should be arrays.
		 * Let's save them while we have them.
		 */
		$arrayProperties = array(
			'sections'     => array(),
			'banners'      => array(),
			'icons'        => array(),
			'contributors' => array()
		);
		foreach ( array_keys( $arrayProperties ) as $propertyName ) {
			if ( isset( $data[ $propertyName ] ) && is_array( $data[ $propertyName ] ) ) {
				$arrayProperties[ $propertyName ] = $data[ $propertyName ];
			}
		}

		// Convert the main data to an object.
		if ( is_array( $data ) ) {
			$data = json_decode( jsone_encode( $data ) );
		}

		// Now put our array values back.
		foreach ( $arrayProperties as $propertyName => $propertyValue ) {
			$data->{$propertyName} = $propertyValue;
		}

		return $data;
	}

}
