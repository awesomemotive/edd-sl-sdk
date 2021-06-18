<?php
/**
 * Product.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK;

/**
 * Class Product
 *
 * @package EDD_SL_SDK
 */
class Product {

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var string
	 */
	public $license;

	/**
	 * @var int
	 */
	public $item_id;

	/**
	 * @var string
	 */
	public $file;

	/**
	 * @var string
	 */
	public $version;

	/**
	 * @var string
	 */
	public $slug;

	/**
	 * @var string
	 */
	public $cache_key;

	/**
	 * @var bool
	 */
	public $beta = false;

	/**
	 * Product constructor.
	 *
	 * @param array $args
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $args ) {
		// Verify we have all the required arguments.
		$requiredArgs = [ 'type', 'item_id', 'file', 'version' ];

		// Slug is required for themes.
		if ( 'theme' === $args['type'] ) {
			$requiredArgs[] = 'slug';
		}

		foreach ( $requiredArgs as $requiredArg ) {
			if ( empty( $args[ $requiredArg ] ) ) {
				throw new \InvalidArgumentException( sprintf(
					__( 'Missing required argument: %s' ),
					$requiredArg
				) );
			}
		}

		// If this is a plugin and we don't have a slug, we can make one.
		if ( empty( $args['slug'] ) && ! empty( $args['file'] ) ) {
			$args['slug'] = basename( $args['file'], '.php' );
		}

		// If there's no cache key, make one.
		if ( empty( $args['cache_key'] ) ) {
			$args['cache_key'] = sprintf( '%s_%s', $args['type'], $args['slug'] );
		}

		foreach ( $args as $key => $value ) {
			$this->{$key} = $value;
		}

		$this->id = $this->getId();
	}

	/**
	 * Retrieves the unique ID for this product.
	 * For plugins, this is the `plugin_basename()` value; for themes it's the slug (`get_template()`).
	 *
	 * @since 1.0
	 * @return string
	 */
	private function getId() {
		return 'plugin' === $this->type ? plugin_basename( $this->file ) : $this->slug;
	}

	/**
	 * Builds API arguments for the product.
	 *
	 * @since 1.0
	 * @return array
	 */
	public function toApiArgs() {
		return array(
			'license' => $this->license,
			'item_id' => $this->item_id,
			'version' => $this->version,
			'slug'    => $this->slug,
			'beta'    => $this->beta,
			'url'     => home_url()
		);
	}

}
