<?php  
/**
 * 24 Seven Office Wrapper Class
 *
 * @author Tor Morten Jensen <tormorten@smartmedia.no>
 **/
class Main_24Operations {
	
	private $SEVEN24_APP_ID = '';
	private $SEVEN24_APP_LOGIN = '';
	private $SEVEN24_APP_PASSWORD = '';
	
	/*private $SEVEN24_APP_ID = '8dd85a11-4369-4c9e-86fa-f921eddd3710';
	private $SEVEN24_APP_LOGIN = 'conrad@bluesystems.no';
	private $SEVEN24_APP_PASSWORD = 'Upworks!!';*/
	
	function __construct() {
		
		$this->SEVEN24_APP_ID = get_option('24seven_app_id');
		$this->SEVEN24_APP_LOGIN = get_option('24seven_app_login');
		$this->SEVEN24_APP_PASSWORD = decrypt( get_option('24seven_app_password') );
		
	}
	
	// https://api.24sevenoffice.com/Economy/InvoiceOrder/V001/InvoiceService.asmx?op=SaveInvoices
	public function CreateInvoice( $order_id=false ) {
		
		if( !$order_id ) { return false; }
		
		$connection = new Main_24SevenOffice(
			$this->SEVEN24_APP_ID,
			$this->SEVEN24_APP_LOGIN,
			$this->SEVEN24_APP_PASSWORD
		);
		
		
		$ORDER = wc_get_order( $order_id );
		
		// Basket
		$data = $ORDER->get_data();
		$basket = $ORDER->get_items();
		
		// Payments
		$saved_payments = unserialize( get_option('24seven_payments') );
		$payment_method = $saved_payments[ $data['payment_method'] ];
		//echo $payment_method; die();
		
		if( $order_id == 1580 ) {
			$payments_24seven = $connection->GetPaymentMethods();
			
			//echo $payment_method; die();
			
			/*echo "<pre>";
			print_r( $payments_24seven );
			echo "</pre>";
			die();*/
		}
		
		/*echo "<pre>";
		print_r( $data );
		echo "</pre>";*/
		//die();
		
		$data_basket = array();
		foreach( $basket as $item ) {
			
			$info = $item->get_data();
			$price = get_post_meta( $item['product_id'], '_price', true );
			//$price = str_replace('.', ',', $price);
			
			$sku = get_post_meta( $item['product_id'], '_sku', true );

			$filter = array( "No" => $sku );
			$products_raw = $this->GetProducts( $filter );	
			$products = $products_raw->Product;
			
			if( is_array($products) ) {
				foreach( $products as $product_item ) {
					if( $product_item->No == $sku ) { $product = $product_item; }
				}
			} else {
				$product = $products;
			}
			
			$price = $info['subtotal'] / $info['quantity'];
			
			
			$data_basket[] = array(
				'AccrualDate' => time(),
				'AccrualLength' => 1000,
				'ChangeState' => 'None',
				'Discount' => 0,
				'Id' => $info['product_id'],
				'InPrice' => $price,
				'IsStructureProduct' => false,
				'MainProductId' => 0,
				'Price' => $price,
				'PriceCalc' => 'none', // Accumulated or Fixed or FixedWithSub or none
				'ProductId' => $product->Id,
				'ProductName' => $product->Name,
				//'ProductNo' => $sku,
				'Quantity' => $info['quantity'],
				'Type' => 'Normal', // Normal or Text or TextStrong
				'VatNo' => '',
				'VatRate' => 0,
			);
			
		}
		
		if( $data['shipping_total'] ) {
			
			$filter = array( "No" => get_option('24seven_delivery_product') );
			$products_raw = $this->GetProducts( $filter );	
			$products = $products_raw->Product;
			
			if( is_array($products) ) {
				foreach( $products as $product_item ) {
					if( $product_item->No == $sku ) { $product = $product_item; }
				}
			} else {
				$product = $products;
			}
			
			$data_basket[] = array(
				'AccrualDate' => time(),
				'AccrualLength' => 1000,
				'ChangeState' => 'None',
				'Discount' => 0,
				'Id' => '',
				'InPrice' => $data['shipping_total'],
				'IsStructureProduct' => false,
				'MainProductId' => 0,
				'Price' => $data['shipping_total'],
				'PriceCalc' => 'none', // Accumulated or Fixed or FixedWithSub or none
				'ProductId' => $product->Id,
				'ProductName' => $product->Name,
				'ProductNo' => '',
				'Quantity' => 1,
				'Type' => 'Normal', // Normal or Text or TextStrong
				'VatNo' => '',
				'VatRate' => 0,
			);
			
		}
		
		/*echo "<pre>";
		print_r( $data_basket );
		echo "</pre>";
		die();*/
		
		// Addresses
		$delivery = array();
		$delivery[] = array(
					'City' => $data['billing']['city'],
					'Country' => $data['billing']['country'],
					'Description' => '',
					'Name' => $data['billing']['first_name'].' '.$data['billing']['last_name'],
					'Phone' => $data['billing']['phone'],
					'PostalArea' => '',
					'PostalCode' => $data['billing']['postcode'],
					'State' => $data['billing']['state'],
					'Street' => $data['billing']['address_1'],
					'Type' => 'Invoice', // Invoice or Delivery
		);
		$delivery[] = array(
					'City' => $data['shipping']['city'],
					'Country' => $data['shipping']['country'],
					'Description' => '',
					'Name' => $data['shipping']['first_name'].' '.$data['shipping']['last_name'],
					'Phone' => $data['shipping']['phone'],
					'PostalArea' => '',
					'PostalCode' => $data['shipping']['postcode'],
					'State' => $data['shipping']['state'],
					'Street' => $data['shipping']['address_1'],
					'Type' => 'Delivery', // Invoice or Delivery
		);
		
		/*echo "<pre>";
		print_r( $data['billing'] );
		echo "</pre>";*/
		
		// Company ID
		$data_person = array(
						"Name" => $data['billing']['first_name'],
						"FirstName" => $data['billing']['last_name'],
						"LastName" => $data['billing']['first_name'],
						"NickName" => "",
						"Email" => $data['billing']['email'],
						"Phone" => $data['billing']['phone'],
						);
		
		$person_addresses = $delivery;
		$company_id = $this->CheckPerson( $data_person, $person_addresses );

		$data_send = array(
			'InvoiceId' => $order_id,
			'DeliveryName' => '', // !!!
			'Active' => true,
			'Type' => 'Invoice',
			'CompanyId' => $company_id,
			'CustomerId' => $company_id,
			'CurrencyRate' => 0, // !!!
			'DateInvoiced' => time(),
			'DateOrdered' => time(),
			'ExternalStatus' => $data['status'],
			'IncVAT' => false, // !!!
			'OrderId' => $order_id,
			// Proposal or Pack or Invoiced or Web or Repeating or Collecting or Return or Rest or ForInvoicing or OvertimeRent or Confirmed or Sent or Production or PaymentReminder or OnAccount or Lended or InvoicedCashAccounted or InvoicedPack or Inactive or Hours or SuperStore or Unknown
			'OrderStatus' => 'Web', // 
			'OurReference' => '', // !!!
			'Paid' => time(),
			'PaymentAmount' => $data['total'],
			'PaymentMethodId' => $payment_method,
			'PaymentTimeSpecified' => time(),
			'PaymentTime' => 'No credit',
			'ProjectId' => '',
			'RoundFactor' => 1,
			'RoundType' => 'none', // none or Quarter or Half or Whole
			'SendToFactoring' => false,
			'sysExpandStructure' => false,
			'TotalSum' => $data['total'],
			'TotalTax' => $data['total_tax'],
			'Addresses' => $delivery,
			'InvoiceRows' => $data_basket,
			'TypeOfSale' => 'Auto', // Credit or Cash or Foreign or NonVATable or Auto
			//'DistributionMethod' => 'Manual', // Unchanged or Print or EMail or ElectronicInvoice
		);

		
		$result = $connection->SaveInvoice( $data_send );
		$invoice_id = $result->SaveInvoiceResult;
		
		/*echo "<pre>";
		print_r( $result );
		echo "</pre>";
		die();*/
		
		return $invoice_id;
		
		/*echo  $order_id; echo "<br/>";
		echo $invoice_id;
		die();*/
		
		/*echo "<pre>";
		print_r( $result );
		echo "</pre>";*/
		
	}
	
