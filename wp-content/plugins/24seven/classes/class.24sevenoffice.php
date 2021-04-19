<?php  
/**
 * 24 Seven Office Wrapper Class
 *
 * @author Tor Morten Jensen <tormorten@smartmedia.no>
 **/
class Main_24SevenOffice {

	/**
	 * API Key
	 *
	 * @var The API key from 24SO
	 **/
	private $api_key;

	/**
	 * Username
	 *
	 * @var The Username
	 **/
	private $username;

	/**
	 * Password
	 *
	 * @var The password
	 **/
	private $password;

	/**
	 * Type
	 *
	 * @var The type
	 **/
	private $type = 'Community';

	/**
	 * Service URL
	 *
	 * @var The url to the service
	 **/
	private $service;

	/**
	 * Identity ID
	 *
	 * @var The identity ID
	 **/
	private $identity = '00000000-0000-0000-0000-000000000000';

	/**
	 * Initiates the link
	 *
	 * @param string $api_key The API-key from 24SO
	 * @param string $username The username to 24SO
	 * @param string $password The password to 24SO
	 * 
	 * @return void
	 **/
	public function __construct( $api_key, $username, $password ) {

		$this->api_key = $api_key;
		$this->username = $username;
		$this->password = $password;

	}

	/**
	 * Gets and/or sets the authentication
	 *
	 * @return void
	 **/
	private function get_auth() {

		$options = array ('trace' => true, 'style' => SOAP_RPC, 'use' => SOAP_ENCODED);

		$params = array();
		$params ["credential"]["Type"] = $this->type;
		$params ["credential"]["Username"] = $this->username;
		$encodedPassword = md5(mb_convert_encoding($this->password, 'utf-16le', 'utf-8'));
		$params ["credential"]["Password"] = $this->password;
		$params ["credential"]["ApplicationId"] = $this->api_key;

		$params ["credential"]["IdentityId"] = $this->identity;

		//$authentication = new SoapClient ( "https://webservices.24sevenoffice.com/authenticate/authenticate.asmx?wsdl", $options );
		$authentication = new SoapClient ( "https://api.24sevenoffice.com/authenticate/v001/authenticate.asmx?wsdl", $options );

		$login = true;

		if (!empty($_SESSION['ASP.NET_SessionId']))
		{
		    
		    $authentication->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
		    try
		    {
		         $login = !($authentication->HasSession()->HasSessionResult);
		    }
		    catch ( SoapFault $fault ) 
		    {
		        $login = true;
		    }

		}

		if( $login )
		{
			
		    $result = ($temp = $authentication->Login($params));
		    // set the session id for next time we call this page
		    $_SESSION['ASP.NET_SessionId'] = $result->LoginResult;
		    // each seperate webservice need the cookie set
		    $authentication->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
		    // throw an error if the login is unsuccessful

			/*echo "<pre>";
			print_r( $authentication );
			echo "</pre>";*/
			
		    if($authentication->HasSession()->HasSessionResult == false)
		        throw new SoapFault("0", "Invalid credential information.");
		}

	}

	/**
	 * Sets the service.
	 * 
	 * @param string $service Which service to use
	 * 
	 * @return void
	 **/
	public function set_service( $service = 'Contact/PersonService' ) {
		
		$this->service = $service;

		//$this->service = 'http://webservices.24sevenoffice.com/CRM/'. $service .'.asmx?WSDL';
		//$this->service = 'http://webservices.24sevenoffice.com/'. $service .'.asmx?WSDL';
		//$this->service = 'https://api.24sevenoffice.com/'. $service .'.asmx?WSDL';

	}

	/**
	 * Gets the service
	 *
	 * @return object The current service
	 **/
	private function service() {
		
		$options = array ('trace' => 1, 'style' => SOAP_RPC, 'use' => SOAP_ENCODED);

		$service = new SoapClient ( $this->service, $options );
		$service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

		return $service;

	}

	/**
	 * Makes a call to the soap service
	 * 
	 * @param string $action The action to call
	 * @param string $request The request to make
	 *
	 * @return mixed The result of the call or the exception if errors
	 **/
	public function call( $action, $request ) {

		$this->get_auth();

		try {

			$service = $this->service();

			$request = $this->parse_query( $request );

			$results = $service->__soapCall( $action, array($request) );

		}
		catch (SoapFault $e) {
			$results = 'Errors occured:' . $e;
		}

		return $results;

	}

	/**
	 * Parses the query into a object
	 *
	 * @param array $query The query array
	 * 
	 * @return object The query array as an object
	 **/
	private function parse_query( $query ) {

		return json_decode( json_encode( $query ) );

	}


	// https://api.24sevenoffice.com/CRM/Company/V001/CompanyService.asmx?op=SaveCompanies
	public function SaveCompany( $data ) {

		$this->set_service( 'https://api.24sevenoffice.com/CRM/Company/V001/CompanyService.asmx?WSDL' );

        $request = array(
            'companies' => $data,
        );

        return $this->call( 'SaveCompanies', $request );

	}
	

	public function SaveInvoice( $data ) {
		$this->set_service( 'https://webservices.24sevenoffice.com/Economy/Invoice/InvoiceService.asmx?WSDL' );
		$request = array(
			'invoiceItem' => $data,
		);
		return $this->call( 'SaveInvoice', $request );
	}
	
	
	public function GetCompanies( $data ) {
		$this->set_service( 'https://api.24sevenoffice.com/CRM/Company/V001/CompanyService.asmx?WSDL' );
		$request = array(
			'searchParams' => $data,
		);
		return $this->call( 'GetCompanies', $request );
	}	
	
	
	public function GetProducts( $data ) {
		$this->set_service( 'https://api.24sevenoffice.com/Logistics/Product/V001/ProductService.asmx?WSDL' );
		$request = array(
			'searchParams' => $data,
		);
		return $this->call( 'GetProducts', $request );
	}
	
	
	public function GetPaymentMethods( $data=array() ) {
		$this->set_service( 'https://api.24sevenoffice.com/Economy/InvoiceOrder/V001/InvoiceService.asmx?WSDL' );
		$request = array(
			//'searchParams' => $data,
		);
		return $this->call( 'GetPaymentMethods', $request );
	}
	
	
	// https://webservices.24sevenoffice.com/Economy/Invoice/InvoiceService.asmx?op=GetInvoices
	/*public function GetInvoices( $search_query = array() ) {

		$this->set_service( 'Economy/Invoice/InvoiceService' );

		$request = array(
          'invoiceSearch' => $search_query,
        );

		return $this->call( 'GetInvoices', $request );

	}*/
	
	
	/*public function SavePerson( $data ) {

		$this->set_service( '/CRM/Contact/PersonService' );

        $request = array(
            'personItem' => $data,
        );

        return $this->call( 'SavePerson', $request );

	}*/
	
	

}


?>