<?php
/**
 * PageRegistration.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\AdminPages;

use EDD_SL_SDK\Helpers\Strings;
use EDD_SL_SDK\Models\Product;

class PageRegistration {

	/**
	 * @var Product
	 */
	private $product;

	private $page_title;
	private $menu_title;
	private $capability = 'manage_options';
	private $menu_slug;
	private $display_callback;
	private $icon;
	private $position;

	/**
	 * PageRegistration constructor.
	 *
	 * @param Product $product
	 * @param array   $args
	 */
	public function __construct( Product $product, $args = [] ) {
		$this->product = $product;

		foreach ( $this->buildArgs( $args ) as $key => $value ) {
			$this->{$key} = $value;
		}

		add_action( 'admin_menu', [ $this, 'registerPage' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ], 20 );
	}

	/**
	 * Builds the arguments for registering an admin page.
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	private function buildArgs( $args = [] ) {
		if ( ! is_array( $args ) ) {
			$args = [];
		}

		$title = $this->product->getString( 'admin_page_title_' . $this->product->type );

		return wp_parse_args( $args, [
			'parent_slug'      => null,
			'page_title'       => $title,
			'menu_title'       => $title,
			'capability'       => 'manage_options',
			'menu_slug'        => sprintf( '%s-license', $this->product->slug ),
			'display_callback' => [ $this, 'display' ],
			'icon'             => '',
			'position'         => null
		] );
	}

	/**
	 * Registers the admin page.
	 *
	 * @since 1.0
	 */
	public function registerPage() {
		if ( empty( $this->parent_slug ) ) {
			add_menu_page(
				$this->page_title,
				$this->menu_title,
				$this->capability,
				$this->menu_slug,
				$this->display_callback,
				$this->icon,
				$this->position
			);
		} else {
			add_submenu_page(
				$this->parent_slug,
				$this->page_title,
				$this->menu_title,
				$this->capability,
				$this->menu_slug,
				$this->display_callback,
				$this->position
			);
		}
	}

	public function enqueueAssets() {
		wp_enqueue_script( 'edd-sl-sdk-js' );
		wp_enqueue_style( 'edd-sl-sdk-css' );
	}

	/**
	 * Displays the admin page.
	 */
	public function display() {
		$action = $this->product->licenseIsActivated() ? AjaxHandler::DEACTIVATE_LICENSE : AjaxHandler::ACTIVATE_LICENSE;
		?>
		<div class="wrap">
		<h2><?php echo esc_html( $this->page_title ) ?></h2>
		<form class="edd-sl-sdk-license-form" method="POST">
			<input type="hidden" name="productId" value="<?php echo esc_attr( 'theme' === $this->product->type ? $this->product->slug : $this->product->file ); ?>">
			<input type="hidden" name="productType" value="<?php echo esc_attr( $this->product->type ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
			<?php wp_nonce_field( $action ); ?>
			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row" valign="top">
						<label for="<?php echo esc_attr( sanitize_html_class( $this->product->id . '_license_key' ) ); ?>">
							<?php echo esc_html( $this->product->getString( 'license_key' ) ); ?>
						</label>
					</th>
					<td>
						<input
							type="text"
							id="<?php echo esc_attr( sanitize_html_class( $this->product->id . '_license_key' ) ); ?>"
							class="regular-text"
							name="license_key"
							value="<?php echo esc_attr( $this->product->license ); ?>"
							required
						/>

						<button
							type="submit"
							class="button"
						>
							<?php echo esc_html( $this->product->getString(
								$this->product->licenseIsActivated() ? 'deactivate_license' : 'activate_license'
							) ); ?>
						</button>

						<div class="<?php echo esc_attr( implode( ' ', $this->getStatusClasses() ) ); ?>">
							<?php $this->renderStatusText(); ?>
						</div>
					</td>
				</tr>
				</tbody>
			</table>
		</form>
		<?php
	}

	private function getStatusClasses() {
		$classes = [
			'edd-sl-sdk-license-response'
		];

		if ( $this->product->license ) {
			try {
				$license = $this->product->getLicenseData();

				if ( $license->activated && 'active' === $license->status ) {
					$classes[] = 'edd-sl-sdk-license-response__valid';
				} elseif ( 'valid' !== $license->status ) {
					$classes[] = 'edd-sl-sdk-license-response__invalid';
				}
			} catch ( \Exception $e ) {
				$classes[] = 'edd-sl-sdk-license-response__inactive';
			}
		}

		return array_map( 'sanitize_html_class', $classes );
	}

	/**
	 * Renders a description showing the status of the license key.
	 *
	 * @since 1.0
	 */
	private function renderStatusText() {
		if ( ! $this->product->license ) {
			return;
		}

		try {
			echo esc_html( $this->product->getLicenseData()->getStatusHtml( $this->product->i18n ) );
		} catch ( \Exception $e ) {
			echo esc_html( $this->product->getString( 'license_inactive' ) );
		}
	}

}
