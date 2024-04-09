<?php
/**
 * Register WC_Order_PDF_Download class.
 *
 * @package WCOPD
 */

defined( 'ABSPATH' ) || exit;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Class WC_Order_PDF_Download.
 */
class WC_Order_PDF_Download {

	/**
	 * Construct function of class - WC_Order_PDF_Download.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'wcopd_check_some_other_plugin' ) );
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'wcopd_add_order_pdf_column_header' ) );
		add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'wcopd_add_order_pdf_column_header' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'wcopd_add_order_pdf_column_content', 25, 2 ) );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'wcopd_add_order_pdf_column_content' ), 25, 2 );
		add_action( 'wp_ajax_get_order_details', array( $this, 'wcopd_get_order_details_ajax_code' ) );
		add_action( 'wp_ajax_nopriv_get_order_details', array( $this, 'wcopd_get_order_details_ajax_code' ) );
		add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'wcopd_add_my_account_my_orders_custom_action' ), 10, 2 );

		include_once dirname( WC_ORDER_PDF_PLUGIN_FILE ) . '/vendor/autoload.php';
	}

	/**
	 * Check WooCommerce plugin is installed/activated
	 */
	public function wcopd_check_some_other_plugin() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				function() {
					echo '<div class="error"><p><strong>' . esc_html__( 'WC Order PDF Download plugin requires the WooCommerce plugin to be installed and activated!', 'wc-order-pdf-download' ) . '</strong></p></div>';
				}
			);
			return;
		}
	}

	/**
	 * Add 'Order Invoice' column header to 'Orders' listing page after 'Status' column.
	 *
	 * @param  array $columns columns array.
	 * @return array
	 */
	public function wcopd_add_order_pdf_column_header( $columns ) {
		$new_columns = array();
		foreach ( $columns as $column_name => $column_info ) {
			$new_columns[ $column_name ] = $column_info;
			if ( 'order_status' === $column_name ) {
				$new_columns['order_download'] = esc_html__( 'Order Invoice', 'wc-order-pdf-download' );
			}
		}
		return $new_columns;
	}

	/**
	 * Add download pdf link for the order invoice column.
	 *
	 * @param  string $column_name column name.
	 * @param  object $order_or_order_id order object or id.
	 *
	 * @return void
	 */
	public function wcopd_add_order_pdf_column_content( $column_name, $order_or_order_id ) {
		$order = $order_or_order_id instanceof WC_Order ? $order_or_order_id : wc_get_order( $order_or_order_id );

		if ( 'order_download' === $column_name ) {
			$pdf_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=get_order_details&order_id=' . $order->ID ), 'generate_wp_wcopd' );
			echo "<a class='order_download_pdf_col' title='" . esc_html__( 'Download PDF', 'wc-order-pdf-download' ) . "' href='" . $pdf_url . "'>" . esc_html__( 'Download PDF', 'wc-order-pdf-download' ) . '</a>'; // phpcs:ignore.
		}
	}

	/**
	 * Add download pdf button on frontend order page.
	 *
	 * @param  array  $actions action name.
	 * @param  object $order order object.
	 * @return array
	 */
	public function wcopd_add_my_account_my_orders_custom_action( $actions, $order ) {
		$order_id                = $order->get_id(); // Get the order ID.
		$action_slug             = 'download_wcopdf';
		$pdf_url                 = wp_nonce_url( admin_url( 'admin-ajax.php?action=get_order_details&order_id=' . $order_id . '&my-account' ), 'generate_wp_wcopd' );
		$actions[ $action_slug ] = array(
			'url'  => $pdf_url,
			'name' => esc_html__( 'Download PDF', 'wc-order-pdf-download' ),
		);
		return $actions;
	}

	/**
	 * Add code for process our AJAX request.
	 */
	public function wcopd_get_order_details_ajax_code() {

		check_ajax_referer( 'generate_wp_wcopd', 'security' );

		$order_id = filter_input( INPUT_GET, 'order_id', FILTER_VALIDATE_INT );

		if ( isset( $order_id ) && ! empty( $order_id ) ) {

			// Set default is allowed.
			$allowed = true;

			// Check if user is logged in.
			if ( ! is_user_logged_in() ) {
				$allowed = false;
			}

			// Check the user privileges.
			if ( ! ( current_user_can( 'manage_woocommerce_orders' ) || current_user_can( 'edit_shop_orders' ) ) && ! isset( $_GET['my-account'] ) ) {
				$allowed = false;
			}

			// Check current user can view order.
			if ( ! current_user_can( 'manage_options' ) && isset( $_GET['my-account'] ) ) {
				if ( ! current_user_can( 'view_order', $order_id ) ) {
					$allowed = false;
				}
			}

			if ( ! $allowed ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wc-order-pdf-download' ) );
			}

			$order              = wc_get_order( $order_id );
			$billing_first_name = $order->get_billing_first_name();
			$billing_last_name  = $order->get_billing_last_name();
			$billing_email      = $order->get_billing_email();
			$order_date         = $order->get_date_created();
			$currency           = $order->get_currency();
			$order_total        = $order->get_total();
			$order_status       = $order->get_status();
			$order_items        = $order->get_items();
			$payment_method     = $order->get_payment_method_title();
			$billing_address    = $order->get_formatted_billing_address();
			$shipping_address   = $order->get_formatted_shipping_address();
			$site_logo_id       = get_theme_mod( 'custom_logo' );
			$sitelogo           = wp_get_attachment_image_src( $site_logo_id, 'full' );

			$store_address   = get_option( 'woocommerce_store_address' );
			$store_address_2 = get_option( 'woocommerce_store_address_2' );
			$store_city      = get_option( 'woocommerce_store_city' );
			$store_postcode  = get_option( 'woocommerce_store_postcode' );

			// The country/state.
			$store_raw_country = get_option( 'woocommerce_default_country' );

			// Split the country/state.
			$split_country = explode( ':', $store_raw_country );

			// Country and state separated.
			$store_country = $split_country[0];
			$store_state   = $split_country[1];

			$wc_store_address  = '<b>Store Address:</b><br />';
			$wc_store_address .= $store_address . '<br />';
			$wc_store_address .= ( $store_address_2 ) ? $store_address_2 . '<br />' : '';
			$wc_store_address .= $store_city . ', ' . $store_state . ' ' . $store_postcode . '<br />';
			$wc_store_address .= $store_country;

			$html = '<!DOCTYPE html>
			<html>
			<title>Order PDF</title>
			
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<style>
					@page {
						margin: 0;
				  	}
				  
				  	.store_name {
				  		color: #052b47;
				  	}

					.wcopd_pdf_body {
						padding:50px 50px;
						background-color: #f5e5e5;
					}
			
					.wcopd_pdf_store_details {
						text-align: center;
						width: 100%;
						border: 0;
						margin-bottom: 10mm;
						line-height:20px;
					}
			
					.wcopd_pdf_order_details {
						border: 1px solid #052b47;
						width: 100%;
					}

					.wcopd_pdf_order_heading {
						background-color: #052b47;
						color: #fff;
					}

					.wcopd_pdf_order_value {
						color: #052b47;
						font-weight: 400;
					}

					.wcopd_pdf_store_address {
						margin: 10px 10px;
						color: #052b47;
					}

					.wcopd_pdf_item_data {
						margin-top: 15px;
					}

					.wcopd_pdf_item_data .item_data {
						display: inline-block;
						vertical-align: middle;
						padding-right: 5px;
					}
				</style>
			</head>
			<body class="wcopd_pdf_body">
			<table class="wcopd_pdf_store_details">';

			$html .= '<tr><td>';

			if ( empty( $sitelogo[0] ) ) {
				$html .= '<div class="store_name"><h2>' . get_bloginfo( 'name' ) . '</h2></div>';
			} else {
				$html .= '<div class="store_logo"><img style="max-width:100px" src="' . $sitelogo[0] . '"></div>';
			}

			$html .= '<div class="wcopd_pdf_store_address"><p style="font-size:13px;">' . $wc_store_address . '</p></div></td>';
			$html .= '</tr>';
			$html .= '</table>';

			$html .= '<table class="wcopd_pdf_order_details" cellpadding="10" cellspacing="0">';

			$html .= '<tr><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'Order Number', 'wc-order-pdf-download' ) . '</strong></td><td class="wcopd_pdf_order_value">#' . $order_id . '</td></tr>';
			$html .= '<tr><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'Order Date', 'wc-order-pdf-download' ) . '</strong></td><td class="wcopd_pdf_order_value">' . date_format( $order_date, 'Y/m/d H:i:s' ) . '</td></tr>';
			$html .= '<tr><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'First Name', 'wc-order-pdf-download' ) . '</strong></td><td class="wcopd_pdf_order_value">' . $billing_first_name . '</td></tr>';
			$html .= '<tr><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'Last Name', 'wc-order-pdf-download' ) . '</strong></td><td class="wcopd_pdf_order_value">' . $billing_last_name . '</td></tr>';
			$html .= '<tr><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'Email Address', 'wc-order-pdf-download' ) . '</strong></td><td class="wcopd_pdf_order_value">' . $billing_email . '</td></tr>';
			$html .= '<tr><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'Billing Address', 'wc-order-pdf-download' ) . '</strong></td><td class="wcopd_pdf_order_value">' . $billing_address . '</td></tr>';
			if ( ! empty( $shipping_address ) ) {
				$html .= '<tr><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'Shipping Address', 'wc-order-pdf-download' ) . '</strong></td><td class="wcopd_pdf_order_value">' . $shipping_address . '</td></tr>';
			}
			$html .= '<tr><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'Order Status', 'wc-order-pdf-download' ) . '</strong></td><td class="wcopd_pdf_order_value">' . $order_status . '</td></tr>';
			$html .= '<tr><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'Payment Method', 'wc-order-pdf-download' ) . '</strong></td><td class="wcopd_pdf_order_value">' . $payment_method . '</td></tr>';

			ob_start();
			do_action( 'wcopd_order_pdf_add_extra_order_details', $order );
			$html .= ob_get_clean();

			$html .= '<tr><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'Item Details', 'wc-order-pdf-download' ) . '</strong></td>';

			$html .= '<td><table class="wcopd_pdf_order_details" cellpadding="5" cellspacing="0"><tr><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'Item', 'wc-order-pdf-download' ) . '</strong></td><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'Quantity', 'wc-order-pdf-download' ) . '</strong></td><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'Price', 'wc-order-pdf-download' ) . '</strong></td></tr>';

			foreach ( $order_items as $item_id => $order_item ) {
				$product_id = $order_item['product_id'];
				$product    = wc_get_product( $product_id );
				$html      .= '<tr><td class="wcopd_pdf_order_value"><div class="wcopd_pdf_item_data"><div class="item_data">' . $product->get_image( array( 35, 35 ) ) . '</div><div class="item_data">' . $order_item->get_name() . '</div></div></td><td class="wcopd_pdf_order_value">' . number_format( $order_item->get_quantity(), 2, '.', '' ) . '</td><td class="wcopd_pdf_order_value">' . $currency . ' ' . number_format( $order_item->get_total(), 2, '.', '' ) . '</td></tr>';
			}

			$html .= '</table></td></tr>';
			$html .= '<tr><td class="wcopd_pdf_order_heading"><strong>' . esc_html__( 'Order Total', 'wc-order-pdf-download' ) . '</strong></td><td class="wcopd_pdf_order_value"><strong>' . $currency . ' ' . number_format( $order_total, 2, '.', '' ) . '</strong></td></tr>';

			$html .= '</table></body></html>';
			update_option( 'pdf_html_save', $html );
			$filename = 'order-' . $order_id;

			$options = new Options();

			$options->set( 'isRemoteEnabled', true );
			$options->set( 'isHtml5ParserEnabled', true );
			$options->set( 'defaultFont', 'Courier' );

			$dompdf = new DOMPDF( $options );

			$dompdf->loadHtml( $html );
			$dompdf->setPaper( 'A4', 'portrait' );
			$dompdf->render();
			$dompdf->stream( $filename, array( 'Attachment' => 0 ) );

		}

		exit;
	}

}
