<?php
define('PASSWORD_PLACEHOLDER', '************');

add_action( 'admin_menu', 'seven24_options_page' );
function seven24_options_page() {
    add_menu_page(
        '24seven Office',
        '24seven Office',
        'manage_options',
        'seven24',
        'seven24_page_html'
    );
}


function seven24_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
 
    if ( isset( $_POST['settings-updated'] ) ) {
		
		if( $_POST['24seven_app_password'] != PASSWORD_PLACEHOLDER && $_POST['24seven_app_password'] != '' ) {
			$password_enc = encrypt( $_POST['24seven_app_password'] );
			update_option( '24seven_app_password', $password_enc );
		}
		
		update_option( '24seven_app_id', $_POST['24seven_app_id'] );
		update_option( '24seven_app_login', $_POST['24seven_app_login'] );
		
		update_option( '24seven_order_status', $_POST['24seven_order_status'] );
		update_option( '24seven_delivery_product', $_POST['24seven_delivery_product'] );
		update_option( '24seven_payments', serialize($_POST['24seven_payments']) );
		
        add_settings_error( 'wporg_messages', 'wporg_message', __( 'Settings Saved', 'wporg' ), 'updated' );
    }
	
	ob_start(); 
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="" method="post">
		
			<input type="hidden" name="settings-updated" value="Y" />
			
			<h2>24Seven API credentials</h2>
			
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">App ID</th>
						<td id="front-static-pages">
							<input type="text" name="24seven_app_id" value="<?php echo get_option('24seven_app_id'); ?>" style="width: 400px;" />
						</td>
					</tr>
					<tr>
						<th scope="row">App Login</th>
						<td id="front-static-pages">
							<input type="text" name="24seven_app_login" value="<?php echo get_option('24seven_app_login'); ?>" style="width: 400px;" />
						</td>
					</tr>
					
					<?php
					$password_true = decrypt( get_option('24seven_app_password') );
					//echo $password_true;
					$password = PASSWORD_PLACEHOLDER;
					?>
					<tr>
						<th scope="row">App Password</th>
						<td id="front-static-pages">
							<input type="text" name="24seven_app_password" value="<?php echo $password; ?>" style="width: 400px;" />
						</td>
					</tr>
				</tbody>
			</table>		


			<h2>Other settings</h2>
			
			<table class="form-table" role="presentation">
				<tbody>
				
					<?php
					$order_statuses = wc_get_order_statuses();
					$chosen_status = get_option('24seven_order_status');
					?>
					
					<tr>
						<th scope="row">Order status for invoice</th>
						<td id="front-static-pages">
							<select name="24seven_order_status" style="width: 400px;">
								<option></option>
								<?php foreach( $order_statuses as $key=>$title ) { ?>
									<?php if( $key == $chosen_status ) { $selected = 'selected'; } else { $selected = ''; } ?>
									<option value="<?php echo $key; ?>" <?php echo $selected ?>> <?php echo $title; ?> </option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">Delivery product No</th>
						<td id="front-static-pages">
							<input type="text" name="24seven_delivery_product" value="<?php echo get_option('24seven_delivery_product'); ?>" style="width: 400px;" />
						</td>
					</tr>
					
				</tbody>
			</table>
			
			
			<h2>Payment methods</h2>
			
			<?php
			$saved_payments = unserialize( get_option('24seven_payments') );
			
			$payment_gateways_obj = new WC_Payment_Gateways(); 
			$enabled_payment_gateways = $payment_gateways_obj->payment_gateways();
			
			/*echo "<pre>";
			print_r( $saved_payments );
			echo "</pre>";*/
			?>
			
			<table class="form-table" role="presentation">
				<tbody>
				
					<?php
					//$order_statuses = wc_get_order_statuses();
					//$chosen_status = get_option('24seven_order_status');
					?>
					
					<?php foreach( $enabled_payment_gateways as $code=>$method ) { ?>
					
						<tr>
							<th scope="row"> <?php echo $method->title; ?> </th>
							<td>
								<input type="number" name="24seven_payments[<?php echo $code; ?>]" value="<?php echo $saved_payments[ $code ]; ?>" style="width: 400px;" />
							</td>
						</tr>
					<?php } ?>
					
				</tbody>
			</table>
			
		
            <?php
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
	
	<?php
	$html = ob_get_contents();
	ob_end_clean();
	
	echo $html;
	?>
	
<?php } ?>

<?php
add_filter( 'manage_shop_order_posts_columns', 'add_views_column', 100 );
function add_views_column( $columns ){
	$num = 2;

	$new_columns = array(
		'24seven_invoice_id' => '24Seven Invoice ID',
	);

	return array_slice( $columns, 0, $num ) + $new_columns + array_slice( $columns, $num );
}


add_action( 'manage_shop_order_posts_custom_column', 'order_columns' );
function order_columns( $column_name ) {
	if ( $column_name == '24seven_invoice_id' ) {
		global $post;
		$order_id = $post->ID;
		
		$invoice_id = get_post_meta( $order_id, '24seven_invoice_id', true );
		echo $invoice_id;
	}
}