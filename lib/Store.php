<?php
/**
 * Store.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK;

class Store {

	/**
	 * Unique ID for this store.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Contains all of this store's products.
	 *
	 * @var Product_Registry
	 */
	private $products;

	/**
	 * Store constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args ) {
		foreach ( $args as $key => $value ) {
			// Products is skipped because we set that further down.
			if ( 'products' !== 'key' ) {
				$this->{$key} = $value;
			}
		}

		$args = wp_parse_args( $args, [
			'products' => []
		] );

		$this->set_products( $args['products'] );
	}

	/**
	 * Builds the product registry for this store.
	 *
	 * @param array $products
	 *
	 * @since 1.0
	 */
	private function set_products( $products ) {
		$this->products = new Product_Registry();

		if ( ! empty( $products ) && is_array( $products ) ) {
			foreach ( $products as $product_key => $product_args ) {
				$this->add_product( $product_args );
			}
		}
	}

	/**
	 * Retrieves all of a store's products.
	 *
	 * @param array $query_args
	 *
	 * @since 1.0
	 * @return Product[]
	 */
	public function get_products( $query_args = [] ) {
		return ! empty( $query_args ) ? $this->products->query( $query_args ) : $this->products->get_items();
	}

	/**
	 * Adds a new product.
	 *
	 * @param array $product_args
	 *
	 * @since 1.0
	 * @return Product
	 */
	public function add_product( $product_args ) {
		$product = new Product( $product_args );

		$this->products->add_item( $product->id, $product );

		return $product;
	}

}
