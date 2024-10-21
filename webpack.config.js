const path = require( 'path' );

/**
 * WordPress Dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );
const FixStyleOnlyEntriesPlugin = require( 'webpack-fix-style-only-entries' );
const MiniCSSExtractPlugin = require( 'mini-css-extract-plugin' );

module.exports = {
	...defaultConfig,
	entry: {
		"css/edd-wl": path.resolve( process.cwd(), 'assets/src/css', 'edd-wl.scss' ),
		"js/jquery.validate": path.resolve( process.cwd(), 'assets/src/js', 'jquery.validate.js' ),
		"js/edd-wl": path.resolve( process.cwd(), 'assets/src/js', 'edd-wl.js' ),
		"js/modal": path.resolve( process.cwd(), 'assets/src/js', 'modal.js' ),
		"js/wl-delete": path.resolve( process.cwd(), 'assets/src/js', 'wl-delete.js' ),
	},
	output: {
		path: path.resolve( __dirname, 'assets/build' ),
	},
	plugins: [
		new MiniCSSExtractPlugin(),
		new FixStyleOnlyEntriesPlugin(),
	],
}
