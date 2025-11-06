<?php
/**
 * Plugin Name:       Colonna Metodo di Pagamento Ordini
 * Plugin URI:        https://www.eraclito.it/applicazioni-web/poste-delivery-business-integrazione-woocommerce/
 * Description:       Aggiunge una colonna per filtrare gli ordini in base al metodo di pagamento
 * Version:           3.0.0
 * Author:            Eraclito - Alessio Rosi
 * Author URI:        https://www.eraclito.it
 * License:           GPL2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       payment-method-order-column
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * WC requires at least: 4.0
 * WC tested up to:   10.3.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'ERPMOC_VERSION', '3.0.0' );
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
	 * Default payment method icons mapping
	 *
	 * @var array
	 */
	private $default_payment_icons = array(
		'cod'     => 'cash.svg',
		'bacs'    => 'wired-transfer.svg',
		'cheque'  => 'bollettino.svg',
		'paypal'  => 'credit-card.svg',
		'stripe'  => 'stripe.svg',
		'default' => 'generic.svg',
	);

	/**
	 * Settings option name
	 *
	 * @var string
	 */
	private $option_name = 'er_pmoc_settings';

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
		// Load text domain for translations.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Declare HPOS compatibility.
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );

		// Check if WooCommerce is active.
		add_action( 'plugins_loaded', array( $this, 'check_woocommerce_active' ) );

		// Add settings page.
		add_action( 'admin_menu', array( $this, 'add_settings_menu' ), 99 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

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
	 * Load plugin text domain for translations
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'payment-method-order-column',
			false,
			dirname( plugin_basename( ERPMOC_PLUGIN_FILE ) ) . '/languages/'
		);
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
						__( '<strong>Payment Method Order Column</strong> richiede che %s sia installato e attivo.', 'payment-method-order-column' ),
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
			+ array( $this->column_id => __( 'Metodo di Pagamento', 'payment-method-order-column' ) )
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
			echo esc_html__( 'N/D', 'payment-method-order-column' );
			return;
		}

		$payment_method       = $order->get_payment_method();
		$payment_method_title = $order->get_payment_method_title();

		if ( empty( $payment_method ) ) {
			echo esc_html__( 'N/D', 'payment-method-order-column' );
			return;
		}

		echo wp_kses_post( $this->render_payment_icon( $payment_method, $payment_method_title ) );
	}

	/**
	 * Get payment method icons (custom or default)
	 *
	 * @return array Payment method icons.
	 */
	private function get_payment_icons() {
		$settings = get_option( $this->option_name, array() );
		$icons    = isset( $settings['icons'] ) ? $settings['icons'] : array();

		// Merge with defaults.
		return wp_parse_args( $icons, $this->default_payment_icons );
	}

	/**
	 * Render payment method icon
	 *
	 * @param string $payment_method       Payment method ID.
	 * @param string $payment_method_title Payment method title.
	 * @return string HTML output.
	 */
	private function render_payment_icon( $payment_method, $payment_method_title ) {
		$payment_icons = $this->get_payment_icons();
		$icon_file     = isset( $payment_icons[ $payment_method ] )
			? $payment_icons[ $payment_method ]
			: $payment_icons['default'];

		// Check if it's a custom uploaded icon (URL) or default icon (filename).
		if ( filter_var( $icon_file, FILTER_VALIDATE_URL ) ) {
			$icon_url = $icon_file;
		} else {
			$icon_url = ERPMOC_PLUGIN_URL . 'img/' . $icon_file;
		}

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
		echo '<option value="">' . esc_html__( 'Filtra per metodo di pagamento', 'payment-method-order-column' ) . '</option>';

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
	 * Add settings menu under WooCommerce
	 */
	public function add_settings_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Impostazioni Colonna Metodo di Pagamento', 'payment-method-order-column' ),
			__( 'Colonna Pagamento', 'payment-method-order-column' ),
			'manage_woocommerce',
			'er-payment-method-column',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings
	 */
	public function register_settings() {
		register_setting(
			'er_pmoc_settings_group',
			$this->option_name,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);
	}

	/**
	 * Sanitize settings before saving
	 *
	 * @param array $input Raw input data.
	 * @return array Sanitized data.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		if ( isset( $input['icons'] ) && is_array( $input['icons'] ) ) {
			$sanitized['icons'] = array();
			foreach ( $input['icons'] as $method => $icon ) {
				$method_clean = sanitize_text_field( $method );

				// Skip empty values (they will use defaults).
				if ( empty( $icon ) ) {
					continue;
				}

				// Check if it's a URL or filename.
				if ( filter_var( $icon, FILTER_VALIDATE_URL ) ) {
					$sanitized['icons'][ $method_clean ] = esc_url_raw( $icon );
				} else {
					$sanitized['icons'][ $method_clean ] = sanitize_file_name( $icon );
				}
			}
		}

		return $sanitized;
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'woocommerce_page_er-payment-method-column' !== $hook ) {
			return;
		}

		// Enqueue WordPress media uploader.
		wp_enqueue_media();

		// Enqueue custom admin script.
		wp_enqueue_script(
			'er-pmoc-admin',
			ERPMOC_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			ERPMOC_VERSION,
			true
		);

		// Enqueue admin styles.
		wp_enqueue_style(
			'er-pmoc-admin',
			ERPMOC_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			ERPMOC_VERSION
		);
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Handle form submission.
		if ( isset( $_POST['er_pmoc_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['er_pmoc_settings_nonce'] ) ), 'er_pmoc_save_settings' ) ) {
			$settings = isset( $_POST[ $this->option_name ] ) ? $this->sanitize_settings( wp_unslash( $_POST[ $this->option_name ] ) ) : array();
			update_option( $this->option_name, $settings );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Impostazioni salvate con successo.', 'payment-method-order-column' ) . '</p></div>';
		}

		$settings         = get_option( $this->option_name, array() );
		$payment_icons    = isset( $settings['icons'] ) ? $settings['icons'] : array();
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Impostazioni Colonna Metodo di Pagamento', 'payment-method-order-column' ); ?></h1>
			<p><?php echo esc_html__( 'Personalizza le icone per ogni metodo di pagamento nella lista degli ordini.', 'payment-method-order-column' ); ?></p>

			<form method="post" action="">
				<?php wp_nonce_field( 'er_pmoc_save_settings', 'er_pmoc_settings_nonce' ); ?>

				<table class="form-table er-pmoc-settings-table">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'Metodo di Pagamento', 'payment-method-order-column' ); ?></th>
							<th><?php echo esc_html__( 'Icona Attuale', 'payment-method-order-column' ); ?></th>
							<th><?php echo esc_html__( 'Icona Personalizzata', 'payment-method-order-column' ); ?></th>
							<th><?php echo esc_html__( 'Azioni', 'payment-method-order-column' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						// Add default icon setting first.
						$this->render_icon_setting_row( 'default', __( 'Predefinita (Fallback)', 'payment-method-order-column' ), $payment_icons );

						// Add settings for each available gateway.
						foreach ( $available_gateways as $gateway ) {
							$this->render_icon_setting_row( $gateway->id, $gateway->get_title(), $payment_icons );
						}
						?>
					</tbody>
				</table>

				<?php submit_button( __( 'Salva Impostazioni', 'payment-method-order-column' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render a single icon setting row
	 *
	 * @param string $method_id    Payment method ID.
	 * @param string $method_title Payment method title.
	 * @param array  $payment_icons Saved payment icons.
	 */
	private function render_icon_setting_row( $method_id, $method_title, $payment_icons ) {
		$current_icon = isset( $payment_icons[ $method_id ] ) ? $payment_icons[ $method_id ] : '';

		// Get default icon.
		$default_icon = isset( $this->default_payment_icons[ $method_id ] ) ? $this->default_payment_icons[ $method_id ] : $this->default_payment_icons['default'];

		// Determine current icon URL for preview.
		if ( ! empty( $current_icon ) ) {
			if ( filter_var( $current_icon, FILTER_VALIDATE_URL ) ) {
				$preview_url = $current_icon;
			} else {
				$preview_url = ERPMOC_PLUGIN_URL . 'img/' . $current_icon;
			}
		} else {
			$preview_url = ERPMOC_PLUGIN_URL . 'img/' . $default_icon;
		}

		// Default icon URL for reset functionality.
		$default_icon_url = ERPMOC_PLUGIN_URL . 'img/' . $default_icon;
		?>
		<tr>
			<td><strong><?php echo esc_html( $method_title ); ?></strong><br><code><?php echo esc_html( $method_id ); ?></code></td>
			<td>
				<img src="<?php echo esc_url( $preview_url ); ?>" alt="<?php echo esc_attr( $method_title ); ?>" width="40" height="40" class="er-pmoc-icon-preview" data-method="<?php echo esc_attr( $method_id ); ?>" data-default-url="<?php echo esc_url( $default_icon_url ); ?>">
			</td>
			<td>
				<input type="hidden"
					   name="<?php echo esc_attr( $this->option_name ); ?>[icons][<?php echo esc_attr( $method_id ); ?>]"
					   id="er-pmoc-icon-<?php echo esc_attr( $method_id ); ?>"
					   value="<?php echo esc_attr( $current_icon ); ?>"
					   class="er-pmoc-icon-input">
				<span class="er-pmoc-icon-value">
					<?php
					if ( ! empty( $current_icon ) ) {
						echo esc_html( basename( $current_icon ) );
					} else {
						echo '<em>' . esc_html__( 'Uso icona predefinita', 'payment-method-order-column' ) . '</em>';
					}
					?>
				</span>
			</td>
			<td>
				<button type="button" class="button er-pmoc-upload-icon" data-method="<?php echo esc_attr( $method_id ); ?>">
					<?php echo esc_html__( 'Carica Icona', 'payment-method-order-column' ); ?>
				</button>
				<?php if ( ! empty( $current_icon ) ) : ?>
					<button type="button" class="button er-pmoc-reset-icon" data-method="<?php echo esc_attr( $method_id ); ?>">
						<?php echo esc_html__( 'Ripristina Predefinita', 'payment-method-order-column' ); ?>
					</button>
				<?php endif; ?>
			</td>
		</tr>
		<?php
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
			esc_html__( 'Questo plugin richiede che WooCommerce sia installato e attivo.', 'payment-method-order-column' ),
			esc_html__( 'Errore Attivazione Plugin', 'payment-method-order-column' ),
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