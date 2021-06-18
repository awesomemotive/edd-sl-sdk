<?php
/**
 * Product.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Models;

use EDD_SL_SDK\Exceptions;
use EDD_SL_SDK\SDK;
use EDD_SL_SDK\StoreApi;
use EDD_SL_SDK\Traits\Serializable;

/**
 * Class Product
 *
 * @package EDD_SL_SDK
 */
class Product {

	use Serializable;

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var string
	 */
	public $store_id;

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var string
	 */
	public $license;

	/**
	 * @var int
	 */
	public $item_id;

	/**
	 * @var string
	 */
	public $file;

	/**
	 * @var string
	 */
	public $version;

	/**
	 * @var string
	 */
	public $slug;

	/**
	 * @var string
	 */
	public $cache_key;

	/**
	 * @var bool
	 */
	public $beta = false;

	/**
	 * Product constructor.
	 *
	 * @param array $args
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $args ) {
		// Verify we have all the required arguments.
		$requiredArgs = [ 'type', 'item_id', 'file', 'version', 'store_id' ];

		foreach ( $requiredArgs as $requiredArg ) {
			if ( empty( $args[ $requiredArg ] ) ) {
				throw new \InvalidArgumentException( sprintf(
					__( 'Missing required argument: %s' ),
					$requiredArg
				) );
			}
		}

		// If we don't have a slug, we can make one.
		if ( empty( $args['slug'] ) && ! empty( $args['file'] ) ) {
			$args['slug'] = basename( $args['file'], '.php' );
		}

		// If there's no cache key, make one.
		if ( empty( $args['cache_key'] ) ) {
			$args['cache_key'] = sprintf( 'sl_%s_data_%s', $args['type'], $args['slug'] );
		}

		foreach ( $args as $key => $value ) {
			$this->{$key} = $value;
		}

		$this->id = $this->getId();
	}

	/**
	 * Retrieves the unique ID for this product.
	 * For plugins, this is the `plugin_basename()` value; for themes it's the slug (`get_template()`).
	 *
	 * @since 1.0
	 * @return string
	 */
	private function getId() {
		return 'plugin' === $this->type ? plugin_basename( $this->file ) : $this->slug;
	}

	/**
	 * Builds API arguments for the product.
	 *
	 * @since 1.0
	 * @return array
	 */
	public function toArray() {
		return array(
			'license'    => $this->license,
			'product_id' => $this->item_id,
			'version'    => $this->version,
			'slug'       => $this->slug,
			'beta'       => $this->beta,
		);
	}

	/**
	 * Updates license data in the database.
	 *
	 * @since 1.0
	 *
	 * @param array $data
	 */
	private function updateLicenseData( $data ) {
		$data['last_sync'] = gmdate( 'Y-m-d H:i:s' );

		update_option( $this->cache_key, json_encode( $data ) );
	}

	/**
	 * Retrieves a store API for this product.
	 *
	 * @since 1.0
	 *
	 * @return StoreApi
	 * @throws Exceptions\ItemNotFoundException
	 */
	private function getStoreApi() {
		return new StoreApi( SDK::instance()->storeRegistry->get( $this->store_id ) );
	}

	/**
	 * Activates the product's license key.
	 *
	 * @since 1.0
	 *
	 * @return array
	 * @throws Exceptions\ApiException
	 * @throws Exceptions\ItemNotFoundException
	 */
	public function activateLicense() {
		$licenseData = $this->getStoreApi()->activateLicense( $this );

		if ( ! empty( $licenseData['license'] ) ) {
			$this->updateLicenseData( $licenseData['license'] );
		}

		return $licenseData;
	}

	/**
	 * Deactivates the product's license key.
	 *
	 * @since 1.0
	 *
	 * @returns array
	 * @throws Exceptions\ApiException
	 * @throws Exceptions\ItemNotFoundException
	 */
	public function deactivateLicense() {
		$licenseData = $this->getStoreApi()->deactivateLicense( $this );

		if ( ! empty( $licenseData['license'] ) ) {
			$this->updateLicenseData( $licenseData['license'] );
		}

		return $licenseData;
	}

	/**
	 * Retrieves the License object.
	 *
	 * @since 1.0
	 *
	 * @return License
	 * @throws Exceptions\ItemNotFoundException
	 */
	public function getLicenseData() {
		return License::fromJson( get_option( $this->cache_key ) );
	}

}
