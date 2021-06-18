<?php
/**
 * Environment.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Models;

use EDD_SL_SDK\Traits\Serializable;

class Environment {

	use Serializable;

	/**
	 * @var string Current PHP version.
	 * @since 1.0
	 */
	public $php;

	/**
	 * @var string Current WordPress version.
	 * @since 1.0
	 */
	public $wp;

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
	 * Environment constructor.
	 */
	public function __construct() {
		$this->php         = phpversion();
		$this->wp          = get_bloginfo( 'version' );
		$this->url         = home_url();
		$this->environment = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';
	}
}
