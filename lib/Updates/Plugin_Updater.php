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
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_updates' ) );
		add_filter( 'plugins_api', array( $this, 'show_version_details' ), 10, 3 );
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
	public function show_version_details( $data, $action = '', $args = null ) {
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) ) {
			return $data;
		}

		foreach ( SDK::instance()->store_registry->get_items() as $store_id => $store ) {
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

			return $this->format_version_details( reset( $latest_versions ) );
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
	private function format_version_details( $data ) {
		/*
		 * Overall we want to return an object, but these properties should be arrays.
		 * Let's save them while we have them.
		 */
		$array_properties = array(
			'sections'     => array(),
			'banners'      => array(),
			'icons'        => array(),
			'contributors' => array()
		);
		foreach ( array_keys( $array_properties ) as $property_name ) {
			if ( isset( $data[ $property_name ] ) && is_array( $data[ $property_name ] ) ) {
				$array_properties[ $property_name ] = $data[ $property_name ];
			}
		}

		// Convert the main data to an object.
		if ( is_array( $data ) ) {
			$data = json_decode( jsone_encode( $data ) );
		}

		// Now put our array values back.
		foreach ( $array_properties as $property_name => $property_value ) {
			$data->{$property_name} = $property_value;
		}

		return $data;
	}

}
