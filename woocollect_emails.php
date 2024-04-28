<?php
/**
 * Plugin Name: WooCollect Emails
 * Plugin URI:  
 * Description: Fügt die Option hinzu im WooCommerce Checkout unkompliziert ein Optin für E-Mail-Marketing einzubauen.
 * Version:     1.0
 * Author:      Tobias Horn
 * Author URI:  http://sektor3.digital
 * Text Domain: woocollect-emails
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Include functions and classes
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-newsletter-optin-list-table.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-newsletter-optin-list-table-manual.php';
require_once plugin_dir_path(__FILE__) . 'public/checkout_checkboxes.php'; // Include the checkout checkbox setup
require_once plugin_dir_path(__FILE__) . 'public/unsubscribe.php'; 


// Activation Hooks
register_activation_hook(__FILE__, 'create_newsletter_optin_table');
register_activation_hook(__FILE__, 'create_newsletter_optin_table_manual');

