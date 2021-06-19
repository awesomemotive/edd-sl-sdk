<?php
/**
 * Product.php
 *
 * @package   EDD_SL_SDK
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Models;

use EDD_SL_SDK\AdminPages\PageRegistration;
use EDD_SL_SDK\Exceptions;
use EDD_SL_SDK\Traits\Serializable;

/**
 * Class Product
 *
 * @package EDD_SL_SDK
 */
class Product {

	use Serializable;

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var string
	 */
	public $store_id;

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var string
	 */
	private $license;

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
	 * @var string Option name where the license key is stored.
	 */
	public $license_option_name;

	/**
	 * @var string Option name where the license object (full API response data) is stored.
	 */
	public $license_object_option_name;

	/**
	 * @var bool
	 */
	public $beta = false;

	/**
	 * @var \Closure|null
	 */
	private $license_getter = null;

	/**
	 * @var \Closure|null
	 */
	private $license_setter = null;

	/**
	 * @var array
	 */
	public $i18n = [];

	/**
	 * Product constructor.
	 *
	 * @param array $args
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $args ) {
		$args = $this->fillMissingArgs( $args );

		// Verify we have all the required arguments.
		$this->validateArgs( $args );

		foreach ( $args as $key => $value ) {
			$this->{$key} = $value;
		}

		$this->id = $this->getId();

		if ( ! empty( $args['menu'] ) ) {
			new PageRegistration( $this, $args['menu'] );
		}
	}

	/**
	 * Magic getter for retrieving the license key.
	 *
	 * @param string $property
	 *
	 * @return mixed
	 */
	public function __get( $property ) {
		if ( 'license' === $property ) {
			return $this->getLicense();
		}

		return null;
	}

	/**
	 * Magic setter.
	 *
	 * @param string $property
	 *
	 * @return bool
	 */
	public function __isset( $property ) {
		if ( 'license' === $property ) {
			return (bool) $this->getLicense();
		} elseif ( property_exists( $this, $property ) ) {
			return false === empty( $this->{$property} );
		} else {
			return false;
		}
	}

	/**
	 * Fills in any missing arguments that we're capable of guessing.
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	private function fillMissingArgs( $args ) {
		// If this is a theme and we don't have a version, we can get it.
		if ( isset( $args['type'] ) && 'theme' === $args['type'] && ! isset( $args['version'] ) && ! empty( $args['slug'] ) ) {
			$theme = wp_get_theme( $args['slug'] );

			if ( $theme ) {
				$args['version'] = $theme->get( 'Version' );
			}
		}

		// If we don't have a slug, we can make one.
		if ( empty( $args['slug'] ) && ! empty( $args['file'] ) ) {
			$args['slug'] = basename( $args['file'], '.php' );
		}

		// If there are no option keys, make them.
		if ( ! empty( $args['type'] ) && ! empty( $args['slug'] ) ) {
			if ( empty( $args['license_option_name'] ) ) {
				$args['license_option_name'] = sanitize_key( sprintf( 'sl_%s_%s_license', $args['type'], $args['slug'] ) );
			}

			if ( empty( $args['license_object_option_name'] ) ) {
				$args['license_object_option_name'] = sanitize_key( sprintf( 'sl_%s_%s_license_object', $args['type'], $args['slug'] ) );
			}
		}

		return $args;
	}

	/**
	 * Validates that we have required arguments.
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @throws \InvalidArgumentException
	 */
	private function validateArgs( $args ) {
		$requiredArgs = [ 'type', 'item_id', 'file', 'version', 'store_id' ];

		foreach ( $requiredArgs as $requiredArg ) {
			if ( empty( $args[ $requiredArg ] ) ) {
				throw new \InvalidArgumentException( sprintf(
					__( 'Missing required argument: %s' ),
					$requiredArg
				) );
			}
		}
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
	 * Retrieves the license key from the database.
	 *
	 * @since 1.0
	 *
	 * @return string|false
	 */
	public function getLicense() {
		if ( $this->license_getter instanceof \Closure ) {
			return call_user_func( $this->license_getter );
		}

		return get_option( $this->license_option_name );
	}

	/**
	 * Sets a new license key.
	 *
	 * @since 1.0
	 *
	 * @param string $newLicenseKey
	 */
	public function setLicense( $newLicenseKey ) {
		$previousLicense = $this->getLicense();
		$this->license   = $newLicenseKey;

		if ( $this->license_setter instanceof \Closure ) {
			call_user_func( $this->license_setter, $this->license, $previousLicense );
		}

		update_option( $this->license_option_name, sanitize_text_field( $this->license ) );
	}

	/**
	 * Builds API arguments for the product.
	 *
	 * @since 1.0
	 * @return array
	 */
	public function toArray() {
		return array(
			'license'    => $this->license,
			'product_id' => $this->item_id,
			'version'    => $this->version,
			'slug'       => $this->slug,
			'beta'       => $this->beta,
		);
	}

	/**
	 * Updates license data in the database.
	 *
	 * @since 1.0
	 *
	 * @param array $data
	 */
	private function updateLicenseData( $data ) {
		$data['last_sync'] = gmdate( 'Y-m-d H:i:s' );

		update_option( $this->license_object_option_name, json_encode( $data ) );
	}

	/**
	 * Activates the product's license key.
	 *
	 * @since 1.0
	 *
	 * @return array
	 * @throws Exceptions\ApiException
	 * @throws Exceptions\ItemNotFoundException
	 */
	public function activateLicense() {
		$licenseData = \EDD_SL_SDK\Helpers\Store::getById( $this->store_id )
			->getApiHandler()
			->activateLicense( $this );

		if ( ! empty( $licenseData['license'] ) ) {
			$this->updateLicenseData( $licenseData['license'] );
		}

		return $licenseData;
	}

	/**
	 * Deactivates the product's license key.
	 *
	 * @since 1.0
	 *
	 * @returns array
	 * @throws Exceptions\ApiException
	 * @throws Exceptions\ItemNotFoundException
	 */
	public function deactivateLicense() {
		$licenseData = \EDD_SL_SDK\Helpers\Store::getById( $this->store_id )
			->getApiHandler()
			->deactivateLicense( $this );

		if ( ! empty( $licenseData['license'] ) ) {
			$this->updateLicenseData( $licenseData['license'] );
		}

		return $licenseData;
	}

	/**
	 * Retrieves the License object.
	 *
	 * @since 1.0
	 *
	 * @return License
	 * @throws Exceptions\ItemNotFoundException
	 */
	public function getLicenseData() {
		return License::fromJson( get_option( $this->license_object_option_name ) );
	}

}
