<?php
/**
 * StoreApi.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 */

namespace EDD_SL_SDK;

class StoreApi {

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
	private $verifySsl;

	/**
	 * Store_API constructor.
	 *
	 * @param Store $store
	 */
	public function __construct( $store ) {
		$this->store = $store;

		/**
		 * Whether or not to verify SSL.
		 *
		 * @param bool  $verifySsl
		 * @param Store $store
		 */
		$this->verifySsl = (bool) apply_filters( 'edd_sl_api_request_verify_ssl', true, $this->store );
	}

	/**
	 * Retrieves the latest versions from the store.
	 *
	 * @param Product[] $storeProducts  Registered products to get new versions for. If empty, then all
	 *                                  the store's products are checked.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function checkVersions( $storeProducts = [] ) {
		$this->validateUrl();

		if ( empty( $storeProducts ) ) {
			$storeProducts = $this->store->getProducts();
		}

		$updateArray = array();
		foreach ( $storeProducts as $product ) {
			$updateArray[ $product->id ] = $product->toApiArgs();
		}

		$response = wp_remote_post( $this->store->api_url, array(
			'timeout'   => 15,
			'sslverify' => $this->verifySsl,
			'body'      => json_encode( array(
				'products' => $updateArray
			) )
		) );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			throw new \Exception( sprintf( __( 'Invalid HTTP response code: %d' ), $response_code ) );
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $response['products'] ) || ! is_array( $response['products'] ) ) {
			throw new \Exception( __( 'Invalid response.' ) );
		}

		return $response['products'];
	}

	/**
	 * Validates the store URL.
	 *
	 * @throws \Exception
	 */
	private function validateUrl() {
		if ( empty( $this->store->api_url ) ) {
			throw new \Exception( __( 'Missing store URL.' ) );
		}

		if ( trailingslashit( home_url() ) === trailingslashit( $this->store->api_url ) ) {
			throw new \Exception( __( 'A site cannot ping itself.' ) );
		}
	}

}
