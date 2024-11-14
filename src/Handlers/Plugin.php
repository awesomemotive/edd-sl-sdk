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
				'slug'    => '',
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
		$plugin_basename = plugin_basename( $this->args['file'] );
		add_filter( "plugin_action_links_{$plugin_basename}", array( $this, 'plugin_links' ), 100, 4 );
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
	 * Adds the activation link to the plugin list.
	 *
	 * @since <next-version>
	 * @param array  $actions     The plugin actions.
	 * @param string $plugin_file The plugin file.
	 * @param array  $plugin_data The plugin data.
	 * @param string $context     The context.
	 * @return array
	 */
	public function plugin_links( $actions, $plugin_file, $plugin_data, $context ) {
		if ( ! empty( $this->args['keyless'] ) ) {
			return $actions;
		}
		$actions['edd_sdk_manage'] = sprintf(
			'<button type="button" class="button-link edd-sdk__notice__trigger edd-sdk__notice__trigger--ajax" data-id="license-control" data-product="%1$s" data-slug="%2$s">%3$s</button>',
			$this->args['item_id'],
			$this->get_slug(),
			__( 'Manage License', 'edd-sl-sdk' )
		);

		add_action( 'admin_footer', array( $this, 'license_modal' ) );

		return $actions;
	}

	public function license_modal() {
		static $did_run;
		if ( $did_run ) {
			return;
		}
		$did_run = true;
		?>
		<div class="edd-sdk-notice--overlay"></div>
		<?php
		wp_enqueue_script( 'edd-sdk-notice', EDD_SL_SDK_URL . 'assets/build/js/edd-sl-sdk.js', array(), '1.0.0', true );
		wp_enqueue_style( 'edd-sdk-notice', EDD_SL_SDK_URL . 'assets/build/css/style-edd-sl-sdk.css', array(), '1.0.0' );
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
