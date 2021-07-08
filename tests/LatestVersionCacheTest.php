<?php
/**
 * LatestVersionCacheTest.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Tests;

use EDD_SL_SDK\Models\Store;
use EDD_SL_SDK\Repositories\LatestVersionCache;

/**
 * Class LatestVersionCacheTest
 *
 * @coversDefaultClass \EDD_SL_SDK\Repositories\LatestVersionCache
 *
 * @package EDD_SL_SDK\Tests
 */
class LatestVersionCacheTest extends TestCase {

	/**
	 * @var string
	 */
	private $storeCacheKey;

	/**
	 * @var LatestVersionCache
	 */
	private $repoStub;

	/**
	 * Set up our mock repo.
	 */
	public function setUp() {
		parent::setUp();

		$this->setUpMockRepo();
	}

	/**
	 * Sets up our mocked repository.
	 *
	 * @param array $args Optional argument overrides.
	 */
	private function setUpMockRepo( $args = [] ) {
		/** @var Store $store */
		$store = self::factory()->store->create_and_get( wp_parse_args( $args, [
			'products' => [
				array_merge( self::factory()->product->generate_args(), [
					'license_getter' => static function () {
						return 'license_key';
					}
				] )
			]
		] ) );

		$this->storeCacheKey = 'sl_store_latest_cache_' . $store->id;

		$this->repoStub = $this->getMockBuilder( LatestVersionCache::class )
			->setConstructorArgs( [ $store ] )
			->setMethods( [ 'getRemoteData' ] )
			->getMock();

		$this->repoStub->method( 'getRemoteData' )
			->willReturn( [ 'remote_api_data' ] );
	}

	/**
	 * Sets cached data for the store.
	 *
	 * @param mixed $data
	 */
	private function setCachedData( $data ) {
		update_option( $this->storeCacheKey, $data );
	}

	/**
	 * A store with no cached data should execute a new API request.
	 *
	 * @throws \Exception
	 */
	public function test_store_without_cache_executes_remote_request() {
		$this->assertEmpty( get_option( $this->storeCacheKey ) );

		$this->repoStub->expects( $this->once() )->method( 'getRemoteData' );

		$this->repoStub->getLatestVersions();

		$this->assertNotEmpty( get_option( $this->storeCacheKey ) );
	}

	/**
	 * A store with cached data should not execute an API request.
	 *
	 * @throws \Exception
	 */
	public function test_store_with_valid_cache_doesnt_execute_remote_request() {
		$this->setCachedData( json_encode( [
			'time_stored' => time(),
			'data'        => []
		] ) );

		$this->repoStub->expects( $this->never() )->method( 'getRemoteData' );

		$this->repoStub->getLatestVersions();
	}

	/**
	 * A store with stale cache should execute an API request and update the cache.
	 *
	 * @throws \Exception
	 */
	public function test_store_with_stale_cache_executes_remote_request() {
		$this->setCachedData( json_encode( [
			'time_stored' => strtotime( '-1 day' ),
			'data'        => []
		] ) );

		$this->repoStub->expects( $this->once() )->method( 'getRemoteData' );

		$this->repoStub->getLatestVersions();

		$cached = json_decode( get_option( $this->storeCacheKey ), true );

		// Time stored should be just now.
		$this->assertGreaterThanOrEqual( time() - 1, $cached['time_stored'] );
	}

	/**
	 * A store with caching enabled should perform a remote request the first time, and hit cache the second.
	 *
	 * @throws \Exception
	 */
	public function test_store_with_caching_enabled_should_hit_cache_second_time() {
		$this->repoStub->expects( $this->once() )->method( 'getRemoteData' );

		$this->repoStub->getLatestVersions();

		$this->assertNotEmpty( get_option( $this->storeCacheKey ) );

		$this->repoStub->getLatestVersions();
	}

	/**
	 * A store with caching disabled should always execute remote requests.
	 *
	 * @throws \Exception
	 */
	public function test_store_with_caching_disabled_always_executes_remote_request() {
		$this->setUpMockRepo( [
			'update_cache_duration' => 0
		] );

		$this->repoStub->expects( $this->exactly( 2 ) )->method( 'getRemoteData' );

		$this->repoStub->getLatestVersions();

		// Cache should still be empty.
		$this->assertEmpty( get_option( $this->storeCacheKey ) );

		$this->repoStub->getLatestVersions();
	}

}
