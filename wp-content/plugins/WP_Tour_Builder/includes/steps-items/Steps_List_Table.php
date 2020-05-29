<?php

if (!class_exists('WP_List_Table')) {
    require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Fhpc_Steps_List_Table extends WP_List_Table {

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {
        $columns = array('id' => __('ID','fhpc'), 'title' => __('Title','fhpc'), 'order' => __('Order','fhpc'), 'remove' => '');

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns() {
        return array('id','order');
        return null;
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns() {
        return null;
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . "fhpc_steps";
        $rows = $wpdb->get_results("SELECT * FROM $table_name ORDER BY ordersort ASC");

        $data = array();
        foreach ($rows as $row) {
            $data[] = array('id' => $row->id, 'title' => $row->title, 'order' => $row->ordersort, 'remove' => '');
        }
        return $data;
    }

    // Used to display the value of the id column
    public function column_id($item) {
        return $item['id'];
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'title':
                return '<a href="admin.php?page=fhpc-step-add&step=' . $item['id'] . '">' . $item[$column_name] . '</a><br/><div class="row-actions"><span class="edit"><a href="admin.php?page=fhpc-items&helper='.$item['id'].'" title="View steps">'.__('View steps', 'fhpc').'</a> </span><span class="edit"><a href="admin.php?page=fhpc-items&duplicate='.$item['id'].'" title="Duplicate">'.__('Duplicate', 'fhpc').'</a> </span></div>';
                break;

            case 'remove' :
                return '<a href="admin.php?page=fhpc-steps&remove=' . $item['id'] . '">Delete</a>';
                break;
            case 'id' :
            case 'order' :
                return $item[$column_name];

            default :
                return print_r($item, true);
        }
    }

}

?>