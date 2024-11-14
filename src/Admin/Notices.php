<?php
/**
 * Notices class.
 *
 * @since <next-version>
 *
 * @package EasyDigitalDownloads\Updater
 * @subpackage Admin
 */

namespace EasyDigitalDownloads\Updater\Admin;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Notices
 */
class Notices {

	/**
	 * The notices.
	 *
	 * @var array
	 */
	private $notices = array();

	/**
	 * Notices constructor.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'render' ), 100 );
		add_action( 'wp_ajax_edd_sdk_get_notice', array( $this, 'ajax_get_notice' ) );
	}

	/**
	 * Add a notice.
	 *
	 * @since <next-version>
	 * @param array $args The notice arguments.
	 */
	public static function add( array $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'id'      => '',
				'type'    => 'info',
				'message' => '',
				'classes' => array(),
			)
		);
		if ( empty( $args['message'] ) || empty( $args['id'] ) ) {
			return;
		}

		$classes = array( 'notice' );

		if ( ! empty( $args['type'] ) ) {
			$classes[] = 'notice-' . $args['type'];
		}

		if ( ! empty( $args['classes'] ) ) {
			$classes = array_merge( $classes, $args['classes'] );
		}

		$this->notices[ $args['id'] ] = array(
			'message' => $args['message'],
			'classes' => $classes,
		);
	}

	/**
	 * Render the notices.
	 */
	public function render() {
		if ( empty( $this->notices ) ) {
			return;
		}

		foreach ( $this->notices as $id => $args ) {
			?>
			<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $args['classes'] ) ); ?>">
				<p><?php echo wp_kses_post( $args['message'] ); ?></p>
			</div>
			<?php
		}
	}

	public function ajax_get_notice() {
		$template = filter_input( INPUT_GET, 'template', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! $template ) {
			wp_send_json_error( 'No template provided.' );
		}

		$args = array(
			'item_id' => filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT ),
			'slug'    => filter_input( INPUT_GET, 'slug', FILTER_SANITIZE_SPECIAL_CHARS ),
		);

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
}
