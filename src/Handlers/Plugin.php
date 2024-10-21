<?php

namespace EasyDigitalDownloads\Updater\Handlers;

use EasyDigitalDownloads\Updater\Updaters\Plugin as PluginUpdater;
use EasyDigitalDownloads\Updater\Licensing\License;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Represents the handler for plugins.
 */
class Plugin {

	/**
	 * The URL for the API.
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * The arguments for the updater.
	 *
	 * @var array
	 */
	private $args;

	/**
	 * The slug for the plugin.
	 *
	 * @var string
	 */
	private $slug;

	private $license;

	/**
	 * The class constructor.
	 *
	 * @since <next-version>
	 * @param string $api_url The URL for the API.
	 * @param array  $args    Optional; used only for requests to non-EDD sites.
	 */
	public function __construct( string $api_url, array $args = array() ) {
		$this->api_url = $api_url;
		$this->args    = wp_parse_args(
			$args,
			array(
				'file'    => '',
				'item_id' => false,
				'version' => false,
			)
		);

		if ( empty( $this->args['keyless'] ) ) {
			$this->license = new License( $this->get_slug(), $this->args );
		}

		$this->add_listeners();
	}

	/**
	 * Adds the listeners for the updater.
	 *
	 * @since <next-version>
	 * @return void
	 */
	protected function add_listeners(): void {
		add_action( 'init', array( $this, 'auto_updater' ) );
	}

	/**
	 * Auto updater
	 *
	 * @return  void
	 */
	public function auto_updater() {

		if ( ! current_user_can( 'manage_options' ) && ! wp_doing_cron() ) {
			return;
		}

		$license_key = $this->get_license_key();
		if ( empty( $license_key ) && ! $this->supports_keyless_activation() ) {
			return;
		}

		$args = array(
			'file'    => $this->args['file'],
			'version' => $this->args['version'],
			'license' => $license_key,
			'item_id' => $this->args['item_id'],
			'beta'    => false,
		);

		// Set up the updater.
		new PluginUpdater(
			$this->api_url,
			$args
		);
	}

	/**
	 * Get the license key.
	 *
	 * @return string
	 */
	private function get_license_key() {
		if ( $this->supports_keyless_activation() || empty( $this->license ) ) {
			return '';
		}

		return $this->license->get_license_key();
	}

	/**
	 * Determines if the plugin supports keyless activation.
	 *
	 * @return bool
	 */
	private function supports_keyless_activation() {
		return ! empty( $this->args['keyless'] );
	}

	/**
	 * Gets the slug for the API request.
	 *
	 * @since <next-version>
	 * @return string
	 */
	protected function get_slug(): string {
		if ( empty( $this->args['file'] ) ) {
			return '';
		}

		if ( ! $this->slug ) {
			$this->slug = basename( dirname( $this->args['file'] ) );
		}

		return $this->slug;
	}
}
