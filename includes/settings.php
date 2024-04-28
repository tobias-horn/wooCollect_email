<?php

// Initialize settings
add_action('admin_init', 'newsletter_optin_settings_init');
function newsletter_optin_settings_init() {
    register_setting('newsletter_optin_settings', 'newsletter_optin_enable');
    register_setting('newsletter_optin_settings', 'newsletter_optin_message');

    add_settings_section(
        'newsletter_optin_settings_section',
        'Einstellungen',
        null,  // Removed callback here
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

    // Add new settings section titled "Opt-out"
    add_settings_section(
        'newsletter_optin_opt_out_section',
        'Opt-out',
        'newsletter_optin_opt_out_section_cb',
        'email_optins'
    );
}

function newsletter_optin_enable_cb() {
    $option = get_option('newsletter_optin_enable');
    echo '<input type="checkbox" id="newsletter_optin_enable" name="newsletter_optin_enable" value="1" ' . checked(1, $option, false) . '>';
}

function newsletter_optin_message_cb() {
    $message = get_option('newsletter_optin_message', 'Ja, ich möchte per E-Mail über neue Angebote und Produkte informiert werden.');
    echo '<input type="text" id="newsletter_optin_message" name="newsletter_optin_message" value="' . esc_attr($message) . '" style="width: 100%;">';
}

function newsletter_optin_clv_enable_cb() {
    $clv_enabled = get_option('newsletter_optin_clv_enable');
    echo '<input type="checkbox" id="newsletter_optin_clv_enable" name="newsletter_optin_clv_enable" value="1" ' . checked(1, $clv_enabled, false) . '>';
}

function newsletter_optin_opt_out_section_cb() {
    echo 'Du kannst mithilfe des Shortcodes \'woo_emails_unsubscribe_form\' ein abmelde Formular auf deiner Seite einfügen. Bitte vergiss nicht, den Link zu dieser Seite in deinen E-Mails hinzuzufügen';
}
