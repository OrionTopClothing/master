<?php
	$currency_code_options = get_woocommerce_currencies();
	$currency_symbol = get_woocommerce_currency_symbol();
	$wc_currency_pos = get_option( 'woocommerce_currency_pos' );
	$data_currency = WR_Currency_Hook::get_currency();
	$wc_currency_code = get_woocommerce_currency();

	$currency_position = array(
		'left'        => __( 'Left', 'woocommerce' ) . ' (' . $currency_symbol . '99.99)',
		'right'       => __( 'Right', 'woocommerce' ) . ' (99.99' . $currency_symbol . ')',
		'left_space'  => __( 'Left with space', 'woocommerce' ) . ' (' . $currency_symbol . ' 99.99)',
		'right_space' => __( 'Right with space', 'woocommerce' ) . ' (99.99 ' . $currency_symbol . ')'
	);

	foreach ( $currency_code_options as $code => $name ) {
		$currency_code_options[ $code ] = $name . ' (' . get_woocommerce_currency_symbol( $code ) . ')';
	}
	
	if( $data_currency ) {
		$data_currency = json_encode( $data_currency );
	}
?>

<style type="text/css">
	#list_currency {
		background: #fff;
		-webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    	box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
	}
	#list_currency table {
	    border-spacing: 0;
	    width: 100%;
	}
	#list_currency th {
		text-align:left;
		padding: 16px 0;
	}
	#list_currency td {
		padding-right: 30px;
		box-sizing: border-box;
	}
	#list_currency .td-handle {
		text-align: center;
	}
	#list_currency .handle {
		font-size: 20px;
		color: #999;
		display: inline-block;
		padding: 15px 12px;
		cursor: move;
	}
	#list_currency .td-delete {
		text-align: center;	
		padding-right: 0px;
	}
	#list_currency .delete {
		cursor: pointer;
	    display: inline-block;
	    padding: 10px;
	    color: #a00;
	    transition: all 0.3s;
	}
	#list_currency .delete:hover {
		color: red;
	}
	#list_currency td:first-child,
	#list_currency td:last-child {
		padding-right: 0;
	}
	#list_currency .list-add tr:nth-child(even),
	#list_currency .list-title tr:nth-child(odd) {
		background: #f9f9f9;	
	}
	#list_currency .update:hover {
		color: #00a0d2;
	}
	#list_currency .td-flag {
		padding-right: 0px;
		text-align: center;
	}
	#list_currency .flag {
		display: inline-block;
		width: 35px;
		height: 25px;
		text-align: center;
		padding: 12px 0;
	}
	#list_currency .flag img {
		vertical-align: middle;
	    max-width: 100%;
	    max-height: 100%;
	}
	#list_currency .center {
		text-align: center;
	}
	#add-currency {
		margin: 15px 20px 15px 15px;
	}
	#list_currency .update {
		cursor: pointer;
		padding: 4px 10px;
		transition: all 0.3s;
		color: #0073aa;
	}

	@-webkit-keyframes fa-spin{
		0%{
			-webkit-transform:rotate(0);transform:rotate(0)}
		100%{
			-webkit-transform:rotate(359deg);transform:rotate(359deg)
		}
	}
	@keyframes fa-spin{
		0%{
			-webkit-transform:rotate(0);transform:rotate(0)
		}
		100%{
			-webkit-transform:rotate(359deg);transform:rotate(359deg)
		}
	}
	#list_currency .loading {
		-webkit-animation: fa-spin 2s infinite linear;
		animation: fa-spin 2s infinite linear;
		pointer-events: none;
	}
	#list_currency .list-add th {
		padding: 0;
	}


	#list_currency .wrcc-drop {
		width: 50px;
	}
	#list_currency .wrcc-type,
	#list_currency .wrcc-position {
		width: 25%;
	}
	#list_currency .wrcc-rate {
		width: 240px;
	}
	#list_currency .wrcc-updated {
		width: 140px;
	}
	#list_currency .wrcc-flag {
	    width: 100px;
	}
	#list_currency .wrcc-delete {
	    width: 45px;
	}

</style>

<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce( WR_CC ); ?>" />

