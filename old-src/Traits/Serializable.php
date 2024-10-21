<?php
/**
 * Serializable.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Traits;

trait Serializable {

	/**
	 * Converts properties to an array.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function toArray() {
		return get_object_vars( $this );
	}

	/**
	 * Converts to JSON.
	 *
	 * @since 1.0
	 *
	 * @return false|string
	 */
	public function toJson() {
		return json_encode( $this->toArray() );
	}

}
