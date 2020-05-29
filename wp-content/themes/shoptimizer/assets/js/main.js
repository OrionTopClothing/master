// Main Shoptimizer js.
;
( function( $ ) {
	'use strict';

	$( window ).on( 'load resize', function() {

		if ( 992 < $( window ).width() ) {

		// Make a standard hover menu work on touchscreens. Activate only on devices with a touch screen.
		if ( 'ontouchstart' in window ) {

			// Make the touch event add hover pseudoclass.
			document.addEventListener( 'touchstart', function() {}, true );

			// Modify click event.
			document.addEventListener( 'click', function( e ) {
				var el = $( e.target ).hasClass( 'menu-item-has-children' ) ? $( e.target ) : $( e.target ).closest( '.menu-item-has-children' );
				if ( ! el.length ) {
					return;
				}

				// Remove tapped class from old ones.
				$( '.menu-item-has-children.tapped' ).each( function() {
					if ( this !== el.get( 0 ) ) {
					$( this ).removeClass( 'tapped' );
					}
				} );
				if ( ! el.hasClass( 'tapped' ) ) {

					// First Tap.
					el.addClass( 'tapped' );
					e.preventDefault();
					return false;
				} else {

					// Second Tap.
					return true;
				}
			}, true );
		}
	}
	} );

	// Add close drawer markup
	$( '.post-type-archive-product #secondary' ).prepend( '<div class="close-drawer"></div>' );

	// Add custom id to the single product form
	$( document ).ready( function() {
		$( '.single-product form.cart' ).attr( 'id', 'sticky-scroll' );
	} );

	// Toggle cart drawer.
	$( document ).on( 'click', '.mobile-filter', function( e ) {
		e.stopPropagation();
		e.preventDefault();
		$( 'body' ).toggleClass( 'filter-open' );
	} );

	// Close the drawer when clicking/tapping outside it.
	$( document ).on( 'touchstart click', function( e ) {
		var container = $( '.filter-open #secondary' );
		if ( ! container.is( e.target ) && 0 === container.has( e.target ).length ) {
			$( 'body' ).removeClass( 'filter-open' );
		}
	} );

	// Close drawer - click the x icon.
	$( '.close-drawer' ).on( 'click', function() {
		$( 'body' ).removeClass( 'filter-open' );
	} );

	$( document ).ready( function() {
		var $loading = $( '#ajax-loading' ).hide();
		$( document ).ajaxStart( function() {
			$loading.show();
		} )
		.ajaxStop( function() {
			$loading.hide();
		} );
	} );

	// Add a class if term description text or an image exists.
	if ( 0 < $( '.term-description' ).length ) {
		$( '.woocommerce-products-header' ).addClass( 'description-exists' );
	}

	if ( 0 < $( '.woocommerce-products-header img' ).length ) {
		$( '.woocommerce-products-header' ).addClass( 'image-exists' );
	}

	// Overlay when a full width menu item is hovered over.
	$( document ).ready( function() {
		$( 'li.full-width' ).hover( function() {
			$( '.site-content' ).addClass( 'overlay' );
		},
		function() {
			$( '.site-content' ).removeClass( 'overlay' );
		} );
	} );

	// Mobile menu toggle.
	$( document ).ready( function() {
		$( '.menu-toggle' ).on( 'click', function() {
			$( 'body' ).toggleClass( 'mobile-toggled' );
		return false;
		} );
	} );

	// Close mobile menu - click the x icon.
	$( '.close-drawer' ).on( 'click', function() {
		$( 'body' ).removeClass( 'mobile-toggled' );
	} );

	// Close the mobile menu when clicking/tapping outside it.
	$( document ).on( 'touchstart click', function( e ) {
		var container = $( '.col-full-nav' );
		if ( ! container.is( e.target ) && 0 === container.has( e.target ).length ) {
			$( 'body' ).removeClass( 'mobile-toggled' );
		}
	} );

	// SKU update.
	$( document ).ready( function() {
		$( 'body' ).on( 'DOMSubtreeModified', '.summary .sku', function() {
			var $sku = $( '.summary .sku' ).text();
			$( '.related-wrapper .sku' ).text( $sku );
		} );
	} );

	// Reveal/Hide Mobile sub menus.
	$( 'body .main-navigation ul.menu li.menu-item-has-children .caret' ).on( 'click', function( e ) {
		$( this ).closest( 'li' ).toggleClass( 'dropdown-open' );
		e.preventDefault();
	} );

	// Scroll to top.
	$( '.logo-mark a' ).click( function() {
		$( 'html, body' ).animate( { scrollTop: 0 }, 'slow' );
		return false;
	} );

	// Smooth scroll for sticky single products - for variable, bundle, composite and grouped items
	$( 'a.variable-grouped-sticky[href*="#"]' ).on( 'click', function( e ) {
		e.preventDefault();

		$( 'html, body' ).animate( {
			scrollTop: $( $( this ).attr( 'href' ) ).offset().top - 80}, 500, 'linear' );
	} );


	var $j = jQuery.noConflict();

	$j( window ).on( 'load', function() {
		'use strict';

		// Woo quantity buttons
		shoptimizerWooQuantityButtons();
	} );

	$j( document ).ajaxComplete( function() {
		'use strict';

	// Woo quantity buttons
		shoptimizerWooQuantityButtons();
	} );

	/**
	* WooCommerce quantity buttons
	* @param {number} $quantitySelector Quantity.
	*/
	function shoptimizerWooQuantityButtons( $quantitySelector ) {

		var $quantityBoxes;
		if ( ! $quantitySelector ) {
			$quantitySelector = '.qty';
		}

		$quantityBoxes = $j( 'div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)' ).find( $quantitySelector );
		if ( $quantityBoxes && 'date' !== $quantityBoxes.prop( 'type' ) && 'hidden' !== $quantityBoxes.prop( 'type' ) ) {

			// Add plus and minus icons
			$quantityBoxes.parent().addClass( 'buttons_added' );
			$quantityBoxes.after( '<div class="quantity-nav"><a href="javascript:void(0)" class="quantity-button quantity-up plus">&nbsp;</a><a href="javascript:void(0)" class="quantity-button quantity-down minus">&nbsp;</a></div>' );

			// Target quantity inputs on product pages
			$j( 'input' + $quantitySelector + ':not(.product-quantity input' + $quantitySelector + ')' ).each( function() {
					var $min = parseFloat( $j( this ).attr( 'min' ) );

					if ( $min && 0 < $min && parseFloat( $j( this ).val() ) < $min ) {
						$j( this ).val( $min );
					}
			} );

			$j( '.plus, .minus' ).unbind( 'click' );
			$j( '.plus, .minus' ).on( 'click', function() {

					// Get values
					var $quantityBox     = $j( this ).closest( '.quantity' ).find( $quantitySelector ),
						$currentQuantity = parseFloat( $quantityBox.val() ),
						$maxQuantity     = parseFloat( $quantityBox.attr( 'max' ) ),
						$minQuantity     = parseFloat( $quantityBox.attr( 'min' ) ),
						$step            = $quantityBox.attr( 'step' );

					// Fallback default values
					if ( ! $currentQuantity || '' === $currentQuantity  || 'NaN' === $currentQuantity ) {
						$currentQuantity = 0;
					}
					if ( '' === $maxQuantity || 'NaN' === $maxQuantity ) {
						$maxQuantity = '';
					}

					if ( '' === $minQuantity || 'NaN' === $minQuantity ) {
						$minQuantity = 0;
					}
					if ( 'any' === $step || '' === $step  || undefined === $step || 'NaN' === parseFloat( $step )  ) {
						$step = 1;
					}

					// Change the value
					if ( $j( this ).is( '.plus' ) ) {
						if ( $maxQuantity && ( $maxQuantity === $currentQuantity || $currentQuantity > $maxQuantity ) ) {
							$quantityBox.val( $maxQuantity );
						} else {
							$quantityBox.val( $currentQuantity + parseFloat( $step ) );
						}

					} else {
						if ( $minQuantity && ( $minQuantity === $currentQuantity || $currentQuantity < $minQuantity ) ) {
							$quantityBox.val( $minQuantity );
						} else if ( 0 < $currentQuantity ) {
							$quantityBox.val( $currentQuantity - parseFloat( $step ) );
						}
					}

					// Trigger change event.
					$quantityBox.trigger( 'change' );
				}
			);
		}
	}
}( jQuery ) );

