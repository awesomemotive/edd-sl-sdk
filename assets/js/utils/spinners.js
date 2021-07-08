export function spinButton( button ) {
	button.disabled = true;
	button.classList.add( 'updating-message' );
}

export function unspinButton( button ) {
	button.disabled = false;
	button.classList.remove( 'updating-message' );
}
