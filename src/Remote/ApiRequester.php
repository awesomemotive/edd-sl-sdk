<?php
/**
 * ApiRequester.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Remote;

use EDD_SL_SDK\Models\Store;

abstract class ApiRequester {

	/**
	 * @var int Last HTTP response code.
	 */
	public $lastResponseCode;

	/**
	 * @var string Body of the last API response.
	 */
	public $lastResponseBody;

	/**
	 * @var Store Store the API request is going to.
	 */
	protected $store;

	/**
	 * @var bool Whether or not to verify SSL.
	 */
	protected $verifySsl;

	/**
	 * ApiRequester constructor.
	 *
	 * @param Store $store
	 */
	public function __construct( Store $store ) {
		$this->store = $store;

		/**
		 * Whether or not to verify SSL.
		 *
		 * @param bool                     $verifySsl
		 * @param \EDD_SL_SDK\Models\Store $store
		 */
		$this->verifySsl = (bool) apply_filters( 'edd_sl_api_request_verify_ssl', true, $this->store );
	}

	/**
	 * Performs an API request.
	 *
	 * @since 1.0
	 *
	 * @param string $path
	 * @param array  $body
	 * @param string $method
	 *
	 * @return array
	 */
	public function makeRequest( $path, $body = array(), $method = 'POST' ) {
		// Reset last properties.
		$this->lastResponseCode = 0;
		$this->lastResponseBody = null;

		$response = $this->executeRequest( $path, $body, $method );

		$this->lastResponseCode = wp_remote_retrieve_response_code( $response );
		$this->lastResponseBody = wp_remote_retrieve_body( $response );

		return $response;
	}

	/**
	 * Executes an API request.
	 *
	 * @since 1.0
	 *
	 * @param string $path
	 * @param array  $body
	 * @param string $method
	 *
	 * @return array
	 */
	abstract protected function executeRequest( $path, $body = array(), $method = 'POST' );

}
