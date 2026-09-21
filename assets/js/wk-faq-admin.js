/**
 * Web Kit - FAQ repeater meta box (wp-admin only).
 * Plain JS: add/remove rows, live-update each row's collapsed title, and
 * let clicking a row's header collapse/expand it. No drag-reorder (rows
 * save in DOM order, which is all that's needed here).
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var repeater = document.getElementById( 'wk-faq-repeater' );
		if ( ! repeater ) {
			return;
		}

		var rowsWrap = repeater.querySelector( '.wk-faq-rows' );
		var addBtn   = document.getElementById( 'wk-faq-add' );
		var template = document.getElementById( 'wk-faq-row-template' );
		var l10n     = window.wkFaqAdminL10n || {};
		var counter  = rowsWrap.children.length;

		function bindRow( row ) {
			var removeBtn = row.querySelector( '.wk-faq-remove' );
			var header    = row.querySelector( '.wk-faq-row-header' );
			var qInput    = row.querySelector( '.wk-faq-question' );
			var titleEl   = row.querySelector( '.wk-faq-row-title' );

			removeBtn.addEventListener( 'click', function ( e ) {
				e.stopPropagation();
				var msg = l10n.confirmRemove || 'Remove this FAQ?';
				if ( window.confirm( msg ) ) {
					row.parentNode.removeChild( row );
				}
			} );

			header.addEventListener( 'click', function ( e ) {
				if ( e.target === removeBtn ) {
					return;
				}
				row.classList.toggle( 'is-collapsed' );
			} );

			qInput.addEventListener( 'input', function () {
				titleEl.textContent = qInput.value || ( l10n.newFaqLabel || 'New FAQ' );
			} );
		}

		Array.prototype.forEach.call(
			rowsWrap.querySelectorAll( '.wk-faq-row' ),
			bindRow
		);

		if ( addBtn && template ) {
			addBtn.addEventListener( 'click', function () {
				var html = template.innerHTML.replace( /__INDEX__/g, counter++ );
				var wrapper = document.createElement( 'div' );
				wrapper.innerHTML = html.trim();

				var newRow = wrapper.firstElementChild;
				rowsWrap.appendChild( newRow );
				bindRow( newRow );

				var qInput = newRow.querySelector( '.wk-faq-question' );
				if ( qInput ) {
					qInput.focus();
				}
			} );
		}
	} );
} )();
