<?php
/**
 * StoreFactory.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Tests\PhpUnit\Factories;

use EDD_SL_SDK\Helpers\Store;
use EDD_SL_SDK\SDK;

class StoreFactory extends \WP_UnitTest_Factory_For_Thing {

	/**
	 * StoreFactory constructor.
	 *
	 * @param null $factory
	 */
	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'api_url' => new \WP_UnitTest_Generator_Sequence( 'https://test-%s.easydigitaldownloads.com' ),
			'author'  => 'Store %s',
		];
	}

	/**
	 * Creates, registers, and retrieves a store.
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 * @param null  $generation_definitions
	 *
	 * @return Store
	 */
	public function create_and_get( $args = array(), $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}

	/**
	 * Creates and registers a store.
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return string Store ID
	 */
	public function create_object( $args ) {
		return SDK::instance()->registerStore( $args )->id;
	}

	/**
	 * Updating stores is not supported.
	 *
	 * @since 1.0
	 *
	 * @param int   $object
	 * @param array $fields
	 *
	 * @return mixed|void
	 * @throws \Exception
	 */
	public function update_object( $object, $fields ) {
		throw new \Exception( 'Method not supported.' );
	}

	/**
	 * Retrieves a store by its ID.
	 *
	 * @since 1.0
	 *
	 * @param string $object_id
	 *
	 * @return \EDD_SL_SDK\Models\Store
	 * @throws \EDD_SL_SDK\Exceptions\ItemNotFoundException
	 */
	public function get_object_by_id( $object_id ) {
		return Store::getById( $object_id );
	}
}
