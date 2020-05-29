<?php

/**
 * Plugin Name: WooCommerce - Social Login
 * Plugin URI: https://wpwebelite.com/
 * Description: Allow your customers to login and checkout with social networks such as  Facebook, Twitter, Google, Yahoo, LinkedIn, Foursquare, Windows Live, VK.com, Instagram, Amazon and PayPal.
 * Version: 2.2.1
 * Author: WPWeb
 * Author URI: https://wpwebelite.com/
 * Text Domain: wooslg
 * Domain Path: languages
 * 
 * WC tested up to: 4.0.1
 * Tested up to: 5.4
 * 
 * @package WooCommerce - Social Login
 * @category Core
 * @author WPWeb
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Basic plugin definitions
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
global $wpdb;

if (!defined('WOO_SLG_VERSION')) {
    define('WOO_SLG_VERSION', '2.2.1'); //version of plugin
}
if (!defined('WOO_SLG_URL')) {
    define('WOO_SLG_URL', plugin_dir_url(__FILE__)); // plugin url
}
if (!defined('WOO_SLG_DIR')) {
    define('WOO_SLG_DIR', dirname(__FILE__)); // plugin dir
}
if (!defined('WOO_SLG_SOCIAL_DIR')) {
    define('WOO_SLG_SOCIAL_DIR', WOO_SLG_DIR . '/includes/social'); // social dir
}
if (!defined('WOO_SLG_SOCIAL_LIB_DIR')) {
    define('WOO_SLG_SOCIAL_LIB_DIR', WOO_SLG_DIR . '/includes/social/libraries'); // lib dir
}
if (!defined('WOO_SLG_IMG_URL')) {
    define('WOO_SLG_IMG_URL', esc_url(WOO_SLG_URL) . 'includes/images'); // image url
}
if (!defined('WOO_SLG_ADMIN')) {
    define('WOO_SLG_ADMIN', WOO_SLG_DIR . '/includes/admin'); // plugin admin dir
}
if (!defined('WOO_SLG_USER_PREFIX')) {
    define('WOO_SLG_USER_PREFIX', 'woo_user_'); // username prefix
}
if (!defined('WOO_SLG_USER_META_PREFIX')) {
    define('WOO_SLG_USER_META_PREFIX', 'wooslg_'); // username prefix
}
if (!defined('WOO_SLG_BASENAME')) {
    define('WOO_SLG_BASENAME', basename(WOO_SLG_DIR));
}
if (!defined('WOO_SLG_PLUGIN_KEY')) {
    define('WOO_SLG_PLUGIN_KEY', 'wooslg');
}
if (!defined('WOO_SLG_SOCIAL_BLOCK_DIR')) {
    define('WOO_SLG_SOCIAL_BLOCK_DIR', WOO_SLG_DIR . '/includes/blocks/'); // block dir
}

// Required Wpweb updater functions file
if (!function_exists('wpweb_updater_install')) {
    require_once( 'includes/wpweb-upd-functions.php' );
}

global $woo_slg_options;

/**
 * Activation Hook
 * 
 * Register plugin activation hook.
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
register_activation_hook(__FILE__, 'woo_slg_install');

/**
 * Plugin Setup (On Activation)
 * 
 * Does the initial setup,
 * stest default values for the plugin options.
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
function woo_slg_install() {

    global $wpdb, $woo_slg_options;

    //Get plugin version option
    $woo_slg_set_option = get_option('woo_slg_set_option');

    //get social order options
    $woo_social_order = get_option('woo_social_order');

    if (empty($woo_slg_set_option)) { //check plugin version option
        //get option for when plugin is activating first time
        woo_slg_default_settings();

        $woo_social_order = array('facebook', 'twitter', 'googleplus', 'linkedin', 'yahoo', 'foursquare', 'windowslive', 'vk','apple');

        update_option('woo_social_order', $woo_social_order);

        //update plugin version to option
        update_option('woo_slg_set_option', '1.0');
    }

    //get social order options
    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ($woo_slg_set_option == '1.0') {

        //Update default behaviour for new user's username
        update_option('woo_slg_base_reg_username', '');

        //update plugin version to option
        update_option('woo_slg_set_option', '1.1');
    }

    //get social order options
    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ($woo_slg_set_option == '1.1') {

        //Instagram api added in api array
        $inst_array = array('instagram');
        $woo_social_order = array_merge($woo_social_order, $inst_array);
        update_option('woo_social_order', $woo_social_order);

        //Instagram options
        $instagram_options = array(
            'woo_slg_enable_instagram' => '',
            'woo_slg_inst_client_id' => '',
            'woo_slg_inst_client_secret' => '',
            'woo_slg_inst_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/instagram.png',
            'woo_slg_enable_inst_avatar' => ''
        );

        foreach ($instagram_options as $key => $value) {
            update_option($key, $value);
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '1.2');
    }

    //get social order options
    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ($woo_slg_set_option == '1.2') {

        //Custom icon link options
        $link_options = array(
            'woo_slg_fb_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/facebook-link.png',
            'woo_slg_gp_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/googleplus-link.png',
            'woo_slg_li_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/linkedin-link.png',
            'woo_slg_tw_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/twitter-link.png',
            'woo_slg_yh_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/yahoo-link.png',
            'woo_slg_fs_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/foursquare-link.png',
            'woo_slg_wl_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/windowslive-link.png',
            'woo_slg_vk_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/vk-link.png',
            'woo_slg_inst_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/instagram-link.png',
            'woo_slg_display_link_thank_you' => 'yes',
        );

        foreach ($link_options as $key => $value) {
            update_option($key, $value);
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '1.3');
    }

    //get social order options
    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ($woo_slg_set_option == '1.3') {

        // Amazon and paypal api added in api array
        $authorize_array = array('amazon', 'paypal');
        $woo_social_order = array_merge($woo_social_order, $authorize_array);
        update_option('woo_social_order', $woo_social_order);

        // Amazon and paypal options
        $authorize_options = array(
            'woo_slg_enable_amazon' => '',
            'woo_slg_amazon_client_id' => '',
            'woo_slg_amazon_client_secret' => '',
            'woo_slg_amazon_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/amazon.png',
            'woo_slg_amazon_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/amazon-link.png',
            'woo_slg_enable_paypal' => '',
            'woo_slg_paypal_client_id' => '',
            'woo_slg_paypal_client_secret' => '',
            'woo_slg_paypal_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/paypal.png',
            'woo_slg_paypal_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/paypal-link.png',
            'woo_slg_paypal_environment' => 'sandbox',
        );

        foreach ($authorize_options as $key => $value) {
            update_option($key, $value);
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '1.4');
    }

    //get social order options
    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ($woo_slg_set_option == '1.4') {

        // Social Buttons options
        $Social_buttons_options = array(
            'woo_slg_fb_icon_text' => esc_html__('Sign in with Facebook', 'wooslg'),
            'woo_slg_fb_link_icon_text' => esc_html__('Link your account to Facebook', 'wooslg'),
            'woo_slg_gp_icon_text' => esc_html__('Sign in with Google', 'wooslg'),
            'woo_slg_gp_link_icon_text' => esc_html__('Link your account to Google', 'wooslg'),
            'woo_slg_li_icon_text' => esc_html__('Sign in with LinkedIn', 'wooslg'),
            'woo_slg_li_link_icon_text' => esc_html__('Link your account to LinkedIn', 'wooslg'),
            'woo_slg_tw_icon_text' => esc_html__('Sign in with Twitter', 'wooslg'),
            'woo_slg_tw_link_icon_text' => esc_html__('Link your account to Twitter', 'wooslg'),
            'woo_slg_yh_icon_text' => esc_html__('Sign in with Yahoo', 'wooslg'),
            'woo_slg_yh_link_icon_text' => esc_html__('Link your account to Yahoo', 'wooslg'),
            'woo_slg_fs_icon_text' => esc_html__('Sign in with Foursquare', 'wooslg'),
            'woo_slg_fs_link_icon_text' => esc_html__('Link your account to Foursquare', 'wooslg'),
            'woo_slg_wl_icon_text' => esc_html__('Sign in with Windows Live', 'wooslg'),
            'woo_slg_wl_link_icon_text' => esc_html__('Link your account to Windows Live', 'wooslg'),
            'woo_slg_vk_icon_text' => esc_html__('Sign in with VK.com', 'wooslg'),
            'woo_slg_vk_link_icon_text' => esc_html__('Link your account to VK.com', 'wooslg'),
            'woo_slg_inst_icon_text' => esc_html__('Sign in with Instagram', 'wooslg'),
            'woo_slg_inst_link_icon_text' => esc_html__('Link your account to Instagram', 'wooslg'),
            'woo_slg_amazon_icon_text' => esc_html__('Sign in with Amazon', 'wooslg'),
            'woo_slg_amazon_link_icon_text' => esc_html__('Link your account to Amazon', 'wooslg'),
            'woo_slg_paypal_icon_text' => esc_html__('Sign in with Paypal', 'wooslg'),
            'woo_slg_paypal_link_icon_text' => esc_html__('Link your account to Paypal', 'wooslg'),
            
        );

        foreach ($Social_buttons_options as $key => $value) {
            update_option($key, $value);
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '1.5');
    }

    //get social order options
    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ($woo_slg_set_option == '1.5') {

        $woo_slg_enable_login_page = get_option('woo_slg_enable_login_page');

        if (!empty($woo_slg_enable_login_page) && $woo_slg_enable_login_page == 'yes') {
            update_option('woo_slg_enable_wp_login_page', 'yes');
            update_option('woo_slg_enable_wp_register_page', 'yes');
        }
        //update plugin version to option
        update_option('woo_slg_set_option', '1.6');
    }


    //get social order options
    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ($woo_slg_set_option == '1.6') {

        // added peepso plugin support since 1.6.3
        $woo_slg_peepso_login_page = get_option('woo_slg_enable_peepso_login_page');
        $woo_slg_peepso_registration_page = get_option('woo_slg_enable_peepso_register_page');

        if (empty($woo_slg_peepso_login_page)) {
            update_option('woo_slg_enable_peepso_login_page', '');
        }
        if (empty($woo_slg_peepso_registration_page)) {
            update_option('woo_slg_enable_peepso_register_page', '');
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '1.7');
    }

    //get social order options
    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ($woo_slg_set_option == '1.7') {

        // added peepso plugin support since 1.6.3
        $woo_slg_peepso_login_avatar = get_option('woo_slg_allow_peepso_avatar');

        if (empty($woo_slg_peepso_login_avatar)) {
            update_option('woo_slg_allow_peepso_avatar', '');
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '1.8');
    }

    //get social order options
    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ($woo_slg_set_option == '1.8') {

        // added peepso plugin support since 1.6.3
        $woo_slg_peepso_login_cover = get_option('woo_slg_allow_peepso_cover');

        if (empty($woo_slg_peepso_login_cover)) {
            update_option('woo_slg_allow_peepso_cover', '');
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '1.9');
    }

    //get social options version
    $woo_slg_set_option = get_option('woo_slg_set_option');
    
    if ($woo_slg_set_option == '1.9') {

        $woo_slg_display_link_acc_detail = get_option('woo_slg_display_link_acc_detail', 'yes');

        if (!empty($woo_slg_display_link_acc_detail)) {

            update_option('woo_slg_display_link_acc_detail', 'yes');
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '1.9.1');
    }

    //get social order options
    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ($woo_slg_set_option == '1.9.1') {

        $woo_slg_peepso_avatar_each_time = get_option('woo_slg_peepso_avatar_each_time');
        $woo_slg_peepso_cover_each_time = get_option('woo_slg_peepso_cover_each_time');

        if (!empty($woo_slg_peepso_avatar_each_time)) {

            update_option('woo_slg_peepso_avatar_each_time', '');
        }

        if (!empty($woo_slg_peepso_cover_each_time)) {

            update_option('woo_slg_peepso_cover_each_time', '');
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '2.0');
    }

    //get social order options
    $woo_slg_set_option = get_option('woo_slg_set_option');
    if ( $woo_slg_set_option == '2.0' ) {

        $woo_slg_enable_email = get_option('woo_slg_enable_email');
        $woo_slg_login_email_heading = get_option('woo_slg_login_email_heading');
        $woo_slg_login_email_placeholder = get_option('woo_slg_login_email_placeholder');
        $woo_slg_login_btn_text = get_option('woo_slg_login_btn_text');
        $woo_slg_login_email_seprater_text = get_option('woo_slg_login_email_seprater_text');
        $woo_slg_login_email_position = get_option('woo_slg_login_email_position');

        if (empty($woo_slg_enable_email)) {

            update_option('woo_slg_enable_email', 'no');
        }
        if (empty($woo_slg_login_email_heading)) {

            update_option('woo_slg_login_email_heading', esc_html__('Sign in with e-mail', 'wooslg'));
        }
        if (empty($woo_slg_login_email_placeholder)) {

            update_option('woo_slg_login_email_placeholder', esc_html__('Enter your email address', 'wooslg'));
        }

        if (empty($woo_slg_login_btn_text)) {

            update_option('woo_slg_login_btn_text', esc_html__('Sign in', 'wooslg'));
        }

        if (empty($woo_slg_login_email_seprater_text)) {

            update_option('woo_slg_login_email_seprater_text', esc_html__('OR', 'wooslg'));
        }

        if (empty($woo_slg_login_email_position)) {

            update_option('woo_slg_login_email_position', 'top');
        }

        // Add order of email login
        if( !empty( $woo_social_order ) && !in_array( 'email', $woo_social_order ) ){
            $authorize_array = array('email');
            $woo_social_order = array_merge($woo_social_order, $authorize_array);
            update_option('woo_social_order', $woo_social_order);
        }


        //update plugin version to option
        update_option('woo_slg_set_option', '2.1');
    }

    $woo_slg_set_option = get_option('woo_slg_set_option');
    if ( $woo_slg_set_option == '2.1' ) {

        if (class_exists('Woocommerce')) {

            // Get woocommerce options
            $woo_privacy_policy_page = get_option('wp_page_for_privacy_policy');
            $woo_privacy_policy_text = get_option('woocommerce_registration_privacy_policy_text');
            
            $gdpr_setting = get_option('woo_slg_enable_gdpr');
            $gdpr_privacy_page = get_option('woo_slg_gdpr_privacy_page');
            $gdpr_privacy_text = get_option('woo_slg_gdpr_privacy_policy');

            // Update GDPR options
            if( empty( $gdpr_setting ) ){
                update_option('woo_slg_enable_gdpr', 'yes');
            }

            if( empty( $gdpr_privacy_page ) ) {
                update_option('woo_slg_gdpr_privacy_page', $woo_privacy_policy_page);
            }
            
            if( empty( $gdpr_privacy_text ) ) {            
                update_option('woo_slg_gdpr_privacy_policy', $woo_privacy_policy_text);
            }
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '2.2');
    }

    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ( $woo_slg_set_option == '2.2' ) {


        // Set default email verification option
        $woo_slg_enable_email_varification = get_option('woo_slg_enable_email_varification');

        $email_varification_subject = get_option('woo_slg_mail_subject');
        $email_varification_content = get_option('woo_slg_mail_content');


        // Update email confirmation option
        if( empty( $woo_slg_enable_email_varification ) ){
            update_option('woo_slg_enable_email_varification', '');    
        }

        // Update email confirmation subject
        if( empty( $email_varification_subject ) ){
            update_option('woo_slg_mail_subject', esc_html__('Verification of your account', 'wooslg') );    
        }

        // Update email confirmation content
        if( empty( $email_varification_content ) ){

            $email_content = esc_html__('Please click {verify_link} to verify your email address and complete the registration process.', 'wooslg');
            update_option('woo_slg_mail_content', $email_content );    
        }


        // code to set new option for default role
        $role = get_option( 'default_role' ); // get wordpress default role
        
        $social_role = get_option( 'woo_slg_default_role' );
        
        if( empty( $social_role ) ) {

            // Update default role of user
            update_option( 'woo_slg_default_role', $role);
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '2.3');
    }

    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ( $woo_slg_set_option == '2.3' ) {


        // Get woocommerce options
        $woo_slg_public_js_unique_version = get_option('woo_slg_public_js_unique_version');

        if( empty( $woo_slg_public_js_unique_version ) ){

            // Update option to use timestamp for public js instead of version
            update_option('woo_slg_public_js_unique_version', '');
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '2.4');
    }

    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ( $woo_slg_set_option == '2.4' ) {


        //get social order options
        $woo_social_order = get_option('woo_social_order');

        $woo_social_order = array_unique($woo_social_order);
        $woo_social_order = array_values($woo_social_order);

        if( !empty( $woo_social_order) && !in_array('line', $woo_social_order) ) {
            $woo_social_order[] = 'line';
            update_option('woo_social_order', $woo_social_order);
        }

        //Line options
        $line_options = array(
            'woo_slg_enable_line' => '',
            'woo_slg_line_client_id' => '',
            'woo_slg_line_client_secret' => '',
            'woo_slg_line_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/line.png',
            'woo_slg_line_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/line-link.png',
            'woo_slg_enable_line_avatar' => '',
            'woo_slg_line_icon_text' => esc_html__('Sign in with Line', 'wooslg'),
            'woo_slg_line_link_icon_text' => esc_html__('Link your account to Line', 'wooslg'),
        );

        foreach ($line_options as $key => $value) {
            update_option($key, $value);
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '2.5');
    }

    // Link your account on peepso
    $woo_slg_set_option = get_option('woo_slg_set_option');

    if ( $woo_slg_set_option == '2.5' ) {
        $woo_slg_display_link_peepso_acc_detail = get_option('woo_slg_display_link_peepso_acc_detail');

        if (empty($woo_slg_display_link_peepso_acc_detail)) {

            update_option('woo_slg_display_link_peepso_acc_detail', '');
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '2.6');
    }

    $woo_slg_set_option = get_option('woo_slg_set_option');
    if ( $woo_slg_set_option == '2.6' ) {

        // Update email OTP confirmation option with verification type
        $woo_slg_enable_email_otp_varification = get_option('woo_slg_enable_email_otp_varification');

        $woo_slg_mail_otp_subject = get_option('woo_slg_mail_otp_subject');
        $woo_slg_mail_otp_content = get_option('woo_slg_mail_otp_content');


        // Update OTP email confirmation option
        if( empty( $woo_slg_enable_email_otp_varification ) ){
            update_option('woo_slg_enable_email_otp_varification', '');    
        }

        // Update OTP email confirmation subject
        if( empty( $woo_slg_mail_otp_subject ) ){
            update_option('woo_slg_mail_otp_subject', esc_html__('{otp} is your OTP to login to your {site_title} Account', 'wooslg') );    
        }

        // Update OTP email confirmation content
        if( empty( $woo_slg_mail_otp_content ) ){

            $email_otp_content = sprintf( __('Please use OTP %s{otp}%s to verify your account on {site_title} for Sign in.', 'wooslg'),'<strong>','</strong>');
            update_option('woo_slg_mail_otp_content', $email_otp_content );    
        }

        update_option('woo_slg_set_option', '2.7');
    }


    $woo_slg_set_option = get_option('woo_slg_set_option');
    if ( $woo_slg_set_option == '2.7' ) {

        $apple_array = array('apple');
        $woo_social_order = array_merge($woo_social_order, $apple_array);
        update_option('woo_social_order', $woo_social_order);

        $woo_slg_enable_apple = get_option('woo_slg_enable_apple');
        $woo_slg_apple_client_id = get_option('woo_slg_apple_client_id');

        // check apple is enable or not
        if( empty( $woo_slg_enable_apple ) ){
            update_option('woo_slg_enable_apple', '');    
        }

        // check apple client id is available or not
        if( empty( $woo_slg_apple_client_id ) ){
            update_option('woo_slg_apple_client_id', '');    
        }

        $Social_buttons_options = array(
            'woo_slg_apple_icon_text' => esc_html__('Sign in with Apple', 'wooslg'),
            'woo_slg_apple_link_icon_text' => esc_html__('Link your account to Apple', 'wooslg'),
            
        );

        foreach ($Social_buttons_options as $key => $value) {
            update_option($key, $value);
        }

        $link_options = array(
            'woo_slg_apple_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/apple.png',
            'woo_slg_apple_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/apple-link.png',
        );

        foreach ($link_options as $key => $value) {
            update_option($key, $value);
        }

        //update plugin version to option
        update_option('woo_slg_set_option', '2.8');
    }

    $woo_slg_set_option = get_option('woo_slg_set_option');
    if ( $woo_slg_set_option == '2.8' ) {
        // next update
    }

    
}

/**
 * Deafult Options
 * 
 * Default social login options
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
function woo_slg_default_settings() {

    $options = array(
        'woo_slg_login_heading' => esc_html__('Prefer to Login with Social Media', 'wooslg'),
        'woo_slg_email_notification_type' => 'wordpress',
        'woo_slg_enable_notification' => 'yes',
        'woo_slg_send_new_account_email_to_admin' => 'yes',
        'woo_slg_redirect_url' => '',
        'woo_slg_enable_login_page' => '',
        'woo_slg_enable_wp_login_page' => '',
        'woo_slg_enable_buddypress_login_page' => '',
        'woo_slg_enable_buddypress_register_page' => '',
        'woo_slg_enable_bbpress_login_page' => '',
        'woo_slg_enable_bbpress_register_page' => '',
        'woo_slg_enable_peepso_login_page' => '',
        'woo_slg_allow_peepso_avatar' => '',
        'woo_slg_allow_peepso_cover' => '',
        'woo_slg_enable_peepso_register_page' => '',
        'woo_slg_enable_woo_register_page' => '',
        'woo_slg_enable_expand_collapse' => 'collapse',
        'woo_slg_enable_email' => 'no',
        'woo_slg_login_email_heading' => esc_html__('Sign in with e-mail', 'wooslg'),
        'woo_slg_login_email_placeholder' => esc_html__('Enter your email address', 'wooslg'),
        'woo_slg_login_btn_text' => esc_html__('Sign in', 'wooslg'),
        'woo_slg_login_email_position' => 'top',
        'woo_slg_login_email_seprater_text' => esc_html__('OR', 'wooslg'),
        'woo_slg_enable_facebook' => '',
        'woo_slg_fb_app_id' => '',
        'woo_slg_fb_app_secret' => '',
        'woo_slg_fb_language' => 'en_US',
        'woo_slg_fb_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/facebook.png',
        'woo_slg_fb_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/facebook-link.png',
        'woo_slg_enable_fb_avatar' => '',
        'woo_slg_enable_googleplus' => '',
        'woo_slg_gp_client_id' => '',
        'woo_slg_gp_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/googleplus.png',
        'woo_slg_gp_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/googleplus-link.png',
        'woo_slg_enable_gp_avatar' => '',
        'woo_slg_enable_linkedin' => '',
        'woo_slg_li_app_id' => '',
        'woo_slg_li_app_secret' => '',
        'woo_slg_li_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/linkedin.png',
        'woo_slg_li_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/linkedin-link.png',
        'woo_slg_enable_li_avatar' => '',
        'woo_slg_enable_twitter' => '',
        'woo_slg_tw_consumer_key' => '',
        'woo_slg_tw_consumer_secret' => '',
        'woo_slg_tw_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/twitter.png',
        'woo_slg_tw_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/twitter-link.png',
        'woo_slg_enable_tw_avatar' => '',
        'woo_slg_enable_yahoo' => '',
        'woo_slg_yh_consumer_key' => '',
        'woo_slg_yh_consumer_secret' => '',
        'woo_slg_yh_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/yahoo.png',
        'woo_slg_yh_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/yahoo-link.png',
        'woo_slg_enable_yh_avatar' => '',
        'woo_slg_enable_foursquare' => '',
        'woo_slg_fs_client_id' => '',
        'woo_slg_fs_client_secret' => '',
        'woo_slg_fs_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/foursquare.png',
        'woo_slg_fs_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/foursquare-link.png',
        'woo_slg_enable_fs_avatar' => '',
        'woo_slg_enable_windowslive' => '',
        'woo_slg_wl_client_id' => '',
        'woo_slg_wl_client_secret' => '',
        'woo_slg_wl_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/windowslive.png',
        'woo_slg_wl_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/windowslive-link.png',
        'woo_slg_enable_vk' => '',
        'woo_slg_vk_app_id' => '',
        'woo_slg_vk_app_secret' => '',
        'woo_slg_vk_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/vk.png',
        'woo_slg_vk_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/vk-link.png',
        'woo_slg_enable_vk_avatar' => '',
        'woo_slg_display_link_thank_you' => 'yes',
        'woo_slg_display_link_acc_detail' => 'yes',
        'woo_slg_display_link_peepso_acc_detail' => '',
        'woo_slg_enable_instagram' => '',
        'woo_slg_inst_client_id' => '',
        'woo_slg_inst_client_secret' => '',
        'woo_slg_inst_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/instagram.png',
        'woo_slg_enable_inst_avatar' => '',
        'woo_slg_enable_amazon' => '',
        'woo_slg_amazon_client_id' => '',
        'woo_slg_amazon_client_secret' => '',
        'woo_slg_amazon_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/amazon.png',
        'woo_slg_amazon_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/amazon-link.png',
        'woo_slg_enable_paypal' => '',
        'woo_slg_paypal_client_id' => '',
        'woo_slg_paypal_client_secret' => '',
        'woo_slg_paypal_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/paypal.png',
        'woo_slg_paypal_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/paypal-link.png',
        'woo_slg_paypal_environment' => 'sandbox',
        'woo_slg_enable_line' => '',
        'woo_slg_line_client_id' => '',
        'woo_slg_line_client_secret' => '',
        'woo_slg_line_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/line.png',
        'woo_slg_line_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/line-link.png',
        'woo_slg_enable_line_avatar' => '',
        'woo_slg_apple_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/apple.png',
        'woo_slg_apple_link_icon_url' => esc_url(WOO_SLG_IMG_URL) . '/apple-link.png',
        'woo_slg_line_icon_text' => esc_html__('Sign in with Line', 'wooslg'),
        'woo_slg_line_link_icon_text' => esc_html__('Link your account to Line', 'wooslg'),
        'woo_slg_social_btn_type' => '0',
        'woo_slg_fb_icon_text' => esc_html__('Sign in with Facebook', 'wooslg'),
        'woo_slg_fb_link_icon_text' => esc_html__('Link your account to Facebook', 'wooslg'),
        'woo_slg_gp_icon_text' => esc_html__('Sign in with Google', 'wooslg'),
        'woo_slg_gp_link_icon_text' => esc_html__('Link your account to Google', 'wooslg'),
        'woo_slg_li_icon_text' => esc_html__('Sign in with LinkedIn', 'wooslg'),
        'woo_slg_li_link_icon_text' => esc_html__('Link your account to LinkedIn', 'wooslg'),
        'woo_slg_tw_icon_text' => esc_html__('Sign in with Twitter', 'wooslg'),
        'woo_slg_tw_link_icon_text' => esc_html__('Link your account to Twitter', 'wooslg'),
        'woo_slg_yh_icon_text' => esc_html__('Sign in with Yahoo', 'wooslg'),
        'woo_slg_yh_link_icon_text' => esc_html__('Link your account to Yahoo', 'wooslg'),
        'woo_slg_fs_icon_text' => esc_html__('Sign in with Foursquare', 'wooslg'),
        'woo_slg_fs_link_icon_text' => esc_html__('Link your account to Foursquare', 'wooslg'),
        'woo_slg_wl_icon_text' => esc_html__('Sign in with Windows Live', 'wooslg'),
        'woo_slg_wl_link_icon_text' => esc_html__('Link your account to Windows Live', 'wooslg'),
        'woo_slg_vk_icon_text' => esc_html__('Sign in with VK.com', 'wooslg'),
        'woo_slg_vk_link_icon_text' => esc_html__('Link your account to VK.com', 'wooslg'),
        'woo_slg_inst_icon_text' => esc_html__('Sign in with Instagram', 'wooslg'),
        'woo_slg_inst_link_icon_text' => esc_html__('Link your account to Instagram', 'wooslg'),
        'woo_slg_amazon_icon_text' => esc_html__('Sign in with Amazon', 'wooslg'),
        'woo_slg_amazon_link_icon_text' => esc_html__('Link your account to Amazon', 'wooslg'),
        'woo_slg_paypal_icon_text' => esc_html__('Sign in with Paypal', 'wooslg'),
        'woo_slg_paypal_link_icon_text' => esc_html__('Link your account to Paypal', 'wooslg'),
        'woo_slg_enable_apple' => '',
        'woo_slg_apple_client_id' => '',
        'woo_slg_apple_icon_text' => esc_html__('Sign in with Apple', 'wooslg'),
        'woo_slg_apple_link_icon_text' => esc_html__('Link your account to Apple', 'wooslg'),
        'woo_slg_dismissed_social_login_settings_moved_notice' => true,
        'woo_slg_peepso_avatar_each_time' => '',
        'woo_slg_peepso_cover_each_time' => '',
        'woo_slg_enable_email_varification' => '',
        'woo_slg_mail_subject' => esc_html__('Verification of your account', 'wooslg'),
        'woo_slg_mail_content' => esc_html__('Please click {verify_link} to verify your email address and complete the registration process.', 'wooslg'),
        'woo_slg_enable_email_otp_varification' => '',
        'woo_slg_mail_otp_subject' => esc_html__('{otp} is your OTP to login to your {site_title} Account', 'wooslg'),
        'woo_slg_mail_otp_content' => sprintf( __('Please use OTP %s{otp}%s to verify your account on {site-title} for Sign in.', 'wooslg'),'<strong>','</strong>'),
        'woo_slg_default_role' => 'subscriber',
        'woo_slg_public_js_unique_version' => '',
    );

foreach ($options as $key => $value) {
    update_option($key, $value);
}
}

/**
 * Load Text Domain
 * 
 * This gets the plugin ready for translation.
 * 
 * @package WooCommerce - Social Login
 * @since 1.2.6
 */
