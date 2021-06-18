<?php
/**
 * StoreApi.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 */

namespace EDD_SL_SDK;

use EDD_SL_SDK\Exceptions\ApiException;
use EDD_SL_SDK\Models\Environment;
use EDD_SL_SDK\Models\Product;
use EDD_SL_SDK\Models\Store;

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
	 * @var int
	 */
	private $lastResponseCode;

	/**
	 * @var string
	 */
	private $lastResponseBody;

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
	 * Performs an API request.
	 *
	 * @param string $path   Endpoint URI.
	 * @param array  $body   Body to send with the request.
	 * @param string $method HTTP method.
	 *
	 * @return array|\WP_Error
	 * @throws \Exception
	 */
	private function makeRequest( $path, $body = array(), $method = 'POST' ) {
		// Reset last properties.
		$this->lastResponseCode = 0;
		$this->lastResponseBody = null;

		$this->validateUrl();

		$response = wp_remote_request( sprintf( '%s/%s', untrailingslashit( $this->store->api_url ), $path ), array(
			'method'    => $method,
			'headers'   => array(
				'Content-Type' => 'application/json'
			),
			'timeout'   => 15,
			'sslverify' => $this->verifySsl,
			'body'      => json_encode( $body )
		) );

		if ( is_wp_error( $response ) ) {
			throw new ApiException( $response->get_error_message() );
		}

		$this->lastResponseCode = wp_remote_retrieve_response_code( $response );
		$this->lastResponseBody = wp_remote_retrieve_body( $response );

		return $response;
	}

	/**
	 * Activates a license key.
	 *
	 * @since 1.0
	 *
	 * @param Product $product
	 *
	 * @return array
	 * @throws ApiException
	 */
	public function activateLicense( Product $product ) {
		if ( empty( $product->license ) ) {
			throw new \Exception( 'No license to activate.' );
		}
		if ( empty( $product->item_id ) ) {
			throw new \Exception( 'An item_id is required to activate a license.' );
		}

		$environment = new Environment();

		$this->makeRequest( sprintf( 'license/%s/activate', urlencode( $product->license ) ), array(
			'item_id'     => $product->item_id,
			'url'         => $environment->url,
			'environment' => $environment->environment
		) );

		if ( 201 !== $this->lastResponseCode ) {
			throw new ApiException( sprintf( 'Invalid HTTP response code: %d. Response: %s', $this->lastResponseCode, $this->lastResponseBody ) );
		}

		return json_decode( $this->lastResponseBody, true );
	}

	/**
	 * Deactivates a license key.
	 *
	 * @since 1.0
	 *
	 * @param Product $product
	 *
	 * @return array
	 * @throws ApiException
	 */
	public function deactivateLicense( Product $product ) {
		if ( empty( $product->license ) ) {
			throw new \Exception( 'No license to deactivate.' );
		}

		$environment = new Environment();

		$this->makeRequest( sprintf( 'license/%s/deactivate', urlencode( $product->license ) ), array(
			'item_id'     => $product->item_id,
			'url'         => $environment->url,
			'environment' => $environment->environment
		) );

		if ( 201 !== $this->lastResponseCode ) {
			throw new ApiException( sprintf( 'Invalid HTTP response code: %d. Response: %s', $this->lastResponseCode, $this->lastResponseBody ) );
		}

		return json_decode( $this->lastResponseBody, true );
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
		if ( empty( $storeProducts ) ) {
			$storeProducts = $this->store->getProducts();
		}

		$updateArray = array();
		foreach ( $storeProducts as $product ) {
			$updateArray[ $product->id ] = $product->toArray();
		}

		$this->makeRequest( 'products/versions', array(
			'environment' => ( new Environment() )->toArray(),
			'products'    => $updateArray
		) );

		if ( 200 !== $this->lastResponseCode ) {
			throw new ApiException( sprintf( 'Invalid HTTP response code: %d. Response: %s', $this->lastResponseCode, $this->lastResponseBody ) );
		}

		$responseBody = json_decode( $this->lastResponseBody, true );

		if ( empty( $responseBody['products'] ) || ! is_array( $responseBody['products'] ) ) {
			throw new ApiException( sprintf( 'Invalid response from API: %s', $this->lastResponseBody ) );
		}

		return $responseBody['products'];
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
