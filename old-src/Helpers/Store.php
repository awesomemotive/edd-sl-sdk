<?php
/**
 * Store.php
 *
 * @package   edd-sl-sdk
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EDD_SL_SDK\Helpers;

use EDD_SL_SDK\Exceptions\ItemNotFoundException;
use EDD_SL_SDK\SDK;

class Store {

	/**
	 * Retrieves a store by its ID.
	 *
	 * @since 1.0
	 *
	 * @param string $storeId
	 *
	 * @return \EDD_SL_SDK\Models\Store
	 * @throws ItemNotFoundException
	 */
	public static function getById( $storeId ) {
		return SDK::instance()->storeRegistry->get( $storeId );
	}

}
