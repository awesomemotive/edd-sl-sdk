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

	public function __construct( Product $product, $args = [] ) {
		$this->product = $product;

		foreach ( $this->buildArgs( $args ) as $key => $value ) {
			$this->{$key} = $value;
		}

		add_action( 'admin_menu', [ $this, 'registerPage' ] );
	}

	private function buildArgs( $args = [] ) {
		if ( ! is_array( $args ) ) {
			$args = [];
		}

		$title = Strings::getString( 'admin_page_title_' . $this->product->type, $this->product->i18n );

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

	public function display() {

	}

}
