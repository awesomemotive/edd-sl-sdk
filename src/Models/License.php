<?php
/**
 * License.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Models;

use EDD_SL_SDK\Exceptions\ItemNotFoundException;
use EDD_SL_SDK\Helpers\Strings;

class License {

	/**
	 * @var int ID of the license
	 */
	public $id;

	/**
	 * @var string License key.
	 */
	public $license_key;

	/**
	 * @var bool Whether or not the license is activated on this site.
	 */
	public $activated;

	/**
	 * @var string Status of the license.
	 */
	public $status;

	/**
	 * @var int ID of the product this license is for.
	 */
	public $download_id;

	/**
	 * @var int Product price ID.
	 */
	public $price_id;

	/**
	 * @var int ID of the order that started it all.
	 */
	public $payment_id;

	/**
	 * @var string Date the license key was first created.
	 */
	public $date_created;

	/**
	 * @var string|null Date the license expires (MySQL format) or `null` if it never expires.
	 */
	public $expiration;

	/**
	 * @var int Number of times the license has been activated.
	 */
	public $number_activations;

	/**
	 * @var int|string Maximum activations allowed, or `unlimited`.
	 */
	public $activation_limit;

	/**
	 * @var int Number of activations remaining, or `0` if no limit.
	 */
	public $activations_remaining;

	/**
	 * @var string MySQL date for the last time this license data was updated.
	 */
	public $last_sync;

	/**
	 * @var array Customer information.
	 */
	private $customer;

	/**
	 * License constructor.
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {
		foreach ( $data as $property => $value ) {
			$this->{$property} = $value;
		}
	}

	/**
	 * Creates a new License instance from a JSON object.
	 *
	 * @param string $json
	 *
	 * @return License
	 * @throws ItemNotFoundException
	 */
	public static function fromJson( $json ) {
		$licenseArray = json_decode( $json, true );
		if ( empty( $licenseArray ) || ! is_array( $licenseArray ) ) {
			throw new ItemNotFoundException( 'No license data.' );
		}

		return new self( $licenseArray );
	}

	/**
	 * Returns display-ready version of the license status.
	 *
	 * @since 1.0
	 *
	 * @param array $productStrings
	 *
	 * @return string
	 */
	public function getStatusDisplay( $productStrings = [] ) {
		switch ( $this->status ) {
			case 'active' :
				if ( $this->expiration ) {
					return sprintf(
						Strings::getString( 'license_active_expires', $productStrings ),
						date_i18n( get_option( 'date_format' ), strtotime( $this->expiration, current_time( 'timestamp' ) ) )
					);
				} else {
					return Strings::getString( 'license_active', $productStrings );
				}

			case 'disabled' :
				return Strings::getString( 'license_disabled', $productStrings );

			case 'expired' :
				return Strings::getString( 'license_expired', $productStrings );

			default :
				return Strings::getString( 'license_inactive', $productStrings );
		}
	}

}
