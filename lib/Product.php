<?php
/**
 * Product.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 */

namespace EDD_SL_SDK;

/**
 * Class Product
 *
 * @property string|int $id
 *
 * @package EDD_SL_SDK
 */
class Product {

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var string
	 */
	public $license;

	/**
	 * @var string
	 */
	public $item_name;

	/**
	 * @var int
	 */
	public $item_id;

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
	 */
	public function __construct( $args ) {
		foreach ( $args as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Mostly used to dynamically build the `id` property.
	 *
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function __get( $key ) {
		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		}

		if ( property_exists( $this, $key ) ) {
			return $this->{$key};
		}

		return null;
	}

	/**
	 * Retrieves the unique ID for this product.
	 *
	 * Note: this is only unique to the store; not unique across all stores.
	 *
	 * @since 1.0
	 * @return string|null
	 */
	public function get_id() {
		foreach ( [ 'item_id', 'item_slug', 'item_name' ] as $possible_id ) {
			if ( property_exists( $this, $possible_id ) && ! empty( $this->{$possible_id} ) ) {
				return sanitize_key( $this->{$possible_id} );
			}
		}

		return null;
	}

	/**
	 * Builds API arguments for the product.
	 *
	 * @return array
	 */
	public function to_api_args() {
		return array(
			'license'   => $this->license,
			'item_name' => $this->item_name,
			'item_id'   => $this->item_id,
			'version'   => $this->version,
			'slug'      => $this->slug,
			'beta'      => $this->beta,
			'url'       => home_url()
		);
	}

}
