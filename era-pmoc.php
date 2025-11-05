<?php
/**
 * Plugin Name:  Payment Method Order Column
 * Plugin URI:   https://www.eraclito.it/applicazioni-web/poste-delivery-business-integrazione-woocommerce/
 * Description:  Add a column to order list to filter orders by payment method
 * Version:      2.0.0
 * Author:       Eraclito - Alessio Rosi
 * Author URI:   https://www.eraclito.it
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  payment-method-order-column
 * Domain Path:  /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 4.0
 * WC tested up to: 8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'ERPMOC_VERSION', '2.0.0' );
define( 'ERPMOC_PLUGIN_FILE', __FILE__ );
define( 'ERPMOC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ERPMOC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class - Singleton pattern
 */
class ER_Payment_Method_Order_Column {

	/**
	 * Single instance of the class
	 *
	 * @var ER_Payment_Method_Order_Column
	 */
	private static $instance = null;

	/**
	 * Column ID
	 *
	 * @var string
	 */
	private $column_id = 'order_payment_method';

	/**
	 * Filter ID
	 *
	 * @var string
	 */
	private $filter_id = 'filter-by-payment';

	/**
	 * Screen ID for HPOS
	 *
	 * @var string
	 */
	private $screen_id = 'woocommerce_page_wc-orders';

	/**
	 * Payment method icons mapping
	 *
	 * @var array
	 */
	private $payment_icons = array(
		'cod'     => 'cash.svg',
		'bacs'    => 'wired-transfer.svg',
		'cheque'  => 'bollettino.svg',
		'paypal'  => 'credit-card.svg',
		'stripe'  => 'stripe.svg',
		'default' => 'generic.svg',
	);

