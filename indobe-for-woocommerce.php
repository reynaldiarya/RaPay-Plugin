<?php

/**
 * Plugin Name:       Indobe - Bank dan e-Money Indonesia
 * Plugin URI:        https://wordpress.org/plugins/indobe-for-woocommerce
 * Description:       Plugin Pembayaran Bank dan e-Money Indonesia untuk WooCommerce. Mendukung kode unik pembayaran.
 * Version:           1.0.2
 * Author:            Reynaldi Arya
 * Author URI:        https://reynaldiab.com
 * Requires at least: 6.0
 * Tested up to:      7.0
 * WC requires at least: 7.0
 * WC tested up to:   10.8
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Requires Plugins:  woocommerce
 * Text Domain:       indobe-for-woocommerce
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Automattic\WooCommerce\Utilities\FeaturesUtil;

/**
 * List of Indobe Gateways (Single Source of Truth)
 *
 * Format: 'class-file-name' => 'Class_Name'
 * Example: 'bni' => 'WC_Gateway_BNI'
 */
define('INDOBE_GATEWAYS', [
    // Bank
    'bank' => [
        'bni' => 'WC_Gateway_BNI',
        'bca' => 'WC_Gateway_BCA',
        'bri' => 'WC_Gateway_BRI',
        'mandiri' => 'WC_Gateway_Mandiri',
        'jago' => 'WC_Gateway_Jago',
        'cimb-niaga' => 'WC_Gateway_CIMB_Niaga',
        'citibank' => 'WC_Gateway_Citibank',
        'digibank' => 'WC_Gateway_Digibank',
        'hsbc' => 'WC_Gateway_HSBC',
        'jenius' => 'WC_Gateway_Jenius',
        'neo-commerce' => 'WC_Gateway_Neo_Commerce',
        'danamon' => 'WC_Gateway_Danamon',
        'btn' => 'WC_Gateway_BTN',
        'bsi' => 'WC_Gateway_BSI',
        'permata' => 'WC_Gateway_Permata',
        'ocbc-nisp' => 'WC_Gateway_OCBC_NISP',
        'muamalat' => 'WC_Gateway_Muamalat',
        'tmrw' => 'WC_Gateway_TMRW',
        'line-bank' => 'WC_Gateway_Line_Bank',
        'seabank' => 'WC_Gateway_Seabank',
        'allo-bank' => 'WC_Gateway_Allo_Bank',
        'krom' => 'WC_Gateway_Krom',
    ],
    // E-Money
    'e-money' => [
        'gopay' => 'WC_Gateway_GoPay',
        'ovo' => 'WC_Gateway_OVO',
        'dana' => 'WC_Gateway_Dana',
        'linkaja' => 'WC_Gateway_LinkAja',
        'shopeepay' => 'WC_Gateway_ShopeePay',
        'qris' => 'WC_Gateway_QRIS',
    ],
]);

/**
 * Helper: Get all gateway class names
 */
function indobe_get_gateway_classes(): array
{
    $classes = [];

    foreach (INDOBE_GATEWAYS as $type => $gateways) {
        $classes = array_merge($classes, array_values($gateways));
    }

    return $classes;
}

/**
 * 1. HPOS and Cart/Checkout Blocks Compatibility Statement
 */
