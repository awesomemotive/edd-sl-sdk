<?php
/**
 * ApiHandlerTest.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Tests;

use EDD_SL_SDK\Exceptions\ApiException;
use EDD_SL_SDK\Models\Product;
use EDD_SL_SDK\Models\Store;
use EDD_SL_SDK\Remote\ApiHandler;
use EDD_SL_SDK\Remote\ApiRequester;

class ApiHandlerTest extends TestCase {

	/**
	 * @var Store
	 */
	private static $store;

	/**
	 * @var Product
	 */
	private static $product;

	/**
	 * Runs once before any tests start.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$store = self::factory()->store->create_and_get( [
			'products' => [
				array_merge( self::factory()->product->generate_args(), [
					'license_getter' => static function () {
						return 'license_key';
					}
				] )
			]
		] );

		$products      = self::$store->getProducts();
		self::$product = reset( $products );
	}

	/**
	 * @return ApiRequester
	 */
	private function getMockApiRequester( $return ) {
		$stub = $this->getMockBuilder( ApiRequester::class )
			->setConstructorArgs( [ self::$store ] )
			->setMethods( [ 'executeRequest' ] )
			->getMock();
		$stub->method( 'executeRequest' )
			->willReturn( $return );

		return $stub;
	}

	/**
	 * @covers \EDD_SL_SDK\Remote\ApiHandler::activateLicense
	 * @throws ApiException
	 */
	public function test_activate_license_key_returns_response_body() {
		$body = [
			'activated' => true,
			'license'   => []
		];

		$apiRequester = $this->getMockApiRequester( [
			'response' => [
				'code' => 201
			],
			'body'     => json_encode( $body )
		] );

		$handler  = new ApiHandler( $apiRequester );
		$response = $handler->activateLicense( self::$product );

		$this->assertSame( 201, $apiRequester->lastResponseCode );
		$this->assertSame( $body, $response );
	}

	/**
	 * @covers \EDD_SL_SDK\Remote\ApiHandler::activateLicense
	 * @throws ApiException
	 */
	public function test_activate_license_key_with_invalid_license_throws_api_exception() {
		$apiRequester = $this->getMockApiRequester( [
			'response' => [
				'code' => 404
			],
			'body'     => json_encode( [
				'error_code'    => 'invalid_license',
				'error_message' => 'This license key does not exist.'
			] )
		] );

		$handler = new ApiHandler( $apiRequester );

		$this->setExpectedException( ApiException::class );
		$handler->activateLicense( self::$product );
	}

}
