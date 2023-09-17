<?php
/**
 * Plugin Name: Default Order Status for WooCommerce
 * Description: Allows users to choose the default order status in WooCommerce.
 * Version: 1.0
 * Author: Yeasin Arafat
 * Author URI: https://www.linkedin.com/in/yeasin-arafat1998/
 * Text Domain: dosfw
 */


function woocommerce_default_order_status_menu() {
    add_menu_page(
        __('Default Order Status', 'dosfw'),
        __('WooCommerce Order Status', 'dosfw'),
        'manage_options',
        'default-order-status-settings',
        'woocommerce_default_order_status_settings_page',
        'dashicons-screenoptions'
    );
}
add_action('admin_menu', 'woocommerce_default_order_status_menu');

// Function to display the options page
function woocommerce_default_order_status_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Get the current default order status option
    $default_order_status = get_option('default_order_status', 'completed');
    
    // Handle form submission
    if (isset($_POST['submit'])) {
        $default_order_status = sanitize_text_field($_POST['default_order_status']);
        update_option('default_order_status', $default_order_status);
        echo '<div class="updated"><p>' . __('Default order status updated.', 'dosfw') . '</p></div>';
    }
    ?>
    <div class="wrap">
        <h2><?php _e('Default Order Status For WooCommerce', 'dosfw'); ?></h2>
        <form method="post" action="">
            <label for="default_order_status"><?php _e('Select Default Order Status:', 'dosfw'); ?></label>
            <select name="default_order_status" id="default_order_status">
                <?php
                // Get a list of WooCommerce order statuses
                $order_statuses = wc_get_order_statuses();
                foreach ($order_statuses as $status => $label) {
                    echo '<option value="' . esc_attr($status) . '" ' . selected($default_order_status, $status, false) . '>' . esc_html($label) . '</option>';
                }
                ?>
            </select>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'dosfw'); ?>">
            </p>
        </form>
    </div>
    <?php
}

// Hook into the order status transition process
function auto_complete_orders($order_id) {
    // Get the default order status option
    $default_order_status = get_option('default_order_status', 'completed');

    // Get the order object
    $order = wc_get_order($order_id);

    // Check if the order status is not already the default status
    if ($order->get_status() !== $default_order_status) {
        // Set the order status to the default status
        $order->update_status($default_order_status);
    }
}

// Hook into order creation and updates
add_action('woocommerce_thankyou', 'auto_complete_orders');
add_action('woocommerce_order_status_changed', 'auto_complete_orders');
