<?php
/**
 * Updater class.
 *
 * @since <next-version>
 *
 * @package EasyDigitalDownloads\Updater\Updaters
 */

namespace EasyDigitalDownloads\Updater\Updaters;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EasyDigitalDownloads\Updater\Requests\API;

abstract class Updater {

	/**
	 * The URL for the API.
	 *
	 * @var string
	 */
	protected $api_url;

	/**
	 * The arguments for the updater.
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * The class constructor.
	 *
	 * @since <next-version>
	 * @param string $api_url The URL for the API.
	 * @param array  $args    Optional; used only for requests to non-EDD sites.
	 */
	public function __construct( $api_url, $args = array() ) {
		$this->api_url = $api_url;
		$defaults      = $this->get_api_request_defaults();
		$this->args    = array_merge( $defaults, array_intersect_key( $args, $defaults ) );
		$this->add_listeners();
	}

	/**
	 * Adds the listeners for the updater.
	 *
	 * @since <next-version>
	 * @return void
	 */
	abstract protected function add_listeners(): void;

	/**
	 * Gets the slug for the API request.
	 *
	 * @since <next-version>
	 * @return string
	 */
	abstract protected function get_slug(): string;

	/**
	 * Gets the name for the API request.
	 *
	 * @since <next-version>
	 * @return string
	 */
	abstract protected function get_name(): string;

	/**
	 * Gets the current version information from the remote site.
	 *
	 * @return array|false
	 */
	protected function get_version_from_remote() {
		$api_handler = new API( $this->api_url );

		return $api_handler->make_request( $this->args );
	}

	/**
	 * Gets the defaults for an API request.
	 *
	 * @since <next-version>
	 * @return array
	 */
	protected function get_api_request_defaults() {
		return array(
			'edd_action'  => 'get_version',
			'item_id'     => '',
			'version'     => '',
			'license'     => '',
			'php_version' => phpversion(),
			'wp_version'  => get_bloginfo( 'version' ),
			'slug'        => $this->get_slug(),
			'beta'        => false,
		);
	}

	/**
	 * Checks if the request should be made to the remote site.
	 *
	 * @since <next-version>
	 * @return bool
	 */
	protected function should_override_wp_check(): bool {
		return ! empty( $this->args['wp_override'] );
	}

	/**
	 * Gets the version number.
	 *
	 * @since <next-version>
	 * @return string
	 */
	protected function get_version() {
		return $this->args['version'];
	}

	/**
	 * Get the version info from the cache, if it exists.
	 *
	 * @param string $cache_key The cache key.
	 * @return object
	 */
	protected function get_cached_version_info() {

		$cache = get_option( $this->get_cache_key() );

		// Cache is expired.
		if ( empty( $cache['timeout'] ) || time() > $cache['timeout'] ) {
			return false;
		}

		return json_decode( $cache['value'] );
	}

	/**
	 * Adds the plugin version information to the database.
	 *
	 * @param string|\stdClass $value     The value to store.
	 * @param string           $cache_key The cache key.
	 */
	protected function set_version_info_cache( $value = '', $cache_key = '' ) {

		if ( empty( $cache_key ) ) {
			$cache_key = $this->get_cache_key();
		}

		$data = array(
			'timeout' => strtotime( $this->get_timeout(), time() ),
			'value'   => wp_json_encode( $value ),
		);

		update_option( $cache_key, $data, false );
	}

	/**
	 * Gets the unique key (option name) for a plugin.
	 *
	 * @since <next-version>
	 * @return string
	 */
	protected function get_cache_key() {
		$key = md5(
			wp_json_encode(
				array(
					$this->get_slug(),
					$this->args['license'],
					(int) (bool) $this->args['beta'],
				)
			)
		);

		return "edd_sl_{$key}";
	}

	/**
	 * Gets the timeout for the cache.
	 *
	 * @since <next-version>
	 * @return string
	 */
	private function get_timeout() {
		return ! empty( $this->args['cache_timeout'] ) ? $this->args['cache_timeout'] : '+3 hours';
	}
}
