# WC Order PDF Download

Download PDFs for WooCommerce orders.

## Description
This WooCommerce extension simplifies invoice generation for your WooCommerce orders. It enables administrators to effortlessly download PDF invoices directly from the order admin page, while customers can conveniently access and download their order PDFs from the "My Account" page.

## Key Features
* Instant PDF Download: Obtain PDF invoices with just one click.
* Admin Page Accessibility: Easily download PDF invoices directly from the order admin page.
* Customer Convenience: Access and download invoices conveniently from the "My Account" page.

## Hooks
* Utilized hooks to incorporate new order items into the PDF,
**add_action( 'wcopd_order_pdf_add_extra_order_details', 'callback_function', 10, 1 );**

## Installation
1. Upload plugin to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.