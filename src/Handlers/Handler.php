<?php
/**
 * Handler class.
 *
 * @since <next-version>
 *
 * @package EasyDigitalDownloads\Updater
 * @subpackage Handlers
 */

namespace EasyDigitalDownloads\Updater\Handlers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EasyDigitalDownloads\Updater\Licensing\License;

abstract class Handler {

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
	protected $args;

	/**
	 * The slug for the plugin.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The license object.
	 *
	 * @var License
	 */
	protected $license;

	/**
	 * The class constructor.
	 *
	 * @since <next-version>
	 * @param string $api_url The URL for the API.
	 * @param array  $args    Optional; used only for requests to non-EDD sites.
	 */
	public function __construct( string $api_url, array $args = array() ) {
		$this->api_url      = $api_url;
		$this->args         = wp_parse_args(
			$args,
			array(
				'file'    => '',
				'item_id' => false,
				'version' => false,
				'api_url' => $api_url,
			)
		);
		$this->args['slug'] = $this->get_slug();

		$this->license = new License( $this->args['slug'], $this->args );

		$this->add_listeners();
		$this->add_general_listeners();
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
			$this->get_localization_args()
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
	 * Initializes the auto updater.
	 *
	 * @since <next-version>
	 * @return void
	 */
	abstract public function auto_updater();

	/**
	 * Adds the listeners for the updater.
	 *
	 * @since <next-version>
	 * @return void
	 */
	abstract protected function add_listeners(): void;

	/**
	 * Adds the listeners used by all handlers.
	 *
	 * @since <next-version>
	 * @return void
	 */
	private function add_general_listeners() {
		$slug = $this->args['slug'];
		add_action( 'init', array( $this, 'auto_updater' ) );
		add_action( 'wp_ajax_edd_sdk_get_notice_' . $slug, array( $this, 'ajax_get_license_overlay' ) );
		add_action( 'wp_ajax_edd_sl_sdk_deactivate_' . $slug, array( $this->license, 'ajax_deactivate' ) );
		add_action( 'wp_ajax_edd_sl_sdk_activate_' . $slug, array( $this->license, 'ajax_activate' ) );
		add_action( 'wp_ajax_edd_sl_sdk_delete_' . $slug, array( $this->license, 'ajax_delete' ) );
		add_action( 'wp_ajax_edd_sl_sdk_update_tracking_' . $slug, array( $this->license, 'ajax_update_tracking' ) );
	}

	/**
	 * Gets the localization arguments.
	 *
	 * @since <next-version>
	 * @return array
	 */
	protected function get_localization_args() {
		return array(
			'ajax_url'     => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'edd_sdk_notice' ),
			'activating'   => esc_html__( 'Activating...', 'edd-sl-sdk' ),
			'deactivating' => esc_html__( 'Deactivating...', 'edd-sl-sdk' ),
			'error'        => esc_html__( 'An unknown error occurred.', 'edd-sl-sdk' ),
		);
	}

	/**
	 * Gets the default API request arguments.
	 *
	 * @since <next-version>
	 * @return array
	 */
	protected function get_default_api_request_args() {
		return array(
			'version'        => $this->args['version'],
			'license'        => $this->license->get_license_key(),
			'item_id'        => $this->args['item_id'],
			'beta'           => false,
			'url'            => $this->args['url'],
			'allow_tracking' => $this->license->get_allow_tracking(),
		);
	}
}