function woo_slg_load_text_domain() {

    // Set filter for plugin's languages directory
    $woo_slg_lang_dir = dirname(plugin_basename(__FILE__)) . '/languages/';
    $woo_slg_lang_dir = apply_filters('woo_slg_languages_directory', $woo_slg_lang_dir);

    // Traditional WordPress plugin locale filter
    $locale = apply_filters('plugin_locale', get_locale(), 'wooslg');
    $mofile = sprintf('%1$s-%2$s.mo', 'wooslg', $locale);

    // Setup paths to current locale file
    $mofile_local = $woo_slg_lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/' . WOO_SLG_BASENAME . '/' . $mofile;

    if (file_exists($mofile_global)) { // Look in global /wp-content/languages/woo-social-login folder
        load_textdomain('wooslg', $mofile_global);
    } elseif (file_exists($mofile_local)) { // Look in local /wp-content/plugins/woo-social-login/languages/ folder
        load_textdomain('wooslg', $mofile_local);
    } else { // Load the default language files
        load_plugin_textdomain('wooslg', false, $woo_slg_lang_dir);
    }
}

/**
 * Add plugin action links
 * 
 * Adds a Settings, Support and Docs link to the plugin list.
 * 
 * @package WooCommerce - Social Login
 * @since 1.2.2
 */
