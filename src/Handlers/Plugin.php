<?php
/**
 * Plugin handler.
 *
 * @since 1.0.0
 *
 * @package EasyDigitalDownloads\Updater\Handlers
 */

namespace EasyDigitalDownloads\Updater\Handlers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

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
	 * @since 1.0.0
	 * @return void
	 */
	protected function add_listeners(): void {
		$plugin_basename = plugin_basename( $this->args['file'] );
		add_filter( "plugin_action_links_{$plugin_basename}", array( $this, 'plugin_links' ), 100, 3 );
		add_action( 'admin_print_scripts-plugins.php', array( $this, 'print_early_trigger_buffer' ) );
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

		$args = wp_parse_args(
			array(
				'file' => $this->args['file'],
			),
			$this->get_default_api_request_args()
		);

		// Set up the updater.
		new PluginUpdater(
			$this->api_url,
			$args,
			$this->messenger
		);
	}

	/**
	 * Adds the activation link to the plugin list.
	 *
	 * @since 1.0.0
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
			$this->messenger->get_manage_license_link_label(),
			$plugin_data['Name']
		);

		add_action( 'admin_footer', array( $this, 'license_modal' ) );

		return $actions;
	}

	/**
	 * Buffers clicks on the license trigger until the SDK script is ready.
	 *
	 * The "Manage License" trigger is rendered with the plugins list, but its
	 * click listener is only bound once the SDK footer script has executed.
	 * On plugin-heavy sites that leaves a multi-second window in which clicks
	 * are silently ignored. This tiny head script catches those early clicks,
	 * shows the core spinner state as feedback, and re-dispatches the click
	 * once the SDK is ready.
	 *
	 * @since 1.0.4
	 * @return void
	 */
	public function print_early_trigger_buffer() {
		static $did_run;
		if ( $did_run ) {
			return;
		}
		$did_run = true;

		$script = <<<'JS'
( function() {
	var pending = false;
	document.addEventListener( 'click', function( event ) {
		var trigger = event.target && event.target.closest ? event.target.closest( '.edd-sdk__notice__trigger' ) : null;
		if ( ! trigger || pending || document.querySelector( '.edd-sdk__notice__overlay' ) ) {
			return;
		}
		event.preventDefault();
		event.stopImmediatePropagation();
		pending = true;
		trigger.classList.add( 'updating-message' );
		var started = Date.now();
		var timer = window.setInterval( function() {
			var ready = document.querySelector( '.edd-sdk__notice__overlay' );
			if ( ! ready && Date.now() - started < 15000 ) {
				return;
			}
			window.clearInterval( timer );
			trigger.classList.remove( 'updating-message' );
			pending = false;
			if ( ready ) {
				trigger.click();
			}
		}, 200 );
	}, true );
} )();
JS;

		wp_print_inline_script_tag( $script );
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
	 * @since 1.0.0
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
