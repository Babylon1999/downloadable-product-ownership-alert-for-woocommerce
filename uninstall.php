<?php


if ( !defined( 'ABSPATH' ) || !defined( 'WP_UNINSTALL_PLUGIN' )) {
    exit; // Exit if accessed directly
}

delete_option( 'downloadable_product_ownership_alert_for_woocommerce_text' );
delete_option( 'downloadable_product_ownership_alert_for_woocommerce_option' );
delete_option( 'downloadable_product_ownership_alert_for_woocommerce_text_download_link' );
delete_option( 'downloadable_product_ownership_alert_for_woocommerce_option_message_wrapper' );

?>