function woo_slg_add_plugin_links($links) {
    $plugin_links = array(
        '<a href="admin.php?page=woo-social-settings">' . esc_html__('Settings', 'wooslg') . '</a>',
        '<a href="https://support.wpwebelite.com/">' . esc_html__('Support', 'wooslg') . '</a>',
        '<a href="https://docs.wpwebelite.com/woocommerce-social-login/">' . esc_html__('Docs', 'wooslg') . '</a>'
    );

    return array_merge($plugin_links, $links);
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'woo_slg_add_plugin_links');

// Add action to read plugin default option to Make it WPML Compatible
add_action('plugins_loaded', 'woo_slg_read_default_options', 999);

/**
 * Re read all options to make it wpml compatible
 *
 * @package WooCommerce - Social Login
 * @since 1.3.0
 */
function woo_slg_read_default_options() {

    // Re-read settings because read plugin default option to Make it WPML Compatible
    global $woo_slg_options;
    $woo_slg_options['woo_slg_login_heading'] = get_option('woo_slg_login_heading');
}

//add action to load plugin
add_action('plugins_loaded', 'woo_slg_plugin_loaded');

/**
 * Load Plugin
 * 
 * Handles to load plugin after
 * dependent plugin is loaded
 * successfully
 * 
 * @package WooCommerce - Social Login
 * @since 1.0.0
 */
