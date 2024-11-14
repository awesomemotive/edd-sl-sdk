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
		return ! empty( $this->args['option_name'] ) ? $this->args['option_name'] : $this->slug . '_license_key';
	}

	/**
	 * Gets the button for the pass field.
	 *
	 * @since 3.1.1
	 * @param string $status The pass status.
	 * @param bool   $echo   Whether to echo the button.
	 * @return string
	 */
	public function get_actions( $status, $echo = false ) {
		$button    = $this->get_button_args( $status );
		$timestamp = time();
		if ( ! $echo ) {
			ob_start();
		}
		?>
		<div class="edd-licensing__actions">
			<button
				class="button button-<?php echo esc_attr( $button['class'] ); ?> edd-license__action"
				data-action="<?php echo esc_attr( $button['action'] ); ?>"
				data-timestamp="<?php echo esc_attr( $timestamp ); ?>"
				data-token="<?php echo esc_attr( \EDD\Utils\Tokenizer::tokenize( $timestamp ) ); ?>"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_sl_sdk_license_handler' ) ); ?>"
			>
				<?php echo esc_html( $button['label'] ); ?>
			</button>
			<?php if ( ! empty( $this->license_key ) && 'activate' === $button['action'] ) : ?>
				<button
					class="button button-secondary edd-license__delete"
					data-action="delete"
					data-timestamp="<?php echo esc_attr( $timestamp ); ?>"
					data-token="<?php echo esc_attr( \EDD\Utils\Tokenizer::tokenize( $timestamp ) ); ?>"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd_sl_sdk_license_handler-delete' ) ); ?>"
				>
					<?php esc_html_e( 'Delete', 'edd-sl-sdk' ); ?>
				</button>
			<?php endif; ?>
		</div>
		<?php
		if ( ! $echo ) {
			return ob_get_clean();
		}
	}

	/**
	 * Get the button parameters based on the status.
	 *
	 * @since 3.1.1
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
}
