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

use EDD_SL_SDK\Models\Product;
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
	 * @covers Product::getLicense
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
	 * @covers Product::getLicense
	 */
	public function test_product_with_no_license_key_saved_returns_false() {
		$product = self::factory()->product->create();

		$this->assertFalse( $product->getLicense() );
	}

	/**
	 * Test setting a license key when using custom getters and setters.
	 *
	 * @covers Product::setLicense
	 */
	public function test_set_license_via_getter_and_setter() {
		$product = self::factory()->product->create( [
			'license_getter' => static function () {
				return get_option( 'my_license_key' );
			},
			'license_setter' => static function ( $newLicense, $previousLicense ) {
				update_option( 'my_license_key', sanitize_text_field( $newLicense ) );
			}
		] );

		$this->assertFalse( $product->license );
		$product->setLicense( 'my_first_license_key' );
		$this->assertSame( 'my_first_license_key', $product->license );
		$this->assertSame( 'my_first_license_key', get_option( 'my_license_key' ) );

		/*
		 * If we weren't using a custom getter/setter, the license would have been saved here.
		 * But because we are, it shouldn't have been touched.
		 */
		$this->assertFalse( get_option( $product->license_option_name ) );
	}

	/**
	 * Test setting a license key when using default storage.
	 *
	 * @covers Product::setLicense
	 */
	public function test_set_license_via_default_options() {
		$product = self::factory()->product->create();

		$this->assertFalse( $product->license );
		$product->setLicense( 'my_first_license_key' );
		$this->assertSame( 'my_first_license_key', $product->license );

		// Make sure it was in fact saved to the default option.
		$this->assertSame( 'my_first_license_key', get_option( $product->license_option_name ) );
	}

	/**
	 * @covers Product::toArray
	 */
	public function test_product_to_array_returns_array() {
		$product      = self::factory()->product->create( [
			'license' => 'license_key',
			'item_id' => 5,
			'version' => '2.5',
			'slug'    => 'my-product',
			'beta'    => true
		] );
		$productArray = $product->toArray();

		$expected = [
			'license'    => 'license_key',
			'product_id' => 5,
			'version'    => '2.5',
			'slug'       => 'my-product',
			'beta'       => true
		];

		krsort( $expected );
		krsort( $productArray );

		$this->assertEquals( $expected, $productArray );
	}

}