	/**
	 * Get the singleton instance
	 *
	 * @return ER_Payment_Method_Order_Column
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor - Private to enforce singleton
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		// Declare HPOS compatibility.
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );

		// Check if WooCommerce is active.
		add_action( 'plugins_loaded', array( $this, 'check_woocommerce_active' ) );

		// Add custom column for both CPT and HPOS.
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_payment_method_column' ), 10, 1 );
		add_filter( 'manage_' . $this->screen_id . '_columns', array( $this, 'add_payment_method_column' ), 10, 1 );

		// Populate column for both CPT and HPOS.
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'populate_payment_method_column' ), 10, 2 );
		add_action( 'manage_' . $this->screen_id . '_custom_column', array( $this, 'populate_payment_method_column' ), 10, 2 );

		// Add filter dropdown for both CPT and HPOS.
		add_action( 'restrict_manage_posts', array( $this, 'add_payment_method_filter' ), 10, 2 );
		add_action( 'woocommerce_order_list_table_restrict_manage_orders', array( $this, 'add_payment_method_filter_hpos' ), 10, 1 );

		// Filter orders for both CPT and HPOS.
		add_filter( 'request', array( $this, 'filter_orders_by_payment_method' ), 10, 1 );
		add_filter( 'woocommerce_order_list_table_prepare_items_query_args', array( $this, 'filter_orders_by_payment_method_hpos' ), 10, 1 );

		// Make column sortable for both CPT and HPOS.
		add_filter( 'manage_edit-shop_order_sortable_columns', array( $this, 'make_column_sortable' ), 10, 1 );
		add_filter( 'manage_' . $this->screen_id . '_sortable_columns', array( $this, 'make_column_sortable' ), 10, 1 );
	}

	/**
	 * Declare HPOS compatibility
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', ERPMOC_PLUGIN_FILE, true );
		}
	}

	/**
	 * Check if WooCommerce is active
	 */
	public function check_woocommerce_active() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
		}
	}

	/**
	 * Display admin notice if WooCommerce is not active
	 */
	public function woocommerce_missing_notice() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: WooCommerce plugin name */
						__( '<strong>Payment Method Order Column</strong> requires %s to be installed and active.', 'payment-method-order-column' ),
						'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>'
					)
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Add payment method column to orders list
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_payment_method_column( $columns ) {
		// Add new column after order status (position 4).
		return array_slice( $columns, 0, 4, true )
			+ array( $this->column_id => __( 'Payment methods', 'woocommerce' ) )
			+ array_slice( $columns, 4, null, true );
	}

	/**
	 * Populate payment method column with data
	 *
	 * @param string $column  Column ID.
	 * @param int    $post_id Order ID.
	 */
	public function populate_payment_method_column( $column, $post_id ) {
		if ( $this->column_id !== $column ) {
			return;
		}

		$order = wc_get_order( $post_id );
		if ( ! $order ) {
			echo esc_html__( 'N/A', 'woocommerce' );
			return;
		}

		$payment_method       = $order->get_payment_method();
		$payment_method_title = $order->get_payment_method_title();

		if ( empty( $payment_method ) ) {
			echo esc_html__( 'N/A', 'woocommerce' );
			return;
		}

		echo wp_kses_post( $this->render_payment_icon( $payment_method, $payment_method_title ) );
	}

	/**
	 * Render payment method icon
	 *
	 * @param string $payment_method       Payment method ID.
	 * @param string $payment_method_title Payment method title.
	 * @return string HTML output.
	 */
	private function render_payment_icon( $payment_method, $payment_method_title ) {
		$icon_file = isset( $this->payment_icons[ $payment_method ] )
			? $this->payment_icons[ $payment_method ]
			: $this->payment_icons['default'];

		$icon_url = ERPMOC_PLUGIN_URL . 'img/' . $icon_file;

		return sprintf(
			'<mark class="order-status tips" style="vertical-align:super;margin-right:5px" data-tip="%s"><span><img style="vertical-align:middle;" src="%s" width="30" height="30" alt="%s"></span></mark>',
			esc_attr( ucfirst( $payment_method_title ) ),
			esc_url( $icon_url ),
			esc_attr( $payment_method_title )
		);
	}

	/**
	 * Add payment method filter dropdown (for CPT - legacy)
	 *
	 * @param string $post_type Current post type.
	 * @param string $which     Location of the extra table nav markup.
	 */
	public function add_payment_method_filter( $post_type, $which ) {
		global $pagenow;

		if ( 'shop_order' !== $post_type || 'edit.php' !== $pagenow ) {
			return;
		}

		$this->render_payment_method_dropdown();
	}

	/**
	 * Add payment method filter dropdown (for HPOS)
	 *
	 * @param string $order_type Order type.
	 */
	public function add_payment_method_filter_hpos( $order_type ) {
		$this->render_payment_method_dropdown();
	}

	/**
	 * Render the payment method dropdown filter
	 */
	private function render_payment_method_dropdown() {
		$current = isset( $_GET[ $this->filter_id ] ) ? sanitize_text_field( wp_unslash( $_GET[ $this->filter_id ] ) ) : '';

		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

		if ( empty( $available_gateways ) ) {
			return;
		}

		echo '<select name="' . esc_attr( $this->filter_id ) . '">';
		echo '<option value="">' . esc_html__( 'Payment methods', 'woocommerce' ) . '</option>';

		foreach ( $available_gateways as $gateway ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $gateway->id ),
				selected( $gateway->id, $current, false ),
				esc_html( $gateway->get_title() )
			);
		}

		echo '</select>';
	}

	/**
	 * Filter orders by payment method (for CPT - legacy)
	 *
	 * @param array $vars Query variables.
	 * @return array Modified query variables.
	 */
	public function filter_orders_by_payment_method( $vars ) {
		global $pagenow, $typenow;

		if ( 'edit.php' !== $pagenow || 'shop_order' !== $typenow ) {
			return $vars;
		}

		if ( ! isset( $_GET[ $this->filter_id ] ) ) {
			return $vars;
		}

		$payment_method = sanitize_text_field( wp_unslash( $_GET[ $this->filter_id ] ) );

		if ( empty( $payment_method ) ) {
			return $vars;
		}

		$vars['meta_key']   = '_payment_method';
		$vars['meta_value'] = $payment_method;

		return $vars;
	}

	/**
	 * Filter orders by payment method (for HPOS)
	 *
	 * @param array $args Query arguments.
	 * @return array Modified query arguments.
	 */
	public function filter_orders_by_payment_method_hpos( $args ) {
		if ( ! isset( $_GET[ $this->filter_id ] ) ) {
			return $args;
		}

		$payment_method = sanitize_text_field( wp_unslash( $_GET[ $this->filter_id ] ) );

		if ( empty( $payment_method ) ) {
			return $args;
		}

		// For HPOS, we need to filter using meta_query.
		if ( ! isset( $args['meta_query'] ) ) {
			$args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		$args['meta_query'][] = array(
			'key'   => '_payment_method',
			'value' => $payment_method,
		);

		return $args;
	}

	/**
	 * Make payment method column sortable
	 *
	 * @param array $columns Sortable columns.
	 * @return array Modified sortable columns.
	 */
	public function make_column_sortable( $columns ) {
		$columns[ $this->column_id ] = '_payment_method';
		return $columns;
	}

	/**
	 * Prevent cloning of the instance
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing of the instance
	 */
	public function __wakeup() {
		throw new Exception( 'Cannot unserialize singleton' );
	}
}

/**
 * Plugin activation hook
 */
function er_pmoc_activation() {
	// Check WooCommerce is active.
	if ( ! class_exists( 'WooCommerce' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'This plugin requires WooCommerce to be installed and active.', 'payment-method-order-column' ),
			esc_html__( 'Plugin Activation Error', 'payment-method-order-column' ),
			array( 'back_link' => true )
		);
	}
}
register_activation_hook( __FILE__, 'er_pmoc_activation' );

/**
 * Plugin deactivation hook
 */
function er_pmoc_deactivation() {
	// Cleanup if needed.
}
register_deactivation_hook( __FILE__, 'er_pmoc_deactivation' );

/**
 * Initialize the plugin
 */
function er_pmoc_init() {
	ER_Payment_Method_Order_Column::get_instance();
}
add_action( 'plugins_loaded', 'er_pmoc_init' );