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
		'admin_page_title_theme'  => 'Theme License',
		'admin_page_title_plugin' => 'Plugin License',
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
