<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/* @wordpress-plugin
 * Plugin Name: 			RAPay - Bank dan e-Money Indonesia
 * Plugin URI: 				https://wordpress.org/plugins/toko-ibest-bank-indonesia-for-woocommerce
 * Description: 			The WooCommerce Bank and e-Money Indonesia Payment Gateway plugin consists of several collections of banks and e-Money in Indonesia for WooCommerce payments.
 * Version: 				3.0.2
 * Author: 					Reynaldi Arya
 * Author URI: 				https://reynaldiab.com
 * Requires at least: 		4.1
 * Tested up to: 			6.0.2
 * WC requires at least: 	3.0.0
 * WC tested up to: 		7.0.0
 * License: 				GNU General Public License v3.0
 * License URI: 			http://www.gnu.org/licenses/gpl-3.0.html
 */

add_action( 'plugins_loaded', 'beipgw_init', 0 );
add_filter ( 'woocommerce_payment_gateways', 'add_beipgw_gateway' );
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'beipgw_plugin_action_links' );

function beipgw_plugin_action_links( $actions ) {
   $actions[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=wc-settings&tab=checkout') ) .'">Settings</a>';
   $actions[] = '<a href="https://trakteer.id/reynaldiarya/tip" target="_blank" style="color:#3db634;">Donate</a>';
   return $actions;
};

function beipgw_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

/**
 * Add an option on Payments setting panel
 **/

	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-bni.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-bca.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-bri.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-mandiri.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-jago.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-cimb-niaga.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-citibank.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-digibank.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-hsbc.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-jenius.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-neo-commerce.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-danamon.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-btn.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-bsi.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-permata.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-ocbc-nisp.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-muamalat.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-tmrw.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-line-bank.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-seabank.php';
	require_once dirname( __FILE__ ) . '/bank/class-wc-gateway-allo-bank.php';
	require_once dirname( __FILE__ ) . '/e-money/class-wc-gateway-ovo.php';
	require_once dirname( __FILE__ ) . '/e-money/class-wc-gateway-gopay.php';
	require_once dirname( __FILE__ ) . '/e-money/class-wc-gateway-dana.php';
	require_once dirname( __FILE__ ) . '/e-money/class-wc-gateway-linkaja.php';
	require_once dirname( __FILE__ ) . '/e-money/class-wc-gateway-shopeepay.php';
	require_once dirname( __FILE__ ) . '/e-money/class-wc-gateway-qris.php';
}

function add_beipgw_gateway( $methods ) {
	$methods[] = 'WC_Gateway_BNI';
	$methods[] = 'WC_Gateway_BCA';
	$methods[] = 'WC_Gateway_BRI';
	$methods[] = 'WC_Gateway_Mandiri';
	$methods[] = 'WC_Gateway_Jago';
	$methods[] = 'WC_Gateway_CIMB_Niaga';
	$methods[] = 'WC_Gateway_Citibank';
	$methods[] = 'WC_Gateway_Digibank';
	$methods[] = 'WC_Gateway_HSBC';
	$methods[] = 'WC_Gateway_Jenius';
	$methods[] = 'WC_Gateway_Neo_Commerce';
	$methods[] = 'WC_Gateway_Danamon';
	$methods[] = 'WC_Gateway_BTN';
	$methods[] = 'WC_Gateway_BSI';
	$methods[] = 'WC_Gateway_Permata';
	$methods[] = 'WC_Gateway_OCBC_NISP';
	$methods[] = 'WC_Gateway_Muamalat';
	$methods[] = 'WC_Gateway_TMRW';
	$methods[] = 'WC_Gateway_Line_Bank';
	$methods[] = 'WC_Gateway_Seabank';
	$methods[] = 'WC_Gateway_Allo_Bank';
	$methods[] = 'WC_Gateway_GoPay';
	$methods[] = 'WC_Gateway_OVO';
	$methods[] = 'WC_Gateway_Dana';
	$methods[] = 'WC_Gateway_LinkAja';
	$methods[] = 'WC_Gateway_ShopeePay';
	$methods[] = 'WC_Gateway_QRIS';

	return $methods;
}

