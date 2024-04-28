<?php

// Initialize settings
add_action('admin_init', 'newsletter_optin_settings_init');
function newsletter_optin_settings_init() {
    register_setting('newsletter_optin_settings', 'newsletter_optin_enable');
    register_setting('newsletter_optin_settings', 'newsletter_optin_message');

    add_settings_section(
        'newsletter_optin_settings_section',
        'Einstellungen',
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
