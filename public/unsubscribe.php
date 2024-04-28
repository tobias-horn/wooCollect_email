<?php
// Add this to your theme's functions.php or to your plugin's files

function handle_unsubscribe_request() {
    if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['email']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'unsubscribe_nonce')) {
        global $wpdb;
        $email = sanitize_email($_POST['email']);
        $tables = [$wpdb->prefix . 'newsletter_optin', $wpdb->prefix . 'newsletter_optin_manual'];

        $found = false;
        foreach ($tables as $table) {
            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE email = %s", $email));
            if ($exists) {
                $wpdb->delete($table, ['email' => $email], ['%s']);
                $found = true;
            }
        }

        if ($found) {
            echo '<p>Du hast dich erfolgreich abgemeldet</p>';
        } else {
            echo '<p>Diese E-Mail Adresse wurde nicht gefunden. Bitte überprüfe deine Angaben.</p>';
        }
    }
}

add_shortcode('woo_emails_unsubscribe_form', 'unsubscribe_form_shortcode');
function unsubscribe_form_shortcode() {
    ob_start();
    ?>
    <style>
        .form-container label, .form-container input[type="email"], .form-container input[type="submit"] {
            display: block;
            margin-bottom: 10px; /* Adds space below each element */
        }
    </style>
    <form class="form-container" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
        <label for="email">Bitte geben deine E-Mail Adresse ein:</label>
        <input type="email" id="email" name="email" required>
        <?php wp_nonce_field('unsubscribe_nonce'); ?>
        <input type="submit" value="Newsletter abbestellen">
    </form>
    <?php
    handle_unsubscribe_request(); // Process the form submission
    return ob_get_clean();
}
?>
