<?php
/**
 * Singleton.php
 *
 * @package   EDD_SL_SDK\Traits
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 */

namespace EDD_SL_SDK\Traits;

trait Singleton {

	/**
	 * @var Singleton
	 */
	private static $instance;

	/**
	 * Retrieves the class instance.
	 *
	 * @return Singleton
	 */
	public static function instance() {
		if ( ! self::$instance instanceof Singleton ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}
