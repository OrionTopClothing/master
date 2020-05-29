<?php 
/*
Plugin Name: NoMorePass login
Plugin URI: https://www.nomorepass.com
Description: Plugin to allow login using NoMorePass app
Author: Jose A. Espinosa
Version: 1.9.3
Author URI: https://www.yoprogramo.com
Text Domain: nomorepass
Domain Path: /languages/
*/

if ( !function_exists( 'add_action' ) ) {
    die();
}

add_action( 'resetpass_form',array('NoMorePass','showSendPassButton'));
add_action ('login_form',array('NoMorePass','showButton'));
add_action ('login_enqueue_scripts',array('NoMorePass','enqueueScripts'));
add_action( 'admin_enqueue_scripts', 'nomorepass_load_scripts_admin' );
add_action('admin_menu', 'nomorepass_plugin_setup_menu');
add_action('admin_init','nomorepass_settings');
add_action('user_register', array('NoMorePass','auto_login') );
add_action('register_form', array('NoMorePass','new_item_register_form'));
add_filter('registration_errors', array ('NoMorePass','registration_errors'), 10, 3 );
add_filter('authenticate', array('NoMorePass','check_only_nmp'), 10, 3 );


load_plugin_textdomain( 'nomorepass', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

function nomorepass_plugin_setup_menu(){
	   add_menu_page( 'NoMorePass', 'NoMorePass login', 'manage_options', 'nomorepass', 'nomorepass_admin_init', 'dashicons-admin-network' );
}

function nomorepass_load_scripts_admin() {
	wp_register_script('nomorepass_admin_script', plugins_url('public/js/admin.js', __FILE__),array('jquery'));
	wp_enqueue_media();
	wp_enqueue_script('nomorepass_admin_script');
}

function nomorepass_admin_init(){
	?>
	<div class="wrap">
		<h2>NoMorePass <?php echo __('Configuration','nomorepass');?></h2>
		<p><?php echo __('Options to configure','nomorepass');?>:</p>
		<form method="post" action="options.php">
		<?php 
			settings_fields ('nomorepass-login-group');
			do_settings_sections ('nomorepass-login-group');
		?>
		<table class="form-table"><tr><th>
		<label><?php echo __('Show login form','nomorepass');?></label></th><td>
		<select id="nomorepass-show-form" name="nomorepass-show-form">
		<?php if (get_option('nomorepass-show-form',1)==0) {
			$selno="selected";
			$selsi="";
		} else {
			$selno="";
			$selsi="selected";
		} ?>
			<option value="0" <?php echo $selno;?> ><?php echo __('NO','nomorepass');?></option>
			<option value="1" <?php echo $selsi;?> ><?php echo __('YES','nomorepass');?></option>
		</select>
		<p class="description"><?php echo __('Show the user / password fields?','nomorepass');?></p>
		</td></tr><tr><th>
		<label><?php echo __('Show password reset','nomorepass');?></label></th><td>
		<select id="nomorepass-show-resetpass" name="nomorepass-show-resetpass">
		<?php if (get_option('nomorepass-show-resetpass')==0) {
			$selno="selected";
			$selsi="";
		} else {
			$selno="";
			$selsi="selected";
		} ?>
			<option value="0" <?php echo $selno;?> ><?php echo __('NO','nomorepass');?></option>
			<option value="1" <?php echo $selsi;?> ><?php echo __('YES','nomorepass');?></option>
		</select>
		<p class="description"><?php echo __('Show the password field when resetting password?','nomorepass');?></p>
		</td></tr><tr><th>
		<label><?php echo __('Auto-launch QR','nomorepass');?></label></th><td>
		<select id="nomorepass-auto-qr" name="nomorepass-auto-qr">
		<?php if (get_option('nomorepass-auto-qr',0)==0) {
			$selno="selected";
			$selsi="";
		} else {
			$selno="";
			$selsi="selected";
		} ?>
			<option value="0" <?php echo $selno;?> ><?php echo __('NO','nomorepass');?></option>
			<option value="1" <?php echo $selsi;?> ><?php echo __('YES','nomorepass');?></option>
		</select>
		<p class="description"><?php echo __('Launch the QR on loading login page?','nomorepass');?></p>
		</td></tr><tr><th>
		<label><?php echo __('Auto-login after registration','nomorepass');?></label></th><td>
		<select id="nomorepass-auto-login" name="nomorepass-auto-login">
		<?php if (get_option('nomorepass-auto-login',0)==0) {
			$selno="selected";
			$selsi="";
		} else {
			$selno="";
			$selsi="selected";
		} ?>
			<option value="0" <?php echo $selno;?> ><?php echo __('NO','nomorepass');?></option>
			<option value="1" <?php echo $selsi;?> ><?php echo __('YES','nomorepass');?></option>
		</select>
		<p class="description"><?php echo __('Auto-login after registration?','nomorepass');?></p>
		</td></tr><tr><th>
		<label><?php echo __('NoMorePass Only','nomorepass');?></label></th><td>
		<select id="nomorepass-login-only" name="nomorepass-login-only">
		<?php if (get_option('nomorepass-login-only',0)==0) {
			$selno="selected";
			$selsi="";
		} else {
			$selno="";
			$selsi="selected";
		} ?>
			<option value="0" <?php echo $selno;?> ><?php echo __('NO','nomorepass');?></option>
			<option value="1" <?php echo $selsi;?> ><?php echo __('YES','nomorepass');?></option>
		</select>
		<p class="description"><?php echo __('Only allow logins using NoMorePass?','nomorepass');?></p>
		</td></tr>
		<tr>
		<th>
		<?php echo __('Custom button','nomorepass');?>
		</th>
		<td>
		<?php 
			$options = get_option('nomorepass-custom-logo');
			$default_image = plugins_url('public/images/button.png', __FILE__);
			if ( !empty( $options ) ) {
				$image_attributes = wp_get_attachment_image_src( $options, array( 36, 36 ) );
				$src = $image_attributes[0];
				$value = $options;
			} else {
				$src = $default_image;
				$value = '';
			}
		?>
			<div class="upload">
            <img data-src="<?php echo $default_image;?>" src="<?php echo $src;?>" height="36px" />
				<div>
					<input type="hidden" name="nomorepass-custom-logo" id="nomorepass-custom-logo" value="<?php echo $value?>" />
					<button type="button" class="upload_image_button button"><?php echo __('Upload','nomorepass');?></button>
					<button type="button" class="remove_image_button button">&times;</button>
				</div>
        	</div>
		</td>
		</tr>
		<tr><th>
		<?php echo __('Custom message','nomorepass');?>
		</th><td>
		<?php $nmp_msg = get_option('nomorepass-custom-msg');
		if (empty($nmp_msg)) {
			$nmp_msg = __('Click on <a href="https://nomorepass.com">NoMorePass</a> icon and use the qrcode to login','nomorepass');
		}
		$settings = array( 'textarea_rows' => 4 );
		echo wp_editor( $nmp_msg , 'nomorepass-custom-msg',$settings );
		?>
		<p class="description" id="tagline-nomorepass-custom-msg"><?php echo __('Custom message','nomorepass');?></p>
		</td></tr>
		</table>
		<?php submit_button(); ?>
		</form>
		<h2><?php echo __('More info','nomorepass');?></h2>
		<a href ="https://nomorepass.com">NoMorePass.com</a>

	</div>
	<?php
}

function nomorepass_settings() {
	register_setting('nomorepass-login-group',
	'nomorepass-show-form',
	'intval');
	register_setting('nomorepass-login-group',
	'nomorepass-show-resetpass',
	'intval');
	register_setting('nomorepass-login-group',
	'nomorepass-auto-qr',
	'intval');
	register_setting('nomorepass-login-group',
	'nomorepass-auto-login',
	'intval');
	register_setting('nomorepass-login-group',
	'nomorepass-login-only',
	'intval');
	register_setting('nomorepass-login-group',
	'nomorepass-custom-logo',
	'string');
	register_setting('nomorepass-login-group',
	'nomorepass-custom-msg',
	'string');
}



class NoMorePass {

	public function __construct() {
		load_plugin_textdomain( 'nomorepass', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	public static function showSendPassButton($user) {
		// Shows the NMP button under reset password button
		$src = NoMorePass::getLogoSrc();
		?><p style="display: block;width: 100%;text-align: center;">
		<a href="javascript:sendpassword()"><img src="<?php echo $src?>" title="<?php echo __('Send to NoMorePass','nomorepass')?>" /></a></p>
		<?php NoMorePass::paintQr();?>
		<script>
		function sendpassword() {
			// Generate QR to receive the data
			var qrelement = document.querySelector('#qrcode');
			NomorePass.init();
			var user = "<?php echo $user->user_login;?>";
			var pass = document.querySelector('#pass1-text').value;
			NomorePass.getQrSend (window.location.hostname,user,pass,{type:'pwd'}, 
				function (text){
					if (text==false){
						alert("Error calling nmp");
					} else {
						// Show the qr with this text
						qrelement.innerHTML="";
						qrelement.style.display="block";
						new QRCode(qrelement, text);
						qrelement.onclick=function(e){
							window.open(text,'_system');
						};
						document.querySelector("#qrcodecont").style.display='block';
						// wait to be scanned and received
						// by the app
						NomorePass.send (function(data){
							qrelement.innerHTML="<p>Password received</p>";
							hideQr();
							document.querySelector('#wp-submit').click();

						})
					}
				}
			);
		}
		document.querySelector(".indicator-hint").innerHTML='<?php echo __('Click to scan with <a href="https://nomorepass.com">NoMorePass</a> app and receive the password directly on your mobile phone','nomorepass');?>';
		document.querySelector(".reset-pass").innerHTML='<?php echo __('New pass for account','nomorepass');?>';
		<?php 
			$showform = get_option('nomorepass-show-resetpass',1);
			if ($showform==0) {
				?>// hidding fields 
			document.querySelector(".user-pass1-wrap").style.display="none";
			window.onload = function () {
				document.querySelector("#wp-submit").style.display="none";
			}
				<?php
			}
		?>
		</script>
		<?php
	}
	public static function showButton() {
		// Shows the NMP button under login window
		$src = NoMorePass::getLogoSrc();
		$nmp_msg = NoMorePass::getNmpMsg();
		?><p style="display: block;width: 100%;text-align: center;">
		<a href="javascript:getpassword()"><img src="<?php echo $src;?>" title="<?php echo __('Enter using NoMorePass','nomorepass')?>" /></a></p>
		<p><?php echo $nmp_msg;?></p>
		<?php NoMorePass::paintQr();?>
		<script>
			function getpassword() {
				// Generate qr, wait on receiving pass 
				// and fill the fields
				var qrelement = document.querySelector('#qrcode');
				NomorePass.init();
				NomorePass.getQrText(window.location.hostname,function(text){
					qrelement.innerHTML="";
					qrelement.style.display="block";
					new QRCode(qrelement, text);
					qrelement.onclick=function(e){
						window.open(text,'_system');
					};
					document.querySelector("#qrcodecont").style.display='block';
					// Waiting...
					NomorePass.start(function(error,data){
						if (error)
							alert (data);
						else {
					<?php if (get_option("nomorepass-login-only",0)==1) {?>
							var container = document.querySelector('#loginform');
							var input = document.createElement("input");
							input.type = "hidden";
							input.name = "nmp_extra_field";
							container.appendChild(input);
						<?php } ?>
							document.querySelector('#user_login').value=data.user;
							document.querySelector('#user_pass').value=data.password;
							document.querySelector('#wp-submit').click();
							qrelement.innerHTML="";
							hideQr();
						}
					});
				});
			}
		<?php 
			$showform = get_option('nomorepass-show-form',1);
			if ($showform==0) {
				?>// hidding fields 
			var tohide=document.querySelectorAll('form label, form input[type=password], form input[type=text], div.wp-pwd');
			for (var i =0; i< tohide.length;i++) tohide[i].style.display='none';
			window.onload = function () {
				document.querySelector(".submit").style.display="none";
			}
				<?php
			}
			$auto = get_option ('nomorepass-auto-qr',0);
			if ($auto==1) {
				?>
				getpassword();
				<?php
			}
		?>
		</script>
		<?php
	}
	public static function registerScripts(){
		wp_register_script('nmp_aes_script', plugins_url('public/js/aes.js', __FILE__));
		wp_register_script('nmp_qrcode_script', plugins_url('public/js/qrcode.js', __FILE__));
		wp_register_script('nomorepass_script', plugins_url('public/js/nomorepass.js', __FILE__));
	}
	public static function enqueueScripts(){
		if(!wp_script_is('nomorepass_script','registered')) {
            NoMorePass::registerScripts();
		}
		wp_enqueue_script('nmp_aes_script');
		wp_enqueue_script('nmp_qrcode_script');
        wp_enqueue_script('nomorepass_script');
	}

	public static function check_only_nmp ($user,$username,$password) {
		if (get_option("nomorepass-login-only",0)==1) {
			if (!isset($_POST['nmp_extra_field'])) {
				remove_action('authenticate', 'wp_authenticate_username_password', 20);
				remove_action('authenticate', 'wp_authenticate_email_password', 20); 
				return new WP_Error( 'denied', __("Only NoMorePass login allowed.",'nomorepass') );
			}
		}
		// wp continua (o no) con la autenticación
		return null;
	}
	// Auto-login
	public static function auto_login ( $user_id ) {
		if (get_option("nomorepass-auto-login",0)==1) {
			// El password viene en el campo password1 que se añadió al registro.
			wp_set_password( $_POST['password1'], $user_id ); 
	
			wp_set_current_user($user_id);
			wp_set_auth_cookie($user_id);
			
			global $_POST;
			if ($_POST['redirect_to'] == "") {
				$redirect = get_home_url();
				$redirect .= "/wp-admin/profile.php";				
			} else {
				$redirect = $_POST['redirect_to'];
			}
	
			wp_redirect($redirect);
	
			wp_new_user_notification($user_id, null, 'both'); //'admin' or blank sends admin notification email only. Anything else will send admin email and user email
	
			exit;
		}
	}

	public static function new_item_register_form() {
		
		if (get_option("nomorepass-auto-login",0)==1) {
			// Bien venía el pass de antes, bien lo generamos nosotros.
			$password1 = ( ! empty( $_POST['password1'] ) ) ? trim( $_POST['password1'] ) :  wp_generate_password();
			?>
			<p class="user-pass1-wrap">
				<input type="hidden" name="password1" id="password1" class="input" value="<?php echo esc_attr( wp_unslash( $password1 ) ); ?>" size="25" /></label>
				<input type="hidden" name="qrisok" id="qrisok" value="NO"/>
			</p>
			<p style="display: block;width: 100%;text-align: center;">
			<a href="javascript:sendpassword()"><img src="<?php echo plugins_url('public/images/button.png', __FILE__)?>" title="<?php echo __('Send to NoMorePass','nomorepass')?>" /></a></p>
			<?php NoMorePass::paintQr();?>
			<script>
			function sendpassword() {
				// Generate QR to receive the data
				var qrelement = document.querySelector('#qrcode');
				NomorePass.init();
				var user = document.querySelector('#user_login').value;
				var pass = document.querySelector('#password1').value;
				var email = document.querySelector('#user_email').value;
				if (user.length==0) {
					document.querySelector('#user_login').focus();
					return;
				}
				if (email.length==0) {
					document.querySelector('#user_email').focus();
					return;
				}
				NomorePass.getQrSend (window.location.hostname,user,pass,{type:'pwd'}, 
					function (text){
						if (text==false){
							alert("Error calling nmp");
						} else {
							// Show the qr with this text
							qrelement.innerHTML="";
							qrelement.style.display="block";
							new QRCode(qrelement, text);
							qrelement.onclick=function(e){
								window.open(text,'_system');
							};
							document.querySelector("#qrcodecont").style.display='block';
							// wait to be scanned and received
							// by the app
							NomorePass.send (function(data){
								qrelement.innerHTML="<p>Password received</p>";
								hideQr();
								document.querySelector('#qrisok').value = "SI";
								document.querySelector('#wp-submit').click();

							})
						}
					}
				);
			}
			document.querySelector(".user-pass1-wrap").style.display="none";
			window.onload = function () {
				document.querySelector("#reg_passmail").innerHTML='<?php echo __('Click to scan with <a href="https://nomorepass.com">NoMorePass</a> app and receive the password directly on your mobile phone','nomorepass');?>';
				document.querySelector(".submit").style.display='none';
				document.onkeypress = stopRKey; 
				function stopRKey(evt) { 
					var evt = (evt) ? evt : ((event) ? event : null); 
					var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
					if ((evt.keyCode == 13) && (node.type=="text"))  {return false;} 
				} 
			}

			</script>
				</p>
			<?php
		}
	
	}
	public static function registration_errors( $errors, $sanitized_user_login, $user_email ) {
		if (get_option("nomorepass-auto-login",0)==1) {
			if ( empty( $_POST['qrisok'] ) || ! empty( $_POST['qrisok'] ) && trim( $_POST['qrisok'] ) != 'SI' ) {
				$errors->add( 'qr_error', __( '<strong>ERROR</strong>: Please, click the NoMorePass icon.', 'nomorepass' ) );
			}
		}
	
		return $errors;
	}

	public static function getNmpMsg() {
		$nmp_msg = get_option('nomorepass-custom-msg');
		if (empty($nmp_msg)) {
			$nmp_msg = __('Click on <a href="https://nomorepass.com">NoMorePass</a> icon and use the qrcode to login','nomorepass');
		}
		return $nmp_msg;
	}

	public static function getLogoSrc() {
		$options = get_option('nomorepass-custom-logo');
		$default_image = plugins_url('public/images/button.png', __FILE__);
		if ( !empty( $options ) ) {
			$image_attributes = wp_get_attachment_image_src( $options, array( 36, 36 ) );
			$src = $image_attributes[0];
		} else {
			$src = $default_image;
		}
		return $src;
	}

	public static function paintQr () {
		?>
		<div id="qrcodecont" style="position: fixed; max-width: 270px; padding: 10px; color: rgb(255, 255, 255); background: rgb(116, 174, 115); height: 380px; opacity: 1; z-index: 2147483647; top: 0px; bottom: 0px; left: 0px; right: 0px; margin: auto; display:none;">
		<div style="position: relative; text-align: left;">
		<img src="<?php echo plugins_url('public/images/logo-entrada-nomorepass.png', __FILE__)?>" alt="nomorepass" style="max-width: 200px;">
		<a id="nmp_close" href="javascript:void(1)" title="close">
		<img src="<?php echo plugins_url('public/images/login-close.png', __FILE__)?>" alt="nomorepass" style="position: absolute; top: 0px; right: 0px; width: 20px;">
		</a></div>
		<div id="qrcode" style="padding: 5px; background: white; display: block;"></div>
		<div style="margin-bottom: 5px; padding: 5px; text-align: center; border-bottom: 1px solid rgb(255, 255, 255);">
		<a href="https://nomorepass.com" style="text-decoration: none; color: rgb(255, 255, 255);">¿Necesitas ayuda?</a>
		</div>
		<div style="display: inline-block;">
		<a href="https://play.google.com/store/apps/details?id=com.biblioeteca.apps.NoMorePass">
		<img src="<?php echo plugins_url('public/images/login-banner-googlestore-en.png', __FILE__)?>" title="Play store" style="width: 45%; margin: 2%; float: left;">
		</a> <a href="https://itunes.apple.com/us/app/no-more-pass/id1199780162">
		<img src="<?php echo plugins_url('public/images/login-banner-appstore-en.png', __FILE__)?>" title="App Store" style="width: 45%; margin: 2%; float: left;"></a>
		</div>
		</div>
		<script>
		function hideQr() {
				NomorePass.stopped=true;
				document.querySelector("#qrcodecont").style.display='none';
		}
		document.getElementById('nmp_close').onclick = function(){
				hideQr();
		};
		</script>
		<?php
	}
}