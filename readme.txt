=== WC Order PDF Download ===
Contributors: yagniksangani
Tags: woocommerce, pdf, invoices, order, print
Requires at least: 5.0
Tested up to: 5.8.1
Requires PHP: 7.3
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Download PDF for WooCommerce orders.

== Description ==
This WooCommerce extension generates a PDF invoice for your woocommerce orders. Admin can download order pdf from the order admin page and customers can download order pdf from the my account page.

= Features =
* One Click PDF Download
* Download the PDF invoice from the order admin page.
* Download invoices from the My Account page
* Use hook to add new order items in pdf table 
add_action( 'wcopd_order_pdf_add_extra_order_details', 'callback_function', 10, 1 );

== Installation ==
1. Upload plugin to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.

== Screenshots ==
1. Admin - Download PDF
2. Customer - Download PDF

= 1.0.0 =
* Initial release.

= 1.0.1 =
* Security updates.

= 1.0.2 =
* Security updates
* Added the hook code to add new order items.