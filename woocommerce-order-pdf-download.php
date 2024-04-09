<?php
/**
 * Plugin Name: WC Order PDF Download
 * Plugin URI: https://github.com/yagniksangani/wc-order-pdf-download
 * Description: A robust WooCommerce plugin tailored for effortless PDF downloads of your orders.
 * Version: 1.2.2
 * Author: Yagnik Sangani
 * Text Domain: wc-order-pdf-download
 * License: GPL v2 or later
 * Author URI: https://github.com/yagniksangani
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * Requires at least: 6.3
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package WCOPD
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WC_ORDER_PDF_PLUGIN_FILE' ) ) {
	define( 'WC_ORDER_PDF_PLUGIN_FILE', __FILE__ );
}

// Include the main WC_Order_PDF_Generate class.
if ( ! class_exists( 'WC_Order_PDF_Generate', false ) ) {
	include_once dirname( WC_ORDER_PDF_PLUGIN_FILE ) . '/includes/class-wc-order-pdf-download.php';
}

$wc_order_pdf_download = new WC_Order_PDF_Download();
