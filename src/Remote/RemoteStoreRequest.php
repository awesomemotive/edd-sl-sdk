<?php
/**
 * RemoteStoreRequest.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 */

namespace EDD_SL_SDK\Remote;

use EDD_SL_SDK\Exceptions\ApiException;

class RemoteStoreRequest extends ApiRequester {

	/**
	 * Executes an API request.
	 *
	 * @param string $path
	 * @param array  $body
	 * @param string $method
	 *
	 * @return array
	 * @throws ApiException
	 * @throws \Exception
	 */
	protected function executeRequest( $path, $body = array(), $method = 'POST' ) {
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

		return $response;
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
