<?php
/**
 * License class.
 *
 * @since <next-version>
 *
 * @package EasyDigitalDownloads\Updater
 * @subpackage Licensing
 */

namespace EasyDigitalDownloads\Updater\Licensing;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class License {

	/**
	 * The slug.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * The arguments.
	 *
	 * @var array
	 */
	private $args;

	/**
	 * The class constructor.
	 *
	 * @since <next-version>
	 * @param string $slug The slug.
	 * @param array  $args The arguments.
	 */
	public function __construct( $slug, $args ) {
		$this->slug = $slug;
		$this->args = $args;
	}

	/**
	 * Get the license key.
	 *
	 * @return string
	 */
	public function get_license_key() {
		return get_option( $this->get_key_option_name() );
	}

	/**
	 * Gets the license key option name.
	 *
	 * @return void
	 */
	public function get_key_option_name() {
		return ! empty( $this->args['option_name'] ) ? $this->args['option_name'] : $this->get_slug() . '_license_key';
	}
}
