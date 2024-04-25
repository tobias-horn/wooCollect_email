<?php

function create_newsletter_optin_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter_optin';

    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            email varchar(255) NOT NULL UNIQUE,
            name varchar(255) NOT NULL,
            order_id bigint(20) NOT NULL UNIQUE,
            PRIMARY KEY (order_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}


function create_newsletter_optin_table_manual() {
    global $wpdb;
    $table_name_manual = $wpdb->prefix . 'newsletter_optin_manual';

    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name_manual}'") != $table_name_manual) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name_manual (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL UNIQUE,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}




function save_newsletter_optin_checkbox($order_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter_optin';
    $order = wc_get_order($order_id);
    $email = $order->get_billing_email();
    $name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

    if (isset($_POST['newsletter_optin']) && $_POST['newsletter_optin'] === '1') {
        update_post_meta($order_id, 'newsletter_optin', 'yes');
        // Get the custom message set in the settings
        $message = get_option('newsletter_optin_message', '');
        // Add an order note with the custom message
        if (!empty($message)) {
            $note = "Der Kunde hat bestätigt: " . $message;
            $order->add_order_note($note);
        }
    } else {
        update_post_meta($order_id, 'newsletter_optin', 'no');
        return; // Exit if the checkbox was not checked
    }

    if ($existing_id) {
        $wpdb->update(
            $table_name,
            array('email' => $email),
            array('%d', '%s', '%s'),
            array('%s')
        );
    } else {
        $wpdb->insert(
            $table_name,
            array('email' => $email, 'name' => $name, 'order_id' => $order_id),
            array('%s', '%s', '%d', '%s')
        );
    }
}

add_action('woocommerce_checkout_update_order_meta', 'save_newsletter_optin_checkbox');



add_action('admin_menu', 'add_optin_emails_admin_page');
function add_optin_emails_admin_page() {
    add_submenu_page(
        'woocommerce-marketing',
        'E-Mail Opt-ins', // Page title
        'E-Mail Opt-ins', // Menu title
        'manage_woocommerce', // Capability
        'email_optins', // Menu slug
        'optin_emails_list_callback' // Callback function
    );
}

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

function handle_email_manual_insertion() {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'add_email_manually_action')) {
        wp_die('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('You are not allowed to perform this operation');
    }

    if (isset($_POST['email']) && is_email($_POST['email'])) {
        global $wpdb;
        $table_name_manual = $wpdb->prefix . 'newsletter_optin_manual';
        $email = sanitize_email($_POST['email']);
        
        if (!is_email($email)) {
            wp_redirect(add_query_arg('message', '3', wp_get_referer()));
            exit;
        }

        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name_manual WHERE email = %s", $email));

        if (!$exists) {
            $wpdb->insert(
                $table_name_manual,
                array('email' => $email),
                array('%s')
            );
            wp_redirect(add_query_arg('message', '1', wp_get_referer()));
        } else {
            wp_redirect(add_query_arg('message', '2', wp_get_referer()));
        }
        exit;
    } else {
        wp_redirect(add_query_arg('message', '3', wp_get_referer()));
        exit;
    }
}
add_action('admin_post_add_email_manually', 'handle_email_manual_insertion');


// Now hook to admin_post to handle form submission
add_action('admin_post_add_email_manually', 'handle_email_manual_insertion');





function optin_emails_list_callback() {
    echo '<div class="wrap"><h1>E-Mail Opt-ins</h1>';


    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter_optin';
    $table_name_manual = $wpdb->prefix . 'newsletter_optin_manual';

    // Concatenate emails from both tables
    $emails = $wpdb->get_col("SELECT email FROM $table_name UNION SELECT email FROM $table_name_manual");
    if (!empty($emails)) {
        $emails_str = implode(', ', $emails);
        echo '<h2>E-Mail Liste</h2>';
        echo '<p>Hier kannst du alle E-Mail Adressen direkt kopieren und mit einem Klick in einen E-Mail Client deiner Wahl einfügen.</p>';
        echo '<div style="display: flex; align-items: center; margin-bottom: 20px;">';
        echo '<strong>Emails:</strong>&nbsp;';
        echo '<input id="email_list" type="text" value="' . esc_attr($emails_str) . '" style="flex: 1; margin-right: 10px;" readonly>';
        echo '<button onclick="copyEmailsToClipboard()" class="button action">Kopieren</button>';
        echo '</div>';
        echo '<p id="copy_success_message" style="display:none; color: green;"></p>';
    }

    $list_table = new Newsletter_Optin_List_Table();
    $list_table->prepare_items();
    // Display the title before the table
    $list_table->display();


    echo '</div>'; // Close the wrap div


    echo '<h2>E-Mail Liste</h2>';
    
    
    // New form for adding email addresses manually
    ?>
    <div class="wrap">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="add_email_manually">
            <input type="text" name="email" placeholder="E-Mail Adresse eingeben" class="regular-text" required>
            <?php wp_nonce_field('add_email_manually_action'); ?>
            <?php submit_button('Kontakt hinzufügen', 'primary', 'submit_email', false); ?>
        </form>
    </div>
    <?php

$list_table_manual = new Newsletter_Optin_List_Table_Manual();
$list_table_manual->prepare_items();
// Display the title before the table
$list_table_manual->display();



    ?>
    <form method="post" action="options.php">
        <?php
        settings_fields('newsletter_optin_settings');
        do_settings_sections('email_optins');
        submit_button();
        ?>
    </form>
    <?php

    ?>
    <script>
    function copyEmailsToClipboard() {
        var copyText = document.getElementById('email_list');
        var message = document.getElementById('copy_success_message');
        copyText.select();
        document.execCommand('copy');
        message.textContent = 'In Zwischenablage kopiert!';
        message.style.display = 'block';
        setTimeout(function() { message.style.display = 'none'; }, 3000);
    }
    </script>
    <?php
}