add_action('before_woocommerce_init', function (): void {
    if (class_exists(FeaturesUtil::class)) {
        FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

/**
 * 2. Block Support Registration (Checkout Block)
 */
add_action('woocommerce_blocks_loaded', function (): void {
    if (!class_exists(AbstractPaymentMethodType::class)) {
        return;
    }

    require_once __DIR__ . '/blocks/class-indobe-blocks-support.php';

    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (PaymentMethodRegistry $payment_method_registry): void {
            $gateways = WC()->payment_gateways()->payment_gateways();
            $our_gateways = indobe_get_gateway_classes();

            foreach ($gateways as $gateway) {
                if ($gateway instanceof WC_Payment_Gateway && str_starts_with($gateway::class, 'WC_Gateway_')) {
                    $gateway_class = $gateway::class;

                    if (in_array($gateway_class, $our_gateways, true)) {
                        $payment_method_registry->register(new Indobe_Blocks_Support($gateway));
                    }
                }
            }
        },
    );
});

/**
 * 3. Plugin Initialization (Load Classes)
 */
add_action('plugins_loaded', function (): void {
    if (!class_exists('WooCommerce')) {
        // 1. Show Error Notification
        add_action('admin_notices', function (): void {
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <strong>Indobe Error:</strong> WooCommerce tidak ditemukan!
                    Plugin ini membutuhkan WooCommerce.
                    <br><em>Plugin Indobe telah dinonaktifkan secara otomatis.</em>
                </p>
            </div>
<?php
        });

        // 2. Automatically Disable This Plugin
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        deactivate_plugins(plugin_basename(__FILE__));

        // 3. Remove the “Plugin Activated” message if the user has just clicked Activate
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        // 4. Stop the script from crashing
        return;
    }

    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    // Load Class Files berdasarkan INDOBE_GATEWAYS
    foreach (INDOBE_GATEWAYS as $type => $gateways) {
        foreach ($gateways as $file_slug => $class_name) {
            $file = __DIR__ . '/' . $type . '/class-wc-gateway-' . $file_slug . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}, 0);

/**
 * 4. Register the Gateway with WooCommerce
 */
add_filter('woocommerce_payment_gateways', function ($methods) {
    foreach (indobe_get_gateway_classes() as $gateway) {
        if (class_exists($gateway)) {
            $methods[] = $gateway;
        }
    }

    return $methods;
});

/**
 * 5. Hide the icon on the customer page if the “Enable Icon” option is disabled
 */
add_filter('woocommerce_gateway_icon', function ($icon_html, $gateway_id) {
    // Berlaku hanya untuk gateway dari Indobe
    $is_indobe_gateway = str_starts_with((string) $gateway_id, 'bank_') || in_array($gateway_id, ['gopay', 'ovo', 'dana', 'linkaja', 'shopeepay', 'qris'], true);

    if ($is_indobe_gateway) {
        // Don't remove icon if in admin dashboard
        if (is_admin() && !wp_doing_ajax()) {
            return $icon_html;
        }

        // Hide the icon on the front end if the option is disabled
        $gateway = WC()->payment_gateways()->payment_gateways()[$gateway_id] ?? null;

        if ($gateway) {
            $show_icon = $gateway->get_option('enable_icon', 'yes') === 'yes';

            if (!$show_icon) {
                return '';
            }
        }
    }

    return $icon_html;
}, 10, 2);

/**
 * 6. Add a link below the plugin description
 */
add_filter('plugin_row_meta', function ($links, $plugin_file) {
    if (plugin_basename(__FILE__) === $plugin_file) {
        $links[] = '<a href="https://trakteer.id/reynaldiarya/tip" target="_blank" style="color:#3db634; font-weight:bold;">Donate</a>';
    }

    return $links;
}, 10, 2);

/**
 * 7. “Advanced” Tab Settings for Unique Codes
 */
add_filter('woocommerce_get_sections_advanced', function (array $sections): array {
    $sections['puc'] = __('Kode Pembayaran', 'indobe-for-woocommerce');

    return $sections;
});

add_filter('woocommerce_get_settings_advanced', function ($settings, $current_section) {
    if ($current_section === 'puc') {
        return [[
            'name' => __('Pengaturan Kode Unik', 'indobe-for-woocommerce'),
            'type' => 'title',
            'desc' => __('Tambahkan 3 digit angka unik pada total pembayaran untuk mempermudah verifikasi transfer manual.', 'indobe-for-woocommerce'),
            'id' => 'puc_options',
        ], [
            'name' => __('Aktifkan Kode Unik', 'indobe-for-woocommerce'),
            'type' => 'checkbox',
            'desc' => __('Ya, aktifkan penambahan kode unik otomatis.', 'indobe-for-woocommerce'),
            'id' => 'indobe_puc_enabled',
            'default' => 'no',
        ], [
            'name' => __('Label Kode Unik', 'indobe-for-woocommerce'),
            'type' => 'text',
            'desc' => __('Teks yang muncul di halaman checkout.', 'indobe-for-woocommerce'),
            'id' => 'indobe_puc_title',
            'default' => 'Kode Pembayaran',
            'placeholder' => 'Kode Pembayaran',
        ], [
            'name' => __('Angka Minimal', 'indobe-for-woocommerce'),
            'type' => 'number',
            'desc' => __('Batas bawah angka acak (Misal: 1).', 'indobe-for-woocommerce'),
            'id' => 'indobe_puc_min',
            'default' => '1',
            'custom_attributes' => ['min' => 1],
        ], [
            'name' => __('Angka Maksimal', 'indobe-for-woocommerce'),
            'type' => 'number',
            'desc' => __('Batas atas angka acak (Misal: 999).', 'indobe-for-woocommerce'),
            'id' => 'indobe_puc_max',
            'default' => '999',
            'custom_attributes' => ['max' => 999],
        ], ['type' => 'sectionend', 'id' => 'puc_options']];
    }

    return $settings;
}, 10, 2);

/**
 * 8. The Logic Behind the Unique Code Fee
 */
add_action('woocommerce_cart_calculate_fees', function ($cart): void {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    if (get_option('indobe_puc_enabled') !== 'yes') {
        return;
    }

    if ($cart->subtotal <= 0) {
        return;
    }

    $min = (int) get_option('indobe_puc_min', 1);
    $max = (int) get_option('indobe_puc_max', 999);
    $title = get_option('indobe_puc_title', 'Kode Pembayaran');

    $unique_code = WC()->session->get('indobe_unique_code');

    if (!$unique_code) {
        try {
            $unique_code = random_int($min, $max);
        } catch (Exception) {
            $unique_code = wp_rand($min, $max); // Fallback
        }
        WC()->session->set('indobe_unique_code', $unique_code);
    }

    if ($unique_code > 0) {
        $cart->add_fee($title, $unique_code);
    }
});

/**
 * 9. Remove the Unique Code from the Session After Checkout
 */
add_action('woocommerce_thankyou', function (): void {
    if (WC()->session) {
        WC()->session->__unset('indobe_unique_code');
    }
});
