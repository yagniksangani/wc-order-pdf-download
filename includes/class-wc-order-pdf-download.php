<?php

/**
 * Register WC_Order_PDF_Download class.
 */

defined( 'ABSPATH' ) || exit;

use Dompdf\Dompdf;
use Dompdf\Options;

class WC_Order_PDF_Download {

	function __construct()
	{		
		add_action( 'plugins_loaded', array( $this,'wcopd_check_some_other_plugin') );
		add_filter( 'manage_edit-shop_order_columns', array( $this,'wcopd_add_order_pdf_column_header'), 20 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this,'wcopd_add_order_pdf_column_content') );		
		add_action( 'wp_ajax_get_order_details', array( $this,'wcopd_get_order_details_ajax_code') );		
		add_action( 'wp_ajax_nopriv_get_order_details', array( $this,'wcopd_get_order_details_ajax_code') );
		add_filter( 'woocommerce_my_account_my_orders_actions', array( $this,'wcopd_add_my_account_my_orders_custom_action'), 10, 2 );
		
		include_once dirname( WC_ORDER_PDF_PLUGIN_FILE ) . '/vendor/autoload.php';
	}

	/**
	 * Check WooCommerce plugin is installed/activated
	 */	
	public function wcopd_check_some_other_plugin() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', function() {				
				echo '<div class="error"><p><strong>' . esc_html__( 'WC Order PDF Download plugin requires the WooCommerce plugin to be installed and activated!', 'wcorderpdf' ) . '</strong></p></div>';
			} );
			return;
		}
	}

	/**
	 * Add 'PDF' column header to 'Orders' page after 'Status' column
	 */
	public function wcopd_add_order_pdf_column_header( $columns ) {
	    $new_columns = array();
	    foreach ( $columns as $column_name => $column_info ) {
	        $new_columns[ $column_name ] = $column_info;
	        if ( 'order_status' === $column_name ) {
	            $new_columns['order_download'] = esc_html__( 'PDF', 'wcorderpdf' );
	        }
	    }
	    return $new_columns;
	}

	/**
	 * Add link for the pdf column
	 */
	public function wcopd_add_order_pdf_column_content( $column ) {
	    global $post; 
	    if ( 'order_download' === $column ) {
	        $order = wc_get_order( $post->ID );        
	        $pdf_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=get_order_details&order_id=' . $post->ID), 'generate_wp_wcopd' );
	        echo "<a class='order_download_pdf_col' href='".$pdf_url."'>".esc_html__( 'Download', 'wcorderpdf' )."</a>";        
	    }
	}

	/**
	 * Add download pdf button on frontend order page
	 */
	public function wcopd_add_my_account_my_orders_custom_action( $actions, $order ) {
		$order_id  = $order->get_id(); // Get the order ID
	    $action_slug = 'download_wcopdf';
	    $pdf_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=get_order_details&order_id=' . $order_id .'&my-account'), 'generate_wp_wcopd' );
	    $actions[$action_slug] = array(
	        'url'  => $pdf_url,
	        'name' => esc_html__( 'Download PDF', 'wcorderpdf' ),
	    );
	    return $actions;
	}

	/**
	 * Add code for process our AJAX request
	 */
	public function wcopd_get_order_details_ajax_code(){
		
		check_ajax_referer( 'generate_wp_wcopd', 'security' );

		if( isset( $_REQUEST['order_id'] ) && !empty( $_REQUEST['order_id'] ) ){
	 		
	 		$order_id = sanitize_text_field( $_REQUEST['order_id'] );

	 		// Set default is allowed
			$allowed = true;

			// Check if user is logged in
			if ( ! is_user_logged_in() ) {
				$allowed = false;
			}

			// Check the user privileges
			if( !( current_user_can( 'manage_woocommerce_orders' ) || current_user_can( 'edit_shop_orders' ) ) && !isset( $_GET['my-account'] ) ) {
				$allowed = false;
			}

			// Check current user can view order
			if ( !current_user_can('manage_options') && isset( $_GET['my-account'] ) ) {
				if ( ! current_user_can( 'view_order', $order_id ) ) {
					$allowed = false;
				}
			}

			if ( ! $allowed ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wcorderpdf' ) );
			}

		 	$order = wc_get_order( $order_id );
		 	$billing_first_name = $order->get_billing_first_name();
			$billing_last_name = $order->get_billing_last_name(); 	
			$billing_email = $order->get_billing_email(); 	
			$order_date = $order->get_date_created();
			$currency = $order->get_currency();
			$order_total = $order->get_total();
			$order_status = $order->get_status();
			$order_items = $order->get_items();		
			$payment_method = $order->get_payment_method_title();	
			$billing_address = $order->get_formatted_billing_address();
			$shipping_address = $order->get_formatted_shipping_address();
			$site_logo_id = get_theme_mod( 'custom_logo' );
			$sitelogo = wp_get_attachment_image_src( $site_logo_id , 'full' );

			$html = '';

			$html .= '<table style="text-align:center;width:100%;border:0;margin-bottom:10mm">';
			$html .= '<tr>';
			
			if(empty($sitelogo[0])){
				$html .= '<td><h2>'.get_bloginfo( 'name' ).'</h2></td>';
			}else{
				$html .= '<td><img style="max-width:200px" src="'.$sitelogo[0].'"></td>';
			}

			$html .= '</tr>';
			$html .= '</table>';

			$html .= '<table cellpadding="10" cellspacing="0" border="1" style="border:1px dashed black;width:100%">';

		 	$html .= '<tr><td><strong>'.esc_html__( "Order Number", "wcorderpdf" ).'</strong></td><td>'.$order_id.'</td></tr>';
		 	$html .= '<tr><td><strong>'.esc_html__( "Order Date", "wcorderpdf" ).'</strong></td><td>'.date_format($order_date,"Y/m/d H:i:s").'</td></tr>';
		 	$html .= '<tr><td><strong>'.esc_html__( "First Name", "wcorderpdf" ).'</strong></td><td>'.$billing_first_name.'</td></tr>';
		 	$html .= '<tr><td><strong>'.esc_html__( "Last Name", "wcorderpdf" ).'</strong></td><td>'.$billing_last_name.'</td></tr>';
		 	$html .= '<tr><td><strong>'.esc_html__( "Email Address", "wcorderpdf" ).'</strong></td><td>'.$billing_email.'</td></tr>';	 	
			$html .= '<tr><td><strong>'.esc_html__( "Billing Address", "wcorderpdf" ).'</strong></td><td>'.$billing_address.'</td></tr>';
			if(!empty($shipping_address)){
				$html .= '<tr><td><strong>'.esc_html__( "Shipping Address", "wcorderpdf" ).'</strong></td><td>'.$shipping_address.'</td></tr>';
			}
			$html .= '<tr><td><strong>'.esc_html__( "Order Status", "wcorderpdf" ).'</strong></td><td>'.$order_status.'</td></tr>';
			if ( ! empty( $payment_method ) ) {
				$html .= '<tr><td><strong>'.esc_html__( "Payment Method", "wcorderpdf" ).'</strong></td><td>'.$payment_method.'</td></tr>';
			}

			ob_start();
			do_action( 'wcopd_order_pdf_add_extra_order_details',$order );
			$html.=ob_get_clean();

			$html .= '<tr><td><strong>'.esc_html__( "Items", "wcorderpdf" ).'</strong></td>';

		 	$html .= '<td><table cellpadding="5" cellspacing="0" border="1" style="border:1px dashed black;width:100%"><tr><td><strong>'.esc_html__( "Item Name", "wcorderpdf" ).'</strong></td><td><strong>'.esc_html__( "Quantity", "wcorderpdf" ).'</strong></td><td><strong>'.esc_html__( "Price", "wcorderpdf" ).'</strong></td></tr>';

			foreach( $order_items as $item_id => $order_item ) {
				ob_start();
				do_action( 'wcopd_order_pdf_add_order_item_meta', $order_item );
				$meta .= ob_get_clean();
				$html .= "<tr><td>".$order_item->get_name(). $meta . "</td><td>".$order_item->get_quantity()."</td><td>".$currency." ".$order_item->get_total()."</td></tr>";
			}

			$html .= '</table></td></tr>';
			$html .= '<tr><td><strong>'.esc_html__( "Order Total", "wcorderpdf" ).'</strong></td><td>'.$currency." ".$order_total.'</td></tr>';

			ob_start();
			do_action( 'wcopd_order_pdf_add_extra_table_rows', $order );
			$html .= ob_get_clean();

			$html .= "</table>";
		 	
			$filename = "order-".$order_id;

			$options = new Options();

			$options->set('isRemoteEnabled', true);
		  	$options->set('isHtml5ParserEnabled', true);
			$options->set('defaultFont', 'Courier');

			$dompdf = new DOMPDF($options);
			
			$dompdf->loadHtml($html);		
			$dompdf->setPaper('A4', 'portrait');		
			$dompdf->render();
			$dompdf->stream($filename,array("Attachment"=>1));

		}

	    exit;
	}

}
