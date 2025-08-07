<?php
/**
 * License status message class.
 *
 * @since <next-version>
 *
 * @package EasyDigitalDownloads\Updater\Licensing\Messages
 * @copyright (c) 2025, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since <next-version>
 */

namespace EasyDigitalDownloads\Updater\Licensing;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * License status message class.
 *
 * @since <next-version>
 */
class Messages {

	/**
	 * The array of license data.
	 *
	 * @var array
	 */
	private $license_data = array();

	/**
	 * The license expiration as a timestamp, or false if no expiration.
	 *
	 * @var bool|int
	 */
	private $expiration = false;

	/**
	 * The current timestamp.
	 *
	 * @var int
	 */
	private $now;

	/**
	 * Constructor.
	 *
	 * @since <next-version>
	 * @param array $license_data The license data.
	 */
	public function __construct( $license_data = array() ) {
		$this->license_data = wp_parse_args(
			$license_data,
			array(
				'status'      => '',
				'expires'     => '',
				'name'        => '',
				'license_key' => '',
			)
		);
		$this->now          = current_time( 'timestamp' );
		if ( ! empty( $this->license_data['expires'] ) && 'lifetime' !== $this->license_data['expires'] ) {
			if ( ! is_numeric( $this->license_data['expires'] ) ) {
				$this->expiration = strtotime( $this->license_data['expires'], $this->now );
			} else {
				$this->expiration = $this->license_data['expires'];
			}
		}
	}

	/**
	 * Gets the appropriate licensing message from an array of license data.
	 *
	 * @since <next-version>
	 * @return string
	 */
	public function get_message() {
		return $this->build_message();
	}

	/**
	 * Builds the message based on the license data.
	 *
	 * @sinc <next-version>
	 * @return string
	 */
	private function build_message() {
		switch ( $this->license_data['status'] ) {

			case 'expired':
				$message = sprintf(
					/* translators: 1: license expiration date. */
					__( 'Your license key expired on %1$s. Please renew your license key.', 'edd-sl-sdk' ),
					edd_date_i18n( $this->expiration )
				);
				break;

			case 'revoked':
			case 'disabled':
				$message = __( 'Your license key has been disabled.', 'edd-sl-sdk' );
				break;

			case 'missing':
				$message = __( 'Invalid license. Please verify it.', 'edd-sl-sdk' );
				break;

			case 'site_inactive':
				$message = __( 'Your license key is not active for this URL.', 'edd-sl-sdk' );
				break;

			case 'invalid':
			case 'invalid_item_id':
			case 'item_name_mismatch':
			case 'key_mismatch':
				if ( ! empty( $this->license_data['name'] ) ) {
					$message = sprintf(
						/* translators: the extension name. */
						__( 'This appears to be an invalid license key for %s.', 'edd-sl-sdk' ),
						$this->license_data['name']
					);
				} else {
					$message = __( 'This appears to be an invalid license key.', 'edd-sl-sdk' );
				}
				break;

			case 'no_activations_left':
				$message = __( 'Your license key has reached its activation limit.', 'edd-sl-sdk' );
				break;

			case 'license_not_activable':
				$message = __( 'The key you entered belongs to a bundle, please use the product specific license key.', 'edd-sl-sdk' );
				break;

			case 'deactivated':
				$message = __( 'Your license key has been deactivated.', 'edd-sl-sdk' );
				break;

			case 'valid':
				$message = $this->get_valid_message();
				break;

			default:
				$message = __( 'Unlicensed: currently not receiving updates.', 'edd-sl-sdk' );
				break;
		}

		return $message;
	}

	/**
	 * Gets the message text for a valid license.
	 *
	 * @since <next-version>
	 * @return string
	 */
	private function get_valid_message() {
		if ( ! empty( $this->license_data['expires'] ) && 'lifetime' === $this->license_data['expires'] ) {
			return __( 'License key never expires.', 'edd-sl-sdk' );
		}

		if ( ( $this->expiration > $this->now ) && ( $this->expiration - $this->now < ( DAY_IN_SECONDS * 30 ) ) ) {
			return sprintf(
				/* translators: the license expiration date. */
				__( 'Your license key expires soon! It expires on %s.', 'edd-sl-sdk' ),
				edd_date_i18n( $this->expiration )
			);
		}

		return sprintf(
			/* translators: the license expiration date. */
			__( 'Your license key expires on %s.', 'edd-sl-sdk' ),
			edd_date_i18n( $this->expiration )
		);
	}
}
