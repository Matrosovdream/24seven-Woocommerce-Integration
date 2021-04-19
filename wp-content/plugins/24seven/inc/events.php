<?php
add_action( 'save_post', 'action_save_order', 100, 1);
function action_save_order( $ID ) {
	
	$post = get_post( $ID );
	
	if( $post->post_type == 'shop_order' ) {
		
		$update_status = get_option('24seven_order_status');
		
		if( $post->post_status != $update_status ) { return true; }
		
		$isset_invoice = get_post_meta( $ID, '24seven_invoice_id', true );
		
		if( $ID != 1580 ) { if( $isset_invoice ) { return true; } }
		
		$operations = new Main_24Operations();
		
		$invoice_id = $operations->CreateInvoice( $ID );
		
		update_post_meta( $ID, '24seven_invoice_id', $invoice_id );
		
		return true;
		
	}
	
}