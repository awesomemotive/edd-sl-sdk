<?php
/**
 * Factory.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Tests\PhpUnit;

use EDD_SL_SDK\Tests\PhpUnit\Factories\ProductFactory;
use EDD_SL_SDK\Tests\PhpUnit\Factories\StoreFactory;

class Factory extends \WP_UnitTest_Factory {

	/**
	 * @var StoreFactory
	 */
	public $store;

	/**
	 * @var ProductFactory
	 */
	public $product;

	/**
	 * Factory constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->setUpFactories();
	}

	/**
	 * Sets up all factories.
	 *
	 * @since 1.0
	 */
	private function setUpFactories() {
		$this->store   = new StoreFactory( $this );
		$this->product = new ProductFactory( $this );
	}

}