function woo_slg_plugin_loaded() {

    // load first text domain.
    woo_slg_load_text_domain();

    /**
     * Deactivation Hook
     * 
     * Register plugin deactivation hook.
     * 
     * @package WooCommerce - Social Login
     * @since 1.0.0
     */
    register_deactivation_hook(__FILE__, 'woo_slg_uninstall');

    /**
     * Plugin Setup (On Deactivation)
     * 
     * Delete  plugin options.
     * 
     * @package WooCommerce - Social Login
     * @since 1.0.0
     */
    function woo_slg_uninstall() {

        global $wpdb;

        // Getting delete option
        $woo_slg_delete_options = get_option('woo_slg_delete_options');

        if ($woo_slg_delete_options == 'yes') {

            $options = array(
                'woo_slg_set_option', 'woo_social_order', 'woo_slg_login_heading', 'woo_slg_enable_notification',
                'woo_slg_redirect_url', 'woo_slg_enable_login_page', 'woo_slg_enable_on_checkout_page', 'woo_slg_enable_wp_login_page', 'woo_slg_enable_wp_register_page', 'woo_slg_enable_buddypress_login_page', 'woo_slg_enable_buddypress_register_page',
                'woo_slg_enable_bbpress_login_page', 'woo_slg_enable_bbpress_register_page', 'woo_slg_enable_woo_register_page',
                'woo_slg_enable_peepso_login_page', 'woo_slg_enable_peepso_register_page', 'woo_slg_allow_peepso_avatar', 'woo_slg_allow_peepso_cover',
                'woo_slg_enable_facebook', 'woo_slg_fb_app_id',
                'woo_slg_fb_app_secret', 'woo_slg_fb_language', 'woo_slg_fb_icon_url', 'woo_slg_fb_link_icon_url', 'woo_slg_enable_fb_avatar',
                'woo_slg_enable_googleplus', 'woo_slg_gp_client_id', 'woo_slg_gp_icon_url',
                'woo_slg_enable_gp_avatar', 'woo_slg_enable_linkedin', 'woo_slg_li_app_id', 'woo_slg_li_app_secret',
                'woo_slg_li_icon_url', 'woo_slg_enable_li_avatar', 'woo_slg_enable_twitter', 'woo_slg_tw_consumer_key',
                'woo_slg_tw_consumer_secret', 'woo_slg_tw_icon_url', 'woo_slg_enable_tw_avatar', 'woo_slg_enable_yahoo',
                'woo_slg_yh_consumer_key', 'woo_slg_yh_consumer_secret', 'woo_slg_yh_icon_url',
                'woo_slg_enable_yh_avatar', 'woo_slg_enable_foursquare', 'woo_slg_fs_client_id', 'woo_slg_fs_client_secret',
                'woo_slg_fs_icon_url', 'woo_slg_enable_fs_avatar', 'woo_slg_enable_windowslive', 'woo_slg_wl_client_id',
                'woo_slg_wl_client_secret', 'woo_slg_wl_icon_url', 'woo_slg_enable_vk', 'woo_slg_vk_app_id',
                'woo_slg_vk_app_secret', 'woo_slg_vk_icon_url', 'woo_slg_enable_vk_avatar', 'woo_slg_enable_instagram',
                'woo_slg_inst_client_id', 'woo_slg_inst_client_secret', 'woo_slg_inst_icon_url', 'woo_slg_enable_inst_avatar',
                'woo_slg_delete_options', 'woo_slg_enable_expand_collapse', 'woo_slg_fb_link_icon_url', 'woo_slg_gp_link_icon_url',
                'woo_slg_li_link_icon_url', 'woo_slg_tw_link_icon_url', 'woo_slg_yh_link_icon_url', 'woo_slg_fs_link_icon_url',
                'woo_slg_wl_link_icon_url', 'woo_slg_vk_link_icon_url', 'woo_slg_inst_link_icon_url', 'woo_slg_display_link_thank_you', 'woo_slg_display_link_acc_detail','woo_slg_display_link_peepso_acc_detail',

                'woo_slg_enable_amazon', 'woo_slg_amazon_client_id', 'woo_slg_amazon_client_secret', 'woo_slg_amazon_icon_url',
                'woo_slg_amazon_link_icon_url', 'woo_slg_enable_paypal', 'woo_slg_paypal_client_id', 'woo_slg_paypal_client_secret', 'woo_slg_paypal_icon_url',
                'woo_slg_paypal_link_icon_url', 'woo_slg_paypal_environment','woo_slg_enable_email_varification','woo_slg_mail_subject','woo_slg_mail_content','woo_slg_enable_email_otp_varification','woo_slg_mail_otp_subject','woo_slg_mail_otp_content','woo_slg_default_role','woo_slg_public_js_unique_version',
                'woo_slg_apple_link_icon_url','woo_slg_apple_client_id','woo_slg_enable_apple','woo_slg_apple_icon_url'
            );

            //Get v1.4.1 options
            $options_v141 = array(
                'woo_slg_fb_icon_text', 'woo_slg_fb_link_icon_text', 'woo_slg_gp_icon_text', 'woo_slg_gp_link_icon_text', 'woo_slg_li_icon_text',
                'woo_slg_li_link_icon_text', 'woo_slg_tw_icon_text', 'woo_slg_tw_link_icon_text', 'woo_slg_yh_icon_text', 'woo_slg_yh_link_icon_text',
                'woo_slg_fs_icon_text', 'woo_slg_fs_link_icon_text', 'woo_slg_wl_icon_text', 'woo_slg_wl_link_icon_text', 'woo_slg_vk_icon_text',
                'woo_slg_vk_link_icon_text', 'woo_slg_inst_icon_text', 'woo_slg_inst_link_icon_text', 'woo_slg_amazon_icon_text',
                'woo_slg_amazon_link_icon_text', 'woo_slg_paypal_icon_text', 'woo_slg_paypal_link_icon_text', 'woo_slg_social_btn_type', 'woo_slg_social_btn_position', 'woo_slg_social_btn_hooks',
                'woo_slg_enable_line', 'woo_slg_line_client_id', 'woo_slg_line_client_secret', 'woo_slg_line_icon_url', 'woo_slg_enable_line_avatar', 'woo_slg_line_icon_text', 'woo_slg_line_link_icon_text','woo_slg_apple_icon_text','woo_slg_apple_link_icon_text'
            );

            //Merge version 1.4.1 options key to old
            $options = array_merge($options, $options_v141);

            //delete all options
            foreach ($options as $key) {
                delete_option($key);
            }
        }
    }

    /**
     * Start Session
     * 
     * @package WooCommerce - Social Login
     * @since 1.0.0
     */
    // function woo_slg_start_session() {
    //     global $pagenow;
        
    //     if( $pagenow != 'site-health.php' ) { // added condition to fix wordpress site health rest api error
    //         if (!session_id()) {
    //             session_start();
    //         }
    //     }
    // } // commneted the code because no more use of this function bacased removed the session use from plugin

    //add action init for starting a session
    //add_action('init', 'woo_slg_start_session');

    /**
     * Includes Files
     * 
     * Includes some required files for plugin
     * 
     * @package WooCommerce - Social Login
     * @since 1.0.0
     */
    function woo_slg_global_settings() {

        global $woo_slg_options;

        $woo_slg_options['woo_slg_login_heading'] = get_option('woo_slg_login_heading');
        $woo_slg_options['woo_slg_enable_notification'] = get_option('woo_slg_enable_notification');
        $woo_slg_options['woo_slg_email_notification_type'] = get_option('woo_slg_email_notification_type');
        $woo_slg_options['woo_slg_send_new_account_email_to_admin'] = get_option('woo_slg_send_new_account_email_to_admin');
        $woo_slg_options['woo_slg_redirect_url'] = get_option('woo_slg_redirect_url');
        $woo_slg_options['woo_slg_enable_login_page'] = get_option('woo_slg_enable_login_page');
        $woo_slg_options['woo_slg_enable_wp_login_page'] = get_option('woo_slg_enable_wp_login_page');
        $woo_slg_options['woo_slg_enable_on_checkout_page'] = get_option('woo_slg_enable_on_checkout_page');
        $woo_slg_options['woo_slg_enable_wp_register_page'] = get_option('woo_slg_enable_wp_register_page');
        $woo_slg_options['woo_slg_enable_buddypress_login_page'] = get_option('woo_slg_enable_buddypress_login_page');
        $woo_slg_options['woo_slg_enable_buddypress_register_page'] = get_option('woo_slg_enable_buddypress_register_page');
        $woo_slg_options['woo_slg_enable_bbpress_login_page'] = get_option('woo_slg_enable_bbpress_login_page');
        $woo_slg_options['woo_slg_enable_bbpress_register_page'] = get_option('woo_slg_enable_bbpress_register_page');
        $woo_slg_options['woo_slg_enable_woo_register_page'] = get_option('woo_slg_enable_woo_register_page');
        $woo_slg_options['woo_slg_enable_expand_collapse'] = get_option('woo_slg_enable_expand_collapse');
        $woo_slg_options['woo_slg_enable_email'] = get_option('woo_slg_enable_email');
        $woo_slg_options['woo_slg_login_email_heading'] = get_option('woo_slg_login_email_heading');
        $woo_slg_options['woo_slg_login_email_placeholder'] = get_option('woo_slg_login_email_placeholder');
        $woo_slg_options['woo_slg_login_btn_text'] = get_option('woo_slg_login_btn_text');
        $woo_slg_options['woo_slg_login_email_seprater_text'] = get_option('woo_slg_login_email_seprater_text');
        $woo_slg_options['woo_slg_login_email_position'] = get_option('woo_slg_login_email_position');
        $woo_slg_options['woo_slg_enable_gdpr'] = get_option('woo_slg_enable_gdpr');
        $woo_slg_options['woo_slg_gdpr_privacy_page'] = get_option('woo_slg_gdpr_privacy_page');
        $woo_slg_options['woo_slg_gdpr_privacy_policy'] = get_option('woo_slg_gdpr_privacy_policy');

        $woo_slg_options['woo_slg_enable_facebook'] = get_option('woo_slg_enable_facebook');
        $woo_slg_options['woo_slg_fb_app_id'] = get_option('woo_slg_fb_app_id');
        $woo_slg_options['woo_slg_fb_app_secret'] = get_option('woo_slg_fb_app_secret');
        $woo_slg_options['woo_slg_fb_language'] = get_option('woo_slg_fb_language');
        $woo_slg_options['woo_slg_fb_icon_url'] = get_option('woo_slg_fb_icon_url');
        $woo_slg_options['woo_slg_fb_link_icon_url'] = get_option('woo_slg_fb_link_icon_url');
        $woo_slg_options['woo_slg_enable_fb_avatar'] = get_option('woo_slg_enable_fb_avatar');
        $woo_slg_options['woo_slg_enable_googleplus'] = get_option('woo_slg_enable_googleplus');
        $woo_slg_options['woo_slg_gp_client_id'] = get_option('woo_slg_gp_client_id');
        $woo_slg_options['woo_slg_gp_icon_url'] = get_option('woo_slg_gp_icon_url');
        $woo_slg_options['woo_slg_gp_link_icon_url'] = get_option('woo_slg_gp_link_icon_url');
        $woo_slg_options['woo_slg_enable_gp_avatar'] = get_option('woo_slg_enable_gp_avatar');
        $woo_slg_options['woo_slg_enable_linkedin'] = get_option('woo_slg_enable_linkedin');
        $woo_slg_options['woo_slg_li_app_id'] = get_option('woo_slg_li_app_id');
        $woo_slg_options['woo_slg_li_app_secret'] = get_option('woo_slg_li_app_secret');
        $woo_slg_options['woo_slg_li_icon_url'] = get_option('woo_slg_li_icon_url');
        $woo_slg_options['woo_slg_li_link_icon_url'] = get_option('woo_slg_li_link_icon_url');
        $woo_slg_options['woo_slg_enable_li_avatar'] = get_option('woo_slg_enable_li_avatar');
        $woo_slg_options['woo_slg_enable_twitter'] = get_option('woo_slg_enable_twitter');
        $woo_slg_options['woo_slg_tw_consumer_key'] = get_option('woo_slg_tw_consumer_key');
        $woo_slg_options['woo_slg_tw_consumer_secret'] = get_option('woo_slg_tw_consumer_secret');
        $woo_slg_options['woo_slg_tw_icon_url'] = get_option('woo_slg_tw_icon_url');
        $woo_slg_options['woo_slg_tw_link_icon_url'] = get_option('woo_slg_tw_link_icon_url');
        $woo_slg_options['woo_slg_enable_tw_avatar'] = get_option('woo_slg_enable_tw_avatar');
        $woo_slg_options['woo_slg_enable_yahoo'] = get_option('woo_slg_enable_yahoo');
        $woo_slg_options['woo_slg_yh_consumer_key'] = get_option('woo_slg_yh_consumer_key');
        $woo_slg_options['woo_slg_yh_consumer_secret'] = get_option('woo_slg_yh_consumer_secret');
        $woo_slg_options['woo_slg_yh_icon_url'] = get_option('woo_slg_yh_icon_url');
        $woo_slg_options['woo_slg_yh_link_icon_url'] = get_option('woo_slg_yh_link_icon_url');
        $woo_slg_options['woo_slg_enable_yh_avatar'] = get_option('woo_slg_enable_yh_avatar');
        $woo_slg_options['woo_slg_enable_foursquare'] = get_option('woo_slg_enable_foursquare');
        $woo_slg_options['woo_slg_fs_client_id'] = get_option('woo_slg_fs_client_id');
        $woo_slg_options['woo_slg_fs_client_secret'] = get_option('woo_slg_fs_client_secret');
        $woo_slg_options['woo_slg_fs_icon_url'] = get_option('woo_slg_fs_icon_url');
        $woo_slg_options['woo_slg_fs_link_icon_url'] = get_option('woo_slg_fs_link_icon_url');
        $woo_slg_options['woo_slg_enable_fs_avatar'] = get_option('woo_slg_enable_fs_avatar');
        $woo_slg_options['woo_slg_enable_windowslive'] = get_option('woo_slg_enable_windowslive');
        $woo_slg_options['woo_slg_wl_client_id'] = get_option('woo_slg_wl_client_id');
        $woo_slg_options['woo_slg_wl_client_secret'] = get_option('woo_slg_wl_client_secret');
        $woo_slg_options['woo_slg_wl_icon_url'] = get_option('woo_slg_wl_icon_url');
        $woo_slg_options['woo_slg_wl_link_icon_url'] = get_option('woo_slg_wl_link_icon_url');
        $woo_slg_options['woo_slg_enable_vk'] = get_option('woo_slg_enable_vk');
        $woo_slg_options['woo_slg_vk_app_id'] = get_option('woo_slg_vk_app_id');
        $woo_slg_options['woo_slg_vk_app_secret'] = get_option('woo_slg_vk_app_secret');
        $woo_slg_options['woo_slg_vk_icon_url'] = get_option('woo_slg_vk_icon_url');
        $woo_slg_options['woo_slg_vk_link_icon_url'] = get_option('woo_slg_vk_link_icon_url');
        $woo_slg_options['woo_slg_enable_vk_avatar'] = get_option('woo_slg_enable_vk_avatar');
        $woo_slg_options['woo_slg_enable_instagram'] = get_option('woo_slg_enable_instagram');
        $woo_slg_options['woo_slg_inst_client_id'] = get_option('woo_slg_inst_client_id');
        $woo_slg_options['woo_slg_inst_client_secret'] = get_option('woo_slg_inst_client_secret');
        $woo_slg_options['woo_slg_inst_icon_url'] = get_option('woo_slg_inst_icon_url');
        $woo_slg_options['woo_slg_inst_link_icon_url'] = get_option('woo_slg_inst_link_icon_url');
        $woo_slg_options['woo_slg_enable_inst_avatar'] = get_option('woo_slg_enable_inst_avatar');
        $woo_slg_options['woo_social_order'] = get_option('woo_social_order');
        $woo_slg_options['woo_slg_base_reg_username'] = get_option('woo_slg_base_reg_username');
        $woo_slg_options['woo_slg_display_link_thank_you'] = get_option('woo_slg_display_link_thank_you');
        $woo_slg_options['woo_slg_display_link_acc_detail'] = get_option('woo_slg_display_link_acc_detail');
        $woo_slg_options['woo_slg_display_link_peepso_acc_detail'] = get_option('woo_slg_display_link_peepso_acc_detail');
        $woo_slg_options['woo_slg_enable_amazon'] = get_option('woo_slg_enable_amazon');
        $woo_slg_options['woo_slg_amazon_client_id'] = get_option('woo_slg_amazon_client_id');
        $woo_slg_options['woo_slg_amazon_client_secret'] = get_option('woo_slg_amazon_client_secret');
        $woo_slg_options['woo_slg_amazon_icon_url'] = get_option('woo_slg_amazon_icon_url');
        $woo_slg_options['woo_slg_amazon_link_icon_url'] = get_option('woo_slg_amazon_link_icon_url');
        $woo_slg_options['woo_slg_enable_paypal'] = get_option('woo_slg_enable_paypal');
        $woo_slg_options['woo_slg_paypal_client_id'] = get_option('woo_slg_paypal_client_id');
        $woo_slg_options['woo_slg_paypal_client_secret'] = get_option('woo_slg_paypal_client_secret');
        $woo_slg_options['woo_slg_paypal_icon_url'] = get_option('woo_slg_paypal_icon_url');
        $woo_slg_options['woo_slg_paypal_link_icon_url'] = get_option('woo_slg_paypal_link_icon_url');
        $woo_slg_options['woo_slg_paypal_environment'] = get_option('woo_slg_paypal_environment');
        
        $woo_slg_options['woo_slg_enable_line'] = get_option('woo_slg_enable_line');
        $woo_slg_options['woo_slg_line_client_id'] = get_option('woo_slg_line_client_id');
        $woo_slg_options['woo_slg_line_client_secret'] = get_option('woo_slg_line_client_secret');
        $woo_slg_options['woo_slg_line_icon_url'] = get_option('woo_slg_line_icon_url');
        $woo_slg_options['woo_slg_line_link_icon_url'] = get_option('woo_slg_line_link_icon_url');
        $woo_slg_options['woo_slg_enable_line_avatar'] = get_option('woo_slg_enable_line_avatar');
        $woo_slg_options['woo_slg_line_icon_text'] = get_option('woo_slg_line_icon_text');
        $woo_slg_options['woo_slg_line_link_icon_text'] = get_option('woo_slg_line_link_icon_text');
        

        $woo_slg_options['woo_slg_social_btn_type'] = get_option('woo_slg_social_btn_type');

        $woo_slg_options['woo_slg_social_btn_position'] = get_option('woo_slg_social_btn_position', 'bottom');

        $woo_slg_options['woo_slg_social_btn_hooks'] = get_option('woo_slg_social_btn_hooks');

        $woo_slg_options['woo_slg_fb_icon_text'] = get_option('woo_slg_fb_icon_text');
        $woo_slg_options['woo_slg_fb_link_icon_text'] = get_option('woo_slg_fb_link_icon_text');
        $woo_slg_options['woo_slg_gp_icon_text'] = get_option('woo_slg_gp_icon_text');
        $woo_slg_options['woo_slg_gp_link_icon_text'] = get_option('woo_slg_gp_link_icon_text');
        $woo_slg_options['woo_slg_li_icon_text'] = get_option('woo_slg_li_icon_text');
        $woo_slg_options['woo_slg_li_link_icon_text'] = get_option('woo_slg_li_link_icon_text');
        $woo_slg_options['woo_slg_tw_icon_text'] = get_option('woo_slg_tw_icon_text');
        $woo_slg_options['woo_slg_tw_link_icon_text'] = get_option('woo_slg_tw_link_icon_text');
        $woo_slg_options['woo_slg_yh_icon_text'] = get_option('woo_slg_yh_icon_text');
        $woo_slg_options['woo_slg_yh_link_icon_text'] = get_option('woo_slg_yh_link_icon_text');
        $woo_slg_options['woo_slg_fs_icon_text'] = get_option('woo_slg_fs_icon_text');
        $woo_slg_options['woo_slg_fs_link_icon_text'] = get_option('woo_slg_fs_link_icon_text');
        $woo_slg_options['woo_slg_wl_icon_text'] = get_option('woo_slg_wl_icon_text');
        $woo_slg_options['woo_slg_wl_link_icon_text'] = get_option('woo_slg_wl_link_icon_text');
        $woo_slg_options['woo_slg_vk_icon_text'] = get_option('woo_slg_vk_icon_text');
        $woo_slg_options['woo_slg_vk_link_icon_text'] = get_option('woo_slg_vk_link_icon_text');
        $woo_slg_options['woo_slg_inst_icon_text'] = get_option('woo_slg_inst_icon_text');
        $woo_slg_options['woo_slg_inst_link_icon_text'] = get_option('woo_slg_inst_link_icon_text');
        $woo_slg_options['woo_slg_amazon_icon_text'] = get_option('woo_slg_amazon_icon_text');
        $woo_slg_options['woo_slg_amazon_link_icon_text'] = get_option('woo_slg_amazon_link_icon_text');
        $woo_slg_options['woo_slg_paypal_icon_text'] = get_option('woo_slg_paypal_icon_text');
        $woo_slg_options['woo_slg_paypal_link_icon_text'] = get_option('woo_slg_paypal_link_icon_text');
        $woo_slg_options['woo_slg_enable_peepso_login_page'] = get_option('woo_slg_enable_peepso_login_page');
        $woo_slg_options['woo_slg_enable_peepso_register_page'] = get_option('woo_slg_enable_peepso_register_page');
        $woo_slg_options['woo_slg_allow_peepso_avatar'] = get_option('woo_slg_allow_peepso_avatar');
        $woo_slg_options['woo_slg_allow_peepso_cover'] = get_option('woo_slg_allow_peepso_cover');
        $woo_slg_options['woo_slg_peepso_avatar_each_time'] = get_option('woo_slg_peepso_avatar_each_time');
        $woo_slg_options['woo_slg_peepso_cover_each_time'] = get_option('woo_slg_peepso_cover_each_time');

        $woo_slg_options['woo_slg_enable_email_varification'] = get_option('woo_slg_enable_email_varification');
        $woo_slg_options['woo_slg_mail_subject'] = get_option('woo_slg_mail_subject');
        $woo_slg_options['woo_slg_mail_content'] = get_option('woo_slg_mail_content');
        $woo_slg_options['woo_slg_enable_email_otp_varification'] = get_option('woo_slg_enable_email_otp_varification');
        $woo_slg_options['woo_slg_mail_otp_subject'] = get_option('woo_slg_mail_otp_subject');
        $woo_slg_options['woo_slg_mail_otp_content'] = get_option('woo_slg_mail_otp_content');
        $woo_slg_options['woo_slg_default_role'] = get_option( 'woo_slg_default_role' );

        $woo_slg_options['woo_slg_enable_apple'] = get_option('woo_slg_enable_apple');
        $woo_slg_options['woo_slg_apple_icon_text'] = get_option('woo_slg_apple_icon_text');
        $woo_slg_options['woo_slg_apple_link_icon_text'] = get_option('woo_slg_apple_link_icon_text');
        $woo_slg_options['woo_slg_apple_client_id'] = get_option( 'woo_slg_apple_client_id' );
        $woo_slg_options['woo_slg_apple_link_icon_url'] = get_option( 'woo_slg_apple_link_icon_url' );
        $woo_slg_options['woo_slg_apple_icon_url'] = get_option( 'woo_slg_apple_icon_url' );
        

        $woo_slg_options['woo_slg_public_js_unique_version'] = get_option( 'woo_slg_public_js_unique_version' );

        
        return apply_filters('woo_slg_global_settings', $woo_slg_options);
    }

    function woo_slg_php_version() {
        /* translators: %2$s: PHP version */
        $message      = sprintf(esc_html__('%1$s requires PHP version %2$s+, plugin is currently NOT ACTIVE.', 'nextend-facebook-connect'), 'WooCommerce Social Login', '5.4');
        $html_message = sprintf('<div class="error">%s</div>', wpautop($message));
        echo wp_kses_post($html_message);
    }

    //Global variables
    global $woo_slg_model, $woo_slg_scripts, $woo_slg_render, $woo_slg_persistant_anonymous,
    $woo_slg_shortcodes, $woo_slg_public, $woo_slg_admin,
    $woo_slg_admin_settings_tabs, $woo_slg_options, $woo_slg_opath, $pagenow;

    //Global Options
    $woo_slg_options = woo_slg_global_settings();

    if (!version_compare(PHP_VERSION, '5.4', '>=')) {
        add_action('admin_notices', 'woo_slg_php_version');
    }
    else {
        require_once(WOO_SLG_DIR . '/includes/WSL/Persistent/PersistentStorage.php');
    }
    
    // loads the Misc Functions file
    require_once ( WOO_SLG_DIR . '/includes/woo-slg-misc-functions.php' );
    woo_slg_initialize();

    require_once (WOO_SLG_DIR . '/includes/class-woo-slg-persistant.php');
    $woo_slg_persistant_anonymous = new WooSocialLoginPersistentAnonymous();

    
    //social class loads
    require_once( WOO_SLG_SOCIAL_DIR . '/woo-slg-social.php' );
    

    //Model Class for generic functions
    require_once( WOO_SLG_DIR . '/includes/class-woo-slg-model.php' );
    $woo_slg_model = new WOO_Slg_Model();

    //Scripts Class for scripts / styles
    require_once( WOO_SLG_DIR . '/includes/class-woo-slg-scripts.php' );
    $woo_slg_scripts = new WOO_Slg_Scripts();
    $woo_slg_scripts->add_hooks();

    //Renderer Class for HTML
    require_once( WOO_SLG_DIR . '/includes/class-woo-slg-renderer.php' );
    $woo_slg_render = new WOO_Slg_Renderer();

    //Shortcodes class for handling shortcodes
    require_once( WOO_SLG_DIR . '/includes/class-woo-slg-shortcodes.php' );
    $woo_slg_shortcodes = new WOO_Slg_Shortcodes();
    $woo_slg_shortcodes->add_hooks();

    //Public Class for public functionlities
    require_once( WOO_SLG_DIR . '/includes/class-woo-slg-public.php' );
    $woo_slg_public = new WOO_Slg_Public();
    $woo_slg_public->add_hooks();

    //Admin Pages Class for admin site
    require_once( WOO_SLG_ADMIN . '/class-woo-slg-admin.php' );
    $woo_slg_admin = new WOO_Slg_Admin();
    $woo_slg_admin->add_hooks();

    //Register Widget
    require_once( WOO_SLG_DIR . '/includes/widgets/class-woo-slg-login-buttons.php' );

    //Loads the Templates Functions file
    require_once ( WOO_SLG_DIR . '/includes/woo-slg-template-functions.php' );

    //Loads the Template Hook File
    require_once( WOO_SLG_DIR . '/includes/woo-slg-template-hooks.php' );

    // Check if Wpweb Updter is not activated then load updater from plugin itself
    if (!class_exists('Wpweb_Upd_Admin')) {

        // Load the updater file
        include_once ( WOO_SLG_DIR . '/includes/updater/wpweb-updater.php' );

        // call to updater function
        woo_slg_wpweb_updater();
    } else{ // added code from the end of file to fix the undefind contstant WPWEB_UPD_DOMAIN
        // call to updater function
        woo_slg_wpweb_updater();
    }

    //Loads the file to register block
    require_once( WOO_SLG_SOCIAL_BLOCK_DIR .'/social/index.php' );
}

