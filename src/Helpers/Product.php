<?php
/**
 * Product.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Helpers;

use EDD_SL_SDK\Exceptions\ItemNotFoundException;
use EDD_SL_SDK\SDK;

class Product {

	/**
	 * Retrieves a plugin by its __FILE__.
	 *
	 * @param string $file Plugin file.
	 *
	 * @return \EDD_SL_SDK\Models\Product
	 * @throws ItemNotFoundException
	 */
	public static function getPlugin( $file ) {
		foreach ( SDK::instance()->storeRegistry->getItems() as $store ) {

			$products = $store->getProducts( [ 'type' => 'plugin', 'file' => $file ] );

			if ( ! empty( $products ) ) {
				return reset( $products );
			}
		}

		throw new ItemNotFoundException( 'Plugin not found.' );
	}

	/**
	 * Retrieves a theme by its slug.
	 *
	 * @param string $slug Theme slug.
	 *
	 * @return \EDD_SL_SDK\Models\Product
	 * @throws ItemNotFoundException
	 */
	public static function getTheme( $slug ) {
		foreach ( SDK::instance()->storeRegistry->getItems() as $store ) {

			$products = $store->getProducts( [ 'type' => 'theme', 'slug' => $slug ] );

			if ( ! empty( $products ) ) {
				return reset( $products );
			}
		}

		throw new ItemNotFoundException( 'Theme not found.' );
	}

}
