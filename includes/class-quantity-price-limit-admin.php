<?php

if (!defined('ABSPATH')) {
    exit;
}

class QPLC_Quantity_Price_Limit_Admin {

    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        register_setting('quantity_price_limit_settings', 'qplc_quantity_price_limit_product_limits');
        register_setting('quantity_price_limit_settings', 'qplc_quantity_price_limit_cart_limits');

        add_settings_section('product_limits_section', __('Product Limits', 'wc-quantity-price-limit-for-cart'), array($this, 'qplc_product_limits_section_desc'), 'quantity_price_limit');
        add_settings_section('cart_limits_section', __('Cart Limits', 'wc-quantity-price-limit-for-cart'), array($this, 'qplc_cart_limits_section_desc'), 'quantity_price_limit');

        // Product Limits.
        add_settings_field('product_min_qty', __('Minimum Quantity', 'wc-quantity-price-limit-for-cart'), array($this, 'qplc_product_min_qty_callback'), 'quantity_price_limit', 'product_limits_section');
        add_settings_field('product_max_qty', __('Maximum Quantity', 'wc-quantity-price-limit-for-cart'), array($this, 'qplc_product_max_qty_callback'), 'quantity_price_limit', 'product_limits_section');
        add_settings_field('product_qty_step', __('Quantity Step', 'wc-quantity-price-limit-for-cart'), array($this, 'qplc_product_qty_step_callback'), 'quantity_price_limit', 'product_limits_section');

        // Cart Limits.
        add_settings_field('cart_min_qty', __('Minimum Quantity', 'wc-quantity-price-limit-for-cart'), array($this, 'qplc_cart_min_qty_callback'), 'quantity_price_limit', 'cart_limits_section');
        add_settings_field('cart_max_qty', __('Maximum Quantity', 'wc-quantity-price-limit-for-cart'), array($this, 'qplc_cart_max_qty_callback'), 'quantity_price_limit', 'cart_limits_section');
        add_settings_field('cart_min_total', __('Minimum Total', 'wc-quantity-price-limit-for-cart'), array($this, 'qplc_cart_min_total_callback'), 'quantity_price_limit', 'cart_limits_section');
        add_settings_field('cart_max_total', __('Maximum Total', 'wc-quantity-price-limit-for-cart'), array($this, 'qplc_cart_max_total_callback'), 'quantity_price_limit', 'cart_limits_section');
    }

    public function qplc_product_limits_section_desc() {
        echo '<p>' . esc_html__('Set the minimum and maximum limits for products. Restrictions will be applied to every product individually.', 'wc-quantity-price-limit-for-cart') . '</p>';
    }

    public function qplc_cart_limits_section_desc() {
        echo '<p>' . esc_html__('Set the minimum and maximum limits for the order. Restrictions will be applied to the order total.', 'wc-quantity-price-limit-for-cart') . '</p>';
    }

    public function qplc_product_min_qty_callback() {
        $options = get_option('qplc_quantity_price_limit_product_limits', array('min_qty' => ''));
        $value = $options['min_qty'];
        echo '<input type="number" name="qplc_quantity_price_limit_product_limits[min_qty]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . esc_html__('Set minimum quantity for each product. Keep it blank if you don’t want to set any rule for this.', 'wc-quantity-price-limit-for-cart') . '</p>';
    }

    public function qplc_product_max_qty_callback() {
        $options = get_option('qplc_quantity_price_limit_product_limits', array('max_qty' => ''));
        $value = $options['max_qty'];
        echo '<input type="number" name="qplc_quantity_price_limit_product_limits[max_qty]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . esc_html__('Set maximum quantity for each product. Keep it blank if you don’t want to set any rule for this.', 'wc-quantity-price-limit-for-cart') . '</p>';
    }

    public function qplc_product_qty_step_callback() {
        $options = get_option('qplc_quantity_price_limit_product_limits', array('qty_step' => ''));
        $value = $options['qty_step'];
        echo '<input type="number" name="qplc_quantity_price_limit_product_limits[qty_step]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . esc_html__('Each time the quantity is changed, it will be increased or decreased by this value. Keep it blank if you don’t want to set any rule for this.', 'wc-quantity-price-limit-for-cart') . '</p>';
    }

    public function qplc_cart_min_qty_callback() {
        $options = get_option('qplc_quantity_price_limit_cart_limits', array('min_qty' => ''));
        $value = $options['min_qty'];
        echo '<input type="number" name="qplc_quantity_price_limit_cart_limits[min_qty]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . esc_html__('Set minimum quantity for the order. Keep it blank if you don’t want to set any rule for this.', 'wc-quantity-price-limit-for-cart') . '</p>';
    }

    public function qplc_cart_max_qty_callback() {
        $options = get_option('qplc_quantity_price_limit_cart_limits', array('max_qty' => ''));
        $value = $options['max_qty'];
        echo '<input type="number" name="qplc_quantity_price_limit_cart_limits[max_qty]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . esc_html__('Set maximum quantity for the order. Keep it blank if you don’t want to set any rule for this.', 'wc-quantity-price-limit-for-cart') . '</p>';
    }

    public function qplc_cart_min_total_callback() {
        $options = get_option('qplc_quantity_price_limit_cart_limits', array('min_total' => ''));
        $value = $options['min_total'];
        echo '<input type="number" name="qplc_quantity_price_limit_cart_limits[min_total]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . esc_html__('Set minimum order total. Keep it blank if you don’t want to set any rule for this.', 'wc-quantity-price-limit-for-cart') . '</p>';
    }

    public function qplc_cart_max_total_callback() {
        $options = get_option('qplc_quantity_price_limit_cart_limits', array('max_total' => ''));
        $value = $options['max_total'];
        echo '<input type="number" name="qplc_quantity_price_limit_cart_limits[max_total]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . esc_html__('Set maximum order amount. Keep it blank if you don’t want to set any rule for this.', 'wc-quantity-price-limit-for-cart') . '</p>';
    }
}

?>
