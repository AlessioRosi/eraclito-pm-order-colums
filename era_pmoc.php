<?php
/*
Plugin Name:  Payment Method Order Colums
Plugin URI:   https://www.eraclito.it/applicazioni-web/poste-delivery-business-integrazione-woocommerce/
Description:  Add a column to toder list to filter orders by payment method
Version:      1.1.0
Author:       Eraclito - Alessio Rosi 
Author URI:   https://www.eraclito.it
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
Domain Path:  /languages
*/


// Exit if accessed directly.
//if ( ! defined( 'ABSPATH' ) ) { exit; }
//define('PMOC_VERSION', '1.0');



// Display new column on WooCommerce admin orders list (header)
function filter_manage_edit_shop_order_columns( $columns ) {    
    // Add new column after order status (4) column
    return array_slice( $columns, 0, 4, true )
    + array( 'order_payment_method' => __( 'Payment method', 'woocommerce' ) )
    + array_slice( $columns, 4, NULL, true );
}
add_filter( 'manage_edit-shop_order_columns', 'filter_manage_edit_shop_order_columns', 10, 1 );

// Display details after order status column, on order admin list (populate the column)
function action_manage_shop_order_posts_custom_column( $column, $post_id ) {    
    // Compare
    if ( $column == 'order_payment_method' ) {
        // Get order
        $order = wc_get_order( $post_id );
    
        // Get the payment method
        $payment_method = $order->get_payment_method();
        
        // NOT empty
        if ( ! empty ( $payment_method ) ) {
            echo ucfirst( $payment_method );
        } else {
            echo __( 'N/A', 'woocommerce' );
        }
    }
}
add_action( 'manage_shop_order_posts_custom_column' , 'action_manage_shop_order_posts_custom_column', 10, 2 );

// Add filter dropdown
function action_restrict_manage_posts( $post_type, $which ) {   
    global $pagenow;

    // Compare
    if ( $post_type === 'shop_order' && $pagenow === 'edit.php' ) {
        // Filter ID
        $filter_id  = 'filter-by-payment';
        
        $current    = isset( $_GET[$filter_id] ) ? $_GET[$filter_id] : '';
        
        // Get available gateways
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        // Create a drop-down list 
        echo '<select name="' . $filter_id . '">
        <option value="">' . __( 'Filter by payment method', 'woocommerce' ) . '</option>';

        foreach ( $available_gateways as $key => $available_gateway ) {
            printf( '<option %s value="%s">%s</option>', $key === $current ? 'selected="selected"' : '', $key, ucfirst( $key ) );
        }
        
        echo '</select>';
    }
}
add_action( 'restrict_manage_posts', 'action_restrict_manage_posts', 10, 2 );

// Filter request
function filter_request( $vars ) {
    global $pagenow, $typenow;

    // Filter ID
    $filter_id = 'filter-by-payment';

    // Only on WooCommerce admin orders list
    if ( $pagenow == 'edit.php' && 'shop_order' === $typenow && isset( $_GET[$filter_id] ) && ! empty( $_GET[$filter_id] ) ) {
        $vars['meta_key']   = '_payment_method';
        $vars['meta_value'] = $_GET[$filter_id];
        //$vars['orderby']    = 'meta_value';
    }
    
    return $vars;
}
add_filter( 'request', 'filter_request', 10, 1 );





?>