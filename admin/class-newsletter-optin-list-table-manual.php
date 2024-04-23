<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Newsletter_Optin_List_Table_Manual extends WP_List_Table {
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $this->table_data();
    }

    public function display_title() {
        echo '<h2>Manuelle E-Mail Adressen Tabelle</h2>';
    }

    public function get_columns() {
        return array(
            'email' => 'E-mail',
            'actions' => 'Aktionen'
        );
    }

    public function get_hidden_columns() {
        return array();
    }

    public function get_sortable_columns() {
        return array(
            'email' => array('email', false)
        );
    }

    private function table_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'newsletter_optin_manual';
        $data = array();

        $query = "SELECT id, email FROM {$table_name} ORDER BY id DESC";
        $results = $wpdb->get_results($query, ARRAY_A);

        foreach ($results as $row) {
            $data[] = array(
                'email' => $row['email'],
                'actions' => sprintf('<a href="?page=email_optins&action=delete&email=%s" class="button" onclick="return confirm(\'Bist du sicher, dass du diese E-Mail Adresse löschen möchtest?\')">Löschen</a>', urlencode($row['email']))
            );
        }

        return $data;
    }

    public function column_default($item, $column_name) {
        return isset($item[$column_name]) ? $item[$column_name] : 'Not set';
    }
}
