; ( function ( document, $ ) {
	'use strict';

	$( '.edd-sl-sdk__license-control' ).on( 'click', '.edd-sl-sdk__action', function ( e ) {
		e.preventDefault();

		var $btn = $( this ),
			action = $btn.attr( 'data-action' ),
			ajaxAction = '',
			text = $btn.text();

		if ( $btn.attr( 'disabled' ) ) {
			return;
		}

		switch ( action ) {
			case 'activate':
				ajaxAction = 'eddsdk_activate';
				$btn.text( EDDPassManager.activating );
				break;

			case 'deactivate':
				ajaxAction = 'eddsdk_deactivate';
				$btn.text( EDDPassManager.deactivating );
				break;

			default:
				return;
		}

		$( '.edd-sl-sdk__license-control + .notice' ).remove();
		$( '.edd-sl-sdk__license-control + p' ).remove();
		$btn.removeClass( 'button-primary' ).attr( 'disabled', true ).addClass( 'updating-message' );

		var data = {
			action: ajaxAction,
			token: $btn.attr( 'data-token' ),
			timestamp: $btn.attr( 'data-timestamp' ),
			nonce: $btn.attr( 'data-nonce' ),
			license: $( '#edd_pass_key' ).val(),
		};

		$.post( ajaxurl, data )
			.done( function ( res ) {
				if ( res.success ) {
					$( '.edd-sl-sdk__actions' ).replaceWith( res.data.actions );
					if ( res.data.message ) {
						$( '.edd-sl-sdk__license-control' ).after( res.data.message );
					}
					if ( data.license.length && 'deactivate' === action ) {
						$( '#edd_pass_key' ).attr( 'readonly', false );
					} else if ( 'activate' === action || 'verify' === action ) {
						$( '#edd_pass_key' ).attr( 'readonly', true );
						if ( res.data.url && res.data.url.length ) {
							setTimeout( function () {
								window.location.href = res.data.url;
							}, 1500 );
							return;
						}
					}
				} else {
					$btn.text( text );
					$( '.edd-sl-sdk__license-control' ).after( '<div class="notice inline-notice notice-warning edd-sl-sdk__notice">' + res.data.message + '</div>' );
				}
				$btn.attr( 'disabled', false ).removeClass( 'updating-message' );
			} );
	} );

	$( '.edd-sl-sdk__license-control' ).on( 'click', '.edd-sl-sdk__delete', function ( e ) {
		e.preventDefault();

		var $btn = $( this ),
			ajaxAction = 'eddsdk_delete';

		var data = {
			action: ajaxAction,
			token: $btn.attr( 'data-token' ),
			timestamp: $btn.attr( 'data-timestamp' ),
			nonce: $btn.attr( 'data-nonce' ),
			license: $( '#edd_pass_key' ).val(),
		};

		if ( !data.license ) {
			return;
		}

		$( '.edd-sl-sdk__license-control + .notice' ).remove();
		$( '.edd-sl-sdk__license-control + p' ).remove();
		$btn.attr( 'disabled', true ).addClass( 'updating-message' );
		$( '#edd_pass_key' ).val( '' );

		$.post( ajaxurl, data )
			.done( function ( res ) {
				if ( res.success ) {
					$( '.edd-sl-sdk__license-control' ).after( res.data.message );
					$btn.hide();
				} else {
					$( '.edd-sl-sdk__license-control' ).after( '<div class="notice inline-notice notice-warning">' + res.data.message + '</div>' );
				}
				$btn.attr( 'disabled', false ).removeClass( 'updating-message' );
			} );
	} );
} )( document, jQuery );
