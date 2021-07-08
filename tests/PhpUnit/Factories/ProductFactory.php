<?php
/**
 * ProductFactory.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Tests\PhpUnit\Factories;

use EDD_SL_SDK\Models\Product;

class ProductFactory extends \WP_UnitTest_Factory_For_Thing {

	/**
	 * ProductFactory constructor.
	 *
	 * @param null $factory
	 */
	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'store_id'   => new \WP_UnitTest_Generator_Sequence( 'store-%s' ),
			'type'       => 'plugin',
			'product_id' => new \WP_UnitTest_Generator_Sequence( '%d' ),
			'file'       => new \WP_UnitTest_Generator_Sequence( '/path/to/%s.php' ),
			'version'    => '1.0',
			'slug'       => new \WP_UnitTest_Generator_Sequence( 'slug-%s' ),
			'beta'       => false
		];
	}

	/**
	 * Creates and retrieves a product.
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return Product
	 */
	public function create_and_get( $args = array(), $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}

	/**
	 * Creates a product.
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return Product
	 */
	public function create( $args = array(), $generation_definitions = null ) {
		return parent::create( $args, $generation_definitions );
	}

	/**
	 * Creates a new product.
	 *
	 * @param array $args
	 *
	 * @return Product
	 */
	public function create_object( $args ) {
		return new Product( $args );
	}

	public function update_object( $object, $fields ) {
		throw new \Exception();
	}

	/**
	 * This method is weird, becuase `create_object()` returns the full object instead of an ID.
	 * So we just throw the property back out.
	 *
	 * @param Product $object_id
	 *
	 * @return Product
	 */
	public function get_object_by_id( $object_id ) {
		return $object_id;
	}
}
