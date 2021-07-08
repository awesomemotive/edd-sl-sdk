/* global ajaxurl */

import { spinButton, unspinButton } from "./utils/spinners";

document.addEventListener( 'DOMContentLoaded', () => {
	const licenseForms = document.querySelectorAll( '.edd-sl-sdk-license-form' );
	if ( ! licenseForms ) {
		return;
	}

	licenseForms.forEach( form => {
		form.addEventListener( 'submit', submitLicenseForm );
	} )
} )

/**
 * Handles submitting the license form.
 *
 * @param e
 */
function submitLicenseForm( e ) {
	e.preventDefault();

	const form = this;
	const button = form.querySelector( 'button[type="submit"]' );
	const license = form.querySelector( 'input[name="license_key"]' );

	if ( ! license || ! license.value ) {
		return;
	}

	const statusWrapper = form.querySelector( '.edd-sl-sdk-license-response' );

	if ( button ) {
		spinButton( button );
	}

	fetch( ajaxurl, {
		method: 'POST',
		credentials: 'same-origin',
		body: new FormData( form )
	} )
		.then( response => response.json() )
		.then( data => {
			if ( data.success ) {
				statusWrapper.classList.remove( 'edd-sl-sdk-license-response__invalid' );
				statusWrapper.classList.add( 'edd-sl-sdk-license-response__valid' );

				if ( data.data.message ) {
					statusWrapper.innerHTML = data.data.message;
				}

				if ( data.data.newFormInputs ) {
					replaceFormInputs( form, data.data.newFormInputs );
				}
			} else {
				statusWrapper.classList.remove( 'edd-sl-sdk-license-response__valid' );
				statusWrapper.classList.add( 'edd-sl-sdk-license-response__invalid' );
				statusWrapper.textContent = data.data;
			}
		} )
		.finally( () => {
			unspinButton( button );
		} );
}

/**
 * This replaces some of the form values, to prepare the form for being used in the
 * reverse action. For example: If we just activated a license key, then we replace
 * the nonce and action so we can immediately choose to deactivate the license without
 * requiring a page refresh.
 *
 * @param {HTMLElement} form
 * @param {Object} formData
 */
function replaceFormInputs( form, formData ) {
	Object.keys( formData ).forEach( key => {
		if ( 'buttonText' === key ) {
			const button = form.querySelector( 'button[type="submit"]' );
			if ( button ) {
				button.textContent = formData[key];
			}
		} else {
			const input = form.querySelector( 'input[name="' + key + '"]' );
			if ( input ) {
				input.value = formData[key];
			}
		}
	} )
}
