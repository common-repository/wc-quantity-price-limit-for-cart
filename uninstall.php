<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options.
delete_option('qplc_quantity_price_limit_product_limits');
delete_option('qplc_quantity_price_limit_cart_limits');

?>
