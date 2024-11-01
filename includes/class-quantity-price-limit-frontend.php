<?php

if (!defined('ABSPATH')) {
    exit;
}

class QPLC_Quantity_Price_Limit_Frontend {

    public function __construct() {
        // Hooks for cart validation and product quantity limits.
        add_action('woocommerce_cart_has_errors', array(__CLASS__, 'qplc_output_errors'));
        add_filter('woocommerce_loop_add_to_cart_link', array(__CLASS__, 'qplc_add_to_cart_link'), 10, 2);
        add_filter('woocommerce_quantity_input_args', array(__CLASS__, 'qplc_set_quantity_args'), 10, 2);
        add_filter('woocommerce_add_to_cart_validation', array(__CLASS__, 'qplc_add_to_cart_validation'), 20, 4);
        add_action('woocommerce_check_cart_items', array(__CLASS__, 'qplc_check_cart_items'), 20);
        add_filter('woocommerce_add_to_cart_product_id', array(__CLASS__, 'qplc_set_cart_quantity'));
        add_filter('woocommerce_get_availability', array($this, 'qplc_maybe_show_backorder_message'), 10, 2);
        add_filter('woocommerce_available_variation', array(__CLASS__, 'qplc_available_variation'), 10, 3);

        // wc-cart Block compatibility.
        add_filter('woocommerce_store_api_product_quantity_multiple_of', array($this, 'qplc_filter_cart_item_quantity_multiple_of'), 10, 2);
        add_filter('woocommerce_store_api_product_quantity_minimum', array($this, 'qplc_filter_cart_item_quantity_minimum'), 10, 2);
        add_filter('woocommerce_store_api_product_quantity_maximum', array($this, 'qplc_filter_cart_item_quantity_maximum'), 10, 2);
    }

    public static function qplc_output_errors() {
        wc_print_notices();
    }

    public static function qplc_add_to_cart_link($link, $product) {
        // Modify the add to cart link if necessary.
        return $link;
    }

    public static function qplc_set_quantity_args($args, $product) {
      $product_limits = get_option('qplc_quantity_price_limit_product_limits', array('min_qty' => 1, 'max_qty' => 0, 'qty_step' => 1));
      $min_qty = (int) $product_limits['min_qty'];
      $max_qty = (int) $product_limits['max_qty'];
      $qty_step = max((int) $product_limits['qty_step'], 1);  // Ensure step is at least 1.

      $args['input_value'] = max($args['input_value'], $min_qty);
      $args['min_value'] = $min_qty;
      if ($max_qty > 0) {
          $args['max_value'] = $max_qty;
      }
      $args['step'] = $qty_step;

      // Ensure the initial value is a multiple of the step value.
      if (($args['input_value'] - $min_qty) % $qty_step !== 0) {
          $args['input_value'] = $min_qty;
      }

      return $args;
    }

    public static function qplc_add_to_cart_validation($passed, $product_id, $quantity, $variation_id = 0) {
      $product = wc_get_product($product_id);
      $product_name = $product->get_name();
      $product_sku = $product->get_sku();

      $product_limits = get_option('qplc_quantity_price_limit_product_limits', array('min_qty' => 1, 'max_qty' => 0, 'qty_step' => 1));
      $min_qty = (int) $product_limits['min_qty'];
      $max_qty = (int) $product_limits['max_qty'];
      $qty_step = max((int) $product_limits['qty_step'], 1);  // Ensure step is at least 1.

      if ($quantity < $min_qty) {
          wc_add_notice(sprintf(esc_html__('The minimum required quantity for %s (%s) is %d.', 'wc-quantity-price-limit-for-cart'), $product_name, $product_sku, $min_qty), 'error');
          $passed = false;
      }
      if ($max_qty > 0 && $quantity > $max_qty) {
          wc_add_notice(sprintf(esc_html__('The maximum allowed quantity for %s (%s) is %d.', 'wc-quantity-price-limit-for-cart'), $product_name, $product_sku, $max_qty), 'error');
          $passed = false;
      }
      if (($quantity - $min_qty) % $qty_step !== 0) {
          wc_add_notice(sprintf(esc_html__('You must purchase %s (%s) in quantities of %d.', 'wc-quantity-price-limit-for-cart'), $product_name, $product_sku, $qty_step), 'error');
          $passed = false;
      }
      return $passed;
    }

    public static function qplc_check_cart_items() {
      $cart_limits = get_option('qplc_quantity_price_limit_cart_limits', array('min_qty' => 1, 'max_qty' => 0, 'min_total' => 0, 'max_total' => 0));
      $min_qty = (int) $cart_limits['min_qty'];
      $max_qty = (int) $cart_limits['max_qty'];
      $min_total = (float) $cart_limits['min_total'];
      $max_total = (float) $cart_limits['max_total'];

      $cart_qty = 0;
      $cart_total = 0;

      foreach (WC()->cart->get_cart() as $cart_item) {
          $cart_qty += $cart_item['quantity'];
          $cart_total += $cart_item['line_total'];
      }

      if ($cart_qty < $min_qty) {
          wc_add_notice(sprintf(esc_html__('The minimum required quantity for the cart is %d items.', 'wc-quantity-price-limit-for-cart'), $min_qty), 'error');
      }

      if ($max_qty > 0 && $cart_qty > $max_qty) {
          wc_add_notice(sprintf(esc_html__('The maximum allowed quantity for the cart is %d items.', 'wc-quantity-price-limit-for-cart'), $max_qty), 'error');
      }

      if ($min_total > 0 && $cart_total < $min_total) {
          wc_add_notice(sprintf(esc_html__('The minimum order total is %s.', 'wc-quantity-price-limit-for-cart'), wc_price($min_total)), 'error');
      }

      if ($max_total > 0 && $cart_total > $max_total) {
          wc_add_notice(sprintf(esc_html__('The maximum order total is %s.', 'wc-quantity-price-limit-for-cart'), wc_price($max_total)), 'error');
      }
    }

    public static function qplc_set_cart_quantity($product_id) {
      // Adjust cart quantity if necessary.
      return $product_id;
    }

    public function qplc_maybe_show_backorder_message($availability, $product) {
      // Modify availability message if necessary.
      return $availability;
    }

    public static function qplc_available_variation($data, $product, $variation) {
      // Adjust available variation if necessary.
      return $data;
    }

    public function qplc_filter_cart_item_quantity_multiple_of($multiple_of, $product) {
      $product_limits = get_option('qplc_quantity_price_limit_product_limits', array('qty_step' => 1));
      return max((int) $product_limits['qty_step'], 1);  // Ensure step is at least 1.
    }

    public function qplc_filter_cart_item_quantity_minimum($minimum, $product) {
      $product_limits = get_option('qplc_quantity_price_limit_product_limits', array('min_qty' => 1));
      return (int) $product_limits['min_qty'];
    }

    public function qplc_filter_cart_item_quantity_maximum($maximum, $product) {
      $product_limits = get_option('qplc_quantity_price_limit_product_limits', array('max_qty' => 100));
      return (int) $product_limits['max_qty'];
    }
}

?>