// Check if our message is set and add the admin notice
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case '1':
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('E-Mail erfolgreich hinzugefügt.', 'text-domain'); ?></p>
            </div>
            <?php
            break;
        case '2':
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e('E-Mail existiert bereits.', 'text-domain'); ?></p>
            </div>
            <?php
            break;
        case '3':
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e('Ungültige E-Mail Adresse.', 'text-domain'); ?></p>
            </div>
            <?php
            break;
        default:
            // Optionally handle other cases
            break;
    }
}





// Initialize settings
add_action('admin_init', 'newsletter_optin_settings_init');
function newsletter_optin_settings_init() {
    register_setting('newsletter_optin_settings', 'newsletter_optin_enable');
    register_setting('newsletter_optin_settings', 'newsletter_optin_message');

    add_settings_section(
        'newsletter_optin_settings_section',
        'Einstellungen',
        'newsletter_optin_settings_section_cb',
        'email_optins'
    );

    add_settings_field(
        'newsletter_optin_enable',
        'E-Mail-Marketing Checkbox aktivieren',
        'newsletter_optin_enable_cb',
        'email_optins',
        'newsletter_optin_settings_section'
    );

    add_settings_field(
        'newsletter_optin_message',
        'Eigener Checkboxtext',
        'newsletter_optin_message_cb',
        'email_optins',
        'newsletter_optin_settings_section'
    );


    // Add the new setting field for CLV
    add_settings_field(
        'newsletter_optin_clv_enable',
        'Customer Lifetime Value (CLV) anzeigen',
        'newsletter_optin_clv_enable_cb',
        'email_optins',
        'newsletter_optin_settings_section'
    );

    // Register the new option
    register_setting('newsletter_optin_settings', 'newsletter_optin_clv_enable');
}

function newsletter_optin_settings_section_cb() {
    echo 'Hier kannst du das Verhalten des Plugins beeinflussen.';
}

function newsletter_optin_enable_cb() {
    $option = get_option('newsletter_optin_enable');
    echo '<input type="checkbox" id="newsletter_optin_enable" name="newsletter_optin_enable" value="1" ' . checked(1, $option, false) . '>';
}

function newsletter_optin_message_cb() {
    $message = get_option('newsletter_optin_message', 'Ja, ich möchte per E-Mail über neue Angebote und Produkte informiert werden.');
    echo '<input type="text" id="newsletter_optin_message" name="newsletter_optin_message" value="' . esc_attr($message) . '" style="width: 100%;">';
}

// Callback function for the CLV checkbox
function newsletter_optin_clv_enable_cb() {
    $clv_enabled = get_option('newsletter_optin_clv_enable');
    echo '<input type="checkbox" id="newsletter_optin_clv_enable" name="newsletter_optin_clv_enable" value="1" ' . checked(1, $clv_enabled, false) . '>';
}

function handle_delete_request() {
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['email']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_email')) {
        global $wpdb;
        $email = sanitize_email($_GET['email']);

        // Assume you have different tables for manual and automatic opt-ins as per your classes
        $tables = [$wpdb->prefix . 'newsletter_optin', $wpdb->prefix . 'newsletter_optin_manual'];

        foreach ($tables as $table) {
            $wpdb->delete($table, ['email' => $email], ['%s']);
        }

        // Redirect after deletion to avoid re-deletion on refresh
        wp_redirect(add_query_arg('page', 'email_optins', admin_url('admin.php')));
        exit;
    }
}
add_action('admin_init', 'handle_delete_request');



