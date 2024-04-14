=== WooCommerce Order PDF Invoices Download ===
Contributors: yagniksangani
Donate link: paypal.me/yagniksangani
Tags: woocommerce, pdf, invoices, order, download
Requires at least: 6.3
Tested up to: 6.5.2
Requires PHP: 7.4
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Effortlessly generate and download PDF invoices and slips for your WooCommerce orders.

== Description ==
This WooCommerce extension simplifies invoice generation for your WooCommerce orders. It enables administrators to effortlessly download PDF invoices directly from the order admin page, while customers can conveniently access and download their order PDFs from the "My Account" page.

= Key Features =
* Instant PDF Download: Obtain PDF invoices with just one click.
* Admin Page Accessibility: Easily download PDF invoices directly from the order admin page.
* Customer Convenience: Access and download invoices conveniently from the "My Account" page.

= Hooks =
* Utilized hooks to incorporate new order items into the PDF,
**add_action( 'wcopd_order_pdf_add_extra_order_details', 'callback_function', 10, 1 );**

== Installation ==
1. Upload plugin to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.

== Screenshots ==
1. Admin - Download PDF
2. Customer - Download PDF
3. Invoice - Order PDF

= 1.0.0 =
* Initial release.

= 1.0.1 =
* Security updates.

= 1.0.2 =
* Security updates.
* Added the hook code to add new order items.

= 1.0.3 =
* Security updates.
* Checked the compatibility with the latest wordpress version.

= 1.0.4 =
* Added more style for the invoice PDF.

= 1.2.0 =
* Improved Code Performance: Enhanced the code for better efficiency and performance.
* Strengthened Security Measures: Implemented updates to ensure robust security.
* Verified Compatibility with Latest WordPress Version: Ensured seamless integration by confirming compatibility with the most recent WordPress version.

= 1.2.1 =
* Minor code fixes

= 1.2.2 =
* PDF text color enhancements