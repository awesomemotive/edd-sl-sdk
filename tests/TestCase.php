<?php
/**
 * TestCase.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Tests;

use EDD_SL_SDK\Tests\PhpUnit\Factory;

class TestCase extends \WP_UnitTestCase {

	/**
	 * Retrieves the factory instance
	 *
	 * @since 1.0
	 *
	 * @return Factory
	 */
	protected static function factory() {
		static $factory = null;
		if ( ! $factory ) {
			$factory = new Factory();
		}

		return $factory;
	}

}
