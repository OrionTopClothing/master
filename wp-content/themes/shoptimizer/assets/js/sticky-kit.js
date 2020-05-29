( function() {
var t, i, o;t = window.jQuery, o = t( window ), i = t( document ), t.fn.stick_in_parent = function( e ) {
var s, r, n, l, a, c, p, d, u, f, h, g, k, y;for ( null == e && ( e = {} ), k = e.sticky_class, c = e.inner_scrolling, g = e.recalc_every, h = e.parent, u = e.offset_top, d = e.spacer, n = e.bottoming, y = o.height(), s = i.height(), null == u && ( u = 0 ), null == h && ( h = void 0 ), null == c && ( c = ! 0 ), null == k && ( k = 'is_stuck' ), null == n && ( n = ! 0 ), f = function( t ) {
var i, o;return window.getComputedStyle ? ( t[0], i = window.getComputedStyle( t[0] ), o = parseFloat( i.getPropertyValue( 'width' ) ) + parseFloat( i.getPropertyValue( 'margin-left' ) ) + parseFloat( i.getPropertyValue( 'margin-right' ) ), 'border-box' !== i.getPropertyValue( 'box-sizing' ) && ( o += parseFloat( i.getPropertyValue( 'border-left-width' ) ) + parseFloat( i.getPropertyValue( 'border-right-width' ) ) + parseFloat( i.getPropertyValue( 'padding-left' ) ) + parseFloat( i.getPropertyValue( 'padding-right' ) ) ), o ) : t.outerWidth( ! 0 );
}, this, l = function( e, r, l, a, p, m, v, b ) {
var w, _, x, P, V, F, C, z, I, A, M, S;if ( ! e.data( 'sticky_kit' ) ) {
if ( e.data( 'sticky_kit', ! 0 ), V = s, C = e.parent(), null != h && ( C = C.closest( h ) ), ! C.length ) {
throw 'failed to find stick parent';
} if ( x = ! 1, w = ! 1, ( M = null != d ? d && e.closest( d ) : t( '<div />' ) ) && M.css( 'position', e.css( 'position' ) ), ( z = function() {
var t, n, c;if ( ! b ) {
return y = o.height(), s = i.height(), V = s, t = parseInt( C.css( 'border-top-width' ), 10 ), n = parseInt( C.css( 'padding-top' ), 10 ), r = parseInt( C.css( 'padding-bottom' ), 10 ), l = C.offset().top + t + n, a = C.height(), x && ( x = ! 1, w = ! 1, null == d && ( e.insertAfter( M ), M.detach() ), e.css( {position: '', top: '', width: '', bottom: ''} ).removeClass( k ), c = ! 0 ), p = e.offset().top - ( parseInt( e.css( 'margin-top' ), 10 ) || 0 ) - u, m = e.outerHeight( ! 0 ), v = e.css( 'float' ), M && M.css( {width: f( e ), height: m, display: e.css( 'display' ), 'vertical-align': e.css( 'vertical-align' ), float: v} ), c ? S() : void 0;
}
} )(), m !== a ) {
return P = void 0, F = u, A = g, S = function() {
var t, i, f, h, _;if ( ! b ) {
return f = ! 1, null != A && 0 >= ( A -= 1 ) && ( A = g, z(), f = ! 0 ), f || s === V || ( z(), f = ! 0 ), h = o.scrollTop(), null != P && ( i = h - P ), P = h, x ? ( n && ( _ = h + m + F > a + l, w && ! _ && ( w = ! 1, e.css( {position: 'fixed', bottom: '', top: F} ).trigger( 'sticky_kit:unbottom' ) ) ), h < p && ( x = ! 1, F = u, null == d && ( 'left' !== v && 'right' !== v || e.insertAfter( M ), M.detach() ), t = {position: '', width: '', top: ''}, e.css( t ).removeClass( k ).trigger( 'sticky_kit:unstick' ) ), c && m + u > y && ( w || ( F -= i, F = Math.max( y - m, F ), F = Math.min( u, F ), x && e.css( {top: F + 'px'} ) ) ) ) : h > p && ( x = ! 0, ( t = {position: 'fixed', top: F} ).width = 'border-box' === e.css( 'box-sizing' ) ? e.outerWidth() + 'px' : e.width() + 'px', e.css( t ).addClass( k ), null == d && ( e.after( M ), 'left' !== v && 'right' !== v || M.append( e ) ), e.trigger( 'sticky_kit:stick' ) ), x && n && ( null == _ && ( _ = h + m + F > a + l ), ! w && _ ) ? ( w = ! 0, 'static' === C.css( 'position' ) && C.css( {position: 'relative'} ), e.css( {position: 'absolute', bottom: r, top: 'auto'} ).trigger( 'sticky_kit:bottom' ) ) : void 0;
}
}, I = function() {
return z(), S();
}, _ = function() {
if ( b = ! 0, o.off( 'touchmove', S ), o.off( 'scroll', S ), o.off( 'resize', I ), t( document.body ).off( 'sticky_kit:recalc', I ), e.off( 'sticky_kit:detach', _ ), e.removeData( 'sticky_kit' ), e.css( {position: '', bottom: '', top: '', width: ''} ), C.position( 'position', '' ), x ) {
return null == d && ( 'left' !== v && 'right' !== v || e.insertAfter( M ), M.remove() ), e.removeClass( k );
}
}, o.on( 'touchmove', S ), o.on( 'scroll', S ), o.on( 'resize', I ), t( document.body ).on( 'sticky_kit:recalc', I ), e.on( 'sticky_kit:detach', _ ), setTimeout( S, 0 );
}
}
}, a = 0, p = this.length;a < p;a++ ) {
r = this[a], l( t( r ) );
} return this;
};
} ).call( this );
