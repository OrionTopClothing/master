<?php 

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Settings Page Instagram Tab
 * 
 * The code for the plugins settings page instagram tab
 * 
 * @package WooCommerce - Social Login
 * @since 1.6.4
 */

// Set option for default value
$woo_slg_inst_icon_text = (!empty($woo_slg_inst_icon_text)) ? $woo_slg_inst_icon_text : esc_html__( 'Sign in with Instagram', 'wooslg' ) ;
$woo_slg_inst_link_icon_text = (!empty($woo_slg_inst_link_icon_text)) ? $woo_slg_inst_link_icon_text : esc_html__( 'Link your account to Instagram', 'wooslg' ) ;

?>
<!-- beginning of the instagram settings meta box -->
<div id="woo-slg-instagram" class="post-box-container">
	<div class="metabox-holder">
		<div class="meta-box-sortables ui-sortable">
			<div id="instagram" class="postbox">
				<div class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'wooslg' ); ?>"><br /></div>
					
					<!-- instagram settings box title -->
					<h3 class="hndle">
						<span class="woo-slg-vertical-top"><?php esc_html_e( 'Instagram Settings', 'wooslg' ); ?></span>
					</h3>

					<div class="inside">
					
					<table class="form-table">
						<tbody>
							
							<?php
								// do action for add setting before instagram settings
								do_action( 'woo_slg_before_instagram_setting', $woo_slg_options );
								
							?>
							
							<tr valign="top">
								<th scope="row">
									<label><?php esc_html_e( 'Instagram Application:', 'wooslg' ); ?></label>
								</th>
								<td>
									<span class="description"><?php echo esc_html__( 'Before you can start using Instagram for the social login, you need to create a Instagram Application. You can get a step by step tutorial on how to create Instagram Application on our', 'wooslg' ) . ' <a target="_blank" href="https://docs.wpwebelite.com/social-network-integration/instagram/">' . esc_html__( 'Documentation', 'wooslg' ) . '</a>'; ?></span>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="woo_slg_enable_instagram"><?php esc_html_e( 'Enable Instagram:', 'wooslg' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="woo_slg_enable_instagram" name="woo_slg_enable_instagram" value="1" <?php echo ($woo_slg_enable_instagram=='yes') ? 'checked="checked"' : ''; ?>/>
									<label for="woo_slg_enable_instagram"><?php echo esc_html__( 'Check this box, if you want to enable Instagram social login registration.', 'wooslg' ); ?></label>
								</td>
							</tr>
							
							<tr valign="top">
								<th scope="row">
									<label for="woo_slg_inst_client_id"><?php esc_html_e( 'Instagram Client ID:', 'wooslg' ); ?></label>
								</th>
								<td>
									<input id="woo_slg_inst_client_id" type="text" class="regular-text" name="woo_slg_inst_client_id" value="<?php echo $woo_slg_model->woo_slg_escape_attr( $woo_slg_inst_client_id ); ?>" /></br>
									<span class="description"><?php echo esc_html__( 'Enter Instagram Client ID.', 'wooslg'); ?></span>
								</td>
							</tr>
							
							<tr valign="top">
								<th scope="row">
									<label for="woo_slg_inst_client_secret"><?php esc_html_e( 'Instagram Client Secret:', 'wooslg' ); ?></label>
								</th>
								<td>
									<input id="woo_slg_inst_client_secret" type="text" class="regular-text" name="woo_slg_inst_client_secret" value="<?php echo $woo_slg_model->woo_slg_escape_attr( $woo_slg_inst_client_secret ); ?>" /></br>
									<span class="description"><?php echo esc_html__( 'Enter Instagram Client Secret.', 'wooslg'); ?></span>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									<label><?php echo esc_html__( 'Instagram Callback URL:', 'wooslg' ); ?></label>
								</th>
								<td>
									<span class="description"><?php echo '<code>'.WOO_SLG_INST_REDIRECT_URL.'</code>'; ?></span>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<label for="woo_slg_enable_inst_avatar"><?php esc_html_e( 'Enable Instagram Avatar:', 'wooslg' ); ?></label>
								</th>
								<td>
									<input type="checkbox" id="woo_slg_enable_inst_avatar" name="woo_slg_enable_inst_avatar" value="1" <?php echo ($woo_slg_enable_inst_avatar=='yes') ? 'checked="checked"' : ''; ?>/>
									<label for="woo_slg_enable_inst_avatar"><?php echo esc_html__( 'Check this box, if you want to use Instagram profile pictures as avatars.', 'wooslg' ); ?></label>
								</td>
							</tr>
							
							<tr valign="top">
								<th scope="row">
									<label for="woo_slg_inst_icon_text"><?php esc_html_e( 'Custom Instagram Text:', 'wooslg' ); ?></label>
								</th>
								<td>
									<input id="woo_slg_inst_icon_text" type="text" class="regular-text woo_slg_social_btn_text" name="woo_slg_inst_icon_text" value="<?php echo $woo_slg_model->woo_slg_escape_attr( $woo_slg_inst_icon_text ); ?>" /></br>
									<span class="description"><?php echo esc_html__( 'If you want to use your own Instagram Text, Enter here.', 'wooslg' ); ?></span>
								</td>
							</tr>
							
							<tr valign="top">
								<th scope="row">
									<label for="woo_slg_inst_link_icon_text"><?php esc_html_e( 'Custom Instagram Link Text:', 'wooslg' ); ?></label>
								</th>
								<td>
									<input id="woo_slg_inst_link_icon_text" type="text" class="regular-text woo_slg_social_btn_text" name="woo_slg_inst_link_icon_text" value="<?php echo $woo_slg_model->woo_slg_escape_attr( $woo_slg_inst_link_icon_text ); ?>" /></br>
									<span class="description"><?php echo esc_html__( 'If you want to use your own Instagram Link Text, enter here.', 'wooslg' ); ?></span>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									<label for="woo_slg_inst_icon_url"><?php esc_html_e( 'Custom Instagram Icon:', 'wooslg' ); ?></label>
								</th>
								<td>
									<input id="woo_slg_inst_icon_url" type="text" class="woo_slg_social_btn_image regular-text" name="woo_slg_inst_icon_url" value="<?php echo $woo_slg_model->woo_slg_escape_attr( $woo_slg_inst_icon_url ); ?>" />
									<input type="button" class="woo-slg-upload-file-button button-secondary" value="<?php esc_html_e( 'Upload File', 'wooslg' );?>"/></br>
									<span class="description"><?php echo esc_html__( 'If you want to use your own Instagram Icon, upload one here.', 'wooslg' ); ?></span>
								</td>
							</tr>
							
							<tr valign="top">
								<th scope="row">
									<label for="woo_slg_inst_link_icon_url"><?php esc_html_e( 'Custom Instagram Link Icon:', 'wooslg' ); ?></label>
								</th>
								<td>
									<input id="woo_slg_inst_link_icon_url" type="text" class="woo_slg_social_btn_image regular-text" name="woo_slg_inst_link_icon_url" value="<?php echo $woo_slg_model->woo_slg_escape_attr( $woo_slg_inst_link_icon_url ); ?>" />
									<input type="button" class="woo-slg-upload-file-button button-secondary" value="<?php esc_html_e( 'Upload File', 'wooslg' );?>"/></br>
									<span class="description"><?php echo esc_html__( 'If you want to use your own Instagram Link Icon, upload one here.', 'wooslg' ); ?></span>
								</td>
							</tr>

							<!-- Instagram Settings End -->

							<!-- Page Settings End --><?php
							
							// do action for add setting after instagram settings
							do_action( 'woo_slg_after_instagram_setting', $woo_slg_options );
							
							?>
							<tr>
								<td colspan="2"><?php
									echo apply_filters ( 'woo_slg_settings_submit_button', '<input class="button-primary woo-slg-save-btn" type="submit" name="woo-slg-set-submit" value="'.esc_html__( 'Save Changes','wooslg' ).'" />' );?>
								</td>
							</tr>
						</tbody>
					</table>
				</div><!-- .inside -->
			</div><!-- #instagram -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #woo-slg-instagram -->