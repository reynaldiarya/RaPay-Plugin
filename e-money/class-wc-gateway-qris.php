<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

/**
 * @package   RAPay - Bank dan e-Money Indonesia
 * @author    Reynaldi Arya
 * @category  Checkout Page
 * @copyright Copyright (c) 2021
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 **/

// Constructor for the gateway
class WC_Gateway_QRIS extends WC_Gateway_BACS {
	function __construct() {
		$this->id = "qris";
		$this->method_title = __( "QRIS", 'woocommerce' );
		$this->method_description = __( "Pembayaran melalui QRIS", 'woocommerce' );
		$this->title = __( "Transfer QRIS", 'woocommerce' );
        $this->icon = $this->enable_icon = 'yes' === $this->get_option( 'enable_icon' ) ? plugins_url('assets/logo-qris.png',__FILE__) : null;
		$this->has_fields = false;
		$this->init_form_fields();
		$this->init_settings();

		foreach ( $this->settings as $setting_key => $value ) {
          $this->$setting_key = $value;
      }
      if ( is_admin() ) {
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }
    add_action( 'woocommerce_thankyou_qris', array( $this, 'thankyou_page' ) );
    add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
     
 }

// Initialise Gateway Settings Form Fields
 public function init_form_fields() {
    $this->form_fields = array(
        'enabled' => array(
            'title'     => __( 'Enable / Disable', 'woocommerce' ),
            'label'     => __( 'Enable <strong>QRIS</strong> Transfer Payment Method', 'woocommerce' ),
            'type'      => 'checkbox',
            'default'   => 'no',
        ),
        'title' => array(
            'title'     => __( 'Title', 'woocommerce' ),
            'type'      => 'text',
            'desc_tip'  => __( 'Ini mengatur judul yang dilihat konsumen saat membayar', 'woocommerce' ),
            'default'   => __( 'Transfer QRIS', 'woocommerce' ),
        ),
        'enable_icon' => array(
            'title'         => __('Ikon Pembayaran', 'woocommerce'),
            'label'         => __('Enable Ikon', 'woocommerce'),
            'type'          => 'checkbox',
            'description'   => '<img src="'.plugins_url('assets/logo-qris.png',__FILE__).'" style="height:100%;max-height:32px !important" />',
            'default'       => 'no',
        ),
        'description' => array(
            'title'     => __( 'Description', 'woocommerce' ),
            'type'      => 'textarea',
            'desc_tip'  => __( 'Deskripsi metode pembayaran yang akan dilihat pelanggan di halaman checkout Anda.', 'woocommerce' ),
            'default'   => __( '', 'woocommerce' ),
            // 'css'       => 'max-width:350px;'
        ),
        'instructions' => array(
            'title'       => __( 'Instructions', 'woocommerce' ),
            'type'        => 'textarea',
            'description' => __( 'Instruksi yang akan ditambahkan ke halaman terima kasih dan email.', 'woocommerce' ),
            'default'     => '',
            'desc_tip'    => true,
        ),
        'kode_qr' => array(
	    'title'       => __( 'Link QRIS', 'woocommerce' ),
	    'type'        => 'text',	
	    'description' => __( 'Masukkan Link QRIS.', 'woocommerce' ),
	    'default'     => __( '', 'woocommerce' ),
	    'desc_tip'    => true,
	),
    );      
}	

// Get bank details and place into a list format
private function bank_details( $order_id = '' ) {

    if ( empty( $this->kode_qr ) ) {
        return;
    }

    // Get order and store in $order
    $order          = wc_get_order( $order_id );

    // Get the order country and country $locale
    $country        = $order->get_billing_country();
    $locale         = $this->get_country_locale();

    // Get sortcode label in the $locale array and use appropriate one
    $sortcode = isset( $locale[ $country ]['sortcode']['label'] ) ? $locale[ $country ]['sortcode']['label'] : __( 'Sort Code', 'woocommerce' );

    echo '<strong><h2>' . __( 'Detail Rekening Bank untuk Memproses Pembayaran:', 'woocommerce' ) . '</h2></strong>' . PHP_EOL;

    if( isset( $this->kode_qr ) && !empty( $this->kode_qr ) ){
		?>
			<img src="<?php echo esc_url( $this->kode_qr ); ?>" style="height:100%;max-height:500px;margin-bottom:35px !important" />',
		<?php 
	} 

}

// Output for the order received page
public function thankyou_page( $order_id ) {
    if ( $this->instructions ) {
        echo '<h3>Instruksi Pembayaran</h3>';
        echo wp_kses_post( wpautop( wptexturize( wp_kses_post( $this->instructions ) ) ) );
    }
    $this->bank_details( $order_id );
}

// Process the payment and return the result
public function process_payment( $order_id ) {

    $order = wc_get_order( $order_id );
    
    // Mark as on-hold (we're awaiting the payment)
    $order->update_status( 'on-hold', __( 'Menunggu Pembayaran melalui QRIS', 'woocommerce' ) );

    // Reduce stock levels
    $order->reduce_order_stock();

    // Remove cart
    WC()->cart->empty_cart();

    // Return thankyou redirect
    return array(
        'result'    => 'success',
        'redirect'  => $this->get_return_url( $order )
    );

}

// Email Instructions
public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {

    if ( ! $sent_to_admin && 'qris' === $order->get_payment_method() && $order->has_status( 'on-hold' ) ) {
        if ( $this->instructions ) {
            echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
        }
        $this->bank_details( $order->id );
    }


}


}


?>