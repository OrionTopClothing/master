<?php
/*
 * Plugin Name: WP Flat Tour Builder
 * Version: 3.262
 * Plugin URI: http://codecanyon.net/user/loopus/portfolio
 * Description: This plugin allows you to create easily flat visual tours on your website
 * Author: Biscay Charly (loopus)
 * Author URI: http://codecanyon.net/user/loopus/
 * Requires at least: 3.8
 *
 * @package WordPress
 * @author Biscay Charly (loopus)
 * @since 1.0.0
 */

if (!defined('ABSPATH'))
    exit;

register_activation_hook(__FILE__, 'fhpc_install');
register_uninstall_hook(__FILE__, 'fhpc_uninstall');

global $jal_db_version;
$jal_db_version = "1.0";

// Include plugin class files
require_once('includes/Fhpc_Core.php');
require_once('includes/Fhpc_admin_menu.php');
require_once('includes/steps-items/Steps_List_Table.php');
require_once('includes/steps-items/Items_List_Table.php');

function WP_FlatTourBuilder()
{
    $version = 3.262;
    fhpc_checkDBUpdates($version);
    $instance = Fhpc_Core::instance(__FILE__, $version);
    if (is_null($instance->menu)) {
        $instance->menu = Fhpc_admin_menu::instance($instance);
    }

    return $instance;
}

/**
 * Installation. Runs on activation.
 * @access  public
 * @since   1.0.0
 * @return  void
 */
function fhpc_install()
{
    global $wpdb;
    global $jal_db_version;
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');


    if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . "fhpc_settings'") === $wpdb->prefix . 'fhpc_settings') {
    } else {

        $db_table_name = $wpdb->prefix . "fhpc_steps";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";

            $sql = "CREATE TABLE $db_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                title VARCHAR(120) NOT NULL,
                ordersort mediumint(9) NOT NULL,
                start VARCHAR(32) NOT NULL,
                domElement TEXT NOT NULL,
                page TEXT NOT NULL,
                onAdmin BOOL NOT NULL,
                onceTime BOOL NOT NULL,
                mobileEnabled BOOL NOT NULL DEFAULT '1', 
                onlyMobile BOOL NOT NULL DEFAULT '0',                
                rolesAllowed TEXT NOT NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";
            dbDelta($sql);
        }
        // create items table
        $db_table_name = $wpdb->prefix . "fhpc_items";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";

            $sql = "CREATE TABLE $db_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                title VARCHAR(120) NOT NULL,
                content TEXT NOT NULL,
                ordersort mediumint(9) NOT NULL,
                image VARCHAR(250) NOT NULL,
                type VARCHAR(120) NOT NULL,
                position VARCHAR(120) NOT NULL,
		stepID mediumint(9) NOT NULL,
                actionNeeded VARCHAR(32) NOT NULL DEFAULT 'delay',
                delay FLOAT NOT NULL DEFAULT '5.0',
                domElement TEXT NOT NULL,
                page TEXT NOT NULL,
                overlay BOOL DEFAULT 1,
                closeHelperBtn BOOL DEFAULT 0,
                btnContinue VARCHAR(250) NOT NULL DEFAULT 'Continue',
                btnStop VARCHAR(250) NOT NULL,
                forceRefresh BOOL NOT NULL,
                delayStart FLOAT NOT NULL DEFAULT 0,
		  UNIQUE KEY id (id)
		) $charset_collate;";
            dbDelta($sql);
        }

        // create settings table
        $db_table_name = $wpdb->prefix . "fhpc_settings";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";

            $sql = "CREATE TABLE $db_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,      
                colorA VARCHAR(32) NOT NULL,  
                colorB VARCHAR(32) NOT NULL,
                colorC VARCHAR(32) NOT NULL,
                useThemeFonts BOOL NOT NULL,
                purchaseCode VARCHAR(250) NOT NULL,                
                updated BOOL NOT NULL,
                useHttps BOOL NOT NULL,
                UNIQUE KEY id (id)
		) $charset_collate;";
            dbDelta($sql);
        }

        // default settings
        $table_name = $wpdb->prefix . "fhpc_settings";
        $rows_affected = $wpdb->insert($table_name, array('id' => 1, 'colorA' => '#1abc9c', 'colorB' => '#34495e', 'colorC' => '#bdc3c7'));
        add_option("jal_db_version", $jal_db_version);
    }
}

// End install()

/**
 * Update database
 * @access  public
 * @since   2.0
 * @return  void
 */
function fhpc_checkDBUpdates($version)
{
    global $wpdb;
    $installed_ver = get_option("fhpc_version");
    if (!$installed_ver || $installed_ver < 2.6) {
        // custom email feature
        $table_name = $wpdb->prefix . "fhpc_items";
        $sql = "ALTER TABLE " . $table_name . " ADD forceRefresh BOOL NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD delayStart FLOAT NOT NULL DEFAULT 0;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " MODIFY delay FLOAT NOT NULL DEFAULT 5.0;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 3.232) {
       $table_name = $wpdb->prefix . "fhpc_steps";
        $sql = "ALTER TABLE " . $table_name . " ADD rolesAllowed TEXT NOT NULL;";
        $wpdb->query($sql); 
    }
    if (!$installed_ver || $installed_ver < 3.247) {
       $table_name = $wpdb->prefix . "fhpc_steps";
        $sql = "ALTER TABLE " . $table_name . " ADD onlyMobile BOOL NOT NULL DEFAULT '0';";
        $wpdb->query($sql); 
    }
    
    //rolesAllowed TEXT NOT NULL,
    update_option("fhpc_version", $version);
}

/**
 * Uninstallation.
 * @access  public
 * @since   1.0.0
 * @return  void
 */
function fhpc_uninstall()
{
    global $wpdb;
    global $jal_db_version;
    setcookie('pll_updateH', 4);
    setcookie('pll_updateH', null, -1, '/');
    setcookie('pll_updateH', null, -1, '/');
    unset($_COOKIE['pll_updateH']);
    $table_name = $wpdb->prefix . "fhpc_steps";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "fhpc_items";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "fhpc_settings";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

WP_FlatTourBuilder();
