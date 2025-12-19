<?php

/**
 * Class WC_Gateway_QRIS file.
 *
 * @package WooCommerce\Gateways
 */

use Automattic\WooCommerce\Enums\OrderStatus;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * QRIS Payment Gateway.
 *
 * Provides a QRIS Payment Gateway. Based on standard WooCommerce BACS.
 *
 * @class       WC_Gateway_QRIS
 * @extends     WC_Payment_Gateway
 * @version     4.0.0
 * @package     WooCommerce\Classes\Payment
 */
class WC_Gateway_QRIS extends WC_Payment_Gateway
{
    /**
     * Unique ID for this gateway.
     *
     * @var string
     */
    public const ID = 'qris';

    /**
     * Array of locales
     *
     * @var array
     */
    public $locale;

    /**
     * Gateway instructions that will be added to the thank you page and emails.
     *
     * @var string
     */
    public $instructions;

    /**
     * QR Code URL
     *
     * @var string
     */
    public $qr_code;

    /**
     * Constructor for the gateway.
     */
    public function __construct()
    {
        $this->id                 = self::ID;
        $show_icon = 'yes' === $this->get_option('enable_icon', 'yes');
        $icon_url = $show_icon ? plugins_url('assets/logo-qris.png', __FILE__) : '';
        $this->icon               = apply_filters('woocommerce_qris_icon', $icon_url);
        $this->has_fields         = false;
        $this->method_title       = __('QRIS', 'rapay');
        $this->method_description = __('Lakukan pembayaran melalui transfer langsung ke rekening QRIS.', 'rapay');

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables.
        $this->title        = $this->get_option('title');
        $this->description  = $this->get_option('description');
        $this->instructions = $this->get_option('instructions');
        $this->qr_code      = $this->get_option('qr_code');

        // Actions.
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));

        // Customer Emails.
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled'         => array(
                'title'   => __('Enable/Disable', 'rapay'),
                'type'    => 'checkbox',
                'label'   => __('Enable QRIS', 'rapay'),
                'default' => 'no',
            ),
            'title'           => array(
                'title'       => __('Title', 'rapay'),
                'type'        => 'safe_text',
                'description' => __('Mengatur judul yang dilihat pengguna selama proses checkout.', 'rapay'),
                'default'     => __('Transfer QRIS', 'rapay'),
                'desc_tip'    => true,
            ),
            'enable_icon' => array(
                'title'         => __('Icon', 'rapay'),
                'label'         => __('Enable Icon', 'rapay'),
                'type'          => 'checkbox',
                'description'   => '<img src="' . plugins_url('assets/logo-qris.png', __FILE__) . '" style="height:100%;max-height:32px !important" />',
                'default'       => 'yes',
            ),
            'description'     => array(
                'title'       => __('Description', 'rapay'),
                'type'        => 'textarea',
                'description' => __('Deskripsi metode pembayaran yang akan dilihat pelanggan pada halaman checkout Anda.', 'rapay'),
                'default'     => __('Lakukan pembayaran langsung ke rekening QRIS kami. Mohon gunakan ID Pesanan Anda sebagai referensi pembayaran. Pesanan Anda tidak akan dikirimkan hingga dana telah masuk ke rekening kami.', 'rapay'),
                'desc_tip'    => true,
            ),
            'instructions'    => array(
                'title'       => __('Instructions', 'rapay'),
                'type'        => 'textarea',
                'description' => __('Petunjuk yang akan ditambahkan ke halaman ucapan terima kasih dan email.', 'rapay'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'qr_code' => array(
                'title'       => __('Link QRIS', 'rapay'),
                'type'        => 'text',
                'description' => __('Masukkan Link QRIS.', 'rapay'),
                'default'     => __('', 'rapay'),
                'desc_tip'    => true,
            ),
        );
    }

    /**
     * Output for the order received page.
     *
     * @param int $order_id Order ID.
     */
    public function thankyou_page($order_id)
    {
        if ($this->instructions) {
            echo wp_kses_post(wpautop(wptexturize(wp_kses_post($this->instructions))));
        }
        $this->bank_details($order_id);
    }

    /**
     * Add content to the WC emails.
     *
     * @param WC_Order $order Order object.
     * @param bool     $sent_to_admin Sent to admin.
     * @param bool     $plain_text Email format: plain text or HTML.
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if (! $sent_to_admin && self::ID === $order->get_payment_method()) {
            /**
             * Filter the email instructions order status.
             *
             * @since 7.4
             *
             * @param string $terms The order status.
             * @param object $order The order object.
             */
            $instructions_order_status = apply_filters('woocommerce_qris_email_instructions_order_status', OrderStatus::ON_HOLD, $order);
            if ($order->has_status($instructions_order_status)) {
                if ($this->instructions) {
                    echo wp_kses_post(wpautop(wptexturize($this->instructions)) . PHP_EOL);
                }
                $this->bank_details($order->get_id());
            }
        }
    }

    /**
     * Get bank details and place into a list format.
     *
     * @param int $order_id Order ID.
     */
    private function bank_details($order_id = '')
    {
        if (empty($this->qr_code)) {
            return;
        }

        if (isset($this->qr_code) && !empty($this->qr_code)) {
            ?>
            <img src="<?php echo esc_url($this->qr_code); ?>" style="height:100%;max-height:500px;margin-bottom:35px !important" />
<?php
        }
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id Order ID.
     * @return array
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        if ($order->get_total() > 0) {
            /**
             * Filter the order status for QRIS payment.
             *
             * @since 3.4.0
             *
             * @param string $default_status The default order status.
             * @param object $order          The order object.
             */
            $process_payment_status = apply_filters('woocommerce_qris_process_payment_order_status', OrderStatus::ON_HOLD, $order);
            // Mark as on-hold (we're awaiting the payment).
            $order->update_status($process_payment_status, __('Menunggu pembayaran dari QRIS.', 'rapay'));
        } else {
            $order->payment_complete();
        }

        // Remove cart.
        WC()->cart->empty_cart();

        // Return thankyou redirect.
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }
}
