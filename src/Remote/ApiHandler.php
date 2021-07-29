<?php
/**
 * ApiHandler.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Remote;

use EDD_SL_SDK\Exceptions\ApiException;
use EDD_SL_SDK\Models\System;
use EDD_SL_SDK\Models\Product;

class ApiHandler {

	/**
	 * @var ApiRequester
	 */
	private $apiRequester;

	public function __construct( ApiRequester $requester ) {
		$this->apiRequester = $requester;
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
	 * @throws \Exception
	 */
	public function activateLicense( Product $product ) {
		if ( empty( $product->license ) ) {
			throw new \Exception( 'No license to activate.' );
		}
		if ( empty( $product->product_id ) ) {
			throw new \Exception( 'A product ID is required to activate a license.' );
		}

		$environment = new System();

		$this->apiRequester->makeRequest( sprintf( 'licenses/%s/activations', urlencode( $product->license ) ), array(
			'product_id'  => $product->product_id,
			'url'         => $environment->url,
			'environment' => $environment->environment
		) );

		if ( 201 !== $this->apiRequester->lastResponseCode ) {
			throw new ApiException(
				'Invalid HTTP response code.',
				$this->apiRequester->lastResponseCode,
				$this->apiRequester->lastResponseBody
			);
		}

		return json_decode( $this->apiRequester->lastResponseBody, true );
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
	 * @throws \Exception
	 */
	public function deactivateLicense( Product $product ) {
		if ( empty( $product->license ) ) {
			throw new \Exception( 'No license to deactivate.' );
		}

		$environment = new System();

		$this->apiRequester->makeRequest( sprintf( 'licenses/%s/activations', urlencode( $product->license ) ), array(
			'product_id'  => $product->product_id,
			'url'         => $environment->url,
			'environment' => $environment->environment
		), 'DELETE' );

		if ( 201 !== $this->apiRequester->lastResponseCode ) {
			throw new ApiException(
				'Invalid HTTP response code.',
				$this->apiRequester->lastResponseCode,
				$this->apiRequester->lastResponseBody
			);
		}

		return json_decode( $this->apiRequester->lastResponseBody, true );
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
	public function checkVersions( $storeProducts ) {
		$updateArray = array();
		foreach ( $storeProducts as $product ) {
			$updateArray[ $product->id ] = $product->toArray();
		}

		$this->apiRequester->makeRequest( 'products/releases/latest', array(
			'system'   => ( new System() )->toArray(),
			'products' => $updateArray
		) );

		if ( 200 !== $this->apiRequester->lastResponseCode ) {
			throw new ApiException(
				'Invalid HTTP response code.',
				$this->apiRequester->lastResponseCode,
				$this->apiRequester->lastResponseBody
			);
		}

		$responseBody = json_decode( $this->apiRequester->lastResponseBody, true );

		if ( empty( $responseBody['products'] ) || ! is_array( $responseBody['products'] ) ) {
			throw new ApiException(
				'Invalid response from API.',
				$this->apiRequester->lastResponseCode,
				$this->apiRequester->lastResponseBody
			);
		}

		return $responseBody['products'];
	}

}
