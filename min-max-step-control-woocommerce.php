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


/*Settings for specific products */

/*Add setting for products*/
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

/* Add panel for min max step quantity control */

if( !function_exists("mmscw_add_settings_panel")){
  function mmscw_add_settings_panel(){
    ?>
      <div id="mmscw_settings_panel" class="panel woocommerce_options_panel hidden">
        <div id="quantity_control">
            <div class="min_qty">
               <?php
                  woocommerce_wp_text_input(array(
                    "id" => "mmscw_min_qty",
                    "label"=> __("Set Minimum Quantity","min_max_step_control"),
                    "value"=> get_post_meta(get_the_ID(),"mmscw_min_qty", true ),
                    "desc_tip"=> true,
                     "help_tip" => __("Set Minimum Quantity","min_max_step_control"),
                    "type"=>"number",
                    "step"=> 1,
                    "min"=>0,
                    "max"=>0
                  ));
              ?>
             </div>
          
               <div class="max_qty">
               <?php
                  woocommerce_wp_text_input(array(
                    "id" => "mmscw_max_qty",
                    "label"=> __("Set Maximum Quantity","min_max_step_control"),
                    "value" => get_post_meta(get_the_ID(),"mmscw_max_qty", true ),
                    "desc_tip"=> true,
                     "help_tip" => __("Set Maximum Quantity","min_max_step_control"),
                    "type"=>"number",
                    "step"=> 1,
                    "min"=>0,
                    "max"=>0
                  ));
              ?>
             </div>
          
              <div class="qty_step">
               <?php
                  woocommerce_wp_text_input(array(
                    "id" => "mmscw_qty_step",
                    "label"=> __("Set Quantity Step ","min_max_step_control"),
                    "value"=> get_post_meta(get_the_ID(),"mmscw_qty_step", true ),
                    "desc_tip"=> true,
                     "help_tip" => __("Set Quantity Step","min_max_step_control"),
                    "type"=>"number",
                    "step"=> 1,
                    "min"=>0,
                    "max"=>0
                  ));
              ?>
             </div>
        </div>
      </div>
    <?php
  }
}
add_action("woocommerce_product_data_panels","mmscw_add_settings_panel");

/* Save min max and step meta data */
if( !function_exists("mmscw_save_min_max_step_data")){
  function mmscw_save_min_max_step_data( $id, $post){
    
    $mmscw_min_qty = sanitize_text_field( $_POST["mmscw_min_qty"]);
    $mmscw_max_qty = sanitize_text_field( $_POST["mmscw_max_qty"]);
    $mmscw_qty_step = sanitize_text_field( $_POST["mmscw_qty_step"]);
    
    update_post_meta( $id, "mmscw_min_qty", $mmscw_min_qty, true);
    update_post_meta( $id, "mmscw_max_qty", $mmscw_min_qty, true);
    update_post_meta( $id, "mmscw_qty_step", $mmscw_min_qty, true);
  }
}
add_action( "woocommerce_process_product_meta","mmscw_save_min_max_step_data",333,2 );
