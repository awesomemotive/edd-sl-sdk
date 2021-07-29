<?php
/**
 * System.php
 *
 * Contains information about the current system (PHP version, etc.).
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Models;

use EDD_SL_SDK\Traits\Serializable;

class System {

	use Serializable;

	/**
	 * @var string Current PHP version.
	 * @since 1.0
	 */
	public $php_version;

	/**
	 * @var string Current WordPress version.
	 * @since 1.0
	 */
	public $wp_version;

	/**
	 * @var string Site URL.
	 * @since 1.0
	 */
	public $url;

	/**
	 * @var string Environment. One of: `local`, `development`, `staging`, `production`.
	 * @since 1.0
	 */
	public $environment;

	/**
	 * System constructor.
	 */
	public function __construct() {
		$this->php_version = phpversion();
		$this->wp_version  = get_bloginfo( 'version' );
		$this->url         = home_url();
		$this->environment = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';
	}
}
