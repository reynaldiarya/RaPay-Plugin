<?php

/**
 * Plugin Name:       BEIPay - Bank dan e-Money Indonesia
 * Plugin URI:        https://wordpress.org/plugins/beipay-bank-dan-e-money-indonesia-for-woocommerce
 * Description:       Plugin Pembayaran Bank dan e-Money Indonesia untuk WooCommerce. Mendukung kode unik pembayaran.
 * Version:           4.0.0
 * Author:            Reynaldi Arya
 * Author URI:        https://reynaldiab.com
 * Requires at least: 6.0
 * Tested up to:      6.9
 * WC requires at least: 7.0
 * WC tested up to:   10.4
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       beipay-bank-e-money-indonesia
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Daftar Gateway BEIPay (Single Source of Truth)
 *
 * Format: 'class-file-name' => 'Class_Name'
 * Contoh: 'bni' => 'WC_Gateway_BNI'
 */
define('BEIPay_GATEWAYS', [
    // Bank
    'bank' => [
        'bni'          => 'WC_Gateway_BNI',
        'bca'          => 'WC_Gateway_BCA',
        'bri'          => 'WC_Gateway_BRI',
        'mandiri'      => 'WC_Gateway_Mandiri',
        'jago'         => 'WC_Gateway_Jago',
        'cimb-niaga'   => 'WC_Gateway_CIMB_Niaga',
        'citibank'     => 'WC_Gateway_Citibank',
        'digibank'     => 'WC_Gateway_Digibank',
        'hsbc'         => 'WC_Gateway_HSBC',
        'jenius'       => 'WC_Gateway_Jenius',
        'neo-commerce' => 'WC_Gateway_Neo_Commerce',
        'danamon'      => 'WC_Gateway_Danamon',
        'btn'          => 'WC_Gateway_BTN',
        'bsi'          => 'WC_Gateway_BSI',
        'permata'      => 'WC_Gateway_Permata',
        'ocbc-nisp'    => 'WC_Gateway_OCBC_NISP',
        'muamalat'     => 'WC_Gateway_Muamalat',
        'tmrw'         => 'WC_Gateway_TMRW',
        'line-bank'    => 'WC_Gateway_Line_Bank',
        'seabank'      => 'WC_Gateway_Seabank',
        'allo-bank'    => 'WC_Gateway_Allo_Bank',
        'krom'         => 'WC_Gateway_Krom',
    ],
    // E-Money
    'e-money' => [
        'gopay'     => 'WC_Gateway_GoPay',
        'ovo'       => 'WC_Gateway_OVO',
        'dana'      => 'WC_Gateway_Dana',
        'linkaja'   => 'WC_Gateway_LinkAja',
        'shopeepay' => 'WC_Gateway_ShopeePay',
        'qris'      => 'WC_Gateway_QRIS',
    ],
]);

/**
 * Helper: Mendapatkan semua class name gateway
 */
function beipay_get_gateway_classes(): array
{
    $classes = [];
    foreach (BEIPay_GATEWAYS as $type => $gateways) {
        $classes = array_merge($classes, array_values($gateways));
    }
    return $classes;
}

/**
 * 1. Deklarasi Kompatibilitas HPOS dan Cart/Checkout Blocks
 */
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

/**
 * 2. Registrasi Dukungan Blocks (Checkout Block)
 */
add_action('woocommerce_blocks_loaded', function () {
    if (! class_exists(Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType::class)) {
        return;
    }

    require_once dirname(__FILE__) . '/blocks/class-beipay-bank-e-money-indonesia-blocks-support.php';

    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (\Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
            $gateways = WC()->payment_gateways()->payment_gateways();
            $our_gateways = beipay_get_gateway_classes();

            foreach ($gateways as $gateway) {
                if ($gateway instanceof WC_Payment_Gateway && strpos(get_class($gateway), 'WC_Gateway_') === 0) {
                    $gateway_class = get_class($gateway);
                    if (in_array($gateway_class, $our_gateways, true)) {
                        $payment_method_registry->register(new BEIPay_Blocks_Support($gateway));
                    }
                }
            }
        }
    );
});

/**
 * 3. Inisialisasi Plugin (Load Classes)
 */
