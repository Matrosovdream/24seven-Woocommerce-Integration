<?php


/*
Docs
https://developer.24sevenoffice.com/docs/

Swagger API
http://24sevenapi.bluesystems.no:8080/swagger/ui/index

!!! https://webservices.24sevenoffice.com/Economy/Invoice/InvoiceService.asmx !!!
*/


/*
Create customer:
https://api.24sevenoffice.com/CRM/Company/V001/CompanyService.asmx?op=SaveCompanies
Type: Consumer
*/


/*
add_action('init', 'get_order_handler');
function get_order_handler() {
	
	if( $_GET['create_invoice'] ) {
		
		$operations = new Main_24Operations();
		
		$order_id = 1082;
		$operations->CreateInvoice( $order_id );
		
		die();
		
	}
	
	
	if( $_GET['get_persons'] ) {
		
		$operations = new Main_24Operations();
		$operations->GetPersons();
		
		die();
		
	}
	
	
	if( $_GET['check_person'] ) {
		
		$data = array(
						"Name" => "Stanislav",
						"FirstName" => "Matrosov",
						"NickName" => "",
						"Email" => "matrosov-stanislav@mail.ru8",
						"Phone" => "89995369631",
						);
		
		$operations = new Main_24Operations();
		$operations->CheckPerson( $data );
		
		die();
		
	}

	
	if( $_GET['get_products'] ) {
		
		$operations = new Main_24Operations();
		$operations->GetProducts();
		
		die();
		
	}
	

}
*/







