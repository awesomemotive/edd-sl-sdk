<?php
/**
 * Plugin Name: EDD SL SDK
 * Plugin URI: https://easydigitaldownloads.com
 * Description: The Software Licensing SDK for plugins and themes using Software Licensing.
 * Version: 1.0.0
 * Author: Easy Digital Downloads
 * Author URI: https://easydigitaldownloads.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: edd-sl-sdk
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

if ( ! function_exists( 'edd_sl_sdk_register_1_0_0' ) && function_exists( 'add_action' ) ) { // WRCS: DEFINED_VERSION.

	// Include the autoloader.
	require_once __DIR__ . '/vendor/autoload.php';

	add_action( 'after_setup_theme', array( '\\EasyDigitalDownloads\\Updater\\Versions', 'initialize_latest_version' ), 1, 0 );

	add_action( 'after_setup_theme', 'edd_sl_sdk_register_1_0_0', 0, 0 ); // WRCS: DEFINED_VERSION.

	/**
	 * Registers this version of Action Scheduler.
	 */
	function edd_sl_sdk_register_1_0_0() {
		$version  = '1.0.0';
		$versions = EasyDigitalDownloads\Updater\Versions::instance();
		$versions->register( $version, 'edd_sl_sdk_initialize_1_0_0' ); // WRCS: DEFINED_VERSION.
		if ( ! defined( 'EDD_SL_SDK_VERSION' ) ) {
			define( 'EDD_SL_SDK_VERSION', $version );
		}
	}

	// phpcs:disable Generic.Functions.OpeningFunctionBraceKernighanRitchie.ContentAfterBrace
	/**
	 * Registryializes this version of Action Scheduler.
	 */
	function edd_sl_sdk_initialize_1_0_0() {
		do_action( 'edd_sl_sdk_registry', EasyDigitalDownloads\Updater\Registry::instance() );
	}

	// Support usage in themes - load this version if no plugin has loaded a version yet.
	if ( did_action( 'after_setup_theme' ) && ! doing_action( 'after_setup_theme' ) && ! class_exists( '\\EasyDigitalDownloads\\Updater\\Registry', false ) ) {
		edd_sl_sdk_initialize_1_0_0(); // WRCS: DEFINED_VERSION.
		EasyDigitalDownloads\Updater\Versions::initialize_latest_version();
	}
}

// Folder Path.
if ( ! defined( 'EDD_SL_SDK_DIR' ) ) {
	define( 'EDD_SL_SDK_DIR', __DIR__ );
}

// Folder URL, based on this file.
if ( ! defined( 'EDD_SL_SDK_URL' ) ) {
	$is_https = ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] ) ||
				( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] );

	$protocol      = $is_https ? 'https' : 'http';
	$relative_path = str_replace( realpath( $_SERVER['DOCUMENT_ROOT'] ), '', __DIR__ );
	$relative_path = ltrim( str_replace( '\\', '/', $relative_path ), '/' );

	$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
	define( 'EDD_SL_SDK_URL', trailingslashit( "$protocol://$host/$relative_path" ) );
}
