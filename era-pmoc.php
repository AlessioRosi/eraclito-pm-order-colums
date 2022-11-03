<?php
/*
Plugin Name:  Payment Method Order Column
Plugin URI:   https://www.eraclito.it/applicazioni-web/poste-delivery-business-integrazione-woocommerce/
Description:  Add a column to to oder list to filter orders by payment method
Version:      1.6.2
Author:       Eraclito - Alessio Rosi 
Author URI:   https://www.eraclito.it
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  payment-method-order-column
Domain Path:  /languages
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }
define('ERPMOC_VERSION', '1.6.1');

// register_activation
function er_pmoc_activation() {
 
}

register_activation_hook(__FILE__, 'er_pmoc_activation');

// register_deactivation
function er_pmoc_deactivation() {
 
}
register_deactivation_hook(__FILE__, 'er_pmoc_deactivation');


// Display new column on WooCommerce admin orders list (header)
function er_pmoc_filter_manage_edit_shop_order_columns( $columns ) {    
    // Add new column after order status (4) column
    return array_slice( $columns, 0, 4, true )
    + array( 'order_payment_method' => __( 'Metodo di Pagamento', 'woocommerce' ) )
    + array_slice( $columns, 4, NULL, true );
}
add_filter( 'manage_edit-shop_order_columns', 'er_pmoc_filter_manage_edit_shop_order_columns', 10, 1 );

// Display details after order status column, on order admin list (populate the column)
function er_pmoc_action_manage_shop_order_posts_custom_column( $column, $post_id ) {    
    // Compare
    if ( $column == 'order_payment_method' ) {
        // Get order
        $order = wc_get_order( $post_id );
    
        // Get the payment method
		$payment_method = $order->get_payment_method();
        $payment_method_title = $order->get_payment_method_title();
        
        // NOT empty
        if ( ! empty ( $payment_method ) ) {
			switch ($payment_method) {
				case 'cod':
					//echo '<img src="' . plugin_dir_url( __FILE__ ) .'/img/cash.svg" width="30" height="30">';
					echo "<mark  style='vertical-align:super;margin-right:5px' class='order-status tips' data-tip='".wp_kses_data(ucfirst( $payment_method_title ))."'><span><a href='#'><img style='vertical-align:middle;' src='". plugin_dir_url( __FILE__ )."/img/cash.svg' width='30' height='30'></a></span></mark>";
					break;
				case 'bacs':
					echo "<mark  style='vertical-align:super;margin-right:5px' class='order-status tips' data-tip='".wp_kses_data(ucfirst( $payment_method_title ))."'><span><a href='#'><img style='vertical-align:middle;' src='". plugin_dir_url( __FILE__ )."/img/wired-transfer.svg' width='30' height='30'></a></span></mark>";
					break;
				case 'cheque':
					echo "<mark  style='vertical-align:super;margin-right:5px' class='order-status tips' data-tip='".wp_kses_data(ucfirst( $payment_method_title ))."'><span><a href='#'><img style='vertical-align:middle;' src='". plugin_dir_url( __FILE__ )."/img/bollettino.svg' width='30' height='30'></a></span></mark>";
					break;
				case 'paypal':
					echo "<mark  style='vertical-align:super;margin-right:5px' class='order-status tips' data-tip='".wp_kses_data(ucfirst( $payment_method_title ))."'><span><a href='#'><img style='vertical-align:middle;' src='". plugin_dir_url( __FILE__ )."/img/credit-card.svg' width='30' height='30'></a></span></mark>";
					break;
				case 'stripe':
					echo "<mark  style='vertical-align:super;margin-right:5px' class='order-status tips' data-tip='".wp_kses_data(ucfirst( $payment_method_title ))."'><span><a href='#'><img style='vertical-align:middle;' src='". plugin_dir_url( __FILE__ )."/img/stripe.svg' width='30' height='30'></a></span></mark>";
					break;					
				default:
					echo "<mark  style='vertical-align:super;margin-right:5px' class='order-status tips' data-tip='".wp_kses_data(ucfirst( $payment_method_title ))."'><span><a href='#'><img style='vertical-align:middle;' src='". plugin_dir_url( __FILE__ )."/img/generic.svg' width='30' height='30'></a></span></mark>";
					break;

										
			}

        } else {
            echo __( 'N/A', 'woocommerce' );
        }
    }
}
add_action( 'manage_shop_order_posts_custom_column' , 'er_pmoc_action_manage_shop_order_posts_custom_column', 10, 2 );

// Add filter dropdown
function er_pmoc_action_restrict_manage_posts( $post_type, $which ) {   
    global $pagenow;

    // Compare
    if ( $post_type === 'shop_order' && $pagenow === 'edit.php' ) {
        // Filter ID
        $filter_id  = 'filter-by-payment';
        $payment_filter=sanitize_text_field($_GET[$filter_id]);
        $current    = isset( $payment_filter ) ? $payment_filter : '';
        
        // Get available gateways
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        // Create a drop-down list 
        echo '<select name="' . $filter_id . '">
        <option value="">' . __( 'Metodo di pagamento', 'woocommerce' ) . '</option>';

       // foreach ( $available_gateways as $key => $available_gateway ) {
       	foreach ( $available_gateways as $key ) {
         //   printf( '<option %s value="%s">%s</option>', $key === $current ? 'selected="selected"' : '', $key, ucfirst( $key ) );
         	   printf( '<option %s value="%s">%s</option>', $key->id === $current ? 'selected="selected"' : '', $key->id, $key->get_title() );
        }
        
        echo '</select>';
    }
}
add_action( 'restrict_manage_posts', 'er_pmoc_action_restrict_manage_posts', 10, 2 );

// Filter request
function er_pmoc_filter_request( $vars ) {
    global $pagenow, $typenow;

    // Filter ID
    $filter_id = 'filter-by-payment';
	$payment_filter=sanitize_text_field($_GET[$filter_id]);

    // Only on WooCommerce admin orders list
    if ( $pagenow == 'edit.php' && 'shop_order' === $typenow && isset($payment_filter ) && ! empty( $payment_filter ) ) {
        $vars['meta_key']   = '_payment_method';
        $vars['meta_value'] = $payment_filter;
        //$vars['orderby']    = 'meta_value';
    }
    
    return $vars;
}
add_filter( 'request', 'er_pmoc_filter_request', 10, 1 );


// Make custom column sortable
add_filter( "manage_edit-shop_order_sortable_columns", 'er_pmoc_shop_order_column_meta_field_sortable' );
function er_pmoc_shop_order_column_meta_field_sortable( $columns )
{
    $meta_key = '_payment_method';
    return wp_parse_args( array('order_payment_method' => $meta_key), $columns );
}

?>