<div id="list_currency">
	<div class="list">
		<table class="list-title">
			<tr>
				<th class="wrcc-drop"></th>
				<th class="wrcc-type"><?php esc_html_e( 'Currency type', 'wr-currency' ); ?> <?php echo wc_help_tip( __( 'Select the type of currency that you want to be included in your shop. The currency in the first row will be default and has the base exchange rate of 1.', 'wr-currency' ) ); ?></th>
				<th class="wrcc-position"><?php esc_html_e( 'Currency position', 'wr-currency' ); ?> <?php echo wc_help_tip( __( 'Select how the currency symbol is positioned: before (left) or after (right) the number.', 'wr-currency' ) ); ?></th>
				<th class="wrcc-rate"><?php esc_html_e( 'Exchange rate', 'wr-currency' ); ?> <?php echo wc_help_tip( __( 'You can input your desired exchange rate or click on the refresh button to automatically retrieve the rates from European Central Bank.', 'wr-currency' ) ); ?></th>
				<th class="wrcc-updated"><?php esc_html_e( 'Last Updated', 'wr-currency' ); ?> <?php echo wc_help_tip( __( 'This field shows the last time the exchange rates are updated on European Central Bank.', 'wr-currency' ) ); ?></th>
				<th class="wrcc-flag center"><?php esc_html_e( 'Flag', 'wr-currency' ); ?></th>
				<th class="wrcc-delete"></th>
			</tr>

			<tr class="default">
				<td style="font-size: 18px; text-align: center" title="<?php esc_html_e( 'Default', 'wr-currency' ); ?>"><span class="dashicons dashicons-star-filled"></span></td>
				<td>
					<select name="wrcc[code][]" class="code wc-enhanced-select" style="max-width:300px;">
						<?php
							foreach( $currency_code_options as $key => $val ){
								echo '<option ' . ( ( $key == $wc_currency_code ) ? 'selected="selected"' : NULL ) . ' value="' . $key . '">' . $val . '</option>';
							}
						?>
				    </select>
				</td>
				<td>
					<select class="wc-enhanced-select" name="wrcc[position][]" style="max-width:215px;">
					<?php
						foreach( $currency_position as $key => $val ){
							echo '<option ' . ( ( $key == $wc_currency_pos ) ? 'selected="selected"' : NULL ) . ' value="' . $key . '">' . $val . '</option>';
						}
					?>
					</select>
				</td>
				<td style="padding-left: 7px;font-size: 14px;">1</td>
				<input type="hidden" value="1" name="wrcc[rate][]">
				<td></td>
				<td class="td-flag"><span class="flag"><img src="<?php echo WR_CC_URL . 'assets/images/flag/' . strtolower( $wc_currency_code ) . '.png'; ?>" /></span></td>
				<td></td>
				<input type="hidden" class="id-currency" name="wrcc[id][]" value="0" />
				<input type="hidden" value="" name="wrcc[last_updated][]" >
			</tr>
		</table>

		<table class="list-add">
			<tr>
				<th class="wrcc-drop"></th>
				<th class="wrcc-type"></th>
				<th class="wrcc-position"></th>
				<th class="wrcc-rate"></th>
				<th class="wrcc-updated"></th>
				<th class="wrcc-flag"></th>
				<th class="wrcc-delete"></th>
			</tr>
		</table>
	</div>
	<span id="add-currency" class="button"><?php esc_html_e( 'Add currency', 'wr-currency' ); ?></span>
</div>

<script id="currency-template" type="text/html">
	<tr>
		<td class="td-handle"><span class="handle dashicons dashicons-menu"></span></td>
		<td>
			<select name="wrcc[code][]" class="code wc-enhanced-select" style="max-width:300px;">
				<?php echo '<%;'; ?>if( typeof code == "undefined" ){ var code = "<?php echo $wc_currency_code; ?>"; }<?php echo '%>'; ?>
				<?php
					foreach( $currency_code_options as $key => $val ){
						echo '<option <% if( typeof code != "undefined" && code == "' . $key . '" ) print( "selected=\"selected\"" ); %> value="' . $key . '">' . $val . '</option>';
					}
				?>
		    </select>
		</td>
		<td>
			<select class="wc-enhanced-select" name="wrcc[position][]" style="max-width:215px;">
				<?php echo '<%;'; ?>if( typeof position == "undefined" ){ var position = "<?php echo $wc_currency_pos; ?>"; }<?php echo '%>'; ?>
				<?php
					foreach( $currency_position as $key => $val ){
						echo '<option <% if( typeof position != "undefined" && position == "' . $key . '" ) print( "selected=\"selected\"" ); %> value="' . $key . '">' . $val . '</option>';
					}
				?>
			</select>
		</td>
		<td><input class="rate" type="text" value="<?php echo '<%;'; ?> if( typeof rate != 'undefined' ) print( rate ); <?php echo '%>'; ?>" name="wrcc[rate][]" placeholder="<?php esc_html_e( 'Exchange rate', 'wr-currency' ); ?>" /> <span title="<?php esc_html_e( 'Update rate from European Central Bank.', 'wr-currency' ); ?>" class="update dashicons dashicons-update"></span></td>
		<td class="last_updated_html"><?php echo '<%;'; ?> if( typeof last_updated != 'undefined' ) { print( last_updated ) } else { print( date_now ) }; <?php echo '%>'; ?></td>
		<td class="td-flag"><span class="flag"><img src="<?php echo '<%;'; ?> print( wrls_settings_default.plugin_url + 'assets/images/flag/' + code.toLowerCase() + '.png' );<?php echo '%>'; ?>" /></span></td>
		<td class="td-delete"><span class="delete dashicons dashicons-no-alt"></span></td>
		<input type="hidden" class="id-currency" name="wrcc[id][]" value="<?php echo '<%;'; ?> if( typeof id != 'undefined' ) print( id ); <?php echo '%>'; ?>" />
		<input type="hidden" class="last_updated" name="wrcc[last_updated][]" value="<?php echo '<%;'; ?> if( typeof last_updated != 'undefined' ) { print( last_updated ) } else { print( date_now ) }; <?php echo '%>'; ?>" />
	</tr>
