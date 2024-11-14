<?php
/**
 * License control template.
 *
 * @var $args array
 */
$license = new EasyDigitalDownloads\Updater\Licensing\License( $args['slug'], $args );
?>
<p>
	<?php
	printf(
		/* translators: %s: item name */
		esc_html__( 'Enter your license key for %s:', 'edd-sl-sdk' ),
		esc_html( get_the_title( $args['item_id'] ) )
	);
	?>
</p>
<div class="edd-sl-sdk__license-control">
	<input type="password" autocomplete="off" class="regular-text" id="edd_sl_sdk[<?php echo esc_attr( $args['item_id'] ); ?>]" name="<?php echo esc_attr( $license->get_key_option_name() ); ?>" value="<?php echo esc_attr( $license->get_license_key() ); ?>" data-item="<?php echo esc_attr( $args['item_id'] ); ?>" data-key="<?php echo esc_attr( $license->get_key_option_name() ); ?>">
	<?php
	$license->get_actions( get_option( $license->get_key_option_name() . '_status' ), true );
	?>
</div>