document.addEventListener( 'DOMContentLoaded', function() {
	let lazyImages = [].slice.call( document.querySelectorAll( 'img.lazy' ) );
	let active = false;

	const lazyLoad = function() {
		if ( false === active ) {
			active = true;

			setTimeout( function() {
				lazyImages.forEach( function( lazyImage ) {
					if ( ( lazyImage.getBoundingClientRect().top <= window.innerHeight && 0 <= lazyImage.getBoundingClientRect().bottom ) && 'none' !== getComputedStyle( lazyImage ).display ) {
						lazyImage.src = lazyImage.dataset.src;
						lazyImage.srcset = lazyImage.dataset.srcset;
						lazyImage.classList.remove( 'lazy' );

						lazyImages = lazyImages.filter( function( image ) {
							return image !== lazyImage;
						} );

						if ( 0 === lazyImages.length ) {
							document.removeEventListener( 'scroll', lazyLoad );
							window.removeEventListener( 'resize', lazyLoad );
							window.removeEventListener( 'orientationchange', lazyLoad );
						}
					}
				} );

				active = false;
			}, 200 );
		}
	};

	document.addEventListener( 'scroll', lazyLoad );
	window.addEventListener( 'resize', lazyLoad );
	window.addEventListener( 'orientationchange', lazyLoad );
} );


