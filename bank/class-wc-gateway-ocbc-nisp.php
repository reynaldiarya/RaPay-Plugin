<?php

/**
 * Class WC_Gateway_Bank_OCBC_NISP file.
 *
 * @package WooCommerce\Gateways
 */

use Automattic\WooCommerce\Enums\OrderStatus;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Bank OCBC NISP Payment Gateway.
 *
 * Provides a Bank OCBC NISP Payment Gateway. Based on standard WooCommerce BACS.
 *
 * @class       WC_Gateway_OCBC NISP
 * @extends     WC_Payment_Gateway
 * @version     4.0.0
 * @package     WooCommerce\Classes\Payment
 */
class WC_Gateway_OCBC_NISP extends WC_Payment_Gateway
{
    /**
     * Unique ID for this gateway.
     *
     * @var string
     */
    public const ID = 'bank_ocbc_nisp';

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
     * Account details.
     *
     * @var array
     */
    public $account_details;

    /**
     * Constructor for the gateway.
     */
    public function __construct()
    {
        $this->id                 = self::ID;
        $show_icon = 'yes' === $this->get_option('enable_icon', 'yes');
        $icon_url = $show_icon ? plugins_url('assets/logo-ocbc-nisp.png', __FILE__) : '';
        $this->icon               = apply_filters('woocommerce_bank_ocbc_nisp_icon', $icon_url);
        $this->has_fields         = false;
        $this->method_title       = __('Bank OCBC NISP', 'rapay');
        $this->method_description = __('Lakukan pembayaran melalui transfer langsung ke rekening Bank OCBC NISP.', 'rapay');

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables.
        $this->title        = $this->get_option('title');
        $this->description  = $this->get_option('description');
        $this->instructions = $this->get_option('instructions');

        // Bank OCBC NISP account fields shown on the thanks page and in emails.
        $this->account_details = get_option(
            'woocommerce_bank_ocbc_nisp_accounts',
            array(
                array(
                    'account_name'   => $this->get_option('account_name'),
                    'account_number' => $this->get_option('account_number'),
                    'sort_code'      => $this->get_option('sort_code'),
                    'iban'           => $this->get_option('iban'),
                    'bic'            => $this->get_option('bic'),
                ),
            )
        );

        // Actions.
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'save_account_details'));
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
                'label'   => __('Enable Bank OCBC NISP', 'rapay'),
                'default' => 'no',
            ),
            'title'           => array(
                'title'       => __('Title', 'rapay'),
                'type'        => 'safe_text',
                'description' => __('Mengatur judul yang dilihat pengguna selama proses checkout.', 'rapay'),
                'default'     => __('Transfer Bank OCBC NISP', 'rapay'),
                'desc_tip'    => true,
            ),
            'enable_icon' => array(
                'title'         => __('Icon', 'rapay'),
                'label'         => __('Enable Icon', 'rapay'),
                'type'          => 'checkbox',
                'description'   => '<img src="' . plugins_url('assets/logo-ocbc-nisp.png', __FILE__) . '" style="height:100%;max-height:32px !important" />',
                'default'       => 'no',
            ),
            'description'     => array(
                'title'       => __('Description', 'rapay'),
                'type'        => 'textarea',
                'description' => __('Deskripsi metode pembayaran yang akan dilihat pelanggan pada halaman checkout Anda.', 'rapay'),
                'default'     => __('Lakukan pembayaran langsung ke rekening Bank OCBC NISP kami. Mohon gunakan ID Pesanan Anda sebagai referensi pembayaran. Pesanan Anda tidak akan dikirimkan hingga dana telah masuk ke rekening kami.', 'rapay'),
                'desc_tip'    => true,
            ),
            'instructions'    => array(
                'title'       => __('Instructions', 'rapay'),
                'type'        => 'textarea',
                'description' => __('Petunjuk yang akan ditambahkan ke halaman ucapan terima kasih dan email.', 'rapay'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'account_details' => array(
                'type' => 'account_details',
            ),
        );
    }

    /**
     * Generate account details html.
     *
     * @return string
     */
    public function generate_account_details_html()
    {
        ob_start();

        $country = WC()->countries->get_base_country();
        $locale  = $this->get_country_locale();

        // Get sortcode label in the $locale array and use appropriate one.
        $sortcode = isset($locale[$country]['sortcode']['label']) ? $locale[$country]['sortcode']['label'] : __('Kode Cabang', 'rapay');

        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label>
                    <?php esc_html_e('Account details:', 'rapay'); ?>
                    <?php echo wp_kses_post(wc_help_tip(__('Rincian akun ini akan ditampilkan di halaman terima kasih pesanan dan email konfirmasi.', 'rapay'))); ?>
                </label>
            </th>
            <td class="forminp" id="bank_ocbc_nisp_accounts">
                <div class="wc_input_table_wrapper">
                    <table class="widefat wc_input_table sortable" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="sort">&nbsp;</th>
                                <th><?php esc_html_e('Nama akun', 'rapay'); ?></th>
                                <th><?php esc_html_e('Nomor rekening', 'rapay'); ?></th>
                                <th><?php echo esc_html($sortcode); ?></th>
                                <th><?php esc_html_e('IBAN', 'rapay'); ?></th>
                                <th><?php esc_html_e('BIC / Swift', 'rapay'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="accounts">
                            <?php
                                    $i = -1;
        if ($this->account_details) {
            foreach ($this->account_details as $account) {
                ++$i;

                echo '<tr class="account">
										<td class="sort"></td>
										<td><input type="text" value="' . esc_attr(wp_unslash($account['account_name'])) . '" name="bank_ocbc_nisp_account_name[' . esc_attr($i) . ']" /></td>
										<td><input type="text" value="' . esc_attr($account['account_number']) . '" name="bank_ocbc_nisp_account_number[' . esc_attr($i) . ']" /></td>
										<td><input type="text" value="' . esc_attr($account['sort_code']) . '" name="bank_ocbc_nisp_sort_code[' . esc_attr($i) . ']" /></td>
										<td><input type="text" value="' . esc_attr($account['iban']) . '" name="bank_ocbc_nisp_iban[' . esc_attr($i) . ']" /></td>
										<td><input type="text" value="' . esc_attr($account['bic']) . '" name="bank_ocbc_nisp_bic[' . esc_attr($i) . ']" /></td>
									</tr>';
            }
        }
        ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="7"><a href="#" class="add button"><?php esc_html_e('+ Add account', 'rapay'); ?></a> <a href="#" class="remove_rows button"><?php esc_html_e('Remove selected account(s)', 'rapay'); ?></a></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <script type="text/javascript">
                    jQuery(function() {
                        jQuery('#bank_ocbc_nisp_accounts').on('click', 'a.add', function() {

                            var size = jQuery('#bank_ocbc_nisp_accounts').find('tbody .account').length;

                            jQuery('<tr class="account">\
									<td class="sort"></td>\
									<td><input type="text" name="bank_ocbc_nisp_account_name[' + size + ']" /></td>\
									<td><input type="text" name="bank_ocbc_nisp_account_number[' + size + ']" /></td>\
									<td><input type="text" name="bank_ocbc_nisp_sort_code[' + size + ']" /></td>\
									<td><input type="text" name="bank_ocbc_nisp_iban[' + size + ']" /></td>\
									<td><input type="text" name="bank_ocbc_nisp_bic[' + size + ']" /></td>\
								</tr>').appendTo('#bank_ocbc_nisp_accounts table tbody');

                            return false;
                        });
                    });
                </script>
            </td>
        </tr>
<?php
        return ob_get_clean();
    }

    /**
     * Save account details table.
     */
    public function save_account_details()
    {
        $accounts = array();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verification already handled in WC_Admin_Settings::save()
        if (
            isset($_POST['bank_ocbc_nisp_account_name']) && isset($_POST['bank_ocbc_nisp_account_number'])
            && isset($_POST['bank_ocbc_nisp_sort_code']) && isset($_POST['bank_ocbc_nisp_iban']) && isset($_POST['bank_ocbc_nisp_bic'])
        ) {
            $account_names   = wc_clean(wp_unslash($_POST['bank_ocbc_nisp_account_name']));
            $account_numbers = wc_clean(wp_unslash($_POST['bank_ocbc_nisp_account_number']));
            $sort_codes      = wc_clean(wp_unslash($_POST['bank_ocbc_nisp_sort_code']));
            $ibans           = wc_clean(wp_unslash($_POST['bank_ocbc_nisp_iban']));
            $bics            = wc_clean(wp_unslash($_POST['bank_ocbc_nisp_bic']));

            foreach ($account_names as $i => $name) {
                if (! isset($account_names[$i])) {
                    continue;
                }

                $accounts[] = array(
                    'account_name'   => $account_names[$i],
                    'account_number' => $account_numbers[$i],
                    'sort_code'      => $sort_codes[$i],
                    'iban'           => $ibans[$i],
                    'bic'            => $bics[$i],
                );
            }
        }
        // phpcs:enable

        do_action('woocommerce_update_option', array('id' => 'woocommerce_bank_ocbc_nisp_accounts'));
        update_option('woocommerce_bank_ocbc_nisp_accounts', $accounts);
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
            $instructions_order_status = apply_filters('woocommerce_bank_ocbc_nisp_email_instructions_order_status', OrderStatus::ON_HOLD, $order);
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
        if (empty($this->account_details)) {
            return;
        }

        // Get order and store in $order.
        $order = wc_get_order($order_id);

        // Get the order country and country $locale.
        $country = $order->get_billing_country();
        $locale  = $this->get_country_locale();

        // Get sortcode label in the $locale array and use appropriate one.
        $sortcode = isset($locale[$country]['sortcode']['label']) ? $locale[$country]['sortcode']['label'] : __('Kode Cabang', 'rapay');

        $bank_ocbc_nisp_accounts = apply_filters('woocommerce_bank_ocbc_nisp_accounts', $this->account_details, $order_id);

        if (! empty($bank_ocbc_nisp_accounts)) {
            $account_html = '';
            $has_details  = false;

            foreach ($bank_ocbc_nisp_accounts as $bank_ocbc_nisp_account) {
                $bank_ocbc_nisp_account = (object) $bank_ocbc_nisp_account;

                if ($bank_ocbc_nisp_account->account_name) {
                    $account_html .= '<h3 class="wc-bank-ocbc-nisp-bank-details-account-name">' . wp_kses_post(wp_unslash(implode(' - ', array_filter([$bank_ocbc_nisp_account->account_name ?? null, $this->method_title ?? null])))) . ':</h3>' . PHP_EOL;
                }

                $account_html .= '<ul class="wc-bank-ocbc-nisp-bank-details order_details bacs_details">' . PHP_EOL;

                // Bank OCBC NISP account fields shown on the thanks page and in emails.
                $account_fields = apply_filters(
                    'woocommerce_bank_ocbc_nisp_account_fields',
                    array(
                        'account_number' => array(
                            'label' => __('Nomor Rekening', 'rapay'),
                            'value' => $bank_ocbc_nisp_account->account_number,
                        ),
                        'sort_code'      => array(
                            'label' => $sortcode,
                            'value' => $bank_ocbc_nisp_account->sort_code,
                        ),
                        'iban'           => array(
                            'label' => __('IBAN', 'rapay'),
                            'value' => $bank_ocbc_nisp_account->iban,
                        ),
                        'bic'            => array(
                            'label' => __('BIC', 'rapay'),
                            'value' => $bank_ocbc_nisp_account->bic,
                        ),
                    ),
                    $order_id
                );

                foreach ($account_fields as $field_key => $field) {
                    if (! empty($field['value'])) {
                        $account_html .= '<li class="' . esc_attr($field_key) . '">' . wp_kses_post($field['label']) . ': <strong>' . wp_kses_post(wptexturize($field['value'])) . '</strong></li>' . PHP_EOL;
                        $has_details   = true;
                    }
                }

                $account_html .= '</ul>';
            }

            if ($has_details) {
                echo '<section class="woocommerce-bank-ocbc-nisp-bank-details"><h2 class="wc-bank-ocbc-nisp-bank-details-heading">' . esc_html__('Rincian rekening bank kami', 'rapay') . '</h2>' . wp_kses_post(PHP_EOL . $account_html) . '</section>';
            }
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
             * Filter the order status for Bank OCBC NISP payment.
             *
             * @since 3.4.0
             *
             * @param string $default_status The default order status.
             * @param object $order          The order object.
             */
            $process_payment_status = apply_filters('woocommerce_bank_ocbc_nisp_process_payment_order_status', OrderStatus::ON_HOLD, $order);
            // Mark as on-hold (we're awaiting the payment).
            $order->update_status($process_payment_status, __('Menunggu pembayaran dari Bank OCBC NISP.', 'rapay'));
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

    /**
     * Get country locale if localized.
     *
     * @return array
     */
    public function get_country_locale()
    {
        if (empty($this->locale)) {
            // Locale information to be used - only those that are not 'Sort Code'.
            $this->locale = apply_filters(
                'woocommerce_get_bank_ocbc_nisp_locale',
                array(
                    'AU' => array(
                        'sortcode' => array(
                            'label' => __('BSB', 'rapay'),
                        ),
                    ),
                    'CA' => array(
                        'sortcode' => array(
                            'label' => __('Bank transit number', 'rapay'),
                        ),
                    ),
                    'IN' => array(
                        'sortcode' => array(
                            'label' => __('IFSC', 'rapay'),
                        ),
                    ),
                    'IT' => array(
                        'sortcode' => array(
                            'label' => __('Branch sort', 'rapay'),
                        ),
                    ),
                    'NZ' => array(
                        'sortcode' => array(
                            'label' => __('Bank code', 'rapay'),
                        ),
                    ),
                    'SE' => array(
                        'sortcode' => array(
                            'label' => __('Bank code', 'rapay'),
                        ),
                    ),
                    'US' => array(
                        'sortcode' => array(
                            'label' => __('Routing number', 'rapay'),
                        ),
                    ),
                    'ZA' => array(
                        'sortcode' => array(
                            'label' => __('Branch code', 'rapay'),
                        ),
                    ),
                )
            );
        }

        return $this->locale;
    }
}
