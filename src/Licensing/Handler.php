<?php

namespace EasyDigitalDownloads\Updater\Licensing;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EasyDigitalDownloads\Updater\Requests\API;
use EasyDigitalDownloads\Updater\Utilities\Tokenizer;

class Handler {

	/**
	 * The license object.
	 *
	 * @since <next-version>
	 * @var License
	 */
	private $license;

	/**
	 * The URL for the API.
	 *
	 * @var string
	 */
	protected $api_url;

	/**
	 * The item ID.
	 *
	 * @var int
	 */
	protected $item_id;

	/**
	 * The license key.
	 *
	 * @var string
	 */
	protected $license_key;

	/**
	 * The class constructor.
	 *
	 * @since <next-version>
	 */
	public function __construct( $license ) {
		$this->license = $license;

		add_action( 'wp_ajax_eddsdk_activate', array( $this, 'activate_license' ) );
	}

	public function setting() {
		EasyDigitalDownloads\Updater\Templates::load( 'license-control' );
	}

	public function activate_license() {

		if ( ! $this->can_manage() ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'edd-sl-sdk' ) ) );
		}

		// Gets the license key from the request.
		$license = filter_input( INPUT_POST, 'license', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! $license ) {
			wp_send_json_error( array( 'message' => __( 'Invalid license key.', 'edd-sl-sdk' ) ) );
		}

		// Call the custom API.
		$response = $this->make_remote_request();

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred; please try again.', 'edd-sl-sdk' );
			}
		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch ( $license_data->error ) {

					case 'expired':
						$message = sprintf(
						/* translators: the license key expiration date */
							__( 'Your license key expired on %s.', 'edd-sl-sdk' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, time() ) )
						);
						break;

					case 'disabled':
					case 'revoked':
						$message = __( 'Your license key has been disabled.', 'edd-sl-sdk' );
						break;

					case 'missing':
						$message = __( 'Invalid license.', 'edd-sl-sdk' );
						break;

					case 'invalid':
					case 'site_inactive':
						$message = __( 'Your license is not active for this URL.', 'edd-sl-sdk' );
						break;

					case 'no_activations_left':
						$message = __( 'Your license key has reached its activation limit.', 'edd-sl-sdk' );
						break;

					default:
						$message = __( 'An error occurred; please try again.', 'edd-sl-sdk' );
						break;
				}
			}
		}

		// Check if anything passed on a message constituting a failure.
		if ( ! empty( $message ) ) {
			wp_send_json_error( array( 'message' => $message ) );
		}

		// $license_data->license will be either "valid" or "invalid"
		if ( 'valid' === $license_data->license ) {
			update_option( $this->license->get_option_name(), $license );
		}
		update_option( $this->license->get_option_name() . '_status', $license_data->license );
		wp_send_json_success( array( 'message' => __( 'License activated.', 'edd-sl-sdk' ) ) );
	}

	/**
	 * Gets the button for the pass field.
	 *
	 * @since <next-version>
	 * @param string $status The pass status.
	 * @param string $key    The license key.
	 * @param bool   $echo   Whether to echo the button.
	 * @return string
	 */
	private function get_actions( $status, $key = '', $echo = false ) {
		$button    = $this->get_button_args( $status, $key );
		$timestamp = time();
		if ( ! $echo ) {
			ob_start();
		}
		?>
		<div class="edd-sl-sdk__actions">
			<button
				class="button button-<?php echo esc_attr( $button['class'] ); ?> edd-sl-sdk__action"
				data-action="<?php echo esc_attr( $button['action'] ); ?>"
				data-timestamp="<?php echo esc_attr( $timestamp ); ?>"
				data-token="<?php echo esc_attr( Tokenizer::tokenize( $timestamp ) ); ?>"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd-sl-sdk' ) ); ?>"
			>
				<?php echo esc_html( $button['label'] ); ?>
			</button>
			<?php if ( ! empty( $key ) && in_array( $button['action'], array( 'activate' ), true ) ) : ?>
				<button
					class="button button-secondary edd-sl-sdk__delete"
					data-action="delete"
					data-timestamp="<?php echo esc_attr( $timestamp ); ?>"
					data-token="<?php echo esc_attr( Tokenizer::tokenize( $timestamp ) ); ?>"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd-sl-sdk-delete' ) ); ?>"
				>
					<?php esc_html_e( 'Delete', 'edd-sl-sdk' ); ?>
				</button>
				<?php
			endif;
			?>
		</div>
		<?php
		if ( ! $echo ) {
			return ob_get_clean();
		}
	}

	/**
	 * Get the button parameters based on the status.
	 *
	 * @since <next-version>
	 * @param string $state
	 * @param string $key
	 * @return array
	 */
	private function get_button_args( $state = 'inactive', $key = '' ) {
		if ( ! empty( $key ) && in_array( $state, array( 'valid', 'active' ), true ) ) {
			return array(
				'action' => 'deactivate',
				'label'  => __( 'Deactivate', 'edd-sl-sdk' ),
				'class'  => 'secondary',
			);
		}

		return array(
			'action' => 'activate',
			'label'  => __( 'Activate License', 'edd-sl-sdk' ),
			'class'  => 'primary',
		);
	}

	/**
	 * Whether the current user can manage the extension.
	 * Checks the user capabilities, tokenizer, and nonce.
	 *
	 * @since <next-version>
	 * @param string $nonce The name of the specific nonce to validate.
	 * @return bool
	 */
	private function can_manage( $nonce = 'edd-sl-sdk' ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$token     = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';
		$timestamp = isset( $_POST['timestamp'] ) ? sanitize_text_field( $_POST['timestamp'] ) : '';

		if ( empty( $timestamp ) || empty( $token ) ) {
			return false;
		}

		return Tokenizer::is_token_valid( $token, $timestamp ) && wp_verify_nonce( $_POST['nonce'], $nonce );
	}

	private function make_remote_request() {
		$api_handler = new API( $this->api_url );

		return $api_handler->make_request(
			array(
				'edd_action'  => $this->action,
				'license'     => $this->license_key,
				'item_id'     => $this->item_id,
				'environment' => wp_get_environment_type(),
			)
		);
	}
}
