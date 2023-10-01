<?php

/**
 * Plugin Name: Downloadable Product Ownership Alert for WooCommerce
 * Description: Simple plugin that notifies customers if they already own the downloadable product they're viewing.
 * Version: 1.0.0
 * Author: Saif H. Hassan
 * Author URI: http://saif-hassan.com/
 * Text Domain: downloadable-product-ownership-alert-for-woocommerce
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

 if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


class DownloadableProductOwnershipAlert {
    public function __construct() {
        register_activation_hook( __FILE__, array($this, 'downloadable_product_ownership_alert_for_woocommerce_activate_function' ));

        add_filter('woocommerce_downloadable_products_settings', array($this, 'downloadable_product_ownership_alert_for_woocommerce_add_settings_fields'), 10, 2);

        // Retrieve the hook from the database
        $hook_option = get_option('downloadable_product_ownership_alert_for_woocommerce_option');

        // Add the action dynamically based on the retrieved option
        if (!empty($hook_option)) {
            add_action($hook_option, array($this, 'downloadable_product_ownership_alert_for_woocommerce_notice'));
        }
        if (!$this->should_downloadable_product_ownership_alert_for_woocommerce_activate_run()) {
            return; // Don't run the rest of the constructor
        }

    }

 // Check if the options are avalible in the database upon activation, if not, add them.
public function downloadable_product_ownership_alert_for_woocommerce_activate_function() {

    $options_to_add = array(
        "downloadable_product_ownership_alert_for_woocommerce_option" => "woocommerce_before_add_to_cart_form",
        "downloadable_product_ownership_alert_for_woocommerce_text" => "You already own this product!",
        "downloadable_product_ownership_alert_for_woocommerce_text_download_link" => "Download from order page!",
        "downloadable_product_ownership_alert_for_woocommerce_option_message_wrapper" => "yes"
    );
    
    foreach ($options_to_add as $option_name => $option_value) {
        if (!get_option($option_name)) {
            add_option($option_name, $option_value);
        }
    }
    
}
  // Don't run if WooCommerce-core is not active
    public function should_downloadable_product_ownership_alert_for_woocommerce_activate_run() {
        if ( ! function_exists( 'is_woocommerce_activated' ) ) {
            function is_woocommerce_activated() {
                if ( class_exists( 'woocommerce' ) ) { return true; } else { return false; }
            }
        }}
    
    //  Settings Page
    public function downloadable_product_ownership_alert_for_woocommerce_add_settings_fields($settings) {
        $all_the_settings = array();
    
        $all_the_settings[] = array(
            'name'     => __('Downloadable Product Ownership Alert for WooCommerce', 'downloadable-product-ownership-alert-for-woocommerce'),
            'type'     => 'title',
            'desc'     => __('The following options are used to configure the Downloadable Product Ownership Alert plugin. ', 'downloadable-product-ownership-alert-for-woocommerce'),
            'id'       => 'downloadable_product_ownership_alert_for_woocommerce'
        );
    
        $all_the_settings[] = array(
            'name'     => __('Notice Prefix', 'downloadable-product-ownership-alert-for-woocommerce'),
            'desc_tip' => __('This is the prefix text that will be before the link.', 'downloadable-product-ownership-alert-for-woocommerce'),
            'id'       => 'downloadable_product_ownership_alert_for_woocommerce_text',
            'type'     => 'text',
        );

        $all_the_settings[] = array(
            'name'     => __('Order Page Link', 'downloadable-product-ownership-alert-for-woocommerce'),
            'desc_tip' => __('When you click this link, you will be redirected to the order page.', 'downloadable-product-ownership-alert-for-woocommerce'),
            'id'       => 'downloadable_product_ownership_alert_for_woocommerce_text_download_link',
            'type'     => 'text',
        );
    
        $all_the_settings[] = array(
            'name'     => __('Notice Placement', 'downloadable-product-ownership-alert-for-woocommerce'),
            'desc_tip' => __('Choose where to show the button on the single product page.', 'downloadable-product-ownership-alert-for-woocommerce'),
            'id'       => 'downloadable_product_ownership_alert_for_woocommerce_option',
            'type'     => 'select',
            'options'  => array(
                'woocommerce_before_single_product'    => 'Before Single Product Page Template',
                'woocommerce_before_add_to_cart_form'  => 'Before Add to Cart Button',
                'woocommerce_product_meta_end' => 'After Product Meta',
            ),
            'desc'     => __('Select an option from the dropdown', 'downloadable_product_ownership_alert_for_woocommerce_option'),
        );

        $all_the_settings[] = array(
            'name'    => __( 'Wrap as a WooCommerce message?', 'downloadable-product-ownership-alert-for-woocommerce' ),
            'id'       => 'downloadable_product_ownership_alert_for_woocommerce_option_message_wrapper',
            'default'  => 'yes',
            'type'     => 'checkbox',
            'desc_tip' => __( 'When this option is selected, the notice will be wrapped as a WooCommerce message, similar to the one for cart notices.', 'downloadable-product-ownership-alert-for-woocommerce' ),
        );
    
        $all_the_settings[] = array('type' => 'sectionend', 'id' => 'downloadable_product_ownership_alert_for_woocommerce');
    
        return array_merge($settings, $all_the_settings);
    } 


    /* All the magic happens here */
    public function downloadable_product_ownership_alert_for_woocommerce_notice() {
        global $product;
    
        /* Check if the user is a guest, if true, we don't need to go further */
        if (!is_user_logged_in()) {
            return;
        }
    
        /* Get the customer orders */
        $current_user_id = get_current_user_id();
        $customers_available_downloads = wc_get_customer_available_downloads($current_user_id);
        
        $order_id = null; // Initialize order_id variable
    
        if (!empty($customers_available_downloads) || !$product->is_type('simple')) {
            foreach ($customers_available_downloads as $download) {
                if ($download['product_id'] === $product->get_id() && $download['downloads_remaining'] !== '0') {
                    $order_id = wc_get_order($download['order_id']);
                    break; // Exit the loop as we found a valid download
                }
            }
        }
        /* Show the notice */
        if ($order_id) {
            $text_prefix = get_option('downloadable_product_ownership_alert_for_woocommerce_text');
    
            $option_value = get_option('downloadable_product_ownership_alert_for_woocommerce_option_message_wrapper');
            $message_wrapper_class = $option_value === 'yes' ? 'woocommerce-message' : '';
    
            echo '<div class="downloadable_product_ownership_alert_for_woocommerce_text ' . $message_wrapper_class . '">' . $text_prefix .
                ' <a class="downloadable_product_ownership_alert_for_woocommerce_link" href="' . $order_id->get_view_order_url() . '">' . get_option('downloadable_product_ownership_alert_for_woocommerce_text_download_link') .
                '</a></div>';
        }
    }
    


}



new DownloadableProductOwnershipAlert();