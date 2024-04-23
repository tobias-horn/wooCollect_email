<?php
function add_newsletter_optin_checkbox() {
    if (get_option('newsletter_optin_enable') != '1') {
        return; // Don't show the checkbox if not enabled
    }

    $message = get_option('newsletter_optin_message', 'Ja, ich möchte per E-Mail über neue Angebote und Produkte informiert werden.');
    woocommerce_form_field('newsletter_optin', array(
        'type' => 'checkbox',
        'class' => array('form-row mycheckbox'),
        'label_class' => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
        'input_class' => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
        'required' => false,
        'label' => __('<span class="woocommerce-terms-and-conditions-checkbox-text">' . esc_html($message) . '</span>'),
    ));
}

add_action('woocommerce_review_order_before_submit', 'add_newsletter_optin_checkbox', 10);
