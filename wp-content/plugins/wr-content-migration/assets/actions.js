/**
 * @version    1.0
 * @package    WR_Content_Migration
 * @author     WooRockets Team <support@woorockets.com>
 * @copyright  Copyright (C) 2014 WooRockets.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.woorockets.com
 */

( function( $ ) {
	$( document ).ready( function() {
		var self = document.querySelector( '.wr-content-migration' );

		self.export_btn = self.querySelector( 'a.export' );
		self.import_btn = self.querySelector( 'a.import' );
		self.select_preset = self.querySelector( '.wr-select-content-preset' );
		self.upload_form = self.querySelector( '.wr-upload-exported-file' );
		self.start_import_btn = self.querySelector( 'button.start-import' );
		self.post_id = document.getElementById( 'post_ID' );

		// Add post ID into export / import link.
		if ( self.post_id && self.post_id.value != '' ) {
			if ( self.export_btn ) {
				self.export_btn.href += '&id=' + self.post_id.value;
			}

			if ( self.import_btn ) {
				self.import_btn.href += '&id=' + self.post_id.value;
			}
		}

		// Setup import button.
		if ( self.import_btn ) {
			self.import_btn.addEventListener( 'click', function( event ) {
				event.preventDefault();

				self.querySelector( '.wr-import-content-preset' ).classList.toggle( 'hidden' );
			} );
		}

		// Setup content preset selection.
		if ( self.select_preset ) {
			self.select_preset.addEventListener( 'change', function( event ) {
				if ( event.target.options[ event.target.selectedIndex ].value != '' ) {
					self.start_import_btn.disabled = false;
				} else if ( ! self.input_file || self.input_file.value == '' ) {
					self.start_import_btn.disabled = true;
				}
			} );
		}

		// Setup upload form.
		if ( self.upload_form ) {
			self.input_file = self.querySelector( '.wr-select-exported-file' );
			self.current_file = self.upload_form.querySelector( '.selected-file' );
			self.select_file = self.upload_form.querySelector( '.select-file' );
			self.remove_file = self.upload_form.querySelector( '.remove-file' );

			// Setup link to select file.
			self.select_file.addEventListener( 'click', function( event ) {
				event.preventDefault();

				// Setup media selector once.
				var frame = window.wr_content_migration_media_selector;

				if ( ! frame ) {
					// Create the media frame.
					frame = wp.media( {
						button: {
							text: self.select_file.textContent,
						},
						states: [
							new wp.media.controller.Library( {
								title: self.select_file.textContent,
								library: wp.media.query( { type: [
									'text/xml',
								] } ),
								multiple: false,
								date: false,
							} )
						]
					} );

					// When a file is selected, run a callback.
					frame.on( 'select', function() {
						// Grab the selected attachment.
						var attachment = frame.state().get( 'selection' ).first();

						// Verify the selected file.
						if ( attachment.attributes.url.match( /\.xml$/ ) ) {console.log( self.input_file );
							// Update selected file.
							self.input_file.value = attachment.attributes.url;

							// Show name of selected file.
							self.current_file.textContent = attachment.attributes.url.split( '/' ).pop();

							// Show link to remove selected file.
							self.remove_file.classList.remove( 'hidden' );

							// Enable submit button.
							self.start_import_btn.disabled = false;
						}
					} );

					// Work-around to deselect the uploaded file if it is not a supported file.
					frame.on( 'open', function() {
						frame._checking_interval = setInterval( function() {
							// Check if there is any file selected.
							var attachment = frame.state().get( 'selection' ).first();

							// Verify the selected file.
							if ( attachment && attachment.attributes && attachment.attributes.url ) {
								if ( ! attachment.attributes.url.match( /\.xml$/ ) ) {
									frame.reset();
								}
							}
						}, 50 );
					} );

					frame.on( 'close', function() {
						clearInterval( frame._checking_interval );
					} );

					// Store media selector object for later reference
					window.wr_content_migration_media_selector = frame;
				}

				frame.open();
			} );

			// Setup link to clear selected file.
			self.remove_file.addEventListener( 'click', function( event ) {
				// Clear selected file.
				self.input_file.value = '';

				// Clear name of selected file.
				self.current_file.textContent = '';

				// Hide link to remove selected file.
				self.remove_file.classList.add( 'hidden' );

				if ( ! self.select_preset || self.select_preset.options[ self.select_preset.selectedIndex ].value == '' ) {
					// Disable submit button.
					self.start_import_btn.disabled = true;
				}
			} );
		}

		// Setup start import buttons.
		var buttons = self.querySelectorAll( '.start-import' );

		for ( var i = 0; i < buttons.length; i++ ) {
			buttons[ i ].addEventListener( 'click', function( event ) {
				event.preventDefault();

				// Replace button with loading element.
				var loading = document.createElement( 'span' );

				loading.className = 'spinner is-active';
				loading.style.float = 'none';

				event.target.classList.add( 'hidden' );
				event.target.parentNode.insertBefore( loading, event.target );

				// Get selected preset.
				var link, data;

				if ( event.target.href && event.target.href.indexOf( '&preset=' ) ) {
					link = event.target.href;
					data = {};
				} else if ( self.import_btn ) {
					link = self.import_btn.href;

					if ( self.select_preset && self.select_preset.selectedIndex > 0 ) {
						data = {
							preset: self.select_preset.options[ self.select_preset.selectedIndex ].value,
						};
					} else if ( self.input_file && self.input_file.value != '' ) {
						data = {
							preset: self.input_file.value,
						};
					}
				}

				// Send request to import content preset.
				if ( link && data ) {
					$.ajax( {
						url: link,
						data: data,
						complete: function( response ) {
							var res = response.responseJSON || JSON.parse( response.responseText );

							if ( res && res.success ) {
								if ( ! isNaN( parseInt( res.data ) ) ) {
									window.location.href = 'post.php?action=edit&post=' + res.data;
								} else {
									//window.location.reload();
								}
							} else {
								if ( ! res ) {
									alert( response.responseText );
								} else {
									alert( res.data );
								}

								loading.parentNode.removeChild( loading );
								event.target.classList.remove( 'hidden' );
							}
						},
					} );
				}
			} );
		}
	} );
} )( jQuery );
