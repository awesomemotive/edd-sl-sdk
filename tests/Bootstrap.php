<?php
/**
 * Bootstrap.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Tests;

use EDD_SL_SDK\SDK;

class Bootstrap {

	/**
	 * Bootstrap constructor.
	 */
	public function __construct() {
		$this->loadWordPress();
		$this->loadSdk();
		$this->disableHttpRequests();
		$this->loadHelpers();
	}

	/**
	 * Loads WordPress.
	 */
	private function loadWordPress() {
		$_tests_dir = getenv( 'WP_TESTS_DIR' ) ? : '/tmp/wordpress-tests-lib';
		require $_tests_dir . '/includes/bootstrap.php';
	}

	/**
	 * Loads the SL SDK.
	 */
	private function loadSdk() {
		require_once dirname( dirname( __FILE__ ) ) . '/src/SDK.php';
		SDK::instance();
	}

	/**
	 * Disables HTTP requests for unit tests.
	 */
	private function disableHttpRequests() {
		add_filter( 'pre_http_request', function ( $status = false, $args = [], $url = '' ) {
			return new \WP_Error( 'no_reqs_in_unit_tests', 'HTTP Requests disabled for unit tests' );
		}, 10, 3 );
	}

	/**
	 * Loads helpers.
	 */
	private function loadHelpers() {
		require_once dirname( __FILE__ ) . '/TestCase.php';
		require_once dirname( __FILE__ ) . '/PhpUnit/Factory.php';
		require_once dirname( __FILE__ ) . '/PhpUnit/Factories/StoreFactory.php';
		require_once dirname( __FILE__ ) . '/PhpUnit/Factories/ProductFactory.php';
	}

}

new Bootstrap();
