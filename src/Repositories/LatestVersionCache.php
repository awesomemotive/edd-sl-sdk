<?php
/**
 * LatestVersionCache.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Repositories;

use EDD_SL_SDK\Models\Store;

class LatestVersionCache {

	/** @var Store */
	private $store;

	/** @var string */
	private $cacheKey;

	/**
	 * LatestVersionCache constructor.
	 *
	 * @param Store  $store
	 */
	public function __construct( Store $store ) {
		$this->store    = $store;
		$this->cacheKey = 'sl_store_latest_cache_' . $this->store->id;
	}

	/**
	 * Retrieves cached version information from the options table.
	 *
	 * @since 1.0
	 *
	 * @return array|false
	 */
	private function getCachedData() {
		$cache = get_option( $this->cacheKey );

		if ( empty( $cache ) ) {
			return false;
		}

		$cache = json_decode( $cache, true );
		if ( ! isset( $cache['time_stored'] ) || ! isset( $cache['data'] ) ) {
			return false;
		}

		if ( $cache['time_stored'] + $this->store->update_cache_duration < time() ) {
			return false;
		}

		return $cache['data'];
	}

	/**
	 * Saves cached data.
	 *
	 * @since 1.0
	 *
	 * @param array $data
	 */
	private function setCachedData( $data ) {
		$cache = [
			'time_stored' => time(),
			'data'        => $data
		];

		update_option( $this->cacheKey, json_encode( $cache ) );
	}

	/**
	 * Retrieves the latest versions from the cache, if still valid, or does a fresh remote query.
	 *
	 * @since 1.0
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getLatestVersions() {
		$cachedVersions = $this->getCachedData();

		if ( $cachedVersions ) {
			return $cachedVersions;
		}

		try {
			$latestVersions = $this->store->getApiHandler()->checkVersions( $this->store->getProducts() );

			$this->setCachedData( $latestVersions );

			return $latestVersions;
		} catch ( \Exception $e ) {
			// Cache no results on error.
			$this->setCachedData( [] );

			throw $e;
		}
	}

}
