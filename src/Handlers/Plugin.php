<?php

namespace EasyDigitalDownloads\Updater\Handlers;

use EasyDigitalDownloads\Updater\Updaters\Plugin as PluginUpdater;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Represents the handler for plugins.
 */
class Plugin extends Handler {

	/**
	 * Adds the listeners for the updater.
	 *
	 * @since <next-version>
	 * @return void
	 */
	protected function add_listeners(): void {
		add_action( 'init', array( $this, 'auto_updater' ) );
		$plugin_basename = plugin_basename( $this->args['file'] );
		add_filter( "plugin_action_links_{$plugin_basename}", array( $this, 'plugin_links' ), 100, 3 );
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
	 * @return array
	 */
	public function plugin_links( $actions, $plugin_file, $plugin_data ) {
		if ( ! empty( $this->args['keyless'] ) ) {
			return $actions;
		}
		$actions['edd_sdk_manage'] = sprintf(
			'<button type="button" class="button-link edd-sdk__notice__trigger edd-sdk__notice__trigger--ajax" data-id="license-control" data-product="%1$s" data-slug="%2$s" data-name="%4$s">%3$s</button>',
			$this->args['item_id'],
			$this->args['slug'],
			__( 'Manage License', 'edd-sl-sdk' ),
			$plugin_data['Name']
		);

		add_action( 'admin_footer', array( $this, 'license_modal' ) );

		return $actions;
	}

	/**
	 * Outputs the license modal.
	 *
	 * @since <next-version>
	 * @return void
	 */
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
		wp_localize_script(
			'edd-sdk-notice',
			'edd_sdk_notice',
			array(
				'ajax_url'     => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'edd_sdk_notice' ),
				'activating'   => esc_html__( 'Activating...', 'edd-sl-sdk' ),
				'deactivating' => esc_html__( 'Deactivating...', 'edd-sl-sdk' ),
			)
		);
	}

	/**
	 * AJAX handler for getting a notice.
	 *
	 * @since <next-version>
	 * @return void
	 */
	public function ajax_get_license_overlay() {
		$template = filter_input( INPUT_GET, 'template', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! $template ) {
			wp_send_json_error( 'No template provided.' );
		}

		// $args = array(
		//  'item_id' => filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT ),
		//  'slug'    => filter_input( INPUT_GET, 'slug', FILTER_SANITIZE_SPECIAL_CHARS ),
		//  'name'    => filter_input( INPUT_GET, 'name', FILTER_SANITIZE_SPECIAL_CHARS ),
		// );

		$args            = $this->args;
		$args['license'] = $this->license;
		$args['name']    = filter_input( INPUT_GET, 'name', FILTER_SANITIZE_SPECIAL_CHARS );

		ob_start();
		?>
		<button class="button-link edd-sdk__notice--dismiss">
			×
			<span class="screen-reader-text"><?php esc_html_e( 'Dismiss notice', 'edd-sl-sdk' ); ?></span>
		</button>
		<?php
		\EasyDigitalDownloads\Updater\Templates::load( $template, $args );

		wp_send_json_success( ob_get_clean() );
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
