<?php

namespace EasyDigitalDownloads\Updater\Handlers;

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
		$this->ajax_listeners();
	}

	/**
	 * Adds the listeners for the updater.
	 *
	 * @since <next-version>
	 * @return void
	 */
	abstract protected function add_listeners(): void;

	/**
	 * Adds the AJAX listeners.
	 *
	 * @since <next-version>
	 * @return void
	 */
	private function ajax_listeners() {
		add_action( 'wp_ajax_edd_sdk_get_notice', array( $this, 'ajax_get_license_overlay' ) );
		add_action( 'wp_ajax_edd_sl_sdk_deactivate', array( $this->license, 'ajax_deactivate' ) );
		add_action( 'wp_ajax_edd_sl_sdk_activate', array( $this->license, 'ajax_activate' ) );
		add_action( 'wp_ajax_edd_sl_sdk_delete', array( $this->license, 'ajax_delete' ) );
	}
}