/**
 * Add an option on Advanced tab setting panel
 **/

add_filter( 'woocommerce_get_sections_advanced', 'pcpgw_add_section' );
function pcpgw_add_section( $sections ) {

	$sections['puc'] = __( 'Kode Pembayaran', 'pcpgw' );
	return $sections;
}

/**
 * Add settings to the specific section we created before
 */

add_filter( 'woocommerce_get_settings_advanced', 'puc_all_settings', 10, 2 );
function puc_all_settings( $settings, $current_section ) {

	/**
	 * Check the current section is what we want
	 **/

	if ( $current_section == 'puc' ) {
		$settings_puc 		= array();
		// Add Title to the Settings
		$settings_puc[] 	= array(
			'name' 			=> __( 'Tambahkan Kode Pembayaran Unik', 'pcpgw' ),
			'type' 			=> 'title',
			'desc' 			=> __( 'Untuk dengan mudah mengonfirmasi pembayaran yang dilakukan oleh pelanggan Anda. Anda dapat menambahkan kode pembayaran 3 digit yang dibuat secara otomatis di halaman pembayaran Anda. Jika diaktifkan, kode 3 digit akan meningkatkan total pembayaran.', 'pcpgw' ),
			'id'   			=> 'puc',
		);
		// Build Text field option
		$settings_puc[] 	= array(
			'name'     		=> __( 'Enable / Disable', 'pcpgw' ),
			'type'     		=> 'checkbox',
			'desc'     		=> __( 'Aktifkan Kode Pembayaran', 'pcpgw' ),
			'desc_tip'     	=> __( 'Anda dapat memilih untuk mengaktifkan atau menonaktifkan kode pembayaran unik kapan saja.', 'pcpgw' ),
			'id'       		=> 'woocommerce_puc_enabled',
			'default'  		=> 'no',
		);

		$settings_puc[] 	= array(
			'name'    		=> __( 'Judul Kode Pembayaran', 'pcpgw' ),
			'type'     		=> 'text',
			'desc'     		=> __( 'Ubah judul default untuk opsi Kode Pembayaran', 'pcpgw' ),
			'id'       		=> 'woocommerce_puc_title',
			'placeholder'   => 'Kode Pembayaran',
		);

		$settings_puc[] = array(
			'name'          => __( 'Angka Minimal', 'pcpgw' ),
			'type'	        => 'number',
			'desc'          => __( 'Jumlah minimal penambahan kode pembayaran', 'pcpgw' ),
			'id'            => 'woocommerce_puc_min',
			'default'       => '1',
		);
		$settings_puc[] = array(
			'name'          => __( 'Angka Maksimal', 'pcpgw' ),
			'type'	        => 'number',
			'desc'          => __( 'Jumlah maximal penambahan kode pembayaran', 'pcpgw' ),
			'id'            => 'woocommerce_puc_max',
			'default'       => '999',
		);

		$settings_puc[] = array( 'type' => 'sectionend', 'id' => 'puc' );
		return $settings_puc;

	/**
	 * If not, return the standard settings
	 **/
} else {
	return $settings;
}
}

/**
 * Register Payment Code Function
 *
 * To easily identify customers' payments
 *
 * @return void
 */
if ( 'yes' == get_option( 'woocommerce_puc_enabled' ) ) {
	add_action( 'woocommerce_cart_calculate_fees', 'add_puc' );
	function add_puc(){
		global $woocommerce;

$enable = 1;
$min = get_option('woocommerce_puc_min') ;
$max = get_option('woocommerce_puc_max');
$title = '';
    	if(get_option( 'woocommerce_puc_title' )){
        	$title = (get_option( 'woocommerce_puc_title' ));
    }
    	else {
        	$title = __( 'Kode Pembayaran', 'pcpgw' );
    }

		if ( $enable == 1 && $woocommerce->cart->subtotal != 0){
			if(! is_cart()){
				$cost = rand($min, $max);

				if($cost != 0)
					$woocommerce->cart->add_fee( __($title, 'woocommerce'), $cost);
			}
		}
	}
}