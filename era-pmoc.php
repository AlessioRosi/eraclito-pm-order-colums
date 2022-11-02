<?php
/*
Plugin Name:  Payment Method Order Column
Plugin URI:   https://www.eraclito.it/applicazioni-web/poste-delivery-business-integrazione-woocommerce/
Description:  Add a column to to oder list to filter orders by payment method
Version:      1.6.0
Author:       Eraclito - Alessio Rosi 
Author URI:   https://www.eraclito.it
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
Domain Path:  /languages
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }
define('PMOC_VERSION', '1.6');

// register_activation
function pmoc_activation() {
 
}

register_activation_hook(__FILE__, 'pmoc_activation');

// register_deactivation
function pmoc_deactivation() {
 
}
register_deactivation_hook(__FILE__, 'pmoc_deactivation');


// Display new column on WooCommerce admin orders list (header)
function filter_manage_edit_shop_order_columns( $columns ) {    
    // Add new column after order status (4) column
    return array_slice( $columns, 0, 4, true )
    + array( 'order_payment_method' => __( 'Metodo di Pagamento', 'woocommerce' ) )
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
        $payment_method_title = $order->get_payment_method_title();
        
        // NOT empty
        if ( ! empty ( $payment_method ) ) {
			switch ($payment_method) {
				case 'cod':
					//echo '<img src="' . plugin_dir_url( __FILE__ ) .'/img/cash.svg" width="30" height="30">';
					echo "<mark  style='vertical-align:super;margin-right:5px' class='order-status tips' data-tip='".ucfirst( $payment_method_title )."'><span><a href='#'><img style='vertical-align:middle;' src='". plugin_dir_url( __FILE__ )."/img/cash.svg' width='30' height='30'></a></span></mark>";
					break;
				case 'bacs':
					echo "<mark  style='vertical-align:super;margin-right:5px' class='order-status tips' data-tip='".ucfirst( $payment_method_title )."'><span><a href='#'><img style='vertical-align:middle;' src='". plugin_dir_url( __FILE__ )."/img/wired-transfer.svg' width='30' height='30'></a></span></mark>";
					break;
				case 'cheque':
					echo "<mark  style='vertical-align:super;margin-right:5px' class='order-status tips' data-tip='".ucfirst( $payment_method_title )."'><span><a href='#'><img style='vertical-align:middle;' src='". plugin_dir_url( __FILE__ )."/img/bollettino.svg' width='30' height='30'></a></span></mark>";
					break;
				case 'paypal':
					echo "<mark  style='vertical-align:super;margin-right:5px' class='order-status tips' data-tip='".ucfirst( $payment_method_title )."'><span><a href='#'><img style='vertical-align:middle;' src='". plugin_dir_url( __FILE__ )."/img/credit-card.svg' width='30' height='30'></a></span></mark>";
					break;
				case 'stripe':
					echo "<mark  style='vertical-align:super;margin-right:5px' class='order-status tips' data-tip='".ucfirst( $payment_method_title )."'><span><a href='#'><img style='vertical-align:middle;' src='". plugin_dir_url( __FILE__ )."/img/stripe.svg' width='30' height='30'></a></span></mark>";
					break;					
				default:
					echo "<mark  style='vertical-align:super;margin-right:5px' class='order-status tips' data-tip='".ucfirst( $payment_method_title )."'><span><a href='#'><img style='vertical-align:middle;' src='". plugin_dir_url( __FILE__ )."/img/generic.svg' width='30' height='30'></a></span></mark>";
					break;

										
			}

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
        <option value="">' . __( 'Metodo di pagamento', 'woocommerce' ) . '</option>';

       // foreach ( $available_gateways as $key => $available_gateway ) {
       	foreach ( $available_gateways as $key ) {
         //   printf( '<option %s value="%s">%s</option>', $key === $current ? 'selected="selected"' : '', $key, ucfirst( $key ) );
         	   printf( '<option %s value="%s">%s</option>', $key->id === $current ? 'selected="selected"' : '', $key->id, $key->get_title() );
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


// Make custom column sortable
add_filter( "manage_edit-shop_order_sortable_columns", 'shop_order_column_meta_field_sortable' );
function shop_order_column_meta_field_sortable( $columns )
{
    $meta_key = '_payment_method';
    return wp_parse_args( array('order_payment_method' => $meta_key), $columns );
}


//update section

if( ! class_exists( 'era_pmocChecker' ) ) {

	class era_pmocChecker{

		public $plugin_slug;
		public $version;
		public $cache_key;
		public $cache_allowed;

		public function __construct() {

			$this->plugin_slug = plugin_basename( __DIR__ );
			$this->version = '1.6';
			$this->cache_key = 'eraclito_pmoc_upd';
			$this->cache_allowed = false;

			add_filter( 'plugins_api', array( $this, 'info' ), 20, 3 );
			add_filter( 'site_transient_update_plugins', array( $this, 'update' ) );
			add_action( 'upgrader_process_complete', array( $this, 'purge' ), 10, 2 );

		}

		public function request(){

			$remote = get_transient( $this->cache_key );

			if( false === $remote || ! $this->cache_allowed ) {

				$remote = wp_remote_get(
					'https://www.eraclito.it/pldnw/PMOC/info.json',
					array(
						'timeout' => 10,
						'headers' => array(
							'Accept' => 'application/json'
						)
					)
				);

				if(
					is_wp_error( $remote )
					|| 200 !== wp_remote_retrieve_response_code( $remote )
					|| empty( wp_remote_retrieve_body( $remote ) )
				) {
					return false;
				}

				set_transient( $this->cache_key, $remote, DAY_IN_SECONDS );

			}

			$remote = json_decode( wp_remote_retrieve_body( $remote ) );

			return $remote;

		}


		function info( $res, $action, $args ) {

			 print_r( $action );
			 print_r( $args );

			// do nothing if you're not getting plugin information right now
			if( 'plugin_information' !== $action ) {
				return $res;
			}

			// do nothing if it is not our plugin
			if( $this->plugin_slug !== $args->slug ) {
				return $res;
			}

			// get updates
			$remote = $this->request();

			if( ! $remote ) {
				return $res;
			}

			$res = new stdClass();

			$res->name = $remote->name;
			$res->slug = $remote->slug;
			$res->version = $remote->version;
			$res->tested = $remote->tested;
			$res->requires = $remote->requires;
			$res->author = $remote->author;
			$res->author_profile = $remote->author_profile;
			$res->download_link = $remote->download_url;
			$res->trunk = $remote->download_url;
			$res->requires_php = $remote->requires_php;
			$res->last_updated = $remote->last_updated;

			$res->sections = array(
				'description' => $remote->sections->description,
				'installation' => $remote->sections->installation,
				'changelog' => $remote->sections->changelog
			);

			if( ! empty( $remote->banners ) ) {
				$res->banners = array(
					'low' => $remote->banners->low,
					'high' => $remote->banners->high
				);
			}

			return $res;

		}

		public function update( $transient ) {

			if ( empty($transient->checked ) ) {
				return $transient;
			}

			$remote = $this->request();

			if(
				$remote
				&& version_compare( $this->version, $remote->version, '<' )
				&& version_compare( $remote->requires, get_bloginfo( 'version' ), '<=' )
				&& version_compare( $remote->requires_php, PHP_VERSION, '<' )
			) {
				$res = new stdClass();
				$res->slug = $this->plugin_slug;
				$res->plugin = plugin_basename( __FILE__ ); 
				$res->new_version = $remote->version;
				$res->tested = $remote->tested;
				$res->package = $remote->download_url;

				$transient->response[ $res->plugin ] = $res;

	    }

			return $transient;

		}

		public function purge( $upgrader, $options ){

			if (
				$this->cache_allowed
				&& 'update' === $options['action']
				&& 'plugin' === $options[ 'type' ]
			) {
				// just clean the cache when new plugin version is installed
				delete_transient( $this->cache_key );
			}

		}


	}

	new era_pmocChecker();

}




?>