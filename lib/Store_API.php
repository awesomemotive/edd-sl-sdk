<?php
/**
 * Store_API.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 */

namespace EDD_SL_SDK;

class Store_API {

	/**
	 * Store details.
	 *
	 * @var Store
	 */
	private $store;

	/**
	 * Whether or not to verify SSL.
	 *
	 * @var bool
	 */
	private $verify_ssl;

	/**
	 * Store_API constructor.
	 *
	 * @param Store $store_id
	 */
	public function __construct( $store ) {
		$this->store = $store;

		/**
		 * Whether or not to verify SSL.
		 *
		 * @param bool  $verify_ssl
		 * @param Store $store
		 */
		$this->verify_ssl = (bool) apply_filters( 'edd_sl_api_request_verify_ssl', true, $this->store );
	}

	/**
	 * Retrieves the latest versions from the store.
	 *
	 * @param Product[] $store_products Registered products to get new versions for. If empty, then all
	 *                                  the store's products are checked.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function check_versions( $store_products = [] ) {
		$this->validate_url();

		if ( empty( $store_products ) ) {
			$store_products = $this->store->get_products();
		}

		$update_array = array();
		foreach ( $store_products as $product ) {
			$update_array[ $product->id ] = $product->to_api_args();
		}

		$response = wp_remote_get( $this->store->store_url, array(
			'timeout'   => 15,
			'sslverify' => $this->verify_ssl,
			'body'      => [
				'edd_action' => 'get_version',
				'products'   => $update_array
			]
		) );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			throw new \Exception( sprintf( __( 'Invalid HTTP response code: %d' ), $response_code ) );
		}

		$response = $this->format_version_response( json_decode( wp_remote_retrieve_body( $response ), true ) );

		if ( empty( $response ) ) {
			throw new \Exception( __( 'Invalid response.' ) );
		}

		return $response;
	}

	/**
	 * Formats the version check response.
	 *
	 * @param array $products
	 *
	 * @since 1.0
	 * @return array
	 */
	private function format_version_response( $products ) {
		if ( ! is_array( $products ) ) {
			return [];
		}

		foreach ( $products as $key => $product ) {
			// Unserialize arrays.
			foreach ( [ 'sections', 'banners', 'icons' ] as $property_name ) {
				if ( isset( $product[ $property_name ] ) ) {
					$product[ $property_name ] = maybe_unserialize( $product[ $property_name ] );
				}
			}

			$products[ $key ] = $product;
		}

		return $products;
	}

	/**
	 * Validates the store URL.
	 *
	 * @throws \Exception
	 */
	private function validate_url() {
		if ( empty( $this->store->store_url ) ) {
			throw new \Exception( __( 'Missing store URL.' ) );
		}

		return; // @todo remove

		if ( trailingslashit( home_url() ) === trailingslashit( $this->store->store_url ) ) {
			throw new \Exception( __( 'A site cannot ping itself.' ) );
		}
	}

}
