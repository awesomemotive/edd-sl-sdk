<?php
/**
 * AssetLoader.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\AdminPages;

use EDD_SL_SDK\SDK;

class AssetLoader {

	/**
	 * AssetLoader constructor.
	 */
	public function __construct() {
		$this->registerJs();
		$this->registerCss();
	}

	/**
	 * Retrieves the URL to an asset by its filename.
	 *
	 * @since 1.0
	 *
	 * @param string $assetName
	 *
	 * @return string
	 */
	private function getAssetUrl( $assetName ) {
		$assetDir = trailingslashit( dirname( SDK::$dir ) ) . 'assets/build/' . $assetName;

		return content_url( str_replace( WP_CONTENT_DIR, '', $assetDir ) );
	}

	/**
	 * Registers admin JavaScript.
	 *
	 * @since 1.0
	 */
	private function registerJs() {
		wp_register_script(
			'edd-sl-sdk-js',
			$this->getAssetUrl( 'index.js' ),
			[],
			SDK::$version,
			true
		);
	}

	/**
	 * Registers admin CSS.
	 *
	 * @since 1.0
	 */
	private function registerCss() {
		wp_register_style(
			'edd-sl-sdk-css',
			$this->getAssetUrl( 'style-index.css' ),
			[],
			SDK::$version
		);
	}

}
