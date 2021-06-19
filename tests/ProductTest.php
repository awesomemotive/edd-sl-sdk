<?php
/**
 * ProductTest.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Tests;

use EDD_SL_SDK\Models\Store;

/**
 * Class ProductTest
 *
 * @coversDefaultClass Product
 *
 * @package EDD_SL_SDK\Tests
 */
class ProductTest extends TestCase {

	/**
	 * @var Store
	 */
	private static $store;

	/**
	 * Runs once before any tests.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$store = self::factory()->store->create_and_get();
	}

	/**
	 * @covers \EDD_SL_SDK\Models\Product::getLicense
	 */
	public function test_product_with_license_getter_returns_license() {
		$product = self::factory()->product->create( [
			'license_getter' => static function () {
				return 'my_license_key';
			}
		] );

		$this->assertSame( 'my_license_key', $product->getLicense() );
	}

	/**
	 * @covers \EDD_SL_SDK\Models\Product::getLicense
	 */
	public function test_product_with_no_license_key_saved_returns_false() {
		$product = self::factory()->product->create();

		$this->assertFalse( $product->getLicense() );
	}

}
