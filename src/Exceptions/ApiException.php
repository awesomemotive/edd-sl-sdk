<?php
/**
 * ApiException.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Exceptions;

class ApiException extends \Exception {

	/**
	 * @var string|null Body of the last API response (JSON string).
	 */
	public $responseBody = null;

	/**
	 * ApiException constructor.
	 *
	 * @param string $message      Exception message.
	 * @param int    $code         HTTP response code.
	 * @param null   $responseBody Response body.
	 */
	public function __construct( $message = "", $code = 0, $responseBody = null ) {
		parent::__construct( $message, $code, null );

		$this->responseBody = $responseBody;
	}

	/**
	 * Retrieves the error code from the API message, if available.
	 *
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public function getApiErrorCode() {
		$responseBody = json_decode( $this->responseBody );

		if ( ! empty( $responseBody->error_code ) ) {
			return $responseBody->error_code;
		}

		return null;
	}

	/**
	 * Retrieves the error message from the API message, if available.
	 *
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public function getApiErrorMessage() {
		$responseBody = json_decode( $this->responseBody );

		if ( ! empty( $responseBody->error_message ) ) {
			return $responseBody->error_message;
		} elseif ( ! empty( $responseBody->message ) ) {
			return $responseBody->message;
		}

		return $this->getMessage();
	}

}
