<?php
/**
 * ThemeUpdater.php
 *
 * @package   EDD_SL_SDK\Updates
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD_SL_SDK\Updates;

use EDD_SL_SDK\Traits\Singleton;

class ThemeUpdater extends Updater {

	use Singleton;

	/**
	 * Product type
	 *
	 * @since 1.0
	 * @var string
	 */
	private $type = 'theme';
}