/**
 * Add plugin to updater list and create updater object
 * 
 * @package WooCommerce - Social Login
 * @since 1.6.3
 */
function woo_slg_wpweb_updater() {

    // Plugin updates
    wpweb_queue_update(plugin_basename(__FILE__), WOO_SLG_PLUGIN_KEY);

    /**
     * Include Auto Updating Files
     * 
     * @package WooCommerce - Social Login
     * @since 1.0.0
     */
    if (class_exists('Wpweb_Upd_Admin'))
        require_once( WPWEB_UPD_DIR . '/updates/class-plugin-update-checker.php' ); // auto updating
    else
        require_once( WOO_SLG_WPWEB_UPD_DIR . '/updates/class-plugin-update-checker.php' ); // auto updating

    $WpwebWooslgUpdateChecker = new WpwebPluginUpdateChecker(
        WPWEB_UPD_DOMAIN . '/Updates/WOOSLG/license-info.php', __FILE__, WOO_SLG_PLUGIN_KEY
    );

    /**
     * Auto Update
     * 
     * Get the license key and add it to the update checker.
     * 
     * @package WooCommerce - Social Login
     * @since 1.0.0
     */
    function woo_slg_add_secret_key($query) {

        $plugin_key = WOO_SLG_PLUGIN_KEY;

        $query['lickey'] = wpweb_get_plugin_purchase_code($plugin_key);
        return $query;
    }

    $WpwebWooslgUpdateChecker->addQueryArgFilter('woo_slg_add_secret_key');
}
