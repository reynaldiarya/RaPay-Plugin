=== BEIPay - Bank dan e-Money Indonesia ===
Contributors: reynaldiarya
Donate link: https://trakteer.id/reynaldiarya/tip
Tags: woocommerce, payment, method, gateway, indonesia
Requires at least: 6.0
Requires PHP: 7.4
Tested up to: 6.9
Stable tag: 4.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Add Indonesian Bank & e-Money payments (BCA, Mandiri, QRIS, GoPay) to WooCommerce with unique payment codes.

== Description ==
**BEIPay - Bank dan e-Money Indonesia**

The WooCommerce Bank and e-Money Indonesia Payment Gateway plugin provides a comprehensive collection of Indonesian banks and e-Wallets for WooCommerce payments. 

By default, WooCommerce only provides a generic bank transfer option. This plugin expands that by offering specific payment methods for major Indonesian banks (BCA, BNI, Mandiri, BRI, Jago) and e-Money services (Dana, LinkAja, OVO, GoPay). 

**Key Features:**
* **Payment Icons:** Display professional logos for every bank and wallet (can be toggled on/off).
* **Unique Payment Code (Kode Unik):** Automatically adds a random 3-digit code to the total checkout amount to make it easier to verify transfers in your bank mutation.
* **HPOS Support:** Fully compatible with High-Performance Order Storage.
* **Blocks Support:** Compatible with the new WooCommerce Checkout Blocks.

**Available Payment Methods:**
* **Banks:** BCA, BNI, BRI, Mandiri, Jago, Neo Commerce, Digibank, Citibank, HSBC, TMRW, Line Bank, Allo Bank, OCBC NISP, CIMB Niaga, Danamon, BTN, BSI (Syariah), Permata, Muamalat, Seabank, Jenius, Krom.
* **e-Money / Wallets:** OVO, GoPay, Dana, LinkAja, ShopeePay.
* **QRIS:** Standard QRIS payment method support.

== Installation ==
1.  Upload the plugin files to the `/wp-content/plugins/beipay-for-woocommerce` directory, or install the plugin through the WordPress plugins screen directly.
2.  Activate the plugin through the 'Plugins' screen in WordPress.
3.  Go to **WooCommerce > Settings > Payments**.
4.  Enable the specific banks or e-money gateways you wish to accept.
5.  (Optional) To enable the Unique Payment Code, go to **WooCommerce > Settings > Advanced > Kode Pembayaran**.
6.  Click **Manage** next to a payment method to configure account numbers and instructions.

== Frequently Asked Questions ==

= Does this plugin process payments automatically? =
No. This is a Direct Bank Transfer (BACS) extender. It displays the bank account details and adds a unique code to the total. You must manually check your bank account (mutation) to confirm the funds have been received before processing the order.

= Does it support the new WooCommerce Checkout Blocks? =
Yes, as of version 4.0.0, this plugin supports the block-based Checkout and Cart pages.

= Can I change the unique code range? =
Yes, you can configure the minimum and maximum range for the unique random code in the settings.

== Screenshots ==
1.  **Checkout Page:** How the payment methods appear to customers during checkout.
2.  **Payment Settings:** The admin interface for enabling/disabling specific banks.
3.  **Unique Code Settings:** Configuration for the random payment code generation.

== Upgrade Notice ==
= 4.0.0 =
Major update: Added support for High-Performance Order Storage (HPOS) and WooCommerce Cart/Checkout Blocks. Please test on a staging site before updating on production.

== Changelog ==
= 4.0.0 - December 17, 2025 =
* Compatibility: WooCommerce 10.4 and WordPress 6.9
* Added: HPOS (High-Performance Order Storage) support
* Added: Cart/Checkout Blocks support for block-based checkout
* Improved: Standardized all gateway classes and IDs
* Improved: Streamlined asset loading and removed code duplication
* Added: Krom Payment

= 3.0.2 - October 16, 2022 =
* Add Setting and Donate Button

= 3.0.1 - October 16, 2022 =
* Fixed Bug

= 3.0.0 - October 16, 2022 =
* Add Pembayaran QRIS

= 2.7.0 - May 23, 2021 =
* Add Pembayaran Allo Bank

= 2.5.0 - December 29, 2021 =
* Add Pembayaran Seabank

= 2.3.0 - July 26, 2021 =
* Add Pembayaran TMRW
* Add Pembayaran Line Bank

= 2.2.0 - July 12, 2021 =
* Add Angka Minimum and Maximum Kode Unik Pembayaran untuk Diacak
* Add Pembayaran Bank Muamalat

= 2.0.1 - July 07, 2021 =
* Improve Security

= 2.0.0 - July 06, 2021 =
* Add Pembayaran Bank Danamon
* Add Pembayaran Bank Tabungan Negara
* Add Pembayaran Bank Syariah Indonesia
* Add Pembayaran Bank Permata

= 1.9.0 - July 01, 2021 =
* Fixed Bug

= 1.8.0 - June 27, 2021 =
* Add Pembayaran Bank Jago
* Add Pembayaran Bank Neo Commerce
* Add Pembayaran Digibank
* Add Pembayaran Citibank
* Add Pembayaran HSBC
* Add Pembayaran Bank OCBC NISP
* Add Pembayaran Bank CIMB Niaga
* Add Pembayaran Jenius

= 1.5.0 - June 26, 2021 =
* Add Ikon pembayaran

= 1.4.0 - June 23, 2021 =
* Add Pembayaran LinkAja

= 1.2.0 - June 18, 2021 =
* Fixed Bug

= 1.0.0 - May 23, 2021 =
* Initial Release