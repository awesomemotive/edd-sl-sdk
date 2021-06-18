<?php
/**
 * Store.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Models;

use EDD_SL_SDK\Exceptions;
use EDD_SL_SDK\ProductRegistry;

class Store {

	/**
	 * Unique ID for this store.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * API URL. This is the domain that Software Licensing is installed on.
	 *
	 * @var string
	 */
	public $api_url;

	/**
	 * Contains all of this store's products.
	 *
	 * @var ProductRegistry
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

		$this->setProducts( $args['products'] );
	}

	/**
	 * Builds the product registry for this store.
	 *
	 * @param array $products
	 *
	 * @since 1.0
	 */
	private function setProducts( $products ) {
		$this->products = new ProductRegistry();

		if ( ! empty( $products ) && is_array( $products ) ) {
			foreach ( $products as $product_args ) {
				$this->addProduct( $product_args );
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
	public function getProducts( $query_args = [] ) {
		return ! empty( $query_args ) ? $this->products->query( $query_args ) : $this->products->getItems();
	}

	/**
	 * Adds a new product.
	 *
	 * @param array $product_args
	 *
	 * @since 1.0
	 * @return Product
	 */
	public function addProduct( $product_args ) {
		$product_args['store_id'] = $this->id;

		$product = new Product( $product_args );

		try {
			return $this->products->get( $product->id );
		} catch ( Exceptions\ItemNotFoundException $e ) {
			$this->products->addItem( $product->id, $product );

			return $product;
		}
	}

}
