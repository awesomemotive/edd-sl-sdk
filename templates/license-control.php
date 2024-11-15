<?php
/**
 * License control template.
 *
 * @var $args array
 */
$name = $args['name'];
if ( 'theme' === $args['type'] ) {
	$name = wp_get_theme()->get( 'Name' );
}
?>
<p>
	<?php
	printf(
		/* translators: %s: item name */
		esc_html__( 'License key for %s:', 'edd-sl-sdk' ),
		esc_html( $name )
	);
	?>
</p>
<div class="edd-sl-sdk__license-control">
	<input type="password" autocomplete="off" class="edd-sl-sdk__license--input regular-text" id="edd_sl_sdk[<?php echo esc_attr( $args['item_id'] ); ?>]" name="<?php echo esc_attr( $args['license']->get_key_option_name() ); ?>" value="<?php echo esc_attr( $args['license']->get_license_key() ); ?>" data-item="<?php echo esc_attr( $args['item_id'] ); ?>" data-key="<?php echo esc_attr( $args['license']->get_key_option_name() ); ?>" data-slug="<?php echo esc_attr( $args['slug'] ); ?>" />
	<?php
	$args['license']->get_actions( true );
	?>
</div>
