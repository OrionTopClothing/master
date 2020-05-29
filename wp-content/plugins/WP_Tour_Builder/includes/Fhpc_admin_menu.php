<?php
if (!defined('ABSPATH'))
    exit;

class Fhpc_admin_menu {

    /**
     * The single instance 
     * @var 	object
     * @access  private
     * @since 	1.0.0
     */
    private static $_instance = null;

    /**
     * The main plugin object.
     * @var 	object
     * @access  public
     * @since 	1.0.0
     */
    public $parent = null;

    /**
     * Prefix for plugin settings.
     * @var     string
     * @access  publicexport
     * 
     * @since   1.0.0
     */
    public $base = '';

    /**
     * Available settings for plugin.
     * @var     array
     * @access  public
     * @since   1.0.0
     */
    public $settings = array();

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $templates_url;

    public function __construct($parent) {
        $this->_token = 'Fhpc';
        $this->parent = $parent;
        $this->dir = dirname($parent->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $parent->file)));
        $this->templates_url = esc_url(trailingslashit(plugins_url('/templates/', $parent->file)));

        add_action('admin_menu', array($this, 'add_menu_item'));
        add_action('wp_ajax_nopriv_fhpc_item_save', array($this, 'item_save'));
        add_action('wp_ajax_fhpc_item_save', array($this, 'item_save'));
        add_action('wp_ajax_nopriv_fhpc_step_save', array($this, 'step_save'));
        add_action('wp_ajax_fhpc_step_save', array($this, 'step_save'));
        add_action('wp_ajax_nopriv_fhpc_settings_save', array($this, 'settings_save'));
        add_action('wp_ajax_fhpc_settings_save', array($this, 'settings_save'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_styles'), 10, 1);
        add_action('admin_head', array($this->parent, 'options_custom_styles'));
        if (isset($_GET['activateWebsite'])) {
            $this->activateLicense();
        }
    }

    /**
     * Load admin CSS.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function admin_enqueue_styles($hook = '') {

        wp_register_style($this->_token . '-colpick', esc_url($this->assets_url) . 'css/colpick.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-colpick');
        wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/admin.min.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-admin');
    }

// End admin_enqueue_styles()

    /**
     * Load admin Javascript.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function admin_enqueue_scripts($hook = '') {

        if (isset($_GET['page']) && strrpos($_GET['page'], 'fhpc') !== false) {
            wp_register_script($this->_token . '-colpick', esc_url($this->assets_url) . 'js/colpick.js', array('jquery'), $this->_version);
            wp_enqueue_script($this->_token . '-colpick');
            wp_register_script($this->_token . '-admin', esc_url($this->assets_url) . 'js/admin.js', array('jquery'), $this->_version);
            wp_enqueue_script($this->_token . '-admin');

            $settings = $this->getSettings();
            if ($settings->useHttps) {
                wp_localize_script($this->_token . '-admin', 'homeurl', home_url('', 'https') . '/');
                wp_localize_script($this->_token . '-admin', 'adminurl', admin_url('', 'https') . '/');
            } else {
                wp_localize_script($this->_token . '-admin', 'homeurl', home_url() . '/');
                wp_localize_script($this->_token . '-admin', 'adminurl', admin_url() . '/');
            }
        }
    }

    /**
     * Add settings link to plugin list table
     * @param  array $links Existing links
     * @return array 		Modified links
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=fhpc_menu">' . __('Settings', 'fhpc') . '</a>';
        array_push($links, $settings_link);
        return $links;
    }

    /**
     * Add menu to admin
     * @return void
     */
    public function add_menu_item() {
        add_menu_page('WP Tour Builder', __('Flat Tour Builder', 'fhpc'), 'manage_options', 'fhpc_menu', array($this, 'submenu_settings'), 'dashicons-lightbulb');
        $menuSlag = 'fhpc_menu';
        add_submenu_page($menuSlag, 'Tours', __('Tours', 'fhpc'), 'manage_options', 'fhpc-steps', array($this, 'submenu_steps'));
        add_submenu_page($menuSlag, 'Edit Tour', __('Edit Tour', 'fhpc'), 'manage_options', 'fhpc-step-add', array($this, 'submenu_step_add'));
        add_submenu_page($menuSlag, 'Steps', __('Steps', 'fhpc'), 'manage_options', 'fhpc-items', array($this, 'submenu_items'));
        add_submenu_page($menuSlag, 'Edit Step', __('Edit Step', 'fhpc'), 'manage_options', 'fhpc-item-add', array($this, 'submenu_item_add'));
        add_submenu_page($menuSlag, 'Import', __('Import', 'fhpc'), 'manage_options', 'fhpc-import', array($this, 'submenu_import'));
        add_submenu_page($menuSlag, 'Export', __('Export', 'fhpc'), 'manage_options', 'fhpc-export', array($this, 'submenu_export'));
    }

    /**
     * Menu export render
     * @return void
     */
    function submenu_export() {
        global $wpdb;

        if (!is_dir(plugin_dir_path(__FILE__) . '../tmp')) {
            mkdir(plugin_dir_path(__FILE__) . '../tmp');
            chmod(plugin_dir_path(__FILE__) . '../tmp', 0747);
        }

        $destination = plugin_dir_path(__FILE__) . '../tmp/export_tour_builder.zip';
        $zip = new ZipArchive();
        if (file_exists($destination)) {
            unlink($destination);
        }
        if ($zip->open($destination, ZipArchive::CREATE) !== true) {
            return false;
        }

        $jsonExport = array();
        $table_name = $wpdb->prefix . "fhpc_settings";
        $settings = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id ASC LIMIT 1");
        if (count($settings) > 0) {
            $settings = $settings[0];
            $settings->purchaseCode = '';
            $settings->updated = 0;
            $jsonExport['settings'] = array($settings);
        } else {
            $jsonExport['settings'] = array();
        }

        $table_name = $wpdb->prefix . "fhpc_steps";
        $steps = array();
        foreach ($wpdb->get_results("SELECT * FROM $table_name") as $key => $row) {
            $steps[] = $row;
        }

        $jsonExport['steps'] = $steps;
        $table_name = $wpdb->prefix . "fhpc_items";
        $items = array();
        foreach ($wpdb->get_results("SELECT * FROM $table_name") as $key => $row) {
            $items[] = $row;
        }

        $jsonExport['items'] = $items;
        $fp = fopen(plugin_dir_path(__FILE__) . '../tmp/export_tour_builder.json', 'w');
        fwrite($fp, json_encode($jsonExport));
        fclose($fp);

        $zip->addfile(plugin_dir_path(__FILE__) . '../tmp/export_tour_builder.json', 'export_tour_builder.json');
        $zip->close();
        ?>
        <div class="wrap testExport">
            <h2><?php echo __('Export data', 'fhpc'); ?></h2>
            <p>
        <?php echo __('Export all this plugin datas to a zip file will can be imported on another website.', 'fhpc'); ?>
            </p>
            <p>
                <a download class="button-primary" href="<?php echo esc_url(trailingslashit(plugins_url('/', $this->parent->file))) . 'tmp/export_tour_builder.zip'; ?>"><?php echo __('Export', 'fhpc'); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * Menu import render
     * @return void
     */
    function submenu_import() {
        global $wpdb;
        ?>
        <div class="wrap testImport">
            <h2><?php echo __('Import data', 'fhpc'); ?></h2>
        <?php
        $displayForm = true;
        $settings = $this->getSettings();
//            $pageID = $settings->form_page_id;
        if (isset($_GET['import']) && isset($_FILES['importFile'])) {
            $error = false;
            if (!is_dir(plugin_dir_path(__FILE__) . '../tmp')) {
                mkdir(plugin_dir_path(__FILE__) . '../tmp');
                chmod(plugin_dir_path(__FILE__) . '../tmp', 0747);
            }
            $target_path = plugin_dir_path(__FILE__) . '../tmp/export_tour_builder.zip';
            if (@move_uploaded_file($_FILES['importFile']['tmp_name'], $target_path)) {


                $upload_dir = wp_upload_dir();
                if (!is_dir($upload_dir['path'])) {
                    mkdir($upload_dir['path']);
                }

                $zip = new ZipArchive;
                $res = $zip->open($target_path);
                if ($res === TRUE) {
                    $zip->extractTo(plugin_dir_path(__FILE__) . '../tmp/');
                    $zip->close();

                    $jsonfilename = 'export_tour_builder.json';
                    if (!file_exists(plugin_dir_path(__FILE__) . '../tmp/export_tour_builder.json')) {
                        $jsonfilename = 'export_tour_builder';
                    }
                    if (file_exists(plugin_dir_path(__FILE__) . '../tmp/export_helper_creator.json')) {
                        $jsonfilename = 'export_helper_creator.json';
                    }

                    $file = file_get_contents(plugin_dir_path(__FILE__) . '../tmp/' . $jsonfilename);
                    $dataJson = json_decode($file, true);

                    $table_name = $wpdb->prefix . "fhpc_settings";
                    $wpdb->query("TRUNCATE TABLE $table_name");
                    $wpdb->insert($table_name, $dataJson['settings'][0]);

                    $table_name = $wpdb->prefix . "fhpc_steps";
                    $wpdb->query("TRUNCATE TABLE $table_name");
                    foreach ($dataJson['steps'] as $key => $value) {
                        foreach ($value as $keyV => $valueV) {
                            if ($keyV == 'page') {
                                if (strrpos($valueV, site_url()) === false) {
                                    
                                } else {
                                    $valueV = substr($valueV, strlen(site_url()) + 1);
                                    $value[$keyV] = $valueV;
                                }
                            }
                        }
                        $wpdb->insert($table_name, $value);
                    }

                    $table_name = $wpdb->prefix . "fhpc_items";
                    $wpdb->query("TRUNCATE TABLE $table_name");
                    foreach ($dataJson['items'] as $key => $value) {
                        foreach ($value as $keyV => $valueV) {
                            if ($keyV == 'page') {
                                if (strrpos($valueV, site_url()) === false) {
                                    
                                } else {
                                    $valueV = substr($valueV, strlen(site_url()) + 1);
                                    $value[$keyV] = $valueV;
                                }
                            }
                        }
                        $wpdb->insert($table_name, $value);
                    }
                    $files = glob(plugin_dir_path(__FILE__) . '../tmp/*');
                    foreach ($files as $file) {
                        if (is_file($file))
                            unlink($file);
                    }

                    //$this->updateCSS();
                } else {
                    $error = true;
                }
            } else {
                $error = true;
            }
            if ($error) {
                echo '<div class="error">An error occurred during the transfer</div>';
            } else {
                $displayForm = false;
                echo '<div class="updated">Data has been imported.</div>';
            }
        }
        if ($displayForm) {
            ?>
                <p>
                <?php echo __('Import here the zip file created using the "Export" tool.', 'fhpc'); ?>

                </p>
                <div class="error" style="color: red;">
            <?php echo __('WARNING: import data will overwrite existing ones!', 'fhpc'); ?>

                </div>
                <form action="admin.php?page=fhpc-import&import=1" method="post" enctype="multipart/form-data">
                    <p>
                        <input id="importFile" type="file" name="importFile" placeholder="Select the .zip file"/>
                        <label for="importFile"> <span class="description">
            <?php echo __('Select the generated .zip file', 'fhpc'); ?></span> </label>
                    </p>
                    <p>
                        <button type="submit" class="button-primary">
            <?php echo __('Import', 'fhpc'); ?>
                        </button>
                    </p>
                </form>
            <?php
        }
        ?>
        </div>
            <?php
        }

        /**
         * Get specific Item datas
         * @return object
         */
        private function getItemDatas($item_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . "fhpc_items";
            $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id='%s' LIMIT 1", $item_id));
            return $rows[0];
        }

        /**
         * Get specific Step datas
         * @return object
         */
        private function getStepDatas($step_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . "fhpc_steps";
            $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id='%s' LIMIT 1", $step_id));
            return $rows[0];
        }

        /**
         * Get Steps datas
         * @return Array
         */
        private function getStepsData() {
            global $wpdb;
            $table_name = $wpdb->prefix . "fhpc_steps";
            $rows = $wpdb->get_results("SELECT * FROM $table_name");

            $data = array();
            foreach ($rows as $row) {
                $data[] = array('id' => $row->id, 'title' => $row->title, 'order' => $row->ordersort, 'page' => $row->page, 'onAdmin' => $row->onAdmin);
            }
            return $data;
        }

        public function submenu_settings() {
            global $wpdb;
            $table_name = $wpdb->prefix . "fhpc_settings";
            $settings = $wpdb->get_results("SELECT * FROM $table_name WHERE id=1 LIMIT 1");
            $settings = $settings[0];
            ?>
        <div class="wrap testSettings">
            <div class="wrap">
                <h2><?php echo __('Settings', 'fhpc'); ?></h2>
                <div id="fhpc_response"></div>
                <form id="form_settings" method="post" action="#" onsubmit="qc_process(this);
                        return false;">
                    <input id="id" type="hidden" name="id" value="1">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row"><?php echo __('Main Color', 'fhpc'); ?></th>
                                <td>
                                    <input id="colorA" class="colorpick" type="text" name="colorA" placeholder="Choose a color" value="<?php
        echo $settings->colorA;
        ?>">
                                    <label for="colorA"> <span class="description"><?php echo __('This is the main color', 'fhpc'); ?></span> </label></td>
                            </tr>    
                            <tr>
                                <th scope="row"><?php echo __('Color of the dialog title', 'fhpc'); ?></th>
                                <td>
                                    <input id="colorB" class="colorpick" type="text" name="colorB" placeholder="Choose a color" value="<?php
        echo $settings->colorB;
        ?>">
                                    <label for="colorB"> <span class="description"><?php echo __('This is the dialog title color', 'fhpc'); ?></span> </label></td>
                            </tr>       
                            <tr>
                                <th scope="row"><?php echo __('Color of the "text" steps', 'fhpc'); ?></th>
                                <td>
                                    <input id="colorC" class="colorpick" type="text" name="colorC" placeholder="Choose a color" value="<?php
        echo $settings->colorC;
        ?>">
                                    <label for="colorC"> <span class="description"><?php echo __('This is the "text" steps color', 'fhpc'); ?></span> </label></td>
                            </tr>    
                            <tr>
                                <th scope="row"><?php echo __('Use theme fonts ?', 'fhpc'); ?></th>
                                <td>
                                    <select id="useThemeFonts" name="useThemeFonts">
                                        <option value="0"><?php echo __('No', 'fhpc'); ?></option>
                                        <option value="1" <?php
        if ($settings->useThemeFonts) {
            echo 'selected';
        }
            ?>><?php echo __('Yes', 'fhpc'); ?></option>
                                    </select>
                                </td>
                            </tr>  
                            <tr>
                                <th scope="row"><?php echo __('Use HTTPS ?', 'fhpc'); ?></th>
                                <td>
                                    <select id="useHttps" name="useHttps">
                                        <option value="0"><?php echo __('No', 'fhpc'); ?></option>
                                        <option value="1" <?php
                                if ($settings->useHttps) {
                                    echo 'selected';
                                }
            ?>><?php echo __('Yes', 'fhpc'); ?></option>
                                    </select>
                                </td>
                            </tr>                               

                            <tr>
                                <th scope="row"></th>
                                <td>
                                    <input type="submit" value="<?php echo __('Save', 'fhpc'); ?>" class="button-primary"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <script>
                        function qc_process(e) {

                            var error = false;
                            jQuery('#colorA').removeClass('field-error');
                            if (jQuery("#colorA").val().length != 7) {
                                error = true;
                                jQuery('#colorA').addClass('field-error');
                            }
                            jQuery('#colorB').removeClass('field-error');
                            if (jQuery("#colorB").val().length != 7) {
                                error = true;
                                jQuery('#colorB').addClass('field-error');
                            }
                            if (!error) {
                                jQuery("#fhpc_response").hide();
                                var data = {action: "fhpc_settings_save"};
                                jQuery('#form_settings input, #form_settings select, #form_settings textarea').each(function () {
                                    if (jQuery(this).attr('name')) {
                                        eval('data.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
                                    }
                                });
                                jQuery.post(ajaxurl, data, function (response) {
                                    jQuery("#fhpc_response").html('<div id="message" class="updated"><p><strong><?php echo __('Settings saved', 'fhpc'); ?></strong>.</p></div>');
                                    // jQuery("#testfc_response").html(response);
                                    setTimeout(function () {
                                        document.location.href = 'admin.php?page=fhpc_menu';
                                    }, 250);
                                });
                            }
                        }
                    </script>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * save settings
     * @return void
     */
    public function settings_save() {
        global $wpdb;
        $response = "Error, try again later.";
        $table_name = $wpdb->prefix . "fhpc_settings";
        $sqlDatas = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'action' && $key != 'pll_ajax_backend') {
                $sqlDatas[$key] = sanitize_text_field(stripslashes($value));
            }
        }
        $wpdb->update($table_name, $sqlDatas, array('id' => 1));
        $response = '<div id="message" class="updated"><p>Step <strong>saved</strong>.</p></div>';
        echo $response;
        // $this->updateCSS();
        die();
    }

    /**
     * remove specific item
     * @return void
     */
    private function remove_item($item_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "fhpc_items";
        $wpdb->delete($table_name, array('id' => $item_id));
    }

    /**
     * Menu steps items render
     * @return void
     */
    public function submenu_items() {

        if (isset($_GET['remove'])) {
            $this->remove_item($_GET['remove']);
        }
        $helperID = 0;
        if (isset($_GET['helper'])) {
            $helperID = $_GET['helper'];
        } else if($_GET['duplicate']){
            $tourID = $_GET['duplicate'];
            $table_steps = $wpdb->prefix . "fhpc_steps";
            $table_items = $wpdb->prefix . "fhpc_items";
            
            $tour = $wpdb->get_results("SELECT * FROM $table_steps WHERE id=$tourID LIMIT 1");
            if(count($tour)>0){
                $tour = $tour[0];                
                unset($tour->id);
                $tour->title = $tour->title . ' (1)';
                $wpdb->insert($table_steps, (array) $tour);
                $newTourID = $wpdb->insert_id;
                
                
                $steps = $wpdb->get_results("SELECT * FROM $table_steps WHERE stepID=$tourID LIMIT 1");
                foreach ($steps as $step) {
                    $step->stepID = $newTourID;
                    
                }
                
            }
            
            //duplicate
        }
        $itemsTable = new Fhpc_Items_List_Table();
        $itemsTable->helperID = $helperID;
        $itemsTable->prepare_items();
        ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2><?php echo __('Steps list', 'fhpc'); ?> <a href="admin.php?page=fhpc-item-add" class="add-new-h2"><?php echo __('Add New', 'fhpc'); ?></a></h2>

        <?php $itemsTable->display(); ?>
        </div>

            <?php
        }

        /**
         * Menu add item render
         * @return void
         */
        public function submenu_item_add() {

            $stepsData = $this->getStepsData();
            $datas = false;
            if (isset($_GET['item'])) {
                $datas = $this->getItemDatas($_GET['item']);
                $helper = $this->getStepDatas($datas->stepID);
            }
            ?>
        <div class="wrap">
            <h2><?php echo __('Edit a step', 'fhpc'); ?></h2>
            <div id="fhpc_response"></div>
            <form id="form_item" method="post" action="#" onsubmit="qc_process(this);
                    return false;">
                <input id="id" type="hidden" name="id" value="<?php
        if ($datas) {
            echo $datas->id;
        } else {
            echo '0';
        }
            ?>"/>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><?php echo __('Tour', 'fhpc'); ?></th>
                            <td>
                                <select id="stepID" name="stepID" placeholder="<?php echo __('Select tour', 'fhpc'); ?>">
        <?php
        foreach ($stepsData as $step) {
            $sel = '';
            if ($datas && $step['id'] == $datas->stepID) {
                $sel = 'selected';
            }
            echo '<option value="' . $step['id'] . '" ' . $sel . ' data-page="' . $step['page'] . '" data-admin="' . $step['onAdmin'] . '">' . $step['title'] . '</value>';
        }
        ?>
                                </select>
                                <label for="stepID">
                                    <span class="description"><?php echo __('Choose a tour', 'fhpc'); ?></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Order', 'fhpc'); ?></th>
                            <td>
                                <input id="ordersort" type="number" name="ordersort" placeholder="Item order" value="<?php
                            if ($datas) {
                                echo $datas->ordersort;
                            } else {
                                echo '0';
                            }
        ?>">
                                <label for="ordersort">
                                    <span class="description"><?php echo __('Steps take place according to the defined order', 'fhpc'); ?></span>
                                </label>
                            </td>
                        </tr>      
                        <tr>
                            <th scope="row"><?php echo __('Title', 'fhpc'); ?></th>
                            <td>
                                <input id="title" type="text" name="title" placeholder="Step title" value="<?php
                        if ($datas) {
                            echo $datas->title;
                        }
                        ?>">
                                <label for="title"> <span class="description"><?php echo __('This is the step title', 'fhpc'); ?></span> </label></td>
                        </tr>                           
                        <tr>
                            <th scope="row"><?php echo __('Type', 'fhpc'); ?></th>
                            <td>
                                <select id="type" name="type" placeholder="Select type">
        <?php
        $sel1 = '';
        $sel2 = '';
        $sel3 = '';
        if ($datas && $datas->type == 'dialog') {
            $sel2 = 'selected';
        } else if ($datas && $datas->type == 'text') {
            $sel3 = 'selected';
        } else {
            $sel1 = 'selected';
        }
        echo '<option value="tooltip" ' . $sel1 . '>' . __('Tooltip', 'fhpc') . '</value>';
        echo '<option value="dialog" ' . $sel2 . '>' . __('Dialog', 'fhpc') . '</value>';
        echo '<option value="text" ' . $sel3 . '>' . __('Text', 'fhpc') . '</value>';
        ?>
                                </select>
                                <label for="type">
                                    <span class="description"><?php echo __('Tooltip, Text or Dialog ?', 'fhpc'); ?></span>
                                </label>
                            </td>
                        </tr>                           
                        <tr class="only_tooltip">
                            <th scope="row"><?php echo __('Target DOM element', 'fhpc'); ?></th>
                            <td>
                                <input type="text" id="domElement" name="domElement" value="<?php
                            if ($datas) {
                                echo $datas->domElement;
                            }
                            ?>" />
                                <span style="display:none;">
                                <?php
                                if ($datas && $datas->domElement != "") {
                                    echo __('Element selected', 'fhpc');
                                } else {
                                    echo __('No selection', 'fhpc');
                                }
                                ?>
                                </span>
                                <a href="javascript:" onclick="fhpc_chooseItemTarget();" class="button-primary"><?php echo __('Selection', 'fhpc'); ?></a>

                                <label> <span class="description"><?php echo __('Select a dom element', 'fhpc'); ?></span> </label></td>
                        </tr>
                        <tr >
                            <th scope="row"><?php echo __('Page', 'fhpc'); ?></th>
                            <td>

                                <input id="page" type="text" name="page" placeholder="<?php echo __('Page', 'fhpc'); ?>" value="<?php
                            if ($datas) {
                                echo $datas->page;
                            }
                            ?>">
                                <label for="page"> <span class="description"><?php echo __('Select the page (facultative)', 'fhpc'); ?></span> </label>
                            </td>
                        </tr>


                        <tr style="display: none;" >
                            <th scope="row"><?php echo __('On website or backend ?', 'fhpc'); ?></th>
                            <td>
                                <select id="onAdmin" name="onAdmin">
        <?php
        echo '<option value="0" ' . $sel1 . '>Frontend</value>';
        echo '<option value="1" ' . $sel2 . '>Admin</value>';
        ?>
                                </select>
                                <label for="onAdmin"> <span class="description"><?php echo __('On website or backend ?', 'fhpc'); ?></span> </label></td>
                        </tr>   
                        <tr class="only_tooltip">
                            <th scope="row"><?php echo __('Position', 'fhpc'); ?></th>
                            <td>
                                <select id="position" name="position" >
        <?php
        $sel1 = '';
        $sel2 = '';
        if ($datas && $datas->position == 'top') {
            $sel2 = 'selected';
        } else {
            $sel1 = 'selected';
        }
        echo '<option value="bottom" ' . $sel1 . '>' . __('Bottom', 'fhpc') . '</value>';
        echo '<option value="top" ' . $sel2 . '>' . __('Top', 'fhpc') . '</value>';
        ?>
                                </select>
                                <label for="position">
                                    <span class="description"><?php echo __('Choose the tooltip position', 'fhpc'); ?></span>
                                </label>
                            </td>
                        </tr>
                        <tr class="only_tooltip">
                            <th scope="row"><?php echo __('Content', 'fhpc'); ?></th>
                            <td>

                                <textarea id="content_tooltip" type="content_tooltip" name="content_tooltip" style="height: 100px;" placeholder="<?php echo __('Content', 'fhpc'); ?>"><?php
                            if ($datas) {
                                echo $datas->content;
                            }
                            ?></textarea>
                                <label for="content_tooltip"> <span class="description"><?php echo __('This is the step content', 'fhpc'); ?></span> </label>
                            </td>
                        </tr>
                        <tr class="only_dialog">
                            <th scope="row"><?php echo __('Content', 'fhpc'); ?></th>
                            <td>

        <?php
        $content = "";
        if ($datas) {
            $content = $datas->content;
        }
        wp_editor($content, 'content', array(
            'tinymce' => array(
                'height' => 80
            ))
        );
        ?>
                                <label for="content"> <span class="description"><?php echo __('This is the step content', 'fhpc'); ?></span> </label></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Overlay', 'fhpc'); ?></th>
                            <td>
                                <select id="overlay" name="overlay">
        <?php
        $sel1 = '';
        $sel2 = '';
        if ($datas && !$datas->overlay) {
            $sel1 = 'selected';
        } else {
            $sel2 = 'selected';
        }
        echo '<option value="0" ' . $sel1 . '>' . __('No', 'fhpc') . '</value>';
        echo '<option value="1" ' . $sel2 . '>' . __('Yes', 'fhpc') . '</value>';
        ?>
                                </select>
                                <label for="overlay">
                                    <span class="description"><?php echo __('Use overlay mask ?', 'fhpc'); ?></span>
                                </label>
                            </td>
                        </tr>   
                        <tr>
                            <th scope="row"><?php echo __('Add a button to close the tour ?', 'fhpc'); ?></th>
                            <td>
                                <select id="closeHelperBtn" name="closeHelperBtn">
        <?php
        $sel1 = '';
        $sel2 = '';
        if ($datas && !$datas->closeHelperBtn) {
            $sel1 = 'selected';
        } else {
            $sel2 = 'selected';
        }
        echo '<option value="0" ' . $sel1 . '>' . __('No', 'fhpc') . '</value>';
        echo '<option value="1" ' . $sel2 . '>' . __('Yes', 'fhpc') . '</value>';
        ?>
                                </select>
                                <label for="closeHelperBtn">
                                    <span class="description"><?php echo __('Add a close button to stop the tour', 'fhpc'); ?></span>
                                </label>
                            </td>
                        </tr>                    



                        <tr class="only_tooltip">
                            <th scope="row"><?php echo __('Action to continue', 'fhpc'); ?></th>
                            <td>
                                <select id="actionNeeded" name="actionNeeded" placeholder="Select an action">
        <?php
        $sel1 = '';
        $sel2 = '';
        $sel3 = '';
        $sel4 = '';
        if ($datas && $datas->actionNeeded == 'click') {
            $sel1 = 'selected';
        } else {
            $sel2 = 'selected';
        }
        echo '<option value="click" ' . $sel1 . '>' . __('Click', 'fhpc') . '</value>';
        echo '<option value="delay" ' . $sel2 . '>' . __('Duration', 'fhpc') . '</value>';
        ?>
                                </select>
                                <label for="actionNeeded">
                                    <span class="description"><?php echo __('Select an action', 'fhpc'); ?></span>
                                </label>
                            </td>
                        </tr>

                        <tr class="only_dialog">
                            <th scope="row"><?php echo __('Button "Continue" text', 'fhpc'); ?></th>
                            <td>
                                <input id="btnContinue" type="text" name="btnContinue" placeholder="Continue button label" value="<?php
                            if ($datas) {
                                echo $datas->btnContinue;
                            } else {
                                echo __('Continue', 'fhpc');
                            }
        ?>">
                                <label for="btnContinue">
                                    <span class="description"><?php echo __("Let this field empty if you don't want this button", 'fhpc'); ?></span>
                                </label>
                            </td>
                        </tr>
                        <tr class="only_dialog">
                            <th scope="row"><?php echo __('Button "Stop" text', 'fhpc'); ?></th>
                            <td>
                                <input id="btnStop" type="text" name="btnStop" placeholder="Stop button label" value="<?php
                        if ($datas) {
                            echo $datas->btnStop;
                        }
                        ?>">
                                <label for="btnStop">
                                    <span class="description"><?php echo __("Leave this field empty if you don't want this button", 'fhpc'); ?></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Duration', 'fhpc'); ?></th>
                            <td>
                                <input id="delay" type="number" name="delay" step="0.1" placeholder="Duration" value="<?php
                        if ($datas) {
                            echo $datas->delay;
                        } else {
                            echo '5';
                        }
        ?>">
                                <label for="delay">
                                    <span class="description"><?php echo __('Defines a delay in seconds', 'fhpc'); ?></span>
                                </label>
                            </td>
                        </tr>
                        <tr class="only_tooltip">
                            <th scope="row"><?php echo __('Delay before showing tooltip', 'fhpc'); ?></th>
                            <td>
                                <input id="delayStart" type="number" step="0.1" name="delayStart" placeholder="<?php echo __('Delay', 'fhpc'); ?>" value="<?php
                        if ($datas) {
                            echo $datas->delayStart;
                        } else {
                            echo '0';
                        }
        ?>">
                                <label for="delayStart">
                                    <span class="description"><?php echo __('Useful if the item does not appear immediately (effect of appearance...)', 'fhpc'); ?></span>
                                </label>
                            </td>
                        </tr>


                        <tr>
                            <th scope="row"></th>
                            <td>
                                <input type="submit" value="Save" class="button-primary"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        <?php //echo site_url();         ?>
        </div>
        <script>
            function checkStep() {
                if (jQuery('#page').val() == "") {
                    var page = jQuery('#stepID :selected').data('page');
                    jQuery('#page').val(page);
                }
                jQuery('#onAdmin').val(jQuery('#stepID :selected').data('admin'));
            }
            function checkType() {
                var type = jQuery('#type').val();
                if (type == 'tooltip') {
                    jQuery('.only_dialog').hide();
                    jQuery('.only_tooltip').show();
                } else if (type == 'dialog') {
                    jQuery('#delayStart').val(0);
                    jQuery('.only_dialog').show();
                    jQuery('.only_tooltip').hide();
                    checkBtns();
                } else {
                    jQuery('#delayStart').val(0);
                    jQuery('.only_dialog').hide();
                    jQuery('.only_tooltip').hide();
                    jQuery('#content_tooltip').parent().parent().show();
                    jQuery('#overlay').parent().parent().hide();
                    jQuery('#overlay').val('1');
                    jQuery('#delay').parent().parent().show();
                    jQuery('#actionNeeded').parent().parent().hide();
                    jQuery('#actionNeeded').val('delay');
                }
            }
            function checkBtns() {
                if ((jQuery('#type').val() == 'dialog') && (jQuery('#btnContinue').val().length > 0 || jQuery('#btnStop').val().length > 0)) {
                    jQuery('#actionNeeded').val('click');
                    jQuery('#actionNeeded').parent().parent().hide();
                    jQuery('#delay').parent().parent().hide();
                } else if (jQuery('#type').val() == 'dialog') {
                    jQuery('#actionNeeded').val('delay');
                    jQuery('#delay').parent().parent().show();
                }
            }
            function checkAction() {
                if (jQuery('#actionNeeded').val() == 'click' && jQuery('#type').val() != "text") {
                    jQuery('#delay').parent().parent().hide();
                } else {
                    jQuery('#delay').parent().parent().show();
                }
            }
            function checkOverlay() {
                if (jQuery('#overlay').val() == '1') {
                    jQuery('#closeHelperBtn').parent().parent().show();
                } else {
                    jQuery('#closeHelperBtn').parent().parent().hide();
                }
            }
            function qc_process(e) {

                var error = false;

                jQuery('#domElement').prev().prev('span').css({
                    color: '#000'
                });
                jQuery('#domElement').removeClass('field-error');
                jQuery('#title').removeClass('field-error');
                if (jQuery("#title").val().length < 3) {
                    error = true;
                    jQuery('#title').addClass('field-error');
                }
                if (jQuery('#type').val() == 'tooltip' && jQuery('#domElement').val().length < 2) {
                    error = true;
                    jQuery('#domElement').prev().prev('span').css({
                        color: 'red'
                    });
                    jQuery('#domElement').addClass('field-error');
                }

                if (!error) {
                    jQuery("#fhpc_response").hide();
                    var data = {action: "fhpc_item_save"};
                    jQuery('#form_item input, #form_item select, #form_item textarea').each(function () {
                        if (jQuery(this).attr('name')) {
                            if (jQuery(this).attr('name') != "content_tooltip" && jQuery(this).attr('name') != 'onAdmin' && jQuery(this).attr('name') != 'shortcode-filter') {
                                eval('data.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
                            }
                        }
                    });
                    var editor = tinyMCE.get('content');
                    if (editor) {
                        data.content = editor.getContent();
                    } else {
                        data.content = jQuery('#content').val();
                    }
                    if (jQuery('#type').val() != 'dialog') {
                        data.content = jQuery('#content_tooltip').val();
                    }

                    jQuery.post(ajaxurl, data, function (response) {
                        jQuery("#fhpc_response").html('<div id="message" class="updated"><p><strong><?php echo __('Step saved', 'fhpc'); ?></strong>.</p></div>');
                        jQuery("#fhpc_response").fadeIn(250);
                        jQuery('#id').val(response);
                        document.location.href = '#wpwrap';
                    });
                }
            }
            jQuery(document).ready(function () {
                jQuery('#type').change(checkType);
                checkType();
                jQuery('#actionNeeded').change(checkAction);
                checkAction();
                jQuery('#overlay').change(checkOverlay);
                checkOverlay();
                jQuery('#stepID').change(checkStep);
                checkStep();
                jQuery('#btnContinue').keyup(checkBtns);
                jQuery('#btnStop').keyup(checkBtns);

            });
        </script>
        <?php
    }

    /**
     * save item
     * @return void
     */
    public function item_save() {
        global $wpdb;
        $response = "Error, try again later.";
        $table_name = $wpdb->prefix . "fhpc_items";
        $sqlDatas = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'action' && $key != 'id' && $key != 'pll_ajax_backend') {
                if ($key == 'page') {
                    if (strrpos($value, site_url()) === false) {
                        
                    } else {
                        $value = substr($value, strlen(site_url()) + 1);
                    }
                    if (substr($value, -2, 2) == '//') {
                        $value = substr($value, 0, -1);
                    }
                }
                $sqlDatas[$key] = stripslashes($value);
            }
        }
        if ($_POST['id'] > 0) {
            $wpdb->update($table_name, $sqlDatas, array('id' => sanitize_text_field($_POST['id'])));
            $response = $_POST['id'];
        } else {
            $rows_affected = $wpdb->insert($table_name, $sqlDatas);
            $lastid = $wpdb->insert_id;
            $response = $lastid;
        }


        echo $response;
        die();
    }

    /**
     * Menu steps render
     * @return void
     */
    public function submenu_steps() {


        if (isset($_GET['remove'])) {
            $this->remove_step($_GET['remove']);
        }

        $stepTable = new Fhpc_Steps_List_Table();
        $stepTable->prepare_items();
        ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2> <?php echo __('Tours list', 'fhpc'); ?> <a href="admin.php?page=fhpc-step-add" class="add-new-h2"><?php echo __('Add New', 'fhpc'); ?></a></h2>

        <?php $stepTable->display(); ?>
        </div>
        <?php
    }

    /**
     * Remove a step
     * @return void
     */
    private function remove_step($step_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "fhpc_items";
        $wpdb->delete($table_name, array('stepID' => $step_id));

        $table_name = $wpdb->prefix . "fhpc_steps";
        $wpdb->delete($table_name, array('id' => $step_id));
    }

    private function isUpdated() {
        return true;
    }

    /**
     * Menu add step render
     * @return void
     */
    function submenu_step_add() {

        $datas = false;
        if (isset($_GET['step'])) {
            $datas = $this->getStepDatas($_GET['step']);
        }
        ?>
        <div class="wrap">
            <h2><?php echo __('Edit a tour', 'fhpc'); ?></h2>
            <div id="fhpc_response"></div>
            <form id="form_step" method="post" action="#" onsubmit="qc_process(this);
                    return false;">
                <input id="id" type="hidden" name="id" value="<?php
        if ($datas) {
            echo $datas->id;
        } else {
            echo '0';
        }
        ?>">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">Title</th>
                            <td>
                                <input id="title" type="text" name="title" placeholder="<?php echo __('Tour name', 'fhpc'); ?>" value="<?php
               if ($datas) {
                   echo $datas->title;
               }
               ?>">
                                <label for="title"> <span class="description"><?php echo __('This is the tour name', 'fhpc'); ?></span> </label></td>
                        </tr>    
                        <tr>
                            <th scope="row"><?php echo __('Start method', 'fhpc'); ?></th>
                            <td>
                                <select id="start" name="start" placeholder="<?php echo __('Select start method', 'fhpc'); ?>">
        <?php
        $sel1 = '';
        $sel2 = '';
        if ($datas && $datas->start == 'click') {
            $sel2 = 'selected';
        } else {
            $sel1 = 'selected';
        }
        echo '<option value="auto" ' . $sel1 . '>' . __('Start automatically', 'fhpc') . '</value>';
        echo '<option value="click" ' . $sel2 . '>' . __('On click on an element', 'fhpc') . '</value>';
        ?>
                                </select>
                                <label for="start"> <span class="description"><?php echo __('How does the tour start ?', 'fhpc'); ?></span> </label></td>
                        </tr>   

                        <tr>
                            <th scope="row">Run once ?</th>
                            <td>
                                <select id="onceTime" name="onceTime" placeholder="<?php echo __('Run only once ?', 'fhpc'); ?>">
        <?php
        $sel1 = '';
        $sel2 = '';
        if ($datas && $datas->onceTime) {
            $sel2 = 'selected';
        } else {
            $sel1 = 'selected';
        }
        echo '<option value="0" ' . $sel1 . '>' . __('No', 'fhpc') . '</value>';
        echo '<option value="1" ' . $sel2 . '>' . __('Yes', 'fhpc') . '</value>';
        ?>
                                </select>
                                <label for="onceTime"> <span class="description"><?php echo __('Run once the tour ?', 'fhpc'); ?></span> </label></td>
                        </tr>   


                        <tr>
                            <th scope="row"><?php echo __('On website or backend ?', 'fhpc'); ?></th>
                            <td>
                                <select id="onAdmin" name="onAdmin">
        <?php
        $sel1 = '';
        $sel2 = '';
        if ($datas && $datas->onAdmin) {
            $sel2 = 'selected';
        } else {
            $sel1 = 'selected';
        }
        echo '<option value="0" ' . $sel1 . '>' . __('Frontend', 'fhpc') . '</value>';
        echo '<option value="1" ' . $sel2 . '>' . __('Backend', 'fhpc') . '</value>';
        ?>
                                </select>
                                <label for="onAdmin"> <span class="description"><?php echo __('On website or backend ?', 'fhpc'); ?></span> </label></td>
                        </tr>   

                        <tr>
                            <th scope="row"><?php echo __('Target DOM element', 'fhpc'); ?></th>
                            <td>
                                <span>
        <?php
        if ($datas && $datas->domElement != "") {
            echo __('Element selected', 'fhpc');
        }
        ?>
                                </span>
                                <a href="javascript:" onclick="fhpc_chooseItemTarget();" class="button-primary"><?php echo __('Selection', 'fhpc'); ?></a>
                                <input type="text" id="domElement" name="domElement" value="<?php
                                    if ($datas) {
                                        echo $datas->domElement;
                                    }
                                    ?>" />
                                <label> <span class="description"><?php echo __('Select a dom element', 'fhpc'); ?></span> </label></td>
                        </tr>

                        <tr>
                            <th scope="row"><?php echo __('Page url', 'fhpc'); ?></th>
                            <td>
                                <input id="page" type="text" name="page" placeholder="http://" value="<?php
                        if ($datas) {
                            echo $datas->page;
                        }
                        ?>">
                                <label for="page"> <span class="description"><?php echo __('Leave empty to apply tour to all pages', 'fhpc'); ?></span> </label></td>
                        </tr>  

                        <tr>
                            <th scope="row"><?php echo __('Activate on mobile ?', 'fhpc'); ?></th>
                            <td>
                                <select id="mobileEnabled" name="mobileEnabled"  onchange="ftb_mobileEnabledChanged();">
        <?php
        $sel1 = '';
        $sel2 = '';
        if ($datas && !$datas->mobileEnabled) {
            $sel2 = 'selected';
        } else {
            $sel1 = 'selected';
        }
        echo '<option value="1" ' . $sel1 . '>' . __('Yes', 'fhpc') . '</value>';
        echo '<option value="0" ' . $sel2 . '>' . __('No', 'fhpc') . '</value>';
        ?>
                                </select>
                                <label for="onAdmin"> <span class="description"><?php echo __('Is this tour enabled on mobile ?', 'fhpc'); ?></span> </label></td>
                        </tr>  

                        <tr>
                            <th scope="row"><?php echo __('Activate on mobile only ?', 'fhpc'); ?></th>
                            <td>
                                <select id="onlyMobile" name="onlyMobile">
        <?php
        $sel1 = '';
        $sel2 = '';
        if ($datas && $datas->onlyMobile) {
            $sel1 = 'selected';
        } else {
            $sel2 = 'selected';
        }
        echo '<option value="1" ' . $sel1 . '>' . __('Yes', 'fhpc') . '</value>';
        echo '<option value="0" ' . $sel2 . '>' . __('No', 'fhpc') . '</value>';
        ?>
                                </select>
                                <label for="onlyMobile"> <span class="description"><?php echo __('Do you want to show the tour on mobile devices only ?', 'fhpc'); ?></span> </label></td>
                        </tr>  


                        <tr>
                            <th scope="row"><?php _e('Roles allowed to see it', 'fhpc'); ?></th>
                            <td>
        <?php
        global $wp_roles;
        $allowedRoles = array();
        if ($datas && $datas->rolesAllowed != "") {
            $allowedRoles = explode(',', $datas->rolesAllowed);
        }
        $selected = '';
        foreach ($wp_roles->roles as $role) {
            if (in_array(strtolower($role['name']), $allowedRoles)) {
                $selected = 'checked';
            } else {
                $selected = '';
            }
            echo '<p><input name="rolesAllowed" value="' . strtolower($role['name']) . '"  type="checkbox" ' . $selected . ' /><span style="padding-left: 16px;">' . $role['name'] . '</span></p>';
        }
        if (!$datas || $datas->rolesAllowed == "") {
            $selected = 'checked';
        }
        echo '<p><input name="rolesAllowed" value=""  type="checkbox" ' . $selected . ' /><span style="padding-left: 16px;">' . __('Everybody', 'fhpc') . '</span></p>';
        ?>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"></th>
                            <td>
                                <input type="submit" value="<?php echo __('Save', 'fhpc'); ?>" class="button-primary"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
        <script>
            function changeOnAdmin() {
                if (jQuery('#onAdmin').val() == '1') {
                    jQuery('#form_step #page').val(document.location.href.substr(0, document.location.href.lastIndexOf('/')));
                } else {
                    jQuery('#form_step #page').val('');
                }
            }
            function checkStart() {
                if (jQuery('#start').val() == 'click') {
                    jQuery('#domElement').parent().parent().show();
                    jQuery('#onceTime').parent().parent().hide();
                } else {
                    jQuery('#domElement').parent().parent().hide();
                    jQuery('#onceTime').parent().parent().show();
                }
            }
            function ftb_mobileEnabledChanged() {
                if (jQuery('#mobileEnabled').val() == 1) {
                    jQuery('#onlyMobile').closest('tr').slideDown();
                } else {
                    jQuery('#onlyMobile').val(0);
                    jQuery('#onlyMobile').closest('tr').slideUp();
                }
            }
            function qc_process(e) {
                var error = false;
                jQuery('#title').removeClass('field-error');
                if (jQuery("#title").val().length < 3) {
                    error = true;
                    jQuery('#title').addClass('field-error');
                }
                if (!error) {
                    jQuery("#fhpc_response").hide();
                    var data = {action: "fhpc_step_save"};
                    jQuery('#form_step input, #form_step select').each(function () {
                        if (jQuery(this).attr('name') && jQuery(this).attr('name') != 'rolesAllowed') {
                            eval('data.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
                        }
                    });

                    var rolesAllowed = '';
                    jQuery('input[name=rolesAllowed]:checked').each(function () {
                        rolesAllowed += jQuery(this).val() + ',';
                    });
                    if (rolesAllowed.length > 0) {
                        rolesAllowed = rolesAllowed.substr(0, rolesAllowed.length - 1);
                    }
                    data.rolesAllowed = rolesAllowed;

                    if (jQuery('#id').val() > 0) {
                        eval('localStorage.removeItem(' + jQuery('#id').val() + ');');
                    }

                    jQuery.post(ajaxurl, data, function (response) {
                        jQuery("#fhpc_response").html('<div id="message" class="updated"><p>Helper <strong>saved</strong>.</p></div>');
                        jQuery("#fhpc_response").fadeIn(250);
                        jQuery('#id').val(response);
                        document.location.href = '#wpwrap';
                    });

                }
            }
            jQuery(document).ready(function () {
                jQuery('#onAdmin').change(changeOnAdmin);
                jQuery('#start').change(checkStart);
                checkStart();
            });

        </script>
        <?php
    }

    /**
     * save step
     * @return void
     */
    function step_save() {
        global $wpdb;
        $response = "Error, try again later.";
        $table_name = $wpdb->prefix . "fhpc_steps";
        $sqlDatas = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'action' && $key != 'id' && $key != 'pll_ajax_backend') {
                if ($key == 'page') {
                    if (strrpos($value, site_url()) === false) {
                        
                    } else {
                        if (strlen($value) > 0 && ($value == site_url() || $value == site_url() . '/' || $value == '/')) {
                            $value = '/';
                        } else {
                            $value = substr($value, strlen(site_url()) + 1);
                        }
                    }
                    if (substr($value, -2, 2) == '//') {
                        $value = substr($value, 0, -1);
                    }
                    $wpdb->query("UPDATE " . $wpdb->prefix . "fhpc_items SET page='$value' WHERE stepID=" . $_POST['id'] . " AND type!='tooltip' ");
                }
                $sqlDatas[$key] = sanitize_text_field(stripslashes($value));
            }
        }
        if ($_POST['id'] > 0) {
            $wpdb->update($table_name, $sqlDatas, array('id' => $_POST['id']));
            $response = $_POST['id'];
        } else {
            $rows_affected = $wpdb->insert($table_name, $sqlDatas);
            $lastid = $wpdb->insert_id;
            $response = $lastid;
        }
        echo $response;
        die();
    }

    /**
     * Return settings.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function getSettings() {
        global $wpdb;
        $table_name = $wpdb->prefix . "fhpc_settings";
        $settings = $wpdb->get_results("SELECT * FROM $table_name WHERE id=1 LIMIT 1");
        if (count($settings) > 0) {
            return $settings[0];
        } else {
            return false;
        }
    }

    /**
     * update CSS
     * @return void
     */
    private function updateCSS() {
        $settings = $this->getSettings();
        $colorsStyles = '
        .fhpc_tooltip,.fhpc_button,.fhpc_button:hover  {    
            background-color: ' . $settings->colorA . ';
        }
        #fhpc_closeHelperBtn, .fhpc_text h2 {
            color: ' . $settings->colorA . ' !important;
        }
        .fhpc_dialog h3 {
            color: ' . $settings->colorB . ' !important;
        }
        .fhpc_tooltip[data-position="bottom"] .fhpc_arrow{
            border-color: transparent transparent ' . $settings->colorA . ' transparent !important;            
        }
        .fhpc_tooltip[data-position="top"] .fhpc_arrow{
            border-color: ' . $settings->colorA . ' transparent transparent transparent !important;            
        }
        .fhpc_text {
            color: ' . $settings->colorC . ' !important;
        }';
        if (!$settings->useThemeFonts) {
            $colorsStyles .= '.fhpc_text,.fhpc_text h2,.fhpc_dialog h3 ,.fhpc_tooltip .fhpc_content {font-family: \'Lato\';}';
        }

        $fp = fopen(plugin_dir_path(__FILE__) . '../assets/css/colors.css', 'w');
        fwrite($fp, $colorsStyles);
        fclose($fp);
    }

    /**
     * Main Instance
     *
     *
     * @since 1.0.0
     * @static
     * @return Main instance
     */
    public static function instance($parent) {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($parent);
        }
        return self::$_instance;
    }

    // End instance()

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone() {
        _doing_it_wrong(__FUNCTION__, '', $this->parent->_version);
    }

// End __clone()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        _doing_it_wrong(__FUNCTION__, '', $this->parent->_version);
    }

// End __wakeup()
}
