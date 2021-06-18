<?php
/**
 * Registry
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 */

namespace EDD_SL_SDK;

class Registry extends \ArrayObject {

	/**
	 * Array of registered items.
	 *
	 * @var array
	 */
	private $items = array();

	/**
	 * Sets all items.
	 *
	 * @param array $items
	 */
	public function setItems( $items ) {
		$this->items = $items;
	}

	/**
	 * Adds a single item to the registry.
	 *
	 * @param int|string $itemId     Item ID.
	 * @param mixed      $attributes Item attributes/value.
	 */
	public function addItem( $itemId, $attributes ) {
		$this->items[ $itemId ] = $attributes;
	}

	/**
	 * Removes an item by its ID.
	 *
	 * @param int|string $item_id Item ID.
	 */
	public function removeItem( $item_id ) {
		unset( $this->items[ $item_id ] );
	}

	/**
	 * Retrieves a single item by its ID.
	 *
	 * @param int|string $item_id
	 *
	 * @return mixed|false
	 */
	public function get( $item_id ) {
		if ( isset( $this->items[ $item_id ] ) ) {
			return $this->items[ $item_id ];
		}

		return false;
	}

	/**
	 * Retrieve all items.
	 *
	 * @return array
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * Queries the registry fields against a set of specific parameters.
	 *
	 * Makes querying of any registry's items fast and straightforward with dynamically-built
	 * query arguments. For instance, if a registry entry contains an 'affiliate_id' key and
	 * the intent is to query all records with a given key and value, passing a given affiliate
	 * ID via 'affiliate_id__in' will filter items to only those with one of the given affiliate
	 * IDs. One or more dynamically-built arguments can be passed in this way to filter results.
	 *
	 * Example:
	 *
	 *     $registry->query( array(
	 *         'affiliate_id__in' => array( 1, 2, 3 ),
	 *         'status__not_in'   => array( 'pending', 'rejected' ),
	 *     ) );
	 *
	 * @param array $args {
	 *     List of arguments for querying registered items. Some arguments, like `$field`, `$field__in`,
	 *     and `$field__not_in` can be used multiple times to narrow down the results. Fields can be
	 *     appended with __in or __not_in to automatically filter by or exclude a list of values.
	 *
	 *     @type array  $key__in       Explicit keys for items to query from, where `$key` represents an
	 *                                 item key. If no items are found matching the given key(s), they key__in
	 *                                 argument will be ignored and the query set will comprise of all items.
	 *     @type string $field         Field/value pair to explicitly query items for where. Can be used multiple
	 *                                 times.
	 *     @type array  $field__in     Dynamic filter where the `$field` portion of the argument name represents
	 *                                 the field name and the value(s) represent the values to query matching
	 *                                 items for. For example, 'affiliate_id__in' or 'referrals__in'. Can be used
	 *                                 multiple times.
	 *     @type array  $field__not_in Dynamic filter where the `$field` portion of the argument name represents
	 *                                 the field name and the value(s) represent the values used to exclude matching
	 *                                 items. For example, 'affiliate_id__not_in' or 'referral__not_in'. Can be used
	 *                                 multiple times.
	 * }
	 * @return array<string, array> Keyed items filtered by the specified parameters.
	 */
	public function query( $args ) {
		$results = array();

		$all_items = $this->getItems();

		// Filter out IDs before starting.
		if ( isset( $args['key__in'] ) ) {
			$items = array_intersect_key( $all_items, array_flip( $args['key__in'] ) );

			unset( $args['key__in'] );
		} else {
			$items = $all_items;
		}

		foreach ( $items as $item_key => $item_value ) {
			$valid = true;

			// Convert value to array if it isn't one already.
			$array_value = is_array( $item_value ) ? $item_value : (array) $item_value;

			foreach ( $args as $key => $arg ) {
				// Process the argument key
				$processed = explode( '__', $key );

				// Set the field type to the first item in the array.
				$field = $processed[0];

				// If there was some specificity after a __, use it.
				$type = count( $processed ) > 1 ? $processed[1] : 'in';

				// Bail early if this field is not in this item.
				if ( ! isset( $array_value[ $field ] ) ) {
					continue;
				}

				$object_field = $array_value[ $field ];

				// Convert argument to an array. This allows us to always use array functions for checking.
				if ( ! is_array( $arg ) ) {
					$arg = array( $arg );
				}

				// Convert field to array. This allows us to always use array functions to check.
				if ( ! is_array( $object_field ) ) {
					$object_field = array( $object_field );
				}

				// Run the intersection.
				$fields = array_intersect( $arg, $object_field );

				// Check based on type.
				switch ( $type ) {
					case 'not_in':
						$valid = empty( $fields );
						break;
					case 'and':
						$valid = count( $fields ) === count( $arg );
						break;
					default:
						$valid = ! empty( $fields );
						break;
				}

				if ( false === $valid ) {
					break;
				}
			}

			if ( true === $valid ) {
				$results[ $item_key ] = $item_value;
			}
		}

		return $results;
	}

}
