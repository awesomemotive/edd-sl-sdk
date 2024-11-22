<?php
/**
 * Theme handler.
 *
 * @since <next-version>
 *
 * @package EasyDigitalDownloads\Updater\Handlers
 */

namespace EasyDigitalDownloads\Updater\Handlers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EasyDigitalDownloads\Updater\Updaters\Theme as ThemeUpdater;

/**
 * Represents the handler for themes.
 */
class Theme extends Handler {

	/**
	 * Adds the listeners for the updater.
	 *
	 * @since <next-version>
	 * @return void
	 */
	protected function add_listeners(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	/**
	 * Adds the menu item.
	 *
	 * @since <next-version>
	 * @return void
	 */
	public function add_menu() {
		global $submenu;

		if ( empty( $submenu['themes.php'] ) ) {
			return;
		}

		$submenu['themes.php'][] = array( // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			sprintf(
				'<span class="edd-sdk__notice__trigger edd-sdk__notice__trigger--ajax" data-product="%s" data-slug="%s">%s</span>',
				$this->args['item_id'],
				$this->args['slug'],
				__( 'Theme License', 'edd-sl-sdk' )
			),
			'manage_options',
			'edd_sl_sdk_theme_license',
		);

		add_action( 'admin_footer', array( $this, 'license_modal' ) );
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
			'version' => $this->args['version'],
			'license' => $license_key,
			'item_id' => $this->args['item_id'],
			'beta'    => false,
			'url'     => $this->args['url'],
		);

		// Set up the updater.
		new ThemeUpdater(
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
		return $this->args['id'];
	}
}
