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
	 * @return object
	 * @throws \Exception
	 */
	public function check_version( $store_products = [] ) {
		$this->validate_url();

		if ( empty( $store_products ) ) {
			$store_products = $this->store->get_products();
		}

		$update_array = array();
		foreach ( $store_products as $product ) {
			$update_array[] = $product->to_api_args();
		}

		$response = wp_remote_get( $this->store->store_url, array(
			'timeout'   => 15,
			'sslverify' => $this->verify_ssl,
			'body'      => array(
				'edd_action'   => 'get_version',
				'update_array' => $update_array
			)
		) );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			throw new \Exception( sprintf( __( 'Invalid HTTP response code: %d' ), $response_code ) );
		}

		$response = $this->format_version_response( json_decode( wp_remote_retrieve_body( $response ) ) );

		if ( empty( $response ) ) {
			throw new \Exception( __( 'Invalid response.' ) );
		}

		return $response;
	}

	/**
	 * @param object $response
	 *
	 * @return object|false
	 */
	private function format_version_response( $response ) {
		if ( ! $response || ! isset( $response->sections ) ) {
			return false;
		}

		$response->sections = maybe_unserialize( $response->sections );

		if ( isset( $response->banners ) ) {
			$response->banners = maybe_unserialize( $response->banners );
		}

		if ( isset( $response->icons ) ) {
			$response->icons = maybe_unserialize( $response->icons );
		}

		if ( ! empty( $response->sections ) && ( is_array( $response->sections ) || is_object( $response->sections ) ) ) {
			foreach ( $response->sections as $key => $section ) {
				$response->$key = (array) $section;
			}
		}

		return $response;
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

		if ( trailingslashit( home_url() ) === trailingslashit( $this->store->store_url ) ) {
			throw new \Exception( __( 'A site cannot ping itself.' ) );
		}
	}

}
