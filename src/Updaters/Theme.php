<?php
/**
 * Theme updater class.
 *
 * @package EasyDigitalDownloads\Updater\Updaters
 */

namespace EasyDigitalDownloads\Updater\Updaters;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Represents the Theme class for handling licensing.
 */
class Theme extends Updater {

	/**
	 * Check for Updates at the defined API endpoint and modify the update array.
	 *
	 * This function dives into the update API just when WordPress creates its update array,
	 * then adds a custom API call and injects the custom plugin data retrieved from the API.
	 * It is reassembled from parts of the native WordPress plugin update code.
	 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
	 *
	 * @param array $transient_data Update array build by WordPress.
	 * @return array Modified update array with custom plugin data.
	 */
	public function check_update( $transient_data ) {

		if ( ! is_object( $transient_data ) ) {
			$transient_data = new \stdClass();
		}

		if ( ! empty( $transient_data->response ) && ! empty( $transient_data->response[ $this->get_name() ] ) && ! $this->should_override_wp_check() ) {
			return $transient_data;
		}

		$current = $this->get_limited_data();
		if ( false !== $current && is_object( $current ) && isset( $current->new_version ) ) {
			if ( version_compare( $this->get_version(), $current->new_version, '<' ) ) {
				$transient_data->response[ $this->get_name() ] = $current;
			} else {
				// Populating the no_update information is required to support auto-updates in WordPress 5.5.
				$transient_data->no_update[ $this->get_name() ] = $current;
			}
		}
		$transient_data->last_checked                 = time();
		$transient_data->checked[ $this->get_name() ] = $this->get_version();

		return $transient_data;
	}

	/**
	 * Adds the hooks for the updater.
	 *
	 * @since <next-version>
	 * @return void
	 */
	protected function add_listeners(): void {
		add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_update' ) );
	}

	/**
	 * Gets the slug for the API request.
	 *
	 * @since <next-version>
	 * @return string
	 */
	protected function get_slug(): string {
		return wp_get_theme()->get_template();
	}

	/**
	 * Gets the name for the API request.
	 *
	 * @since <next-version>
	 * @return string
	 */
	protected function get_name(): string {
		return $this->get_slug();
	}

	/**
	 * Gets the current version information from the remote site.
	 *
	 * @return array|false
	 */
	protected function get_version_from_remote() {

		$request = parent::get_version_from_remote();
		if ( ! $request ) {
			return false;
		}

		if ( isset( $request->sections ) ) {
			$request->sections = maybe_unserialize( $request->sections );
		}

		if ( isset( $request->banners ) ) {
			$request->banners = maybe_unserialize( $request->banners );
		}

		if ( isset( $request->icons ) ) {
			$request->icons = maybe_unserialize( $request->icons );
		}

		if ( ! empty( $request->sections ) ) {
			foreach ( $request->sections as $key => $section ) {
				$request->$key = (array) $section;
			}
		}

		return $request;
	}

	/**
	 * Gets the defaults for an API request.
	 *
	 * @since <next-version>
	 * @return array
	 */
	protected function get_api_request_defaults() {
		$defaults        = parent::get_api_request_defaults();
		$defaults['url'] = '';

		return $defaults;
	}

	/**
	 * Gets a limited set of data from the API response.
	 * This is used for the update_plugins transient.
	 *
	 * @since 3.2.10
	 * @return \stdClass|false
	 */
	private function get_limited_data() {
		$version_info = $this->get_repo_api_data();
		if ( ! $version_info ) {
			return false;
		}

		return array(
			'theme'        => $this->get_slug(),
			'new_version'  => $version_info->new_version,
			'url'          => $this->args['url'],
			'package'      => $version_info->package,
			'requires'     => $version_info->requires,
			'requires_php' => $version_info->requires_php,
		);
	}

	/**
	 * Get repo API data from store.
	 * Save to cache.
	 *
	 * @return \stdClass
	 */
	private function get_repo_api_data() {
		$version_info = $this->get_cached_version_info();
		if ( false !== $version_info ) {
			return $version_info;
		}

		$version_info = $this->get_version_from_remote();
		if ( ! $version_info ) {
			return false;
		}

		// This is required for your plugin to support auto-updates in WordPress 5.5.
		$version_info->plugin = $this->get_name();
		$version_info->id     = $this->get_name();
		$version_info->tested = $this->get_tested_version( $version_info );
		if ( ! isset( $version_info->requires ) ) {
			$version_info->requires = '';
		}
		if ( ! isset( $version_info->requires_php ) ) {
			$version_info->requires_php = '';
		}

		$this->set_version_info_cache( $version_info );

		return $version_info;
	}

