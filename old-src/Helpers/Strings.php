<?php
/**
 * Strings.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Helpers;

class Strings {

	private static $strings = [
		'activate_license'                 => 'Activate License',
		'admin_page_title_theme'           => 'Theme License',
		'admin_page_title_plugin'          => 'Plugin License',
		'deactivate_license'               => 'Deactivate License',
		'license_active'                   => 'Your license key is active.',
		'license_active_expires'           => 'Your license key is active and valid until %s.',
		'license_deactivated_successfully' => 'License deactivated successfully.',
		'license_disabled'                 => 'This license has been disabled.',
		'license_expired'                  => 'This license has expired.',
		'license_inactive'                 => 'License not activated.',
		'license_key'                      => 'License Key',

		// API Errors
		'invalid_license'                  => 'Invalid license key.',
	];

	/**
	 * Retrieves a string by its ID.
	 *
	 * @since 1.0
	 *
	 * @param string $stringId
	 * @param array  $overrides
	 *
	 * @return string
	 */
	public static function getString( $stringId, $overrides = [] ) {
		if ( is_array( $overrides ) && isset( $overrides[ $stringId ] ) ) {
			return $overrides[ $stringId ];
		}

		if ( ! isset( self::$strings[ $stringId ] ) ) {
			return '';
		}

		return self::$strings[ $stringId ];
	}

}
