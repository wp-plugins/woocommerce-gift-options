<?php
/*
Plugin Name: WooCommerce Gift Options
Plugin URI: http://aheadzen.com/
Description: Add Send as Gift option on checkout page below Order note section.
Author: Aheadzen Team
Version: 1.0.0
Author URI: http://aheadzen.com/
*/

global $gift_packing_fees;
$gift_packing_fees = get_option( 'product_gift_packing_cost');
if(!$gift_packing_fees){$gift_packing_fees=50;}

// Init settings
$gift_paking_settings = array(
	array(
		'name' 		=> __( 'Gift Packing Cost', 'aheadzen' ),
		'desc' 		=> __( 'You can see the option for gift packing and cost of gift packing on checkout page. eg:50', 'aheadzen' ),
		'id' 		=> 'product_gift_packing_cost',
		'type' 		=> 'text',
		'desc_tip'  => true
	)
);
		
add_action('init','aheadzen_gift_pack_init');
function aheadzen_gift_pack_init()
{
	load_plugin_textdomain('aheadzen', false, basename( dirname( __FILE__ ) ) . '/languages');
}
add_action( 'woocommerce_after_order_notes', 'my_custom_checkout_field' );
function my_custom_checkout_field( $checkout ) {
 
    woocommerce_form_field( 'giftpack', array(
        'type'          => 'checkbox',
        'class'         => array('giftpack-checkbox form-row-wide'),
        'label'         => __('Send as Gift','aheadzen'),
		), $checkout->get_value( 'giftpack' ));
	?>
	<script>
	jQuery('.giftpack-checkbox input[type="checkbox"]').on('click', function () {
			jQuery("body").trigger("update_checkout");
	});
	</script>
	<?php
}

add_action( 'woocommerce_cart_calculate_fees', 'woo_add_cart_fee' );
function woo_add_cart_fee() { 
	global $woocommerce,$gift_packing_fees;
 	if($_POST){
		parse_str($_POST['post_data'],$data);
		if($data['giftpack'] || $_POST['giftpack']){
			$woocommerce->cart->add_fee( __('Gift Packing Charge', 'aheadzen'), $gift_packing_fees );
		}
	}
	
}

/**
 * Update the order meta with field value
 **/
add_action( 'woocommerce_checkout_update_order_meta', 'gift_pack_checkout_field_update_order_meta' ); 
function gift_pack_checkout_field_update_order_meta( $order_id ) {
	if($_POST['giftpack'])
	{
		update_post_meta( $order_id, 'giftpack',$_POST['giftpack']);
	}

}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'gift_pack_checkout_field_display_admin_order_meta', 10, 1 );
function gift_pack_checkout_field_display_admin_order_meta($order){
	if(get_post_meta( $order->id, 'giftpack', true ))
	{
		$giftpack = __('Yes','aheadzen');
	}else{
		$giftpack = __('No','aheadzen');
	}
   echo '<p><strong>'.__('Gift Packing Charge','aheadzen').':</strong> ' . $giftpack . '</p>';
}

add_action('woocommerce_email_after_order_table', 'gift_pack_checkout_field_order_meta_keys');
function gift_pack_checkout_field_order_meta_keys( $order ) {
	if(get_post_meta( $order->id, 'giftpack', true ))
	{
		$giftpack = __('Yes','aheadzen');
	}else{
		$giftpack = __('No','aheadzen');
	}
	printf(__('Gift Packing : %s'),$giftpack);
    return $keys;
}

add_action( 'woocommerce_settings_checkout_process_options', 'gift_pack_admin_settings');
add_action( 'woocommerce_update_options_checkout', 'gift_pack_save_admin_settings' );
		
function gift_pack_admin_settings() {
	global $gift_paking_settings;
	woocommerce_admin_fields( $gift_paking_settings );
}

function gift_pack_save_admin_settings() {
	global $gift_paking_settings;
	woocommerce_update_options( $gift_paking_settings );
}