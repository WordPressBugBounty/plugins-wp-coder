( function () {
	'use strict';

	// Anchored on the "Name" field's class name rather than the modal title,
	// because the title comes from a translatable post-type label ("Add Pattern"
	// in English core, something else in every other locale and in older/newer
	// core versions that word it differently).
	function findCreatePatternModal() {
		var dialogs = document.querySelectorAll( '.components-modal__frame[role="dialog"]' );

		for ( var i = 0; i < dialogs.length; i++ ) {
			if ( dialogs[ i ].querySelector( '.patterns-create-modal__name-input' ) ) {
				return dialogs[ i ];
			}
		}

		return null;
	}

	// The modal renders exactly one ToggleControl (Synced), so no need to
	// match its (also translatable) label text.
	function findSyncToggleInput( modal ) {
		return modal.querySelector( '.components-form-toggle input[type="checkbox"]' );
	}

	var handledModals = new WeakSet();

	function maybeUncheckSync( modal ) {
		if ( handledModals.has( modal ) ) {
			return;
		}

		var input = findSyncToggleInput( modal );

		// Toggle not rendered yet - retry on the next mutation batch.
		if ( ! input ) {
			return;
		}

		if ( input.checked ) {
			input.click();
		}

		handledModals.add( modal );
	}

	function startObserving() {
		var observer = new MutationObserver( function () {
			var modal = findCreatePatternModal();

			if ( modal ) {
				maybeUncheckSync( modal );
			}
		} );

		observer.observe( document.body, { childList: true, subtree: true } );
	}

	if ( document.body ) {
		startObserving();
	} else {
		document.addEventListener( 'DOMContentLoaded', startObserving );
	}
} )();
