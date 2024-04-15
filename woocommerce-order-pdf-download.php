<?php
/**
 * Plugin Name: Download PDF Invoices for WooCommerce Orders
 * Plugin URI: https://github.com/yagniksangani/wc-order-pdf-download
 * Description: Effortlessly generate and download PDF invoices for your WooCommerce orders.
 * Version: 1.3.0
 * Author: Yagnik Sangani
 * Text Domain: wc-order-pdf-download
 * License: GPL v2 or later
 * Author URI: https://github.com/yagniksangani
 * Tested up to: 6.5.2
 * Requires PHP: 7.4
 * Requires at least: 6.3
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package WCOPD
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/plugin.php';

define( 'WCOPD_FILE', __FILE__ );
define( 'WCOPD_PLUGIN', plugin_basename( __FILE__ ) );
define( 'WCOPD_WC_VERSION', '7.0.0' );

// Include the main WC_Order_PDF_Download class.
require_once dirname( WCOPD_FILE ) . '/includes/class-wc-order-pdf-download.php';
