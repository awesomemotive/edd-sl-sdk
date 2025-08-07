<?php

namespace EasyDigitalDownloads\Updater;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

class Templates {

	public static function load( string $file, array $args = array() ) {
		$templates_path = self::get_templates_path();
		$template       = $templates_path . $file . '.php';

		if ( ! file_exists( $template ) ) {
			return;
		}

		load_template( $template, false, $args );
	}

	/**
	 * Get the templates path.
	 *
	 * @since <next-version>
	 * @return string
	 */
	private static function get_templates_path() {
		return apply_filters( 'edd_sl_sdk_templates_path', trailingslashit( EDD_SL_SDK_DIR ) . 'templates/' );
	}
}