	// https://developer.24sevenoffice.com/docs/companyservice.html
	// https://api.24sevenoffice.com/CRM/Company/V001/CompanyService.asmx?op=SaveCompanies
	public function CreatePerson( $data = array(), $person_addresses ) {
		
		$connection = new Main_24SevenOffice(
			$this->SEVEN24_APP_ID,
			$this->SEVEN24_APP_LOGIN,
			$this->SEVEN24_APP_PASSWORD
		);
		
		$email = array('Description' => '', 'Name' => '', 'Value' => $data['Email']);
		$addresses = array( 
			"Home" => $email, 
			"Invoice" => $email, 
			"Primary" => $email, 
			"Work" => $email, 
		);
		
		$phone = array('Description' => '', 'Value' => $data['Phone']);
		$phones = array( 
			"Home" => $phone,
			//"Fax" => $phone, 
			"Mobile" => $phone, 
			"Primary" => $phone,
			"Work" => $phone,
		);
		
		$p_addresses = array();
		foreach( $person_addresses as $addr ) {
			$p_addresses[ $addr['Type'] ] = $addr;
		}
 		
		$post_address = $person_addresses[0];
		$post_addresses = array( 
			"Post" => $p_addresses['Delivery'],
			"Delivery" => $p_addresses['Delivery'],
			"Invoice" => $p_addresses['Invoice'],
			//"Fax" => $phone, 
			//"Mobile" => $phone, 
			//"Primary" => $phone,
			//"Work" => $phone,
		);
		
		$companies = array();
		$companies[] = array(
							//"Id" => 111111,
							//"ExternalId" => 111111,
							"Name" => $data['FirstName'],
							"FirstName" => $data['LastName'],
							"NickName" => $data['NickName'],
							"Type" => 'Consumer', // None or Lead or Consumer or Business or Supplier
							//"CompanyEmail" => 'matrosovdream2@gmail.com',
							"EmailAddresses" => $addresses,
							"PhoneNumbers" => $phones,
							"Addresses" => $post_addresses,
							);
							
		/*echo "<pre>";
		print_r( $person_addresses );
		echo "</pre>";						
							
		echo "<pre>";
		print_r( $companies );
		echo "</pre>";					
		die();*/					
							

		$result = $connection->SaveCompany( $companies );
		
		/*echo "<pre>";
		print_r( $result );
		echo "</pre>";*/
		
		return $result->SaveCompaniesResult->Company->Id;

	}	
	
	
	public function GetPersons( $data = array() ) {
		
		$connection = new Main_24SevenOffice(
			$this->SEVEN24_APP_ID,
			$this->SEVEN24_APP_LOGIN,
			$this->SEVEN24_APP_PASSWORD
		);
		
		$data = array(
			"CompanyEmail" => $data['Email']
		);

		$result = $connection->GetCompanies( $data );
		
		return $result->GetCompaniesResult->Company;
		
		/*echo "<pre>";
		print_r( $result );
		echo "</pre>";*/
		
	}
	
	
	public function CheckPerson( $data, $person_addresses ) {
		
		$exist_person = $this->GetPersons( $data );
		$person_id = $exist_person->Id;
		
		if( !$person_id ) {
			return $this->CreatePerson( $data, $person_addresses );
		} else {
			return $person_id;
		}
		
		
		
		/*echo "<pre>";
		print_r( $exist_person );
		echo "</pre>";*/
		
	}
	
	
	public function GetProducts( $data = array() ) {
		
		$connection = new Main_24SevenOffice(
			$this->SEVEN24_APP_ID,
			$this->SEVEN24_APP_LOGIN,
			$this->SEVEN24_APP_PASSWORD
		);
		
		/*$data = array(
			"No" => '109436', // It works, something wrong with the No on the site
			//"EAN1" => '43',
			//"Name" => 'Pukk 63-120'
		);*/

		$result = $connection->GetProducts( $data );
		
		return $result->GetProductsResult;
		
		echo "<pre>";
		print_r( $result );
		echo "</pre>";
		
	}

	
}	