/** @format */
// Generated by CoffeeScript 1.12.3

/**
@license Sticky-kit v1.1.4 | MIT | Leaf Corcoran 2015 | http://leafo.net
 */

( function() {
	var $, doc, win;

	$ = window.jQuery;

	win = $( window );
	doc = $( document );

	$.fn.stick_in_parent = function( opts ) {
		var doc_height,
			elm,
			enable_bottoming,
			fn,
			i,
			inner_scrolling,
			len,
			manual_spacer,
			offset_top,
			outer_width,
			parent_selector,
			recalc_every,
			ref,
			sticky_class,
			win_height;
		if ( null == opts ) {
			opts = {};
		}
		( sticky_class = opts.sticky_class ), ( inner_scrolling =
			opts.inner_scrolling ), ( recalc_every = opts.recalc_every ), ( parent_selector =
			opts.parent ), ( offset_top = opts.offset_top ), ( manual_spacer =
			opts.spacer ), ( enable_bottoming = opts.bottoming );
		win_height = win.height();
		doc_height = doc.height();
		if ( null == offset_top ) {
			offset_top = 0;
		}
		if ( null == parent_selector ) {
			parent_selector = void 0;
		}
		if ( null == inner_scrolling ) {
			inner_scrolling = true;
		}
		if ( null == sticky_class ) {
			sticky_class = 'is_stuck';
		}
		if ( null == enable_bottoming ) {
			enable_bottoming = true;
		}
		outer_width = function( el ) {
			var _el, computed, w;
			if ( window.getComputedStyle ) {
				_el = el[ 0 ];
				computed = window.getComputedStyle( el[ 0 ] );
				w =
					parseFloat( computed.getPropertyValue( 'width' ) ) +
					parseFloat( computed.getPropertyValue( 'margin-left' ) ) +
					parseFloat( computed.getPropertyValue( 'margin-right' ) );
				if ( 'border-box' !== computed.getPropertyValue( 'box-sizing' ) ) {
					w +=
						parseFloat( computed.getPropertyValue( 'border-left-width' ) ) +
						parseFloat( computed.getPropertyValue( 'border-right-width' ) ) +
						parseFloat( computed.getPropertyValue( 'padding-left' ) ) +
						parseFloat( computed.getPropertyValue( 'padding-right' ) );
				}
				return w;
			} else {
				return el.outerWidth( true );
			}
		};
		ref = this;
		fn = function(
			elm,
			padding_bottom,
			parent_top,
			parent_height,
			top,
			height,
			el_float,
			detached
		) {
			var bottomed,
				detach,
				fixed,
				last_pos,
				last_scroll_height,
				offset,
				parent,
				recalc,
				recalc_and_tick,
				recalc_counter,
				spacer,
				tick;
			if ( elm.data( 'sticky_kit' ) ) {
				return;
			}
			elm.data( 'sticky_kit', true );
			last_scroll_height = doc_height;
			parent = elm.parent();
			if ( null != parent_selector ) {
				parent = parent.closest( parent_selector );
			}
			if ( ! parent.length ) {
				throw 'failed to find stick parent';
			}
			fixed = false;
			bottomed = false;
			spacer =
				null != manual_spacer ? manual_spacer && elm.closest( manual_spacer ) : $( '<div />' );
			if ( spacer ) {
				spacer.css( 'position', elm.css( 'position' ) );
			}
			recalc = function() {
				var border_top, padding_top, restore;
				if ( detached ) {
					return;
				}
				win_height = win.height();
				doc_height = doc.height();
				last_scroll_height = doc_height;
				border_top = parseInt( parent.css( 'border-top-width' ), 10 );
				padding_top = parseInt( parent.css( 'padding-top' ), 10 );
				padding_bottom = parseInt( parent.css( 'padding-bottom' ), 10 );
				parent_top = parent.offset().top + border_top + padding_top;
				parent_height = parent.height();
				if ( fixed ) {
					fixed = false;
					bottomed = false;
					if ( null == manual_spacer ) {
						elm.insertAfter( spacer );
						spacer.detach();
					}
					elm
						.css( {
							position: '',
							top: '',
							width: '',
							bottom: ''
						} )
						.removeClass( sticky_class );
					restore = true;
				}
				top = elm.offset().top - ( parseInt( elm.css( 'margin-top' ), 10 ) || 0 ) - offset_top;
				height = elm.outerHeight( true );
				el_float = elm.css( 'float' );
				if ( spacer ) {
					spacer.css( {
						width: outer_width( elm ),
						height: height,
						display: elm.css( 'display' ),
						'vertical-align': elm.css( 'vertical-align' ),
						float: el_float
					} );
				}
				if ( restore ) {
					return tick();
				}
			};
			recalc();
			if ( height === parent_height ) {
				return;
			}
			last_pos = void 0;
			offset = offset_top;
			recalc_counter = recalc_every;
			tick = function() {
				var css, delta, recalced, scroll, will_bottom;
				if ( detached ) {
					return;
				}
				recalced = false;
				if ( null != recalc_counter ) {
					recalc_counter -= 1;
					if ( 0 >= recalc_counter ) {
						recalc_counter = recalc_every;
						recalc();
						recalced = true;
					}
				}
				if ( ! recalced && doc_height !== last_scroll_height ) {
					recalc();
					recalced = true;
				}
				scroll = win.scrollTop();
				if ( null != last_pos ) {
					delta = scroll - last_pos;
				}
				last_pos = scroll;
				if ( fixed ) {
					if ( enable_bottoming ) {
						will_bottom = scroll + height + offset > parent_height + parent_top;
						if ( bottomed && ! will_bottom ) {
							bottomed = false;
							elm
								.css( {
									position: 'fixed',
									bottom: '',
									top: offset
								} )
								.trigger( 'sticky_kit:unbottom' );
						}
					}
					if ( scroll < top ) {
						fixed = false;
						offset = offset_top;
						if ( null == manual_spacer ) {
							if ( 'left' === el_float || 'right' === el_float ) {
								elm.insertAfter( spacer );
							}
							spacer.detach();
						}
						css = {
							position: '',
							width: '',
							top: ''
						};
						elm.css( css ).removeClass( sticky_class ).trigger( 'sticky_kit:unstick' );
					}
					if ( inner_scrolling ) {
						if ( height + offset_top > win_height ) {
							if ( ! bottomed ) {
								offset -= delta;
								offset = Math.max( win_height - height, offset );
								offset = Math.min( offset_top, offset );
								if ( fixed ) {
									elm.css( {
										top: offset + 'px'
									} );
								}
							}
						}
					}
				} else {
					if ( scroll > top ) {
						fixed = true;
						css = {
							position: 'fixed',
							top: offset
						};
						css.width =
							'border-box' === elm.css( 'box-sizing' ) ?
								elm.outerWidth() + 'px' :
								elm.width() + 'px';
						elm.css( css ).addClass( sticky_class );
						if ( null == manual_spacer ) {
							elm.after( spacer );
							if ( 'left' === el_float || 'right' === el_float ) {
								spacer.append( elm );
							}
						}
						elm.trigger( 'sticky_kit:stick' );
					}
				}
				if ( fixed && enable_bottoming ) {
					if ( null == will_bottom ) {
						will_bottom = scroll + height + offset > parent_height + parent_top;
					}
					if ( ! bottomed && will_bottom ) {
						bottomed = true;
						if ( 'static' === parent.css( 'position' ) ) {
							parent.css( {
								position: 'relative'
							} );
						}
						return elm
							.css( {
								position: 'absolute',
								bottom: padding_bottom,
								top: 'auto'
							} )
							.trigger( 'sticky_kit:bottom' );
					}
				}
			};
			recalc_and_tick = function() {
				recalc();
				return tick();
			};
			detach = function() {
				detached = true;
				win.off( 'touchmove', tick );
				win.off( 'scroll', tick );
				win.off( 'resize', recalc_and_tick );
				$( document.body ).off( 'sticky_kit:recalc', recalc_and_tick );
				elm.off( 'sticky_kit:detach', detach );
				elm.removeData( 'sticky_kit' );
				elm.css( {
					position: '',
					bottom: '',
					top: '',
					width: ''
				} );
				parent.position( 'position', '' );
				if ( fixed ) {
					if ( null == manual_spacer ) {
						if ( 'left' === el_float || 'right' === el_float ) {
							elm.insertAfter( spacer );
						}
						spacer.remove();
					}
					return elm.removeClass( sticky_class );
				}
			};
			win.on( 'touchmove', tick );
			win.on( 'scroll', tick );
			win.on( 'resize', recalc_and_tick );
			$( document.body ).on( 'sticky_kit:recalc', recalc_and_tick );
			elm.on( 'sticky_kit:detach', detach );
			return setTimeout( tick, 0 );
		};
		for ( i = 0, len = ref.length; i < len; i++ ) {
			elm = ref[ i ];
			fn( $( elm ) );
		}
		return this;
	};
}.call( this ) );

