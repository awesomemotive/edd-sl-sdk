<?php
/**
 * License class.
 *
 * @since <next-version>
 *
 * @package EasyDigitalDownloads\Updater\Licensing\License
 * @copyright (c) 2025, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since <next-version>
 */

namespace EasyDigitalDownloads\Updater\Licensing;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * License class.
 *
 * @since <next-version>
 */
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
	 * @since <next-version>
	 * @return string
	 */
	public function get_license_key() {
		return get_option( $this->get_key_option_name() );
	}

	/**
	 * Gets the license key option name.
	 *
	 * @since <next-version>
	 * @return string
	 */
	public function get_key_option_name() {
		return ! empty( $this->args['option_name'] ) ? $this->args['option_name'] : "{$this->slug}_license_key";
	}

	/**
	 * Gets the button for the pass field.
	 *
	 * @since <next-version>
	 * @param bool $should_echo Whether to echo the button.
	 * @return string
	 */
	public function get_actions( $should_echo = false ) {
		$license_data = get_option( $this->get_status_option_name() );
		$status       = $license_data->license ?? 'inactive';
		$button       = $this->get_button_args( $status );
		$timestamp    = time();
		if ( ! $should_echo ) {
			ob_start();
		}
		?>
		<div class="edd-sl-sdk-licensing__actions">
			<button
				class="button button-<?php echo esc_attr( $button['class'] ); ?> edd-sl-sdk__action"
				data-action="<?php echo esc_attr( $button['action'] ); ?>"
				data-timestamp="<?php echo esc_attr( $timestamp ); ?>"
				data-token="<?php echo esc_attr( \EasyDigitalDownloads\Updater\Utilities\Tokenizer::tokenize( $timestamp ) ); ?>"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_sl_sdk_license_handler' ) ); ?>"
			>
				<?php echo esc_html( $button['label'] ); ?>
			</button>
			<?php if ( 'activate' === $button['action'] && ! empty( $this->get_license_key() ) ) : ?>
				<button
					class="button button-secondary edd-sl-sdk-license__delete"
					data-action="delete"
					data-timestamp="<?php echo esc_attr( $timestamp ); ?>"
					data-token="<?php echo esc_attr( \EasyDigitalDownloads\Updater\Utilities\Tokenizer::tokenize( $timestamp ) ); ?>"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_sl_sdk_license_handler-delete' ) ); ?>"
				>
					<?php esc_html_e( 'Delete', 'edd-sl-sdk' ); ?>
				</button>
			<?php endif; ?>
		</div>
		<?php
		if ( ! $should_echo ) {
			return ob_get_clean();
		}
	}

	/**
	 * AJAX handler for activating a license.
	 *
	 * @since <next-version>
	 * @return void
	 */
	public function ajax_activate() {
		if ( ! $this->can_manage_license() ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'You do not have permission to manage this license.', 'edd-sl-sdk' ) ),
				)
			);
		}

		$license_key  = filter_input( INPUT_POST, 'license', FILTER_SANITIZE_SPECIAL_CHARS );
		$api_params   = array(
			'edd_action' => 'activate_license',
			'license'    => $license_key,
			'item_id'    => $this->args['item_id'],
		);
		$api          = new \EasyDigitalDownloads\Updater\Requests\API( $this->args['api_url'] );
		$license_data = $api->make_request( $api_params );

		if ( empty( $license_data->success ) ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'There was an error activating your license. Please try again.', 'edd-sl-sdk' ) ),
				)
			);
		}

		update_option( $this->get_key_option_name(), $license_key );
		$this->save( $license_data );

		wp_send_json_success(
			array(
				'message' => wpautop( __( 'Your license was successfully activated.', 'easy-digital-downloads' ) ),
				'actions' => $this->get_actions(),
			)
		);
	}

	/**
	 * AJAX handler for deactivating a license.
	 *
	 * @since <next-version>
	 * @return void
	 */
	public function ajax_deactivate() {
		if ( ! $this->can_manage_license() ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'You do not have permission to manage this license.', 'edd-sl-sdk' ) ),
				)
			);
		}

		$license_key  = filter_input( INPUT_POST, 'license', FILTER_SANITIZE_SPECIAL_CHARS );
		$api_params   = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license_key,
			'item_id'    => $this->args['item_id'],
		);
		$api          = new \EasyDigitalDownloads\Updater\Requests\API( $this->args['api_url'] );
		$license_data = $api->make_request( $api_params );

		if ( empty( $license_data->success ) ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'There was an error deactivating your license. Please try again.', 'edd-sl-sdk' ) ),
				)
			);
		}

		delete_option( $this->get_status_option_name() );

		wp_send_json_success(
			array(
				'message' => wpautop( __( 'Your license was successfully deactivated.', 'easy-digital-downloads' ) ),
				'actions' => $this->get_actions(),
			)
		);
	}

	/**
	 * AJAX handler for deleting a license.
	 *
	 * @since <next-version>
	 * @return void
	 */
	public function ajax_delete() {
		if ( ! $this->can_manage_license( 'edd_sl_sdk_license_handler-delete' ) ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'You do not have permission to manage this license.', 'edd-sl-sdk' ) ),
				)
			);
		}

		delete_option( $this->get_key_option_name() );

		wp_send_json_success(
			array(
				'message' => wpautop( __( 'Your license was successfully deleted.', 'easy-digital-downloads' ) ),
				'actions' => $this->get_actions(),
			)
		);
	}

	/**
	 * AJAX handler for updating data tracking preference.
	 *
	 * @since <next-version>
	 * @return void
	 */
	public function ajax_update_tracking() {
		if ( ! $this->can_manage_license( 'edd_sl_sdk_data_tracking' ) ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'You do not have permission to manage this setting.', 'edd-sl-sdk' ) ),
				)
			);
		}

		$allow_tracking = filter_input( INPUT_POST, 'allow_tracking', FILTER_VALIDATE_BOOLEAN );

		// Save the preference with timestamp
		$option_name = $this->get_key_option_name() . '_allow_tracking';
		$data        = array(
			'allowed'   => $allow_tracking,
			'timestamp' => time(),
		);

		update_option( $option_name, $data );

		$message = $allow_tracking
			? __( 'Data tracking has been enabled.', 'edd-sl-sdk' )
			: __( 'Data tracking has been disabled.', 'edd-sl-sdk' );

		wp_send_json_success(
			array(
				'message' => wpautop( $message ),
			)
		);
	}

	/**
	 * Gets the allow tracking option name.
	 *
	 * @since <next-version>
	 * @return string
	 */
	public function get_allow_tracking() {
		$data = get_option( $this->get_key_option_name() . '_allow_tracking' );

		// Handle legacy boolean values
		if ( is_bool( $data ) ) {
			return $data;
		}

		// Handle new array format with timestamp
		if ( is_array( $data ) && isset( $data['allowed'] ) ) {
			return $data['allowed'];
		}

		return false;
	}

	/**
	 * Gets the license status message.
	 *
	 * @since <next-version>
	 * @return void
	 */
	public function get_license_status_message() {
		$status = get_option( $this->get_status_option_name() );
		if ( empty( $status ) || empty( $status->license ) ) {
			return;
		}

		$messages = new Messages(
			array(
				'status'      => $status->license,
				'license_key' => $this->get_license_key(),
				'expires'     => $status->expires,
				'name'        => $status->item_name,
			)
		);
		$message  = $messages->get_message();
		if ( $message ) {
			echo '<div class="edd-sl-sdk__license-status-message">' . wp_kses_post( wpautop( $message ) ) . '</div>';
		}
	}

	/**
	 * Saves the license data.
	 *
	 * @since <next-version>
	 * @param \stdClass $license_data The license data.
	 * @return void
	 */
	public function save( $license_data ) {
		update_option( $this->get_status_option_name(), $license_data );
	}

	/**
	 * Get the button parameters based on the status.
	 *
	 * @since <next-version>
	 * @param string $state
	 * @return array
	 */
	private function get_button_args( $state = 'inactive' ) {
		if ( in_array( $state, array( 'valid', 'active' ), true ) ) {
			return array(
				'action' => 'deactivate',
				'label'  => __( 'Deactivate', 'edd-sl-sdk' ),
				'class'  => 'secondary',
			);
		}

		return array(
			'action' => 'activate',
			'label'  => __( 'Activate', 'edd-sl-sdk' ),
			'class'  => 'secondary',
		);
	}

	/**
	 * Whether the current user can manage the pass.
	 * Checks the user capabilities, tokenizer, and nonce.
	 *
	 * @since <next-version>
	 * @param string $nonce The name of the specific nonce to validate.
	 * @return bool
	 */
	private function can_manage_license( $nonce_name = 'edd_sl_sdk_license_handler' ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$token     = filter_input( INPUT_POST, 'token', FILTER_SANITIZE_SPECIAL_CHARS );
		$timestamp = filter_input( INPUT_POST, 'timestamp', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( empty( $timestamp ) || empty( $token ) ) {
			return false;
		}

		$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_SPECIAL_CHARS );

		return \EasyDigitalDownloads\Updater\Utilities\Tokenizer::is_token_valid( $token, $timestamp ) && wp_verify_nonce( $nonce, $nonce_name );
	}

	/**
	 * Gets the status option name.
	 *
	 * @since <next-version>
	 * @return string
	 */
	public function get_status_option_name() {
		return ! empty( $this->args['option_name'] ) ? "{$this->args['option_name']}_license" : "{$this->slug}_license";
	}
}
