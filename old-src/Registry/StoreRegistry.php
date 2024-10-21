<?php
/**
 * StoreRegistry.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Registry;

use EDD_SL_SDK\Models\Store;

class StoreRegistry extends Registry {

	/**
	 * Adds a new store to the registry.
	 *
	 * @since 1.0
	 *
	 * @param string $itemId
	 * @param array  $attributes
	 */
	public function addItem( $itemId, $attributes ) {
		// Add the ID as an attribute.
		$attributes['id'] = $itemId;

		$store = new Store( $attributes );

		parent::addItem( $itemId, $store );

		/**
		 * Triggers after a store has been registered.
		 *
		 * @param Store $store
		 *
		 * @since 1.0
		 */
		do_action( 'edd_sl_after_store_registered', $store );

		return $store;
	}

	/**
	 * @return Store[]
	 */
	public function getItems() {
		return parent::getItems();
	}

}
