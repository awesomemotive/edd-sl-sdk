<?php
/**
 * Licensing API
 *
 * Tool for making requests to the Software Licensing API.
 *
 * @package   easy-digital-downloads-updater
 * @copyright Copyright (c) 2024, Easy Digital Downloads, LLC
 * @license   GPLv2 or later
 * @since     <next-version>
 */

namespace EasyDigitalDownloads\Updater\Requests;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Represents the API class for handling licensing.
 */
class API {

	/**
	 * The API URL.
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * The class constructor.
	 *
	 * @since <next-version>
	 * @param null|string $url Optional; used only for requests to non-EDD sites.
	 */
	public function __construct( $url ) {
		$this->api_url = $url;
	}

	/**
	 * Gets the API URL.
	 *
	 * @since <next-version>
	 * @return string
	 */
	public function get_url() {
		return $this->api_url;
	}

	/**
	 * Makes a request to the Software Licensing API.
	 *
	 * @since <next-version>
	 * @param array $api_params The parameters for the API request.
	 * @return false|stdClass
	 */
	public function make_request( $api_params = array() ) {
		if ( empty( $api_params ) || ! is_array( $api_params ) ) {
			return false;
		}

		// If a request has recently failed, don't try again.
		if ( $this->request_recently_failed() ) {
			return false;
		}

		$request = wp_remote_get(
			add_query_arg( $this->get_body( $api_params ), $this->api_url ),
			array(
				'timeout'   => 15,
				'sslverify' => apply_filters( 'https_ssl_verify', true, $this->api_url ),
			)
		);

		// If there was an API error, return false.
		if ( is_wp_error( $request ) || ( 200 !== wp_remote_retrieve_response_code( $request ) ) ) {
			$this->log_failed_request();

			return false;
		}

		return json_decode( wp_remote_retrieve_body( $request ) );
	}

	/**
	 * Updates the API parameters with the defaults.
	 *
	 * @param array $api_params The parameters for the specific request.
	 * @return array
	 */
	private function get_body( array $api_params ) {
		return wp_parse_args(
			$api_params,
			array(
				'url'         => rawurlencode( home_url() ),
				'environment' => wp_get_environment_type(),
			)
		);
	}

	/**
	 * Determines if a request has recently failed.
	 *
	 * @since <next-version>
	 *
	 * @return bool
	 */
	private function request_recently_failed() {
		$failed_request_details = get_option( $this->get_failed_request_cache_key() );

		// Request has never failed.
		if ( empty( $failed_request_details ) || ! is_numeric( $failed_request_details ) ) {
			return false;
		}

		/*
		 * Request previously failed, but the timeout has expired.
		 * This means we're allowed to try again.
		 */
		if ( time() > $failed_request_details ) {
			delete_option( $this->get_failed_request_cache_key() );

			return false;
		}

		return true;
	}

	/**
	 * Logs a failed HTTP request for this API URL.
	 * We set a timestamp for 1 hour from now. This prevents future API requests from being
	 * made to this domain for 1 hour. Once the timestamp is in the past, API requests
	 * will be allowed again. This way if the site is down for some reason we don't bombard
	 * it with failed API requests.
	 *
	 * @since <next-version>
	 */
	private function log_failed_request() {
		update_option( $this->get_failed_request_cache_key(), strtotime( '+1 hour' ), false );
	}

	/**
	 * Retrieves the cache key for the failed requests option.
	 *
	 * @since <next-version>
	 * @return string The cache key for failed requests.
	 */
	private function get_failed_request_cache_key() {
		return 'eddsdk_failed_request_' . md5( $this->api_url );
	}
}
