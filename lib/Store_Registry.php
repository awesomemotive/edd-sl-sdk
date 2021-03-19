<?php
/**
 * Store_Registry.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 */

namespace EDD_SL_SDK;

class Store_Registry extends Registry {

	/**
	 * Adds a new store to the registry.
	 *
	 * @param string $item_id
	 * @param array  $attributes
	 */
	public function add_item( $item_id, $attributes ) {
		// Add the ID as an attribute.
		$attributes['id'] = $item_id;

		$store = new Store( $attributes );

		parent::add_item( $item_id, $store );

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

}