</script>

<script type="text/javascript">
	( function( $ ){
		"use strict";

		$( document ).ready( function(){

			var data_currency = <?php echo $data_currency ? $data_currency: '[]' ?>;

			var monthNames   = [
					'<?php esc_html_e( "Jan", "wr-currency" ); ?>', 
					'<?php esc_html_e( "Feb", "wr-currency" ); ?>', 
					'<?php esc_html_e( "Mar", "wr-currency" ); ?>', 
					'<?php esc_html_e( "Apr", "wr-currency" ); ?>', 
					'<?php esc_html_e( "May", "wr-currency" ); ?>', 
					'<?php esc_html_e( "Jun", "wr-currency" ); ?>', 
					'<?php esc_html_e( "Jul", "wr-currency" ); ?>', 
					'<?php esc_html_e( "Aug", "wr-currency" ); ?>', 
					'<?php esc_html_e( "Sep", "wr-currency" ); ?>', 
					'<?php esc_html_e( "Oct", "wr-currency" ); ?>', 
					'<?php esc_html_e( "Nov", "wr-currency" ); ?>', 
					'<?php esc_html_e( "Dec", "wr-currency" ); ?>'
				],
				date         = new Date(),
				date_now = monthNames[ date.getMonth() ] +  ' ' + ( '0' + date.getDate() ).slice(-2) + ', ' + date.getFullYear();

			// Render list currency
			$.each( data_currency, function( key, val ){
				val[ 'date_now' ] = date_now;
				var template_show = _.template( $( "script#currency-template" ).html() )( val );

				$( '#list_currency .list-add tbody' ).append( template_show );
			} );

			$( document.body ).trigger( 'wc-enhanced-select-init' );

			// Add currency
			$( '#add-currency' ).click( function(){
				var id = 1;

				$( '#list_currency .id-currency' ).each( function(){
					var _this = $(this),
					value = _this.val();

					if( value > id ) {
						id = value;
					}

				} );

				id++;

				var template_show = _.template( $( "script#currency-template" ).html() )( {
					id: id,
					date_now: date_now
				} );
				$( '#list_currency .list-add tbody' ).append( template_show );

				$( document.body ).trigger( 'wc-enhanced-select-init' );
			} );

			// Delete currency
			$( '#list_currency' ).on( 'click', '.delete', function(){
				var delete_confirm = confirm( 'Do you really want to delete this item?' );
				if( delete_confirm ) {
					$(this).closest( 'tr' ).remove();
				}
			} );

			$( '#list_currency .list-add tbody' ).sortable({
				handle: '.handle'
			});

			$( 'body' ).on( 'change', '#list_currency .code', function(){
				var _this = $(this);
				var val = _this.val().toLowerCase();
				var flag = _this.closest( 'tr' ).find( '.flag img' );

				flag.attr( 'src', wrls_settings_default.plugin_url + 'assets/images/flag/' + val + '.png' );

			} );

			$( 'body' ).on( 'click', '#list_currency .update', function(){
				var _this        = $(this),
				parent           = _this.closest( 'tr' ),
				currency_base    = $( '#list_currency .default select.code' ).val(),
				currency_current = parent.find( 'select.code' ).val(),
				input_rate       = parent.find( '.rate' );

				_this.addClass( 'loading' );

				$.getJSON( 'https://query.yahooapis.com/v1/public/yql?q= select Rate from yahoo.finance.xchange where pair = "' + ( currency_base + currency_current ).toUpperCase() + '"&format=json&diagnostics=true&env=store://datatables.org/alltableswithkeys', function( data ) {
					if( data.query != undefined && data.query.results != undefined && data.query.results.rate != undefined && data.query.results.rate.Rate != undefined && data.query.results.rate.Rate != 'N/A' ) {
						var rate = Number( data.query.results.rate.Rate );

						if( rate == 0 ) {
							$.getJSON( 'https://query.yahooapis.com/v1/public/yql?q= select Rate from yahoo.finance.xchange where pair = "' + ( currency_current + currency_base ).toUpperCase() + '"&format=json&diagnostics=true&env=store://datatables.org/alltableswithkeys', function( data ) {
								var rate = 1 / Number( data.query.results.rate.Rate );
								parent.find( '.rate' ).val( rate );
								parent.find( '.last_updated' ).val( date_now );
								parent.find( '.last_updated_html' ).html( date_now );

								_this.removeClass( 'loading' );
							});
						} else {
							parent.find( '.rate' ).val( rate );
							parent.find( '.last_updated' ).val( date_now );
							parent.find( '.last_updated_html' ).html( date_now );
							_this.removeClass( 'loading' );
						}
					} else {
						_this.removeClass( 'loading' );
					}
				});
			} );
		} );

	} )( jQuery )
</script>