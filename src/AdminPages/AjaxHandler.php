<?php
/**
 * AjaxHandler.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\AdminPages;


use EDD_SL_SDK\Exceptions\ApiException;
use EDD_SL_SDK\Exceptions\ItemNotFoundException;
use EDD_SL_SDK\Helpers\Strings;
use EDD_SL_SDK\Models\Product;

class AjaxHandler {

	const ACTIVATE_LICENSE = 'edd_sl_sdk_activate_license';
	const DEACTIVATE_LICENSE = 'edd_sl_sdk_deactivate_license';

	/** @var Product */
	private $product;

	/**
	 * AjaxHandler constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_' . self::ACTIVATE_LICENSE, [ $this, 'activateLicense' ] );
		add_action( 'wp_ajax_' . self::DEACTIVATE_LICENSE, [ $this, 'deactivateLicense' ] );
	}

	/**
	 * Handler for license activations.
	 *
	 * @since 1.0
	 */
	public function activateLicense() {
		check_ajax_referer( self::ACTIVATE_LICENSE );

		$this->product = null;

		try {
			$this->validateRequestAndSetProduct();
			$this->product->setLicense( $_POST['license_key'] );
			$this->product->activateLicense();

			wp_send_json_success( [
				'message'       => $this->product->getLicenseData()->getStatusHtml( $this->product->i18n ),
				'newFormInputs' => [
					'action'     => self::DEACTIVATE_LICENSE,
					'_wpnonce'   => wp_create_nonce( self::DEACTIVATE_LICENSE ),
					'buttonText' => $this->product->getString( 'deactivate_license' )
				]
			] );
		} catch ( ApiException $e ) {
			$this->handleApiException( $e );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * Handler for license deactivations.
	 *
	 * @since 1.0
	 */
	public function deactivateLicense() {
		check_ajax_referer( self::DEACTIVATE_LICENSE );

		$this->product = null;

		try {
			$this->validateRequestAndSetProduct();
			$this->product->deactivateLicense();
			$this->product->setLicense( null );

			wp_send_json_success( [
				'message'       => $this->product->getString( 'license_deactivated_successfully' ),
				'newFormInputs' => [
					'action'     => self::ACTIVATE_LICENSE,
					'_wpnonce'   => wp_create_nonce( self::ACTIVATE_LICENSE ),
					'buttonText' => $this->product->getString( 'activate_license' )
				]
			] );
		} catch ( ApiException $e ) {
			$this->handleApiException( $e );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * Retrieves the product from the request.
	 *
	 * @since 1.0
	 *
	 * @throws ItemNotFoundException
	 * @throws \Exception
	 */
	private function validateRequestAndSetProduct() {
		if ( empty( $_POST['productId'] ) || empty( $_POST['productType'] ) ) {
			throw new \Exception( 'Missing product ID or type.' );
		}

		if ( empty( $_POST['license_key'] ) ) {
			throw new \Exception( 'Missing license key.' );
		}

		if ( 'theme' === $_POST['productType'] ) {
			$this->product = \EDD_SL_SDK\Helpers\Product::getTheme( $_POST['productId'] );
		} else {
			$this->product = \EDD_SL_SDK\Helpers\Product::getPlugin( $_POST['productId'] );
		}
	}

	/**
	 * Handles API exceptions.
	 *
	 * @since 1.0
	 *
	 * @param ApiException $e
	 */
	private function handleApiException( ApiException $e ) {
		$errorMessage = '';
		$errorCode    = $e->getApiErrorCode();
		if ( ! empty( $errorCode ) ) {
			if ( $this->product instanceof Product ) {
				$errorMessage = $this->product->getString( $errorCode );
			} else {
				$errorMessage = Strings::getString( $errorCode );
			}
		}

		if ( empty( $errorMessage ) ) {
			$errorMessage = $e->getMessage();
		}

		wp_send_json_error( $errorMessage );
	}

}