add_action('plugins_loaded', function () {
    if (! class_exists('WooCommerce')) {
        // 1. Tampilkan Notifikasi Error
        add_action('admin_notices', function () {
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <strong>BEIPay Error:</strong> WooCommerce tidak ditemukan! 
                    Plugin ini membutuhkan WooCommerce. 
                    <br><em>Plugin BEIPay telah dinonaktifkan secara otomatis.</em>
                </p>
            </div>
            <?php
        });

        // 2. Nonaktifkan Plugin Ini Secara Otomatis
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        deactivate_plugins(plugin_basename(__FILE__));

        // 3. Hilangkan pesan "Plugin Activated" jika user baru saja klik Activate
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }

        // 4. Hentikan eksekusi script agar tidak crash
        return;
    }

    if (! class_exists('WC_Payment_Gateway')) {
        return;
    }

    // Load Class Files berdasarkan BEIPay_GATEWAYS
    foreach (BEIPay_GATEWAYS as $type => $gateways) {
        foreach ($gateways as $file_slug => $class_name) {
            $file = dirname(__FILE__) . '/' . $type . '/class-wc-gateway-' . $file_slug . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
}, 0);

/**
 * 4. Daftarkan Gateway ke WooCommerce
 */
add_filter('woocommerce_payment_gateways', function ($methods) {
    foreach (beipay_get_gateway_classes() as $gateway) {
        if (class_exists($gateway)) {
            $methods[] = $gateway;
        }
    }
    return $methods;
});

/**
 * 5. Tambahkan Link di Bawah Deskripsi Plugin
 */
add_filter('plugin_row_meta', function ($links, $plugin_file) {
    if (plugin_basename(__FILE__) === $plugin_file) {
        $links[] = '<a href="https://clouden.id/" target="_blank" style="color:#5544f8; font-weight:bold;">Sponsor</a>';
        $links[] = '<a href="https://trakteer.id/reynaldiarya/tip" target="_blank" style="color:#3db634; font-weight:bold;">Donate</a>';
    }
    return $links;
}, 10, 2);

/**
 * 6. Pengaturan Tab "Advanced" untuk Kode Unik
 */
add_filter('woocommerce_get_sections_advanced', function ($sections) {
    $sections['puc'] = __('Kode Pembayaran', 'beipay-bank-e-money-indonesia');
    return $sections;
});

add_filter('woocommerce_get_settings_advanced', function ($settings, $current_section) {
    if ('puc' === $current_section) {
        $settings_puc = array();

        $settings_puc[] = array(
            'name' => __('Pengaturan Kode Unik', 'beipay-bank-e-money-indonesia'),
            'type' => 'title',
            'desc' => __('Tambahkan 3 digit angka unik pada total pembayaran untuk mempermudah verifikasi transfer manual.', 'beipay-bank-e-money-indonesia'),
            'id'   => 'puc_options',
        );

        $settings_puc[] = array(
            'name'    => __('Aktifkan Kode Unik', 'beipay-bank-e-money-indonesia'),
            'type'    => 'checkbox',
            'desc'    => __('Ya, aktifkan penambahan kode unik otomatis.', 'beipay-bank-e-money-indonesia'),
            'id'      => 'woocommerce_puc_enabled',
            'default' => 'no',
        );

        $settings_puc[] = array(
            'name'        => __('Label Kode Unik', 'beipay-bank-e-money-indonesia'),
            'type'        => 'text',
            'desc'        => __('Teks yang muncul di halaman checkout.', 'beipay-bank-e-money-indonesia'),
            'id'          => 'woocommerce_puc_title',
            'default'     => 'Kode Pembayaran',
            'placeholder' => 'Kode Pembayaran',
        );

        $settings_puc[] = array(
            'name'              => __('Angka Minimal', 'beipay-bank-e-money-indonesia'),
            'type'              => 'number',
            'desc'              => __('Batas bawah angka acak (Misal: 1).', 'beipay-bank-e-money-indonesia'),
            'id'                => 'woocommerce_puc_min',
            'default'           => '1',
            'custom_attributes' => array('min' => 1)
        );

        $settings_puc[] = array(
            'name'    => __('Angka Maksimal', 'beipay-bank-e-money-indonesia'),
            'type'    => 'number',
            'desc'    => __('Batas atas angka acak (Misal: 999).', 'beipay-bank-e-money-indonesia'),
            'id'      => 'woocommerce_puc_max',
            'default' => '999',
            'custom_attributes' => array('max' => 999)
        );

        $settings_puc[] = array('type' => 'sectionend', 'id' => 'puc_options');
        return $settings_puc;
    }
    return $settings;
}, 10, 2);

/**
 * 7. Logika Penambahan Biaya Kode Unik (Fee)
 */
add_action('woocommerce_cart_calculate_fees', function ($cart) {
    if (is_admin() && ! defined('DOING_AJAX')) {
        return;
    }

    if ('yes' !== get_option('woocommerce_puc_enabled')) {
        return;
    }

    if ($cart->subtotal <= 0) {
        return;
    }

    $min   = (int) get_option('woocommerce_puc_min', 1);
    $max   = (int) get_option('woocommerce_puc_max', 999);
    $title = get_option('woocommerce_puc_title', 'Kode Pembayaran');

    $unique_code = WC()->session->get('beipay_unique_code');

    if (! $unique_code) {
        try {
            $unique_code = random_int($min, $max);
        } catch (Exception $e) {
            $unique_code = wp_rand($min, $max); // Fallback
        }
        WC()->session->set('beipay_unique_code', $unique_code);
    }

    if ($unique_code > 0) {
        $cart->add_fee($title, $unique_code);
    }
});

/**
 * 8. Hapus Kode Unik dari Sesi setelah Checkout
 */
add_action('woocommerce_thankyou', function () {
    if (WC()->session) {
        WC()->session->__unset('beipay_unique_code');
    }
});
