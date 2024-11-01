<?php
/**
 * Plugin Name: Quantity & Price Limit for Cart
 * Plugin URI: https://technocrackers.com
 * Description: A plugin to set minimum and maximum quantity and price limits for WooCommerce products and cart.
 * Version: 1.0.0
 * Author: TechnoCrackers
 * Author URI: https://technocrackers.com
 * Text Domain: wc-quantity-price-limit-for-cart
 * License: GPLv2
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Main Plugin Class.
if (!class_exists('QPLC_Quantity_Price_Limit_For_Cart')) {

    class QPLC_Quantity_Price_Limit_For_Cart {

        public function __construct() {
            // Load Text Domain.
            add_action('plugins_loaded', array($this, 'qplc_load_textdomain'));

            // Check if WooCommerce is active.
            add_action('admin_init', array($this, 'qplc_check_woocommerce_active'));

            // Add admin menu.
            add_action('admin_menu', array($this, 'qplc_add_admin_menu'));

            add_action('admin_enqueue_scripts', array($this, 'qplc_enqueue_admin_css'));

            //HPOS compatibility
            add_action( 'before_woocommerce_init', array($this,'qplc_hpos_compatibility') );

            // Load admin and frontend classes.
            if (is_admin()) {
                require_once plugin_dir_path(__FILE__) . 'includes/class-quantity-price-limit-admin.php';
                new QPLC_Quantity_Price_Limit_Admin();
            } else {
                require_once plugin_dir_path(__FILE__) . 'includes/class-quantity-price-limit-frontend.php';
                new QPLC_Quantity_Price_Limit_Frontend();
            }
        }

        public function qplc_enqueue_admin_css(){
            wp_register_style( 'quantity-price-limit-cart-admin-css', plugin_dir_url(__FILE__) . 'css/admin-style.css', false, '1.0.0' );
            wp_enqueue_style( 'quantity-price-limit-cart-admin-css' );
        }

        public function qplc_load_textdomain() {
            load_plugin_textdomain('wc-quantity-price-limit-for-cart', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }

        public function qplc_check_woocommerce_active() {
            if (!is_plugin_active('woocommerce/woocommerce.php')) {
                add_action('admin_notices', array($this, 'qplc_notice_for_woocommerce_not_active'));
                deactivate_plugins(plugin_basename(__FILE__));
            }
        }

        public function qplc_notice_for_woocommerce_not_active() {
            echo '<div class="error"><p>' . esc_html__('Quantity & Price Limit for Cart requires WooCommerce to be active.', 'wc-quantity-price-limit-for-cart') . '</p></div>';
        }

        public function qplc_add_admin_menu() {
            add_submenu_page(
                'woocommerce',
                __('Quantity & Price Limit', 'wc-quantity-price-limit-for-cart'),
                __('Quantity & Price Limit', 'wc-quantity-price-limit-for-cart'),
                'manage_options',
                'wc-quantity-price-limit-for-cart',
                array($this, 'qplc_admin_settings_page')
            );
        }

        public function qplc_hpos_compatibility(){
            if( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 
                    'custom_order_tables', 
                    __FILE__, 
                    true
                );
            }
        }

        public function qplc_admin_settings_page() {
            echo '<div class="wrap">';
                echo '<h1>' . esc_html__('Quantity & Price Limit Settings', 'wc-quantity-price-limit-for-cart') . '</h1>';
                echo '<div class="main_plugin_div">';
                    echo '<form method="post" action="options.php">';
                        settings_fields('quantity_price_limit_settings');
                        do_settings_sections('quantity_price_limit');
                        submit_button();
                    echo '</form>';
                    echo '<div class="plugin-sidebar">';
                        echo '<div class="premium-img">';
                            echo '<img src="' .esc_attr( plugin_dir_url(__FILE__) . 'images/premium.png' ). '" alt="premium-features">';
                        echo '</div>';
                        echo '<div class="primium-btn">';
                            echo '<a target="_blank" href="' .esc_attr( 'https://technocrackers.com/wc-quantity-price-limit-for-cart/' ). '">' . esc_html__('Buy Premium') . '</a>';
                        echo '</div>';
                        echo '<h2>' . esc_html__('Premium Features') . '</h2>';
                        echo '<h3>' . esc_html__('Product Restrictions') . '</h3>';
                        echo '<ul>';
                            echo '<li><strong>' . esc_html__('Set Minimum Product Quantity') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Maximum Product Quantity') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Quantity Steps') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Minimum Total Price') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Maximum Total Price') . '</strong></li>';
                        echo '</ul>';
                        echo '<h3>' . esc_html__('Order Restrictions') . '</h3>';
                        echo '<ul>';
                            echo '<li><strong>' . esc_html__('Set Minimum Order Quantity') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Maximum Order Quantity') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Minimum Order Value') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Maximum Order Value') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Apply Category-Specific Limits') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Global and Local Rule Settings') . '</strong></li>';
                        echo '</ul>';
                        echo '<h3>' . esc_html__('Category Restrictions') . '</h3>';
                        echo '<ul>';
                            echo '<li><strong>' . esc_html__('Set Minimum Category Quantity') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Maximum Category Quantity') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Minimum Category Total Price') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Maximum Category Total Price') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Apply Specific Limits to Categories') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Enable Category Limits for Specific Categories') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Display Type Options') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Override Global Settings') . '</strong></li>';
                        echo '</ul>';

                        echo '<h3>' . esc_html__('Variation Restrictions') . '</h3>';
                        echo '<ul>';
                            echo '<li><strong>' . esc_html__('Set Minimum Variation Quantity') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Maximum Variation Quantity') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Quantity Steps for Variations') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Minimum Variation Total Price') . '</strong></li>';
                            echo '<li><strong>' . esc_html__('Set Maximum Variation Total Price') . '</strong></li>';
                        echo '</ul>';
                        echo '<div class="primium-btn">';
                            echo '<a target="_blank" href="' .esc_attr( 'https://technocrackers.com/wc-quantity-price-limit-for-cart/' ). '">' . esc_html__('Buy Premium') . '</a>';
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
            echo '</div>';
        }
    }

    new QPLC_Quantity_Price_Limit_For_Cart();
}

// Activation Hook.
register_activation_hook(__FILE__, 'qplc_quantity_price_limit_activate');
function qplc_quantity_price_limit_activate() {
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(esc_html__('Quantity & Price Limit for Cart requires WooCommerce to be active.', 'wc-quantity-price-limit-for-cart'));
    }

    $pro_version_plugin = 'quantity-price-limit-for-cart-pro/quantity-price-limit-for-cart-pro.php';
    if (is_plugin_active($pro_version_plugin)) {
        deactivate_plugins($pro_version_plugin);
    }

    // Initialize default options.
    $default_product_limits = array(
        'min_qty' => '',
        'max_qty' => '',
        'qty_step' => ''
    );
    $default_cart_limits = array(
        'min_qty' => '',
        'max_qty' => '',
        'min_total' => '',
        'max_total' => ''
    );

    if (get_option('qplc_quantity_price_limit_product_limits') === false) {
        add_option('qplc_quantity_price_limit_product_limits', $default_product_limits);
    }
    if (get_option('qplc_quantity_price_limit_cart_limits') === false) {
        add_option('qplc_quantity_price_limit_cart_limits', $default_cart_limits);
    }
}

// Deactivation Hook.
register_deactivation_hook(__FILE__, 'qplc_quantity_price_limit_deactivate');
function qplc_quantity_price_limit_deactivate() {
    // Clean up.
}
?>
