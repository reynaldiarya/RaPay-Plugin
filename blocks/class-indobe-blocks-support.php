<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Indobe Blocks Support
 *
 * @package Indobe
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Indobe Blocks integration class.
 */
final class Indobe_Blocks_Support extends AbstractPaymentMethodType
{
    /**
     * Payment method name/id
     *
     * @var string
     */
    protected $name;

    /**
     * Gateway instance
     *
     * @var WC_Payment_Gateway
     */
    private $gateway;

    /**
     * Constructor
     *
     * @param WC_Payment_Gateway $gateway Gateway instance.
     */
    public function __construct($gateway = null)
    {
        if ($gateway) {
            $this->gateway = $gateway;
            $this->name = $gateway->id;
            $this->script_handle = 'indobe-' . $this->name . '-blocks';
        }
    }

    /**
     * Initializes the payment method type.
     */
    public function initialize()
    {
        $this->settings = get_option('woocommerce_' . $this->name . '_settings', []);
    }

    /**
     * Returns if this payment method should be active.
     *
     * @return bool
     */
    public function is_active()
    {
        if (!$this->gateway) {
            return false;
        }

        return $this->gateway->is_available();
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles()
    {
        $script_url = plugins_url('blocks/js/frontend.js', dirname(__FILE__));
        $script_asset_path = dirname(__FILE__) . '/js/frontend.asset.php';

        $script_asset = require $script_asset_path;

        if (!wp_script_is('indobe-blocks-integration', 'registered')) {
            wp_register_script(
                'indobe-blocks-integration',
                $script_url,
                $script_asset['dependencies'],
                $script_asset['version'],
                true
            );
        }

        return ['indobe-blocks-integration'];
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data()
    {
        if (!$this->gateway) {
            return [];
        }

        $show_icon = $this->gateway->get_option('enable_icon', 'yes') === 'yes';

        return [
            'name'        => $this->name,
            'title'       => $this->gateway->get_title(),
            'description' => $this->gateway->get_description(),
            'icon'        => $show_icon ? ($this->gateway->icon ?? '') : '',
            'supports'    => $this->get_supported_features(),
        ];
    }

    /**
     * Get supported features
     *
     * @return array
     */
    public function get_supported_features()
    {
        $features = ['products'];

        if ($this->gateway && is_array($this->gateway->supports)) {
            $features = array_merge($features, $this->gateway->supports);
        }

        return array_unique($features);
    }
}
