<?php
/*
 * Plugin Name: Min Max Setp Control for WooCommerce 
 * Plugin URI: https://github.com/hannannexus
 * Description:  Easily Control min max quantity of woocommerce products.
 * Author: Hannan
 * Author URI: https://github.com/hannannexus/
 * License: License: GPLv2 or later
 * Version: 1.0.0
 * Text Domain: min_max_step_control
 */


/*Add setting on product tab*/
if( !function_exists("mmscw_add_new_tab")){
  function mmscw_add_new_tab( $tab ){
    $tab["mmscw_tab"] = array(
      "label"=> __("Min Max Step Control","min_max_step_control"),
      "target"=>"min_max_step_control_option",
      "class"=>['hide_if_external'],
      "priority"=> 25
    );
    
    return  $tab;
  }
}
add_action("woocommerce_product_data_tabs","mmscw_add_new_tab",222,1);
