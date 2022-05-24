# wc-order-pdf-download

Download PDF for WooCommerce orders.

## Description

This WooCommerce extension generates a PDF invoice for your woocommerce orders. Admin can download order pdf from the order admin page and customers can download order pdf from the my account page.

## Features

* One Click PDF Download
* Download the PDF invoice from the order admin page.
* Download invoices from the My Account page
* Use hook to add new order items in pdf table
```
add_action( 'wcopd_order_pdf_add_extra_order_details', 'callback_function', 10, 1 );
```
* Use hook to add order item meta in pdf table
```
add_action( 'wcopd_order_pdf_add_order_item_meta', 'callback_function', 10, 1 );
```
* Use hook to add extra rows to pdf table
```
add_action( 'wcopd_order_pdf_add_extra_table_rows', 'callback_function', 10, 1 );
```
## Installation

1. Upload plugin to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