	/**
	 * Gets the plugin's tested version.
	 *
	 * @since <next-version>
	 * @param object $version_info The version info.
	 * @return null|string
	 */
	private function get_tested_version( $version_info ) {

		// There is no tested version.
		if ( empty( $version_info->tested ) ) {
			return null;
		}

		// Strip off extra version data so the result is x.y or x.y.z.
		list( $current_wp_version ) = explode( '-', get_bloginfo( 'version' ) );

		// The tested version is greater than or equal to the current WP version, no need to do anything.
		if ( version_compare( $version_info->tested, $current_wp_version, '>=' ) ) {
			return $version_info->tested;
		}
		$current_version_parts = explode( '.', $current_wp_version );
		$tested_parts          = explode( '.', $version_info->tested );

		// The current WordPress version is x.y.z, so update the tested version to match it.
		if ( isset( $current_version_parts[2] ) && $current_version_parts[0] === $tested_parts[0] && $current_version_parts[1] === $tested_parts[1] ) {
			$tested_parts[2] = $current_version_parts[2];
		}

		return implode( '.', $tested_parts );
	}

	/**
	 * Convert some objects to arrays when injecting data into the update API
	 *
	 * Some data like sections, banners, and icons are expected to be an associative array, however due to the JSON
	 * decoding, they are objects. This method allows us to pass in the object and return an associative array.
	 *
	 * @since <next-version>
	 * @param stdClass $data The data to convert.
	 * @return array
	 */
	private function convert_object_to_array( $data ) {
		if ( ! is_array( $data ) && ! is_object( $data ) ) {
			return array();
		}
		$new_data = array();
		foreach ( $data as $key => $value ) {
			$new_data[ $key ] = is_object( $value ) ? $this->convert_object_to_array( $value ) : $value;
		}

		return $new_data;
	}

	/**
	 * Gets the changelog link.
	 *
	 * @since <next-version>
	 * @param object $update_cache The update cache.
	 * @return string
	 */
	private function get_changelog_link( $update_cache ) {
		if ( empty( $update_cache->response[ $this->get_name() ]->sections->changelog ) ) {
			return '';
		}

		return add_query_arg(
			array(
				'tab'       => 'plugin-information',
				'plugin'    => rawurlencode( $this->get_slug() ),
				'TB_iframe' => 'true',
				'width'     => 77,
				'height'    => 911,
			),
			self_admin_url( 'network/plugin-install.php' )
		);
	}

	/**
	 * Gets the plugins active in a multisite network.
	 *
	 * @return array
	 */
	private function get_active_plugins() {
		$active_plugins         = (array) get_option( 'active_plugins' );
		$active_network_plugins = (array) get_site_option( 'active_sitewide_plugins' );

		return array_merge( $active_plugins, array_keys( $active_network_plugins ) );
	}

	/**
	 * Gets the update message.
	 *
	 * @param object $update_cache The update cache.
	 * @param string $file         The file.
	 * @return string
	 */
	private function get_message( $update_cache, $file ) {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return ' ' . esc_html__( 'Contact your network administrator to install the update.', 'easy-digital-downloads' );
		}

		$changelog_link = $this->get_changelog_link( $update_cache );
		if ( empty( $update_cache->response[ $this->get_name() ]->package ) && ! empty( $changelog_link ) ) {
			return ' ' . sprintf(
				/* translators: 1. opening anchor tag, do not translate 2. the new plugin version 3. closing anchor tag, do not translate. */
				__( '%1$sView version %2$s details%3$s.', 'easy-digital-downloads' ),
				'<a target="_blank" class="thickbox open-plugin-details-modal" href="' . esc_url( $changelog_link ) . '">',
				esc_html( $update_cache->response[ $this->get_name() ]->new_version ),
				'</a>'
			);
		}

		$update_link = add_query_arg(
			array(
				'action' => 'upgrade-plugin',
				'plugin' => rawurlencode( $this->get_name() ),
			),
			self_admin_url( 'update.php' )
		);

		if ( ! empty( $changelog_link ) ) {
			return ' ' . sprintf(
				__( '%1$sView version %2$s details%3$s or %4$supdate now%5$s.', 'easy-digital-downloads' ),
				'<a target="_blank" class="thickbox open-plugin-details-modal" href="' . esc_url( $changelog_link ) . '">',
				esc_html( $update_cache->response[ $this->get_name() ]->new_version ),
				'</a>',
				'<a target="_blank" class="update-link" href="' . esc_url( wp_nonce_url( $update_link, 'upgrade-plugin_' . $file ) ) . '">',
				'</a>'
			);
		}

		return sprintf(
			' %1$s%2$s%3$s',
			'<a target="_blank" class="update-link" href="' . esc_url( wp_nonce_url( $update_link, 'upgrade-plugin_' . $file ) ) . '">',
			esc_html__( 'Update now.', 'easy-digital-downloads' ),
			'</a>'
		);
	}
}
