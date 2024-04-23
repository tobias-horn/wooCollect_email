<?php


class Newsletter_Optin_List_Table extends WP_List_Table {
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $this->table_data();
    }

    public function display_title() {
        echo '<h2>E-Mail Adressen Tabelle</h2>';
    }

    public function get_columns() {
        $columns = array(
            'email' => 'E-mail',
            'name' => 'Name',
            'order_date' => 'Letzte',
            'order_id' => 'Bestell-ID',
            'actions' => 'Aktionen'
        );
        if (get_option('newsletter_optin_clv_enable') == '1') {
            $columns['clv'] = 'Customer Lifetime Value';
        }
        return $columns;
    }

    public function get_hidden_columns() {
        return array();
    }

    public function get_sortable_columns() {
        return array(
            'email' => array('email', false),
            'name' => array('name', false),
            'order_date' => array('order_date', false),
            'order_id' => array('order_id', false)
        );
    }

    private function table_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'newsletter_optin';
        $data = array();

        // The query now includes a WHERE clause to filter by type "order-based"
        $query = "SELECT * FROM {$table_name} ORDER BY order_id DESC";
        $results = $wpdb->get_results($query, ARRAY_A);

        foreach ($results as $row) {
            $order = wc_get_order($row['order_id']);
            $order_date = $order ? $order->get_date_created()->date('d.m.Y') : 'Bestellung nicht gefunden';

            $entry = array(
                'email' => $row['email'],
                'name' => $row['name'],
                'order_date' => $order_date,
                'order_id' => sprintf('<a href="post.php?post=%d&action=edit">%d</a>', $row['order_id'], $row['order_id']),
                'actions' => sprintf('<a href="?page=email_optins&action=delete&email=%s" class="button" onclick="return confirm(\'Bist du sicher, dass du diese E-Mail Adresse löschen möchtest?\')">Löschen</a>', urlencode($row['email']))
            );

            if (get_option('newsletter_optin_clv_enable') == '1') {
                $entry['clv'] = $this->get_customer_lifetime_value($row['email']);
            }

            $data[] = $entry;
        }

        return $data;
    }

    private function get_customer_lifetime_value($email) {
        global $wpdb;
        $total_spent = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(meta_value)
            FROM {$wpdb->postmeta} pm
            JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_order_total' 
            AND p.post_status = 'wc-completed' 
            AND p.post_type = 'shop_order'
            AND EXISTS (
                SELECT * FROM {$wpdb->postmeta} 
                WHERE post_id = p.ID 
                AND meta_key = '_billing_email' 
                AND meta_value = %s
            )",
            $email
        ));
        return is_null($total_spent) ? wc_price(0) : wc_price($total_spent);
    }

    public function column_default($item, $column_name) {
        return isset($item[$column_name]) ? $item[$column_name] : 'Not set';
    }
}
