<?php
/*
 * Plugin Name: Min Max Step Control for WooCommerce 
 * Plugin URI: https://github.com/hannannexus
 * Description: Easily Control min max quantity of woocommerce products.
 * Author: Hannan
 * Author URI: https://github.com/hannannexus/
 * License: GPLv2 or later
 * Version: 1.0.2
 * Text Domain: min_max_step_control
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add custom tab to product data
add_filter('woocommerce_product_data_tabs', 'mmscw_add_new_tab', 10, 1);
function mmscw_add_new_tab($tabs) {
    $tabs['mmscw_tab'] = array(
        'label'    => __('Min Max Step', 'min_max_step_control'),
        'target'   => 'mmscw_product_data',
        'class'    => array('show_if_simple', 'show_if_variable'),
        'priority' => 25,
    );
    return $tabs;
}

// Add fields to the custom tab
add_action('woocommerce_product_data_panels', 'mmscw_add_settings_panel');
function mmscw_add_settings_panel() {
    global $post;
    ?>
    <div id="mmscw_product_data" class="panel woocommerce_options_panel">
        <div class="options_group">
            <?php
            woocommerce_wp_text_input(array(
                'id'                => '_mmscw_min_qty',
                'label'             => __('Minimum Quantity', 'min_max_step_control'),
                'desc_tip'          => true,
                'description'       => __('Set the minimum quantity for this product', 'min_max_step_control'),
                'type'              => 'number',
                'custom_attributes' => array(
                    'step' => '1',
                    'min'  => '0',
                ),
            ));

            woocommerce_wp_text_input(array(
                'id'                => '_mmscw_max_qty',
                'label'             => __('Maximum Quantity', 'min_max_step_control'),
                'desc_tip'          => true,
                'description'       => __('Set the maximum quantity for this product (0 = unlimited)', 'min_max_step_control'),
                'type'              => 'number',
                'custom_attributes' => array(
                    'step' => '1',
                    'min'  => '0',
                ),
            ));

            woocommerce_wp_text_input(array(
                'id'                => '_mmscw_qty_step',
                'label'             => __('Quantity Step', 'min_max_step_control'),
                'desc_tip'          => true,
                'description'       => __('Set the quantity step/increment for this product', 'min_max_step_control'),
                'type'              => 'number',
                'custom_attributes' => array(
                    'step' => '1',
                    'min'  => '1',
                ),
            ));
            ?>
        </div>
    </div>
    <?php
}

// Save the custom fields
add_action('woocommerce_process_product_meta', 'mmscw_save_custom_fields', 10, 1);
function mmscw_save_custom_fields($post_id) {
    
    // Save minimum quantity
    if (isset($_POST['_mmscw_min_qty'])) {
        update_post_meta($post_id, '_mmscw_min_qty', sanitize_text_field($_POST['_mmscw_min_qty']));
    }
    
    // Save maximum quantity
    if (isset($_POST['_mmscw_max_qty'])) {
        update_post_meta($post_id, '_mmscw_max_qty', sanitize_text_field($_POST['_mmscw_max_qty']));
    }
    
    // Save quantity step
    if (isset($_POST['_mmscw_qty_step'])) {
        update_post_meta($post_id, '_mmscw_qty_step', sanitize_text_field($_POST['_mmscw_qty_step']));
    }
}

// Optional: Add debug function to check if values are saved
add_action('admin_notices', 'mmscw_debug_notice');
function mmscw_debug_notice() {
    global $post;
    
    // Only show on product edit page
    if (isset($post) && $post->post_type == 'product' && isset($_GET['post'])) {
        $min = get_post_meta($post->ID, '_mmscw_min_qty', true);
        $max = get_post_meta($post->ID, '_mmscw_max_qty', true);
        $step = get_post_meta($post->ID, '_mmscw_qty_step', true);
        
        if ($min || $max || $step) {
            echo '<div class="notice notice-info"><p>';
            echo '<strong>Debug Info:</strong> ';
            echo 'Min: ' . esc_html($min) . ' | ';
            echo 'Max: ' . esc_html($max) . ' | ';
            echo 'Step: ' . esc_html($step);
            echo '</p></div>';
        }
    }
}

// ============ FRONTEND FUNCTIONALITY ============

// Apply min/max/step to quantity input arguments
add_filter('woocommerce_quantity_input_args', 'mmscw_quantity_input_args', 10, 2);
function mmscw_quantity_input_args($args, $product) {
    
    $product_id = $product->get_id();
    
    // Get custom values
    $min_qty = get_post_meta($product_id, '_mmscw_min_qty', true);
    $max_qty = get_post_meta($product_id, '_mmscw_max_qty', true);
    $step_qty = get_post_meta($product_id, '_mmscw_qty_step', true);
    
    // Apply minimum quantity
    if (!empty($min_qty) && $min_qty > 0) {
        $args['min_value'] = $min_qty;
        $args['input_value'] = $min_qty; // Set default value to minimum
    }
    
    // Apply maximum quantity (0 means unlimited)
    if (!empty($max_qty) && $max_qty > 0) {
        $args['max_value'] = $max_qty;
    }
    
    // Apply quantity step
    if (!empty($step_qty) && $step_qty > 0) {
        $args['step'] = $step_qty;
    }
    
    return $args;
}

// Validate quantity on add to cart
add_filter('woocommerce_add_to_cart_validation', 'mmscw_validate_add_to_cart', 10, 3);
function mmscw_validate_add_to_cart($passed, $product_id, $quantity) {
    
    $min_qty = get_post_meta($product_id, '_mmscw_min_qty', true);
    $max_qty = get_post_meta($product_id, '_mmscw_max_qty', true);
    $step_qty = get_post_meta($product_id, '_mmscw_qty_step', true);
    
    // Validate minimum quantity
    if (!empty($min_qty) && $quantity < $min_qty) {
        wc_add_notice(
            sprintf(__('The minimum quantity for this product is %s.', 'min_max_step_control'), $min_qty),
            'error'
        );
        return false;
    }
    
    // Validate maximum quantity
    if (!empty($max_qty) && $max_qty > 0 && $quantity > $max_qty) {
        wc_add_notice(
            sprintf(__('The maximum quantity for this product is %s.', 'min_max_step_control'), $max_qty),
            'error'
        );
        return false;
    }
    
    // Validate step quantity
    if (!empty($step_qty) && $step_qty > 0) {
        $min_value = !empty($min_qty) ? $min_qty : 1;
        if (($quantity - $min_value) % $step_qty !== 0) {
            wc_add_notice(
                sprintf(__('Please enter quantity in increments of %s.', 'min_max_step_control'), $step_qty),
                'error'
            );
            return false;
        }
    }
    
    return $passed;
}

// Update cart item quantity validation
add_filter('woocommerce_update_cart_validation', 'mmscw_update_cart_validation', 10, 4);
function mmscw_update_cart_validation($passed, $cart_item_key, $values, $quantity) {
    
    $product_id = $values['product_id'];
    
    $min_qty = get_post_meta($product_id, '_mmscw_min_qty', true);
    $max_qty = get_post_meta($product_id, '_mmscw_max_qty', true);
    $step_qty = get_post_meta($product_id, '_mmscw_qty_step', true);
    
    // Validate minimum quantity
    if (!empty($min_qty) && $quantity < $min_qty) {
        wc_add_notice(
            sprintf(__('The minimum quantity for this product is %s.', 'min_max_step_control'), $min_qty),
            'error'
        );
        return false;
    }
    
    // Validate maximum quantity
    if (!empty($max_qty) && $max_qty > 0 && $quantity > $max_qty) {
        wc_add_notice(
            sprintf(__('The maximum quantity for this product is %s.', 'min_max_step_control'), $max_qty),
            'error'
        );
        return false;
    }
    
    // Validate step quantity
    if (!empty($step_qty) && $step_qty > 0) {
        $min_value = !empty($min_qty) ? $min_qty : 1;
        if (($quantity - $min_value) % $step_qty !== 0) {
            wc_add_notice(
                sprintf(__('Please enter quantity in increments of %s.', 'min_max_step_control'), $step_qty),
                'error'
            );
            return false;
        }
    }
    
    return $passed;
}