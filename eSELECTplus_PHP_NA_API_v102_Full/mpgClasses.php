<?php

#################### mpgGlobals ###########################################


class mpgGlobals
{
	var $Globals=array(
        	        'MONERIS_PROTOCOL' => 'https',
					'MONERIS_HOST' => 'www3.moneris.com', //default
					'MONERIS_TEST_HOST' => 'esqa.moneris.com',
					'MONERIS_US_HOST' => 'esplus.moneris.com',
					'MONERIS_US_TEST_HOST' => 'esplusqa.moneris.com',
        	        'MONERIS_PORT' =>'443',
					'MONERIS_FILE' => '/gateway2/servlet/MpgRequest',
					'MONERIS_US_FILE' => '/gateway_us/servlet/MpgRequest',
					'MONERIS_MPI_FILE' => '/mpi/servlet/MpiServlet',
					'MONERIS_US_MPI_FILE' => '/mpi/servlet/MpiServlet',
                  	'API_VERSION'  =>'PHP NA - 1.0.2',
                  	'CLIENT_TIMEOUT' => '60'
                 	);

 	function mpgGlobals()
 	{
 		// default
 	}

 	function getGlobals()
 	{
  		return($this->Globals);
 	}

}//end class mpgGlobals

###################### curlPost #############################################
class httpsPost 
{
	var $url;
	var $dataToSend;
	var $clientTimeOut;
	var $apiVersion;
	var $response;
	var $debug = FALSE; //default is false for production release

	function httpsPost($url, $dataToSend)
	{
		$this->url=$url;
		$this->dataToSend=$dataToSend;

		if($this->debug == true)
		{
			echo "DataToSend= ".$this->dataToSend;
			echo "\n\nPostURL= " . $this->url;
		}
		
		$g=new mpgGlobals();
		$gArray=$g->getGlobals();
		$clientTimeOut = $gArray['CLIENT_TIMEOUT'];
		$apiVersion = $gArray['API_VERSION'];
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$this->dataToSend);
		curl_setopt($ch,CURLOPT_TIMEOUT,$clientTimeOut);
		curl_setopt($ch,CURLOPT_USERAGENT,$apiVersion);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
		//curl_setopt($ch, CURLOPT_CAINFO, "PATH_TO_CA_BUNDLE");
		
		$this->response=curl_exec ($ch);
		
		curl_close ($ch);
		
		if($this->debug == true)
		{
			echo "\n\nRESPONSE= $this->response\n";
		}
	}
	
	function getHttpsResponse()
	{
		return $this->response;
	}
}

###################### mpgHttpsPost #########################################

class mpgHttpsPost
{

 	var $api_token;
 	var $store_id;
 	var $mpgRequest;
 	var $mpgResponse;
 	var $xmlString;
 	var $txnType;
 	var $isMPI;

 	function mpgHttpsPost($storeid,$apitoken,$mpgRequestOBJ)
 	{

  		$this->store_id=$storeid;
  		$this->api_token= $apitoken;
  		$this->mpgRequest=$mpgRequestOBJ;
  		$this->isMPI=$mpgRequestOBJ->getIsMPI();
  		$dataToSend=$this->toXML();
  		
		$url = $this->mpgRequest->getURL();
		
  		$httpsPost= new httpsPost($url, $dataToSend);	
  		$response = $httpsPost->getHttpsResponse();

  		if(!$response)
  		{

     			$response="<?xml version=\"1.0\"?><response><receipt>".
          			"<ReceiptId>Global Error Receipt</ReceiptId>".
          			"<ReferenceNum>null</ReferenceNum><ResponseCode>null</ResponseCode>".
          			"<AuthCode>null</AuthCode><TransTime>null</TransTime>".
          			"<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>".
          			"<Message>Global Error Receipt</Message><TransAmount>null</TransAmount>".
          			"<CardType>null</CardType>".
          			"<TransID>null</TransID><TimedOut>null</TimedOut>".
          			"<CorporateCard>false</CorporateCard><MessageId>null</MessageId>".
          			"</receipt></response>";
   		}

  		$this->mpgResponse=new mpgResponse($response);

 	}



 	function getMpgResponse()
 	{
  		return $this->mpgResponse;

 	}

 	function toXML( )
 	{

  		$req=$this->mpgRequest;
  		$reqXMLString=$req->toXML();
  		
  		if($this->isMPI === true)
  		{
  			$this->xmlString .="<?xml version=\"1.0\"?>".
								"<MpiRequest>".
									"<store_id>$this->store_id</store_id>".
									"<api_token>$this->api_token</api_token>".
									$reqXMLString.
								"</MpiRequest>";
  		}
  		else
  		{
  			$this->xmlString .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>".
               					"<request>".
               						"<store_id>$this->store_id</store_id>".
               						"<api_token>$this->api_token</api_token>".
                					$reqXMLString.
                				"</request>";
  		}

  		return ($this->xmlString);

 	}

}//end class mpgHttpsPost


###################### mpgHttpsPostStatus #########################################

class mpgHttpsPostStatus
{

 	var $api_token;
 	var $store_id;
 	var $status;
 	var $mpgRequest;
 	var $mpgResponse;

 	function mpgHttpsPost($storeid,$apitoken,$status,$mpgRequestOBJ)
 	{

  		$this->store_id=$storeid;
  		$this->api_token= $apitoken;
  		$this->status=$status;
  		$this->mpgRequest=$mpgRequestOBJ;
  		$dataToSend=$this->toXML();

  		//$transactionType=$mpgRequestOBJ->getTransactionType();
 		
  		$url = $this->mpgRequest->getURL();
		
		echo "\n\nDataToSend: ".$dataToSend;

  		$httpsPost= new httpsPost($url, $dataToSend);	
  		$response = $httpsPost->getHttpsResponse();
  		
  		echo "\n\nRESPONSE = $response";

  		if(!$response)
  		{

     			$response="<?xml version=\"1.0\"?><response><receipt>".
          			"<ReceiptId>Global Error Receipt</ReceiptId>".
          			"<ReferenceNum>null</ReferenceNum><ResponseCode>null</ResponseCode>".
          			"<AuthCode>null</AuthCode><TransTime>null</TransTime>".
          			"<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>".
          			"<Message>Global Error Receipt</Message><TransAmount>null</TransAmount>".
          			"<CardType>null</CardType>".
          			"<TransID>null</TransID><TimedOut>null</TimedOut>".
          			"<CorporateCard>false</CorporateCard><MessageId>null</MessageId>".
          			"</receipt></response>";
   		}

  		$this->mpgResponse=new mpgResponse($response);

 	}



 	function getMpgResponse()
 	{
  		return $this->mpgResponse;

 	}

 	function toXML( )
 	{

  		$req=$this->mpgRequest ;
  		$reqXMLString=$req->toXML();

  		$xmlString .= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>".
               			"<request>".
               			"<store_id>$this->store_id</store_id>".
               			"<api_token>$this->api_token</api_token>".
               			"<status_check>$this->status</status_check>".
                		$reqXMLString.
                		"</request>";

  		return ($xmlString);

 	}

}//end class mpgHttpsPostStatus

############# mpgResponse #####################################################


class mpgResponse
{

	var $responseData;

 	var $p; //parser

 	var $currentTag;
 	var $purchaseHash = array();
 	var $refundHash;
 	var $correctionHash = array();
 	var $isBatchTotals;
 	var $term_id;
 	var $receiptHash = array();
 	var $ecrHash = array();
 	var $CardType;
 	var $currentTxnType;
 	var $ecrs = array();
 	var $cards = array();
 	var $cardHash= array();

	//specifically for Resolver transactions
 	var $resolveData;
 	var $resolveDataHash;
 	var $data_key="";
 	var $DataKeys = array();
 	var $isResolveData;
 	
 	//specifically for VdotMe transactions
 	var $vDotMeInfo;
 	var $isVdotMeInfo;
 	
 	//specifically for MasterPass transactions
 	var $isPaypass;
 	var $isPaypassInfo;
 	var $masterPassData = array();

 	//specifically for MPI transactions
 	var $ACSUrl;
 	var $isMPI = false;
 	
 	//specifically for Risk transactions
 	var $isResults;
 	var $isRule;
 	var $ruleName;
 	var $results = array();
 	var $rules = array();

 	function mpgResponse($xmlString)
 	{

  		$this->p = xml_parser_create();
  		xml_parser_set_option($this->p,XML_OPTION_CASE_FOLDING,0);
  		xml_parser_set_option($this->p,XML_OPTION_TARGET_ENCODING,"UTF-8");
  		xml_set_object($this->p,$this);
  		xml_set_element_handler($this->p,"startHandler","endHandler");
  		xml_set_character_data_handler($this->p,"characterHandler");
  		xml_parse($this->p,$xmlString);
  		xml_parser_free($this->p);

 	}	//end of constructor


 	function getMpgResponseData()
	{
   		return($this->responseData);
 	}

	function getRecurSuccess()
	{
 		return ($this->responseData['RecurSuccess']);
	}

	function getStatusCode()
	{
	 	return ($this->responseData['status_code']);
	}

	function getStatusMessage()
	{
	 	return ($this->responseData['status_message']);
	}

	function getAvsResultCode()
	{
		return ($this->responseData['AvsResultCode']);
	}

	function getCvdResultCode()
	{
		return ($this->responseData['CvdResultCode']);
	}

	function getCardType()
	{
 		return ($this->responseData['CardType']);
	}

	function getTransAmount()
	{
 		return ($this->responseData['TransAmount']);
	}

	function getTxnNumber()
	{
 		return ($this->responseData['TransID']);
	}

	function getReceiptId()
	{
 		return ($this->responseData['ReceiptId']);
	}

	function getTransType()
	{
 		return ($this->responseData['TransType']);
	}

	function getReferenceNum()
	{
 		return ($this->responseData['ReferenceNum']);
	}

	function getResponseCode()
	{
 		return ($this->responseData['ResponseCode']);
	}

	function getISO()
	{
 		return ($this->responseData['ISO']);
	}

	function getBankTotals()
	{
 		return ($this->responseData['BankTotals']);
	}

	function getMessage()
	{
 		return ($this->responseData['Message']);
	}

	function getAuthCode()
	{
 		return ($this->responseData['AuthCode']);
	}

	function getComplete()
	{
 		return ($this->responseData['Complete']);
	}

	function getTransDate()
	{
 		return ($this->responseData['TransDate']);
	}

	function getTransTime()
	{
 		return ($this->responseData['TransTime']);
	}

	function getTicket()
	{
 		return ($this->responseData['Ticket']);
	}

	function getTimedOut()
	{
 		return ($this->responseData['TimedOut']);
	}

	function getCorporateCard()
	{
		return ($this->responseData['CorporateCard']);
    }

    function getCavvResultCode()
    {
		return ($this->responseData['CavvResultCode']);
	}

	function getCardLevelResult()
	{
		return ($this->responseData['CardLevelResult']);
	}

	function getITDResponse()
	{
		return ($this->responseData['ITDResponse']);
	}
	
	function getIsVisaDebit()
	{
		return ($this->responseData['IsVisaDebit']);	
	}
	
	function getMaskedPan()
	{
		return ($this->responseData['MaskedPan']);
	}
	
	function getCfSuccess()
	{
		return ($this->responseData['CfSuccess']);
	}
	
	function getCfStatus()
	{
		return ($this->responseData['CfStatus']);
	}
	
	function getFeeAmount()
	{
		return ($this->responseData['FeeAmount']);
	}
	
	function getFeeRate()
	{
		return ($this->responseData['FeeRate']);
	}
	
	function getFeeType()
	{
		return ($this->responseData['FeeType']);
	}

	//--------------------------- RecurUpdate response fields ----------------------------//

	function getRecurUpdateSuccess()
	{
		return ($this->responseData['RecurUpdateSuccess']);
	}

	function getNextRecurDate()
	{
		return ($this->responseData['NextRecurDate']);
	}

	function getRecurEndDate()
	{
		return ($this->responseData['RecurEndDate']);
	}

	//-------------------------- Resolver response fields --------------------------------//

	function getDataKey()
	{
		return ($this->responseData['DataKey']);
	}

	function getResSuccess()
	{
		return ($this->responseData['ResSuccess']);
	}

	function getPaymentType()
	{
		return ($this->responseData['PaymentType']);
	}

	//------------------------------------------------------------------------------------//

	function getResolveData()
	{
		if($this->responseData['ResolveData']!='null'){
			return ($this->resolveData);
		}

		return $this->responseData['ResolveData'];
	}

	function setResolveData($data_key)
	{
		$this->resolveData=$this->resolveDataHash[$data_key];
	}

	function getResolveDataHash()
	{
		return ($this->resolveDataHash);
	}

	function getDataKeys()
	{
	 	return ($this->DataKeys);
 	}

 	function getResDataDataKey()
	{
		return ($this->resolveData['data_key']);
	}

	function getResDataPaymentType()
	{
		return ($this->resolveData['payment_type']);
	}

	function getResDataCustId()
	{
		return ($this->resolveData['cust_id']);
	}

	function getResDataPhone()
	{
		return ($this->resolveData['phone']);
	}

	function getResDataEmail()
	{
		return ($this->resolveData['email']);
	}

	function getResDataNote()
	{
		return ($this->resolveData['note']);
	}

	function getResDataPan()
	{
		return ($this->resolveData['pan']);
	}

	function getResDataMaskedPan()
	{
		return ($this->resolveData['masked_pan']);
	}

	function getResDataExpDate()
	{
		return ($this->resolveData['expdate']);
	}

	function getResDataAvsStreetNumber()
	{
		return ($this->resolveData['avs_street_number']);
	}

	function getResDataAvsStreetName()
	{
		return ($this->resolveData['avs_street_name']);
	}

	function getResDataAvsZipcode()
	{
		return ($this->resolveData['avs_zipcode']);
	}

	function getResDataCryptType()
	{
		return ($this->resolveData['crypt_type']);
	}
	
	function getResDataSec()
	{
		return ($this->resolveData['sec']);
	}
	
	function getResDataCustFirstName()
	{
		return ($this->resolveData['cust_first_name']);
	}
	
	function getResDataCustLastName()
	{
		return ($this->resolveData['cust_last_name']);
	}
	
	function getResDataCustAddress1()
	{
		return ($this->resolveData['cust_address1']);
	}
	
	function getResDataCustAddress2()
	{
		return ($this->resolveData['cust_address2']);
	}
	
	function getResDataCustCity()
	{
		return ($this->resolveData['cust_city']);
	}
	
	function getResDataCustState()
	{
		return ($this->resolveData['cust_state']);
	}
	
	function getResDataCustZip()
	{
		return ($this->resolveData['cust_zip']);
	}
	
	function getResDataRoutingNum()
	{
		return ($this->resolveData['routing_num']);
	}
	
	function getResDataAccountNum()
	{
		return ($this->resolveData['account_num']);
	}
	
	function getResDataMaskedAccountNum()
	{
		return ($this->resolveData['masked_account_num']);
	}
	
	function getResDataCheckNum()
	{
		return ($this->resolveData['check_num']);
	}
	
	function getResDataAccountType()
	{
		return ($this->resolveData['account_type']);
	}
	
	function getResDataPresentationType()
	{
		return ($this->resolveData['presentation_type']);
	}
	
	function getResDataPAccountNumber()
	{
		return ($this->resolveData['p_account_number']);
	}
	
	//-------------------------- VdotMe specific fields --------------------------------//
	function getVDotMeData()
	{
		return($this->vDotMeInfo);
	}
	
	function getCurrencyCode()
	{
		return ($this->vDotMeInfo['currencyCode']);
	}

	function getPaymentTotal()
	{
		return ($this->vDotMeInfo['total']);
	}

	function getUserFirstName()
	{
		return ($this->vDotMeInfo['userFirstName']);
	}

	function getUserLastName()
	{
		return ($this->vDotMeInfo['userLastName']);
	}

	function getUserName()
	{
		return ($this->vDotMeInfo['userName']);
	}

	function getUserEmail()
	{
		return ($this->vDotMeInfo['userEmail']);
	}

	function getEncUserId()
	{
		return ($this->vDotMeInfo['encUserId']);
	}

	function getCreationTimeStamp()
	{
		return ($this->vDotMeInfo['creationTimeStamp']);
	}

	function getNameOnCard()
	{
		return ($this->vDotMeInfo['nameOnCard']);
	}

	function getExpirationDateMonth()
	{
		return ($this->vDotMeInfo['expirationDate']['month']);
	}

	function getExpirationDateYear()
	{
		return ($this->vDotMeInfo['expirationDate']['year']);
	}

	function getBillingId()
	{
		return ($this->vDotMeInfo['id']);
	}
	
	function getLastFourDigits()
	{
		return ($this->vDotMeInfo['lastFourDigits']);
	}

	function getBinSixDigits()
	{
		return ($this->vDotMeInfo['binSixDigits']);
	}

	function getCardBrand()
	{
		return ($this->vDotMeInfo['cardBrand']);
	}

	function getVDotMeCardType()
	{
		return ($this->vDotMeInfo['cardType']);
	}
	
	function getBillingPersonName()
	{
		return ($this->vDotMeInfo['billingAddress']['personName']);
	}

	function getBillingAddressLine1()
	{
		return ($this->vDotMeInfo['billingAddress']['line1']);
	}

	function getBillingCity()
	{
		return ($this->vDotMeInfo['billingAddress']['city']);
	}

	function getBillingStateProvinceCode()
	{
		return ($this->vDotMeInfo['billingAddress']['stateProvinceCode']);
	}

	function getBillingPostalCode()
	{
		return ($this->vDotMeInfo['billingAddress']['postalCode']);
	}

	function getBillingCountryCode()
	{
		return ($this->vDotMeInfo['billingAddress']['countryCode']);
	}

	function getBillingPhone()
	{
		return ($this->vDotMeInfo['billingAddress']['phone']);
	}

	function getBillingVerificationStatus()
	{
		return ($this->vDotMeInfo['verificationStatus']);
	}
	
	function getIsExpired()
	{
		return ($this->vDotMeInfo['expired']);
	}

	function getPartialShippingCountryCode()
	{
		return ($this->vDotMeInfo['partialShippingAddress']['countryCode']);
	}

	function getPartialShippingPostalCode()
	{
		return ($this->vDotMeInfo['partialShippingAddress']['postalCode']);
	}

	function getShippingPersonName()
	{
		return ($this->vDotMeInfo['shippingAddress']['personName']);
	}

	function getShippingCity()
	{
		return ($this->vDotMeInfo['shippingAddress']['city']);
	}

	function getShippingStateProvinceCode()
	{
		return ($this->vDotMeInfo['shippingAddress']['stateProvinceCode']);
	}

	function getShippingPostalCode()
	{
		return ($this->vDotMeInfo['shippingAddress']['postalCode']);
	}

	function getShippingCountryCode()
	{
		return ($this->vDotMeInfo['shippingAddress']['countryCode']);
	}

	function getShippingPhone()
	{
		return ($this->vDotMeInfo['shippingAddress']['phone']);
	}

	function getShippingDefault()
	{
		return ($this->vDotMeInfo['shippingAddress']['default']);
	}

	function getShippingId()
	{
		return ($this->vDotMeInfo['shippingAddress']['id']);
	}

	function getShippingVerificationStatus()
	{
		return ($this->vDotMeInfo['shippingAddress']['verificationStatus']);
	}

	function getBaseImageFileName()
	{
		return ($this->vDotMeInfo['baseImageFileName']);
	}

	function getHeight()
	{
		return ($this->vDotMeInfo['height']);
	}

	function getWidth()
	{
		return ($this->vDotMeInfo['width']);
	}

	function getIssuerBid()
	{
		return ($this->vDotMeInfo['issuerBid']);
	}

	function getRiskAdvice()
	{
		return ($this->vDotMeInfo['riskData']['advice']);
	}

	function getRiskScore()
	{
		return ($this->vDotMeInfo['riskData']['score']);
	}

	function getAvsResponseCode()
	{
		return ($this->vDotMeInfo['riskData']['avsResponseCode']);
	}

	function getCvvResponseCode()
	{
		return ($this->vDotMeInfo['riskData']['cvvResponseCode']);
	}
	
	//--------------------------- MasterPass response fields -----------------------------//
	
	function getCardBrandId()
	{
		return $this->masterPassData['CardBrandId'];
	}
	
	
	function getCardBrandName()
	{
		return $this->masterPassData['CardBrandName'];
	}
	
	
	function getCardBillingAddressCity()
	{
		return $this->masterPassData['CardBillingAddressCity'];
	}
	
	
	function getCardBillingAddressCountry()
	{
		return $this->masterPassData['CardBillingAddressCountry'];
	}
	
	
	function getCardBillingAddressCountrySubdivision()
	{
		return $this->masterPassData['CardBillingAddressCountrySubdivision'];
	}
	
	
	function getCardBillingAddressLine1()
	{
		return $this->masterPassData['CardBillingAddressLine1'];
	}
	
	
	function getCardBillingAddressLine2()
	{
		return $this->masterPassData['CardBillingAddressLine2'];
	}
	
	
	function getCardBillingAddressPostalCode()
	{
		return $this->masterPassData['CardBillingAddressPostalCode'];
	}
	
	
	function getCardBillingAddressRecipientPhoneNumber()
	{
		return $this->masterPassData['CardBillingAddressRecipientPhoneNumber'];
	}
	
	
	function getCardBillingAddressRecipientName()
	{
		return $this->masterPassData['CardBillingAddressRecipientName'];
	}
	
	
	function getCardCardHolderName()
	{
		return $this->masterPassData['CardCardHolderName'];
	}
	
	
	function getCardExpiryMonth()
	{
		return $this->masterPassData['CardExpiryMonth'];
	}
	
	
	function getCardExpiryYear()
	{
		return $this->masterPassData['CardExpiryYear'];
	}
	
	
	function getContactEmailAddress()
	{
		return $this->masterPassData['ContactEmailAddress'];
	}
	
	
	function getContactFirstName()
	{
		return $this->masterPassData['ContactFirstName'];
	}
	
	
	function getContactLastName()
	{
		return $this->masterPassData['ContactLastName'];
	}
	
	
	function getContactPhoneNumber()
	{
		return $this->masterPassData['ContactPhoneNumber'];
	}
	
	
	function getShippingAddressCity()
	{
		return $this->masterPassData['ShippingAddressCity'];
	}
	
	
	function getShippingAddressCountry()
	{
		return $this->masterPassData['ShippingAddressCountry'];
	}
	
	
	function getShippingAddressCountrySubdivision()
	{
		return $this->masterPassData['ShippingAddressCountrySubdivision'];
	}
	
	function getShippingAddressLine2()
	{
		return $this->masterPassData['ShippingAddressLine2'];
	}
	
	
	function getShippingAddressPostalCode()
	{
		return $this->masterPassData['ShippingAddressPostalCode'];
	}
	
	
	function getShippingAddressRecipientName()
	{
		return $this->masterPassData['ShippingAddressRecipientName'];
	}
	
	
	function getShippingAddressRecipientPhoneNumber()
	{
		return $this->masterPassData['ShippingAddressRecipientPhoneNumber'];
	}
	
	
	function getPayPassWalletIndicator()
	{
		return $this->masterPassData['PayPassWalletIndicator'];
	}
	
	
	function getAuthenticationOptionsAuthenticateMethod()
	{
		return $this->masterPassData['AuthenticationOptionsAuthenticateMethod'];
	}
	
	
	function getAuthenticationOptionsCardEnrollmentMethod()
	{
		return $this->masterPassData['AuthenticationOptionsCardEnrollmentMethod'];
	}
	
	
	function getCardAccountNumber()
	{
		return $this->masterPassData['CardAccountNumber'];
	}
	
	
	function getAuthenticationOptionsEciFlag()
	{
		return $this->masterPassData['AuthenticationOptionsEciFlag'];
	}
	
	
	function getAuthenticationOptionsPaResStatus()
	{
		return $this->masterPassData['AuthenticationOptionsPaResStatus'];
	}
	
	
	function getAuthenticationOptionsSCEnrollmentStatus()
	{
		return $this->masterPassData['AuthenticationOptionsSCEnrollmentStatus'];
	}
	
	
	function getAuthenticationOptionsSignatureVerification()
	{
		return $this->masterPassData['AuthenticationOptionsSignatureVerification'];
	}
	
	
	function getAuthenticationOptionsXid()
	{
		return $this->masterPassData['AuthenticationOptionsXid'];
	}
	
	
	function getAuthenticationOptionsCAvv()
	{
		return $this->masterPassData['AuthenticationOptionsCAvv'];
	}
	
	
	function getTransactionId()
	{
		return $this->masterPassData['TransactionId'];
	}
	
	function getMPRequestToken()
	{
		return ($this->responseData['MPRequestToken']);
	}
	
	function getMPRedirectUrl()
	{
		return ($this->responseData['MPRedirectUrl']);
	}
	
	//------------------- VDotMe & MasterPass shared response fields ---------------------//
	
	function getShippingAddressLine1()
	{
		if ($this->isPaypass)
		{
			return $this->masterPassData['ShippingAddressLine1'];
		}
		else
		{
			return ($this->vDotMeInfo['shippingAddress']['line1']);
		}
	}
//------------------- MPI response fields ---------------------//
	function getMpiType()
	{
		return ($this->responseData['MpiType']);
	}

	function getMpiSuccess()
	{
		if ($this->isMPI === false)
		{
			return ($this->responseData['MpiSuccess']);
		}
		else
		{
			return ($this->responseData['success']);
		}
	}

	function getMpiMessage()
	{
		if ($this->isMPI === false)
		{
			return ($this->responseData['MpiMessage']);
		}
		else
		{
			return ($this->responseData['message']);
		}
	}
	
	function getMpiPaReq()
	{
		if ($this->isMPI === false)
		{
			return ($this->responseData['MpiPaReq']);
		}
		else
		{
			return ($this->responseData['PaReq']);
		}
	}

	function getMpiTermUrl()
	{
		if ($this->isMPI === false)
		{
			return ($this->responseData['MpiTermUrl']);
		}
		else
		{
			return ($this->responseData['TermUrl']);
		}
	}
	
	function getMpiMD()
	{
		if ($this->isMPI === false)
		{
			return ($this->responseData['MpiMD']);
		}
		else
		{
			return ($this->responseData['MD']);
		}
	}

	function getMpiACSUrl()
	{
		if ($this->isMPI === false)
		{
			return ($this->responseData['MpiACSUrl']);
		}
		else
		{
			return ($this->responseData['ACSUrl']);
		}
	}
	
	function getMpiCavv()
	{
		if ($this->isMPI === false)
		{
			return ($this->responseData['MpiCavv']);
		}
		else
		{
			return ($this->responseData['cavv']);
		}
	}

	function getMpiEci()
	{
		if ($this->isMPI === false)
		{
			return ($this->responseData['MpiEci']);
		}
		else
		{
			return ($this->responseData['eci']);
		}
	}


	function getMpiPAResVerified()
	{
		if ($this->isMPI === false)
		{
			return ($this->responseData['MpiPAResVerified']);
		}
		else
		{
			return ($this->responseData['PAResVerified']);
		}
	}
	
	function getMpiResponseData()
	{
		return($this->responseData);
	}
	
	function getMpiInLineForm()
	{
		
		$inLineForm ='<html><head><title>Title for Page</title></head><SCRIPT LANGUAGE="Javascript" >' . 
				"<!--
				function OnLoadEvent()
				{
					document.downloadForm.submit();
				}
				-->
				</SCRIPT>" .
				'<body onload="OnLoadEvent()">
					<form name="downloadForm" action="' . $this->getMpiACSUrl() . 
					'" method="POST">
					<noscript>
					<br>
					<br>
					<center>
					<h1>Processing your 3-D Secure Transaction</h1>
					<h2>
					JavaScript is currently disabled or is not supported
					by your browser.<br>
					<h3>Please click on the Submit button to continue
					the processing of your 3-D secure
					transaction.</h3>
					<input type="submit" value="Submit">
					</center>
					</noscript>
					<input type="hidden" name="PaReq" value="' . $this->getMpiPaReq() . '">
					<input type="hidden" name="MD" value="' . $this->getMpiMD() . '">
					<input type="hidden" name="TermUrl" value="' . $this->getMpiTermUrl() .'">
				</form>
				</body>
				</html>';
	
		return $inLineForm; 
	}
	
	function getMpiPopUpWindow()
	{
		$popUpForm ='<html><head><title>Title for Page</title></head><SCRIPT LANGUAGE="Javascript" >' .
				"<!--
					function OnLoadEvent()
					{
						window.name='mainwindow';
						//childwin = window.open('about:blank','popupName','height=400,width=390,status=yes,dependent=no,scrollbars=yes,resizable=no');
						//document.downloadForm.target = 'popupName';
						document.downloadForm.submit();
					}
					-->
					</SCRIPT>" .
						'<body onload="OnLoadEvent()">
						<form name="downloadForm" action="' . $this->getMpiAcsUrl() .
							'" method="POST">
						<noscript>
						<br>
						<br>
						<center>
						<h1>Processing your 3-D Secure Transaction</h1>
						<h2>
						JavaScript is currently disabled or is not supported
						by your browser.<br>
						<h3>Please click on the Submit button to continue
						the processing of your 3-D secure
						transaction.</h3>
						<input type="submit" value="Submit">
						</center>
						</noscript>
						<input type="hidden" name="PaReq" value="' . $this->getMpiPaReq() . '">
						<input type="hidden" name="MD" value="' . $this->getMpiMD() . '">
						<input type="hidden" name="TermUrl" value="' . $this->getMpiTermUrl() .'">
						</form>
					</body>
					</html>';
	
		return $popUpForm;
	}
	
	
	//-----------------  Risk response fields  ---------------------------------------------------------//
	
	function getRiskResponse()
	{
		return($this->responseData);
	}
	
	function getResults()
	{
		return ($this->results);
	}
	
	function getRules()
	{
		return ($this->rules);
	}
	
	//--------------------------- BatchClose response fields -----------------------------//

	function getTerminalStatus($ecr_no)
	{
 		return ($this->ecrHash[$ecr_no]);
	}

	function getPurchaseAmount($ecr_no,$card_type)
	{
 		return ($this->purchaseHash[$ecr_no][$card_type]['Amount']=="" ? 0:$this->purchaseHash[$ecr_no][$card_type]['Amount']);
	}

	function getPurchaseCount($ecr_no,$card_type)
	{
 		return ($this->purchaseHash[$ecr_no][$card_type]['Count']=="" ? 0:$this->purchaseHash[$ecr_no][$card_type]['Count']);
	}

	function getRefundAmount($ecr_no,$card_type)
	{
 		return ($this->refundHash[$ecr_no][$card_type]['Amount']=="" ? 0:$this->refundHash[$ecr_no][$card_type]['Amount']);
	}

	function getRefundCount($ecr_no,$card_type)
	{
 		return ($this->refundHash[$ecr_no][$card_type]['Count']=="" ? 0:$this->refundHash[$ecr_no][$card_type]['Count']);
	}

	function getCorrectionAmount($ecr_no,$card_type)
	{
 		return ($this->correctionHash[$ecr_no][$card_type]['Amount']=="" ? 0:$this->correctionHash[$ecr_no][$card_type]['Amount']);
	}

	function getCorrectionCount($ecr_no,$card_type)
	{
 		return ($this->correctionHash[$ecr_no][$card_type]['Count']=="" ? 0:$this->correctionHash[$ecr_no][$card_type]['Count']);
	}

	function getTerminalIDs()
	{
 		return ($this->ecrs);
	}

	function getCreditCardsAll()
	{
 		return (array_keys($this->cards));
	}

	function getCreditCards($ecr)
	{
 		return ($this->cardHash[$ecr]);
	}



	function characterHandler($parser,$data)
	{
		if($this->isBatchTotals)
 		{
   			switch($this->currentTag)
    		{
     			case "term_id"    :
				{
					$this->term_id=$data;
					array_push($this->ecrs,$this->term_id);
					$this->cardHash[$data]=array();
					break;
				}

     			case "closed"     :
				{
					$ecrHash=$this->ecrHash;
					$ecrHash[$this->term_id]=$data;
					$this->ecrHash = $ecrHash;
					break;
				}

     			case "CardType"   :
				{
					$this->CardType=$data;
					$this->cards[$data]=$data;
					array_push($this->cardHash[$this->term_id],$data) ;
					break;
				}

     			case "Amount"     :
				{
					if($this->currentTxnType == "Purchase")
					{
						$this->purchaseHash[$this->term_id][$this->CardType]['Amount']=$data;
					}
					elseif( $this->currentTxnType == "Refund")
					{
						$this->refundHash[$this->term_id][$this->CardType]['Amount']=$data;
					}
					elseif( $this->currentTxnType == "Correction")
					{
						$this->correctionHash[$this->term_id][$this->CardType]['Amount']=$data;
					}
					break;
				 }

    			case "Count"     :
				{
					if($this->currentTxnType == "Purchase")
					{
						$this->purchaseHash[$this->term_id][$this->CardType]['Count']=$data;
					}
					elseif( $this->currentTxnType == "Refund")
					{
						$this->refundHash[$this->term_id][$this->CardType]['Count']=$data;
					}
					else if( $this->currentTxnType == "Correction")
					{
						$this->correctionHash[$this->term_id][$this->CardType]['Count']=$data;
					}
					break;
				}
	    	}

 		}
 		elseif($this->isResolveData && $this->currentTag != "ResolveData")
 		{
			if($this->currentTag == "data_key")
			{
				$this->data_key=$data;
				array_push($this->DataKeys,$this->data_key);
				$this->resolveData[$this->currentTag] .=$data;
			}
   			else
   			{
   				$this->resolveData[$this->currentTag] .=$data;
   			}
 		}
 		elseif($this->isVdotMeInfo)
 		{
 			if($this->ParentNode != "")
 				$this->vDotMeInfo[$this->ParentNode][$this->currentTag] .=$data;
 			else
 				$this->vDotMeInfo[$this->currentTag] .=$data;
 		}
 		else if ($this->isPaypassInfo)
 		{
 			$this->masterPassData[$this->currentTag] .=$data;
 		}
 		elseif($this->isResults)
 		{
 			$this->results[$this->currentTag] = $data;
 			 
 		}
 		elseif($this->isRule)
 		{
 		
 			if ($this->currentTag == "RuleName")
 			{
 				$this->ruleName=$data;
 			}
 			$this->rules[$this->ruleName][$this->currentTag] = $data;
 		
 		}
 		else
 		{
 			$this->responseData[$this->currentTag] .=$data;
 		}

	}//end characterHandler



	function startHandler($parser,$name,$attrs)
	{

		$this->currentTag=$name;

		if($this->currentTag == "ResolveData")
		{
			$this->isResolveData=1;
  	 	}
  	 	elseif($this->isResolveData)
  	 	{
  	 		$this->resolveData[$this->currentTag]="";
  	 	}
  	 	elseif($this->currentTag == "MpiResponse")
  	 	{
  	 		$this->isMPI=true;
  	 	}
  	 	elseif($this->currentTag == "VDotMeInfo")
  	 	{
  	 		$this->isVdotMeInfo=1;
  	 	}
  	 	elseif($this->isVdotMeInfo)
  	 	{
  	 		//$this->vDotMeInfo[$this->currentTag]="";
  	 		switch($name){
  	 			case "billingAddress":
  	 				{
  	 					$this->ParentNode=$name;
  	 					break;
  	 				}
  	 			case "partialShippingAddress":
  	 				{
  	 					$this->ParentNode=$name;
  	 					break;
  	 				}
  	 			case "shippingAddress":
  	 				{
  	 					$this->ParentNode=$name;
  	 					break;
  	 				}
  	 			case "riskData":
  	 				{
  	 					$this->ParentNode=$name;
  	 					break;
  	 				}
  	 			case "expirationDate":
  	 				{
  	 					$this->ParentNode=$name;
  	 					break;
  	 				}
  	 		}
  	 	}
  	 	else if($this->currentTag == "PayPassInfo")
  	 	{
  	 		$this->isPaypassInfo=1;
  	 		$this->isPaypass=1;
  	 	}
  		elseif($this->currentTag == "BankTotals")
  	 	{
  	  		$this->isBatchTotals=1;
  	 	}
  		elseif($this->currentTag == "Purchase")
   		{
   	 		$this->purchaseHash[$this->term_id][$this->CardType]=array();
   	 		$this->currentTxnType="Purchase";
   		}
  		elseif($this->currentTag == "Refund")
  	 	{
  	  		$this->refundHash[$this->term_id][$this->CardType]=array();
  	  		$this->currentTxnType="Refund";
  	 	}
  		elseif($this->currentTag == "Correction")
   		{
   	 		$this->correctionHash[$this->term_id][$this->CardType]=array();
   	 		$this->currentTxnType="Correction";
   		}
   		elseif($this->currentTag == "Result")
   		{
   			$this->isResults=1;
   		}
   		elseif($this->currentTag == "Rule")
   		{
   			$this->isRule=1;
   		}
	}

	function endHandler($parser,$name)
	{

	 	$this->currentTag=$name;
	 	if($this->currentTag == "ResolveData")
		{
			$this->isResolveData=0;
			if($this->data_key!="")
			{
				$this->resolveDataHash[$this->data_key]=$this->resolveData;
				$this->resolveData=array();
			}
	 	} 	
	 	elseif($this->currentTag == "VDotMeInfo")
	 	{
	 		$this->isVdotMeInfo=0;
	 	} 	
	 	elseif($this->isVdotMeInfo)
	 	{
	 		switch($this->currentTag){
	 			case "billingAddress":
	 				{
	 					$this->ParentNode="";
	 					break;
	 				}
	 			case "partialShippingAddress":
	 				{
	 					$this->ParentNode="";
	 					break;
	 				}
	 			case "shippingAddress":
	 				{
	 					$this->ParentNode="";
	 					break;
	 				}
	 			case "riskData":
	 				{
	 					$this->ParentNode="";
	 					break;
	 				}
	 			case "expirationDate":
	 				{
	 					$this->ParentNode="";
	 					break;
	 				}	
	 		}
	 	}
	 	elseif($name == "BankTotals")
	  	{
	    	$this->isBatchTotals=0;
	   	}
	   	else if($this->currentTag == "PayPassInfo")
	   	{
	   		$this->isPaypassInfo=0;
	   	}
	   	elseif($name == "Result")
	   	{
	   		$this->isResults=0;
	   	}
	   	elseif($this->currentTag == "Rule")
	   	{
	   		$this->isRule=0;
	   	}

 		$this->currentTag="/dev/null";
	}

}//end class mpgResponse


################## mpgRequest ###########################################################

class mpgRequest
{

 	var $txnTypes =array(
 				//Basic
 				'batchclose' => array('ecr_number'),
 				'card_verification' =>array('order_id','cust_id','pan','expdate', 'crypt_type'),
 				'cavv_preauth' =>array('order_id','cust_id', 'amount', 'pan','expdate', 'cavv','crypt_type','dynamic_descriptor', 'wallet_indicator'),
 				'cavv_purchase' => array('order_id','cust_id', 'amount', 'pan','expdate', 'cavv','crypt_type','dynamic_descriptor', 'wallet_indicator'),
 				'completion' => array('order_id', 'comp_amount','txn_number', 'crypt_type', 'cust_id', 'dynamic_descriptor'),
 				'contactless_purchase' => array('order_id','cust_id','amount','track2','pan','expdate', 'pos_code','dynamic_descriptor'),
 				'contactless_purchasecorrection' => array('order_id','txn_number'),
 				'contactless_refund' => array('order_id','amount','txn_number'),
 				'forcepost'=> array('order_id','cust_id','amount','pan','expdate','auth_code','crypt_type','dynamic_descriptor'),
 				'ind_refund' => array('order_id','cust_id', 'amount','pan','expdate', 'crypt_type','dynamic_descriptor'),
	 			'opentotals' => array('ecr_number'),
	 			'preauth' =>array('order_id','cust_id', 'amount', 'pan', 'expdate', 'crypt_type','dynamic_descriptor'),
	 			'purchase'=> array('order_id','cust_id', 'amount', 'pan', 'expdate', 'crypt_type','dynamic_descriptor'),
	 			'purchasecorrection' => array('order_id', 'txn_number', 'crypt_type', 'cust_id', 'dynamic_descriptor'),
	 			'reauth' =>array('order_id','cust_id', 'amount', 'orig_order_id', 'txn_number', 'crypt_type', 'dynamic_descriptor'),
	 			'recur_update' => array('order_id','cust_id','pan','expdate','recur_amount','add_num_recurs','total_num_recurs','hold','terminate'),
	 			'refund' => array('order_id', 'amount', 'txn_number', 'crypt_type', 'cust_id', 'dynamic_descriptor'),
 				
 				//Encrypted
 				'enc_card_verification' => array('order_id','cust_id','enc_track2','device_type', 'crypt_type'),
 				'enc_forcepost' => array('order_id','cust_id','amount','enc_track2','device_type','auth_code','crypt_type','dynamic_descriptor'),
 				'enc_ind_refund' => array('order_id','cust_id','amount','enc_track2','device_type','crypt_type','dynamic_descriptor'),
 				'enc_preauth' => array('order_id','cust_id','amount','enc_track2','device_type','crypt_type','dynamic_descriptor'),
 				'enc_purchase' => array('order_id','cust_id','amount','enc_track2','device_type','crypt_type','dynamic_descriptor'),
 				'enc_res_add_cc' => array('cust_id','phone','email','note','enc_track2','device_type','crypt_type'),
 				'enc_res_update_cc' => array('data_key','cust_id','phone','email','note','enc_track2','device_type','crypt_type'),
 				'enc_track2_forcepost' => array('order_id','cust_id','amount','enc_track2','pos_code','device_type','auth_code','dynamic_descriptor'),
 				'enc_track2_ind_refund' => array('order_id','cust_id','amount','enc_track2','pos_code','device_type','dynamic_descriptor'),
 				'enc_track2_preauth' => array('order_id','cust_id','amount','enc_track2','pos_code','device_type','dynamic_descriptor'),
 				'enc_track2_purchase' => array('order_id','cust_id','amount','enc_track2','pos_code','device_type','dynamic_descriptor'),
 				
 				//Interac Online
	 			'idebit_purchase' =>array('order_id', 'cust_id', 'amount','idebit_track2','dynamic_descriptor'),
	 			'idebit_refund' =>array('order_id','amount','txn_number'),
 			
 				//Vault
 				'res_add_cc' => array('cust_id','phone','email','note','pan','expdate','crypt_type'),
				'res_add_token' => array('data_key','cust_id','phone','email','note','expdate','crypt_type'),
 				'res_card_verification_cc' => array('data_key','order_id', 'crypt_type', 'expdate'),
 				'res_cavv_preauth_cc' => array('data_key','order_id','cust_id','amount','cavv','crypt_type','dynamic_descriptor','expdate'),
 				'res_cavv_purchase_cc' => array('data_key','order_id','cust_id','amount','cavv','crypt_type','dynamic_descriptor','expdate'),
 				'res_delete' => array('data_key'),
 				'res_get_expiring' => array(),
 				'res_ind_refund_cc' => array('data_key','order_id','cust_id','amount','crypt_type','dynamic_descriptor'),
				'res_iscorporatecard' => array('data_key'),
 				'res_lookup_full' => array('data_key'),
				'res_lookup_masked' => array('data_key'),
 				'res_mpitxn' => array('data_key','xid','amount','MD','merchantUrl','accept','userAgent','expdate'),
 				'res_preauth_cc' => array('data_key','order_id','cust_id','amount','crypt_type','dynamic_descriptor','expdate'),
				'res_purchase_cc' => array('data_key','order_id','cust_id','amount','crypt_type','dynamic_descriptor','expdate'),
 				'res_temp_add' => array('pan','expdate','crypt_type','duration'),
 				'res_temp_tokenize' => array('order_id', 'txn_number', 'duration', 'crypt_type'),
				'res_tokenize_cc' => array('order_id','txn_number','cust_id','phone','email','note'),
				'res_update_cc' => array('data_key','cust_id','phone','email','note','pan','expdate','crypt_type'),
 				
 				//Track2
 				'track2_completion' => array('order_id', 'comp_amount','txn_number','pos_code','dynamic_descriptor'),
 				'track2_forcepost'=>array('order_id','cust_id', 'amount', 'track2','pan','expdate','pos_code','auth_code','dynamic_descriptor'),
				'track2_ind_refund' => array('order_id','amount','track2','pan','expdate','cust_id','pos_code','dynamic_descriptor'),
	 			'track2_preauth' => array('order_id','cust_id','amount','track2','pan','expdate','pos_code','dynamic_descriptor'),
	 			'track2_purchase' =>array('order_id','cust_id','amount','track2','pan','expdate','pos_code','dynamic_descriptor'),
	 			'track2_purchasecorrection' => array('order_id', 'txn_number'),
	 			'track2_refund' => array('order_id', 'amount', 'txn_number','dynamic_descriptor'),
 				
 				//VDotMe
 				'vdotme_completion' => array('order_id','comp_amount','txn_number','crypt_type','cust_id','dynamic_descriptor'),
 				'vdotme_getpaymentinfo' => array('callid'),
 				'vdotme_preauth' => array('order_id','amount','callid','crypt_type','cust_id','dynamic_descriptor'),
 				'vdotme_purchase' => array('order_id','amount','callid','crypt_type','cust_id','dynamic_descriptor'),
 				'vdotme_purchasecorrection' => array('order_id','txn_number','crypt_type','cust_id','dynamic_descriptor'),
 				'vdotme_reauth' => array('order_id','orig_order_id','txn_number','amount','crypt_type','cust_id','dynamic_descriptor'),
 				'vdotme_refund' => array('order_id','txn_number','amount','crypt_type','cust_id','dynamic_descriptor'),
 				
 				//MasterPass
	 			'paypass_send_shopping_cart' => array('subtotal', 'suppress_shipping_address'),
	 			'paypass_retrieve_checkout_data' => array('oauth_token', 'oauth_verifier', 'checkout_resource_url'),
	 			'paypass_purchase' => array('order_id', 'cust_id', 'amount', 'mp_request_token', 'crypt_type', 'dynamic_descriptor'),
	 			'paypass_cavv_purchase' => array('order_id', 'cavv', 'cust_id', 'amount', 'mp_request_token', 'crypt_type', 'dynamic_descriptor'),
	 			'paypass_preauth' => array('order_id', 'cust_id', 'amount', 'mp_request_token', 'crypt_type', 'dynamic_descriptor'),
	 			'paypass_cavv_preauth' => array('order_id', 'cavv', 'cust_id', 'amount', 'mp_request_token', 'crypt_type', 'dynamic_descriptor'),
	 			'paypass_txn' => array('xid', 'amount', 'mp_request_token', 'MD', 'merchantUrl', 'accept', 'userAgent'),
 				
 				//US ACH
	 			'us_ach_credit' => array('order_id','cust_id','amount'),
 				'us_ach_debit' => array('order_id','cust_id','amount'),
	 			'us_ach_fi_enquiry' => array('routing_num'),
	 			'us_ach_reversal' => array('order_id','txn_number'),
	 			
 				//US Basic
 				'us_batchclose' => array('ecr_number'),
 				'us_card_verification' => array('order_id','cust_id','pan','expdate'),
 				'us_cavv_preauth' => array('order_id','cust_id', 'amount', 'pan','expdate', 'cavv','crypt_type','dynamic_descriptor', 'wallet_indicator'),
 				'us_cavv_purchase'=> array('order_id','cust_id','amount','pan','expdate', 'cavv', 'commcard_invoice','commcard_tax_amount','crypt_type',
 					'dynamic_descriptor', 'wallet_indicator'),
 				'us_completion' => array('order_id', 'comp_amount','txn_number', 'crypt_type', 'commcard_invoice','commcard_tax_amount'),
 				'us_contactless_purchase' => array('order_id','cust_id','amount','track2','pan','expdate','commcard_invoice','commcard_tax_amount','pos_code','dynamic_descriptor'),
 				'us_contactless_purchasecorrection' => array('order_id','txn_number'),
 				'us_contactless_refund' => array('order_id','amount','txn_number'),
	 			'us_forcepost'=> array('order_id','cust_id','amount','pan','expdate','auth_code','crypt_type','dynamic_descriptor'),
 				'us_ind_refund' => array('order_id','cust_id', 'amount','pan','expdate', 'crypt_type','dynamic_descriptor'),
	 			'us_opentotals' => array('ecr_number'),
	 			'us_pinless_debit_purchase' => array('order_id','amount','pan','expdate','cust_id','presentation_type','intended_use','p_account_number'),
	 			'us_pinless_debit_refund' => array('order_id', 'amount', 'txn_number'),
	 			'us_preauth' => array('order_id','cust_id', 'amount', 'pan', 'expdate', 'crypt_type', 'dynamic_descriptor'),
	 			'us_purchase'=> array('order_id','cust_id', 'amount', 'pan', 'expdate', 'crypt_type', 'commcard_invoice','commcard_tax_amount','dynamic_descriptor'),
	 			'us_purchasecorrection' => array('order_id', 'txn_number', 'crypt_type'),
	 			'us_reauth' => array('order_id','cust_id','orig_order_id','txn_number','amount','crypt_type'),
	 			'us_recur_update' => array('order_id', 'cust_id', 'pan', 'expdate', 'recur_amount','add_num_recurs', 'total_num_recurs', 'hold', 'terminate','avs_street_number', 'avs_street_name', 'avs_zipcode'),
	 			'us_refund' => array('order_id', 'amount', 'txn_number', 'crypt_type'),
 				
 				//US Encrypted
 				'us_enc_card_verification' => array('order_id','cust_id','enc_track2','device_type'),
 				'us_enc_forcepost' => array('order_id','cust_id','amount','enc_track2','device_type','auth_code','crypt_type','dynamic_descriptor'),
 				'us_enc_ind_refund' => array('order_id','cust_id','amount','enc_track2','device_type','crypt_type','dynamic_descriptor'),
 				'us_enc_preauth' => array('order_id','cust_id','amount','enc_track2','device_type','crypt_type','dynamic_descriptor'),
 				'us_enc_purchase' => array('order_id','cust_id','amount','enc_track2','device_type','crypt_type','commcard_invoice','commcard_tax_amount','dynamic_descriptor'),
	 			'us_enc_res_add_cc' => array('cust_id','phone','email','note','enc_track2','device_type','crypt_type'),
	 			'us_enc_res_update_cc' => array('data_key','cust_id','phone','email','note','enc_track2','device_type','crypt_type'),
 				'us_enc_track2_forcepost' => array('order_id','cust_id','amount','enc_track2','pos_code','device_type','auth_code','dynamic_descriptor'),
 				'us_enc_track2_ind_refund' => array('order_id','cust_id','amount','enc_track2','pos_code','device_type','dynamic_descriptor'),
	 			'us_enc_track2_preauth' => array('order_id','cust_id','amount','enc_track2','pos_code','device_type','dynamic_descriptor'),
	 			'us_enc_track2_purchase' => array('order_id','cust_id','amount','enc_track2','pos_code','device_type','commcard_invoice','commcard_tax_amount','dynamic_descriptor'),
 				
 				//US Vault
 				'us_res_add_cc' => array('cust_id','phone','email','note','pan','expdate','crypt_type'),
 				'us_res_add_ach' => array('cust_id','phone','email','note'),
 				'us_res_add_pinless' => array('cust_id','phone','email','note','pan','expdate','presentation_type','p_account_number'),
 				'us_res_add_token' => array('cust_id','phone','email','note','data_key','crypt_type','expdate'),
 				'us_res_delete' => array('data_key'),
 				'us_res_get_expiring' => array(),
 				'us_res_ind_refund_ach' => array('data_key','order_id','cust_id','amount'),
 				'us_res_ind_refund_cc' => array('data_key','order_id','cust_id','amount','crypt_type','dynamic_descriptor'),
 				'us_res_iscorporatecard' => array('data_key'),
 				'us_res_lookup_full' => array('data_key'),
 				'us_res_lookup_masked' => array('data_key'),
 				'us_res_preauth_cc' => array('data_key','order_id','cust_id','amount','crypt_type','dynamic_descriptor'),
 				'us_res_purchase_ach' => array('data_key','order_id','cust_id','amount'),
 				'us_res_purchase_cc' => array('data_key','order_id','cust_id','amount','crypt_type','commcard_invoice','commcard_tax_amount','dynamic_descriptor'),
 				'us_res_purchase_pinless' => array('data_key','order_id','cust_id','amount','intended_use','p_account_number'),
 				'us_res_temp_add' => array('pan','expdate','duration','crypt_type'),	
 				'us_res_tokenize_cc' => array('order_id','txn_number','cust_id','phone','email','note'),
 				'us_res_update_cc' => array('data_key','cust_id','phone','email','note','pan','expdate','crypt_type'),
 				'us_res_update_ach' => array('data_key','cust_id','phone','email','note'),
 				'us_res_update_pinless' => array('data_key','cust_id','phone','email','note','pan','expdate','presentation_type','p_account_number'),
 				
 				//US Track2
	 			'us_track2_completion' => array('order_id', 'comp_amount','txn_number','pos_code', 'commcard_invoice','commcard_tax_amount'),
	 			'us_track2_forcepost'=>array('order_id','cust_id', 'amount', 'track2','pan','expdate','pos_code','auth_code','dynamic_descriptor'),
 				'us_track2_ind_refund' => array('order_id','amount','track2','pan','expdate','cust_id','pos_code','dynamic_descriptor'),
 				'us_track2_preauth' => array('order_id','cust_id','amount','track2','pan','expdate','pos_code','dynamic_descriptor'),
 				'us_track2_purchase' =>array('order_id','cust_id','amount','track2','pan','expdate', 'commcard_invoice','commcard_tax_amount','pos_code','dynamic_descriptor'),
	 			'us_track2_purchasecorrection' => array('order_id', 'txn_number'),
	 			'us_track2_refund' => array('order_id', 'amount', 'txn_number'),
 				
 				//MPI - Common CA and US
	 			'txn' =>array('xid', 'amount', 'pan', 'expdate','MD', 'merchantUrl','accept','userAgent','currency','recurFreq', 'recurEnd','install'),
	 			'acs'=> array('PaRes','MD'),
 				
 				//Group Transaction - Common CA and US
 				'group'=> array('order_id', 'txn_number', 'group_ref_num', 'group_type'),
 			
 				//Risk - CA only
 				'session_query' => array('order_id','session_id','service_type','event_type'),
 				'attribute_query' => array('order_id','policy_id','service_type')
			);

	var $txnArray;
	var $procCountryCode = "";
	var $testMode = "";
	var $isMPI = "";
	
	function mpgRequest($txn)
	{

 		if(is_array($txn))
   		{
    			$this->txnArray = $txn;
   		}
 		else
   		{
    			$temp[0]=$txn;
    			$this->txnArray=$temp;
   		}
	}
	
	function setProcCountryCode($countryCode)
	{
		$this->procCountryCode = ((strcmp(strtolower($countryCode), "us") >= 0) ? "_US" : "");
	}
	
	function getIsMPI() 
	{
		$txnType = $this->getTransactionType();
		
		if((strcmp($txnType, "txn") === 0) || (strcmp($txnType, "acs") === 0))
  		{
  			//$this->setIsMPI(true);
  			return true;
  		}
  		else
  		{
  			return false;
  		}
	}
	
	function setTestMode($state)
	{
		if($state === true)
		{
			$this->testMode = "_TEST";
		}
		else
		{
			$this->testMode = "";
		}
	}

	function getTransactionType()
	{
  		$jtmp=$this->txnArray;
  		$jtmp1=$jtmp[0]->getTransaction();
  		$jtmp2=array_shift($jtmp1);
  		return $jtmp2;
	}
	
	function getURL()
	{
		$g=new mpgGlobals();
  		$gArray=$g->getGlobals();
  		
  		$txnType = $this->getTransactionType();
  		
  		if(strpos($txnType, "us_") !== false)
  		{
  			$this->setProcCountryCode("US");
  		}
  		
  		if((strcmp($txnType, "txn") === 0) || (strcmp($txnType, "acs") === 0))
  		{
  			//$this->setIsMPI(true);
  			$this->isMPI = "_MPI";
  		}
  		else
  		{
  			$this->isMPI = "";
  		}
  		
  		$hostId = "MONERIS".$this->procCountryCode.$this->testMode."_HOST";
  		$fileId = "MONERIS".$this->procCountryCode.$this->isMPI."_FILE";
  		
  		$url =  $gArray['MONERIS_PROTOCOL']."://".
  				$gArray[$hostId].":".
  				$gArray['MONERIS_PORT'].
  				$gArray[$fileId];
  		
  		//echo "PostURL: " . $url;
  		
  		return $url;
	}

	var $xmlString;
	function toXML()
	{
 		$tmpTxnArray=$this->txnArray;
 		$txnArrayLen=count($tmpTxnArray); //total number of transactions

 		for($x=0;$x < $txnArrayLen;$x++)
 		{
			$txnObj=$tmpTxnArray[$x];
			$txn=$txnObj->getTransaction();

			$txnType=array_shift($txn);
			if (($this->procCountryCode === "_US") && (strpos($txnType, "us_") !== 0))
			{
				if((strcmp($txnType, "txn") === 0) || (strcmp($txnType, "acs") === 0) || (strcmp($txnType, "group") === 0))
				{
					//do nothing
				}
				else
				{
					$txnType = "us_".$txnType;
				}
			}
			$tmpTxnTypes=$this->txnTypes;
			$txnTypeArray=$tmpTxnTypes[$txnType];
			$txnTypeArrayLen=count($txnTypeArray); //length of a specific txn type

			$txnXMLString="";
			
			//for risk transactions only
			if((strcmp($txnType, "attribute_query") === 0) || (strcmp($txnType, "session_query") === 0))
			{
				$txnXMLString .="<risk>";
			}
				
			$txnXMLString .="<$txnType>";

			for($i=0;$i < $txnTypeArrayLen ;$i++)
			{
				//Will only add to the XML if the tag was passed in by merchant
				if(array_key_exists($txnTypeArray[$i], $txn))
                {
				 	$txnXMLString  .="<$txnTypeArray[$i]>"   //begin tag
									.$txn[$txnTypeArray[$i]] // data
									. "</$txnTypeArray[$i]>"; //end tag
				}
			}
			
   			$recur  = $txnObj->getRecur();
  			if($recur != null)
   			{
         		$txnXMLString .= $recur->toXML();
   			}
   			
			$avs  = $txnObj->getAvsInfo();
			if($avs != null)
			{
				$txnXMLString .= $avs->toXML();
			}

			$cvd  = $txnObj->getCvdInfo();
			if($cvd != null)
			{
				$txnXMLString .= $cvd->toXML();
			}

   			$custInfo = $txnObj->getCustInfo();
   			if($custInfo != null)
   			{
        		$txnXMLString .= $custInfo->toXML();
   			}
   			
   			$ach = $txnObj->getAchInfo();
   			if($ach != null)
   			{
   				$txnXMLString .= $ach->toXML();
   			}
   			
   			$convFee  = $txnObj->getConvFeeInfo();
   			if($convFee != null)
   			{
   				$txnXMLString .= $convFee->toXML();
   			}
   			
   			$sessionQuery  = $txnObj->getSessionAccountInfo(); 			
   			if($sessionQuery != null)
   			{
   				$txnXMLString .= $sessionQuery->toXML();
   			}
   			
   			$attributeQuery  = $txnObj->getAttributeAccountInfo();   			
   			if($attributeQuery != null)
   			{
   				$txnXMLString .= $attributeQuery->toXML();
   			}
   	

   			$txnXMLString .="</$txnType>";
   			
   			//for risk transactions only
   			if((strcmp($txnType, "attribute_query") === 0) || (strcmp($txnType, "session_query") === 0))
   			{
   				$txnXMLString .="</risk>";
   			}
   			
   			$this->xmlString .=$txnXMLString;

 		}
 		return $this->xmlString;

	}//end toXML



}//end class


##################### mpgCustInfo #######################################################

class mpgCustInfo
{


 	var $level3template = array(	cust_info=>array('email','instructions',
                 			billing => array('first_name', 'last_name', 'company_name', 'address',
                                    			 'city', 'province', 'postal_code', 'country',
                                    			 'phone_number', 'fax','tax1', 'tax2','tax3',
                                    			 'shipping_cost'),
                 			shipping => array('first_name', 'last_name', 'company_name', 'address',
                                   			  'city', 'province', 'postal_code', 'country',
                                   			  'phone_number', 'fax','tax1', 'tax2', 'tax3',
                                   			  'shipping_cost'),
                 			item   => array ('name', 'quantity', 'product_code', 'extended_amount')
                		)
           		);

 	var $level3data;
 	var $email;
 	var $instructions;

 	function mpgCustInfo($custinfo=0,$billing=0,$shipping=0,$items=0)
 	{
 		if($custinfo)
   		{
    			$this->setCustInfo($custinfo);
   		}
 	}

 	function setCustInfo($custinfo)
 	{
 		$this->level3data['cust_info']=array($custinfo);
 	}

 	function setEmail($email)
	{
   		$this->email=$email;
   		$this->setCustInfo(array(email=>$email,instructions=>$this->instructions));
 	}

 	function setInstructions($instructions)
	{
 		$this->instructions=$instructions;
   		$this->setCustinfo(array(email=>$this->email,instructions=>$instructions));
 	}

 	function setShipping($shipping)
 	{
  		$this->level3data['shipping']=array($shipping);
 	}

 	function setBilling($billing)
 	{
  		$this->level3data['billing']=array($billing);
 	}

 	function setItems($items)
 	{
   		if(! $this->level3data['item'])
		{
			$this->level3data['item']=array($items);
   	 	}
   		else
		{
			$index=count($this->level3data['item']);
			$this->level3data['item'][$index]=$items;
		}
 	}

 	function toXML()
 	{
  		$xmlString=$this->toXML_low($this->level3template,"cust_info");
  		return $xmlString;
 	}

 	function toXML_low($template,$txnType)
 	{

  	for($x=0;$x<count($this->level3data[$txnType]);$x++)
   	{
     	if($x>0)
     	{
      		$xmlString .="</$txnType><$txnType>";
     	}
     	$keys=array_keys($template);
     	for($i=0; $i < count($keys);$i++)
     	{
        	$tag=$keys[$i];

        	if(is_array($template[$keys[$i]]))
        	{
          		$data=$template[$tag];

          		if(! count($this->level3data[$tag]))
           		{
            		continue;
           		}
          		$beginTag="<$tag>";
          		$endTag="</$tag>";

          		$xmlString .=$beginTag;

          		#if(is_array($data))
           		{
            		$returnString=$this->toXML_low($data,$tag);
            		$xmlString .= $returnString;
           		}
          		$xmlString .=$endTag;
        	}
        	else
        	{
         		$tag=$template[$keys[$i]];
         		$beginTag="<$tag>";
         		$endTag="</$tag>";
         		$data=$this->level3data[$txnType][$x][$tag];
         		$xmlString .=$beginTag.$data .$endTag;
        	}

     	}//end inner for

    }//end outer for

    return $xmlString;
	}//end toXML_low

}//end class

##################### mpgRecur #####################################################

class mpgRecur{

	var $params;
	var $recurTemplate = array('recur_unit','start_now','start_date','num_recurs','period','recur_amount');

	function mpgRecur($params)
	{
		$this->params = $params;
		if( (! $this->params['period']) )
		{
			$this->params['period'] = 1;
		}
	}

	function toXML()
	{
		$xmlString = "";

		foreach($this->recurTemplate as $tag)
		{
			$xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
		}

		return "<recur>$xmlString</recur>";
	}

}//end class

##################### mpgAvsInfo #######################################################

class mpgAvsInfo
{

    var $params;
    var $avsTemplate = array('avs_street_number','avs_street_name','avs_zipcode','avs_email','avs_hostname','avs_browser','avs_shiptocountry','avs_shipmethod','avs_merchprodsku','avs_custip','avs_custphone');

    function mpgAvsInfo($params)
    {
        $this->params = $params;
    }

    function toXML()
    {
        $xmlString = "";

        foreach($this->avsTemplate as $tag)
        {
        	//will only add to the XML the tags from the template that were also passed in by the merchant
			if(array_key_exists($tag, $this->params))
			{
				$xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
			}
        }

        return "<avs_info>$xmlString</avs_info>";
    }

}//end class

##################### mpgCvdInfo #######################################################

class mpgCvdInfo
{

    var $params;
    var $cvdTemplate = array('cvd_indicator','cvd_value');

    function mpgCvdInfo($params)
    {
        $this->params = $params;
    }

    function toXML()
    {
        $xmlString = "";

        foreach($this->cvdTemplate as $tag)
        {
            $xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
        }

        return "<cvd_info>$xmlString</cvd_info>";
    }

}//end class

##################### mpgAchInfo #######################################################

class mpgAchInfo
{

	var $params;
	var $achTemplate = array('sec','cust_first_name','cust_last_name',
			'cust_address1','cust_address2','cust_city',
			'cust_state','cust_zip','routing_num','account_num',
			'check_num','account_type','micr');

	function mpgAchInfo($params)
	{
		$this->params = $params;
	}

	function toXML()
	{
		$xmlString = "";

		foreach($this->achTemplate as $tag)
		{
			$xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
		}

		return "<ach_info>$xmlString</ach_info>";
	}

}//end class

##################### mpgConvFeeInfo #######################################################

class mpgConvFeeInfo
{

	var $params;
	var $convFeeTemplate = array('convenience_fee');

	function mpgConvFeeInfo($params)
	{
		$this->params = $params;
	}

	function toXML()
	{
		$xmlString = "";

		foreach($this->convFeeTemplate as $tag)
		{
			$xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
		}

		return "<convfee_info>$xmlString</convfee_info>";
	}

}//end class

##################### mpgTransaction ################################################

class mpgTransaction
{

	var $txn;
	var $custInfo = null;
	var $recur = null;
	var $cvd = null;
	var $avs = null;
	var $convFee = null;

	function mpgTransaction($txn)
	{
		$this->txn=$txn;
	}

	function getCustInfo()
	{
		return $this->custInfo;
	}

	function setCustInfo($custInfo)
	{
		$this->custInfo = $custInfo;
		array_push($this->txn,$custInfo);
	}

	function getRecur()
	{
		return $this->recur;
	}

	function setRecur($recur)
	{
		$this->recur = $recur;
	}

	function getTransaction()
	{
		return $this->txn;
	}

	function getCvdInfo()
	{
		return $this->cvd;
	}

	function setCvdInfo($cvd)
	{
		$this->cvd = $cvd;
	}

	function getAvsInfo()
	{
		return $this->avs;
	}

	function setAvsInfo($avs)
	{
		$this->avs = $avs;
	}
	
	function getAchInfo()
	{
		return $this->ach;
	}
	
	function setAchInfo($ach)
	{
		$this->ach = $ach;
	}
	
	function setConvFeeInfo($convFee)
	{
		$this->convFee = $convFee;
	}
	
	function getConvFeeInfo()
	{
		return $this->convFee;
	}
	
	function setExpiryDate($expdate)
	{
		$this->expdate = $expdate;
	}
	
	function getExpiryDate()
	{
		return $this->expdate;
	}
	
	function getAttributeAccountInfo()
	{
		return $this->attributeAccountInfo;
	}
	
	function setAttributeAccountInfo($attributeAccountInfo)
	{
		$this->attributeAccountInfo = $attributeAccountInfo;
	}
	
	function getSessionAccountInfo()
	{
		return $this->sessionAccountInfo;
	}
	
	function setSessionAccountInfo($sessionAccountInfo)
	{
		$this->sessionAccountInfo = $sessionAccountInfo;
	}

}//end class mpgTransaction

###################### MpiHttpsPost #########################################

class MpiHttpsPost
{

	var $api_token;
	var $store_id;
	var $mpiRequest;
	var $mpiResponse;

	function MpiHttpsPost($storeid,$apitoken,$mpiRequestOBJ)
	{

		$this->store_id=$storeid;
		$this->api_token= $apitoken;
		$this->mpiRequest=$mpiRequestOBJ;
		$dataToSend=$this->toXML();

		$url = $this->mpiRequest->getURL();
		
		echo "\n\nDataToSend: ".$dataToSend;

  		$httpsPost= new httpsPost($url, $dataToSend);	
  		$response = $httpsPost->getHttpsResponse();
  		
  		echo "\n\nRESPONSE = $response";

		if(!$response)
		{

			$response="<?xml version=\"1.0\"?>".
					"<MpiResponse>".
					"<type>null</type>".
					"<success>false</success>".
					"<message>null</message>".
					"<PaReq>null</PaReq>".
					"<TermUrl>null</TermUrl>".
					"<MD>null</MD>".
					"<ACSUrl>null</ACSUrl>".
					"<cavv>null</cavv>".
					"<PAResVerified>null</PAResVerified>".
					"</MpiResponse>";
		}

		// echo "$response";exit();

		$this->mpiResponse=new MpiResponse($response);
			
	}



	function getMpiResponse()
	{
		return $this->mpiResponse;

	}

	function toXML( )
	{

		$req=$this->mpiRequest ;
		$reqXMLString=$req->toXML();

		$xmlString .="<?xml version=\"1.0\"?>".
				"<MpiRequest>".
				"<store_id>$this->store_id</store_id>".
				"<api_token>$this->api_token</api_token>".
				$reqXMLString.
				"</MpiRequest>";

		return ($xmlString);

	}

}//end class mpiHttpsPost

############# MpiResponse #####################################################


class MpiResponse{

	var $responseData;

	var $p; //parser

	var $currentTag;
	var $receiptHash = array();
	var $currentTxnType;

	var $ACSUrl;

	function MpiResponse($xmlString)
	{

		$this->p = xml_parser_create();
		xml_parser_set_option($this->p,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($this->p,XML_OPTION_TARGET_ENCODING,"UTF-8");
		xml_set_object($this->p, $this);
		xml_set_element_handler($this->p,"startHandler","endHandler");
		xml_set_character_data_handler($this->p,"characterHandler");
		xml_parse($this->p,$xmlString);
		xml_parser_free($this->p);

	}//end of constructor

	//vbv start

	function getMpiMessage()
	{
		return ($this->responseData['message']);
	}


	function getMpiSuccess()
	{
		return ($this->responseData['success']);
	}

	function getMpiPAResVerified()
	{
		return ($this->responseData['PAResVerified']);
	}

	function getMpiAcsUrl()
	{
		return ($this->responseData['ACSUrl']);
	}

	function getMpiPaReq()
	{
		return ($this->responseData['PaReq']);
	}
	
	function getMpiTermUrl()
	{
		return ($this->responseData['TermUrl']);
	}

	function getMpiMD()
	{
		return ($this->responseData['MD']);
	}

	function getMpiCavv()
	{
		return ($this->responseData['cavv']);
	}

	function getMpiEci()
	{
		return ($this->responseData['eci']);
	}

	function getMpiResponseData()
	{
		return($this->responseData);
	}

	function getMpiPopUpWindow()
	{
		$popUpForm ='<html><head><title>Title for Page</title></head><SCRIPT LANGUAGE="Javascript" >' .
					"<!--
					function OnLoadEvent()
					{
						window.name='mainwindow';
						//childwin = window.open('about:blank','popupName','height=400,width=390,status=yes,dependent=no,scrollbars=yes,resizable=no');
						//document.downloadForm.target = 'popupName';
						document.downloadForm.submit();
					}
					-->
					</SCRIPT>" .
					'<body onload="OnLoadEvent()">
						<form name="downloadForm" action="' . $this->getMpiAcsUrl() .
						'" method="POST">
						<noscript>
						<br>
						<br>
						<center>
						<h1>Processing your 3-D Secure Transaction</h1>
						<h2>
						JavaScript is currently disabled or is not supported
						by your browser.<br>
						<h3>Please click on the Submit button to continue
						the processing of your 3-D secure
						transaction.</h3>
						<input type="submit" value="Submit">
						</center>
						</noscript>
						<input type="hidden" name="PaReq" value="' . $this->getMpiPaReq() . '">
						<input type="hidden" name="MD" value="' . $this->getMpiMD() . '">
						<input type="hidden" name="TermUrl" value="' . $this->getMpiTermUrl() .'">
						</form>
					</body>
					</html>';

		return $popUpForm;
	}


	function getMpiInLineForm()
	{

		$inLineForm ='<html><head><title>Title for Page</title></head><SCRIPT LANGUAGE="Javascript" >' .
					"<!--
					function OnLoadEvent()
					{
						document.downloadForm.submit();
					}
					-->
					</SCRIPT>" .
					'<body onload="OnLoadEvent()">
						<form name="downloadForm" action="' . $this->getMpiAcsUrl() .
						'" method="POST">
						<noscript>
						<br>
						<br>
						<center>
						<h1>Processing your 3-D Secure Transaction</h1>
						<h2>
						JavaScript is currently disabled or is not supported
						by your browser.<br>
						<h3>Please click on the Submit button to continue
						the processing of your 3-D secure
						transaction.</h3>
						<input type="submit" value="Submit">
						</center>
						</noscript>
						<input type="hidden" name="PaReq" value="' . $this->getMpiPaReq() . '">
						<input type="hidden" name="MD" value="' . $this->getMpiMD() . '">
						<input type="hidden" name="TermUrl" value="' . $this->getMpiTermUrl() .'">
						</form>
					</body>
					</html>';

		return $inLineForm;
	}

	function characterHandler($parser,$data)
	{
		$this->responseData[$this->currentTag] .=trim($data);
	}//end characterHandler

	function startHandler($parser,$name,$attrs)
	{
		$this->currentTag=$name;
	}


	function endHandler($parser,$name)
	{

	}


}//end class MpiResponse

################## mpiRequest ###########################################################

class MpiRequest
{

	var $txnTypes =array(

			txn =>array('xid', 'amount', 'pan', 'expdate','MD', 'merchantUrl','accept','userAgent','currency','recurFreq', 'recurEnd','install'),
			acs=> array('PaRes','MD')
	);
	var $txnArray;
	var $procCountryCode = "";
	var $testMode = "";

	function MpiRequest($txn)
	{

		if(is_array($txn))
		{
			$this->txnArray = $txn;
		}
		else
		{
			$temp[0]=$txn;
			$this->txnArray=$temp;
		}
	}
	function setProcCountryCode($countryCode)
	{
		$this->procCountryCode = ((strcmp(strtolower($countryCode), "us") >= 0) ? "_US" : "");
	}
	
	function setTestMode($state)
	{
		if($state === true)
		{
			$this->testMode = "_TEST";
		}
		else
		{
			$this->testMode = "";
		}
	}
	
	function getURL()
	{
		$g=new mpgGlobals();
		$gArray=$g->getGlobals();
	
		//$txnType = $this->getTransactionType();
	
		$hostId = "MONERIS".$this->procCountryCode.$this->testMode."_HOST";
		$fileId = "MONERIS".$this->procCountryCode."_MPI_FILE";
	
		$url =  $gArray['MONERIS_PROTOCOL']."://".
				$gArray[$hostId].":".
				$gArray['MONERIS_PORT'].
				$gArray[$fileId];
	
		echo "PostURL: " . $url;
	
		return $url;
	}
	
	function toXML()
	{

		$tmpTxnArray=$this->txnArray;
		$txnArrayLen=count($tmpTxnArray); //total number of transactions

		for($x=0;$x < $txnArrayLen;$x++)
		{
			$txnObj=$tmpTxnArray[$x];
			$txn=$txnObj->getTransaction();
	
			$txnType=array_shift($txn);
			$tmpTxnTypes=$this->txnTypes;
			$txnTypeArray=$tmpTxnTypes[$txnType];
			$txnTypeArrayLen=count($txnTypeArray); //length of a specific txn type
	
			$txnXMLString="";
			
			for($i=0;$i < $txnTypeArrayLen ;$i++)
			{
				$txnXMLString  .="<$txnTypeArray[$i]>"   //begin tag
									.$txn[$txnTypeArray[$i]] // data
						   	   . "</$txnTypeArray[$i]>"; //end tag
			}
		 
			$txnXMLString = "<$txnType>$txnXMLString";

			$txnXMLString .="</$txnType>";
	
			$xmlString .=$txnXMLString;
		}

		return $xmlString;

	}//end toXML

}//end class MpiRequest



class MpiTransaction
{
	var $txn;

	function MpiTransaction($txn)
	{
		$this->txn=$txn;
	}

	function getTransaction()
	{
		return $this->txn;
	}
}//end class MpiTransaction


###################### riskHttpsPost #########################################

class riskHttpsPost{

	var $api_token;
	var $store_id;
	var $riskRequest;
	var $riskResponse;

	function riskHttpsPost($storeid,$apitoken,$riskRequestOBJ)
	{

		$this->store_id=$storeid;
		$this->api_token= $apitoken;
		$this->riskRequest=$riskRequestOBJ;
		$dataToSend=$this->toXML();

  		//$this->txnType=$mpgRequestOBJ->getTransactionType();
  		
		$url = $this->riskRequest->getURL();
		
		echo "\n\nDataToSend: ".$dataToSend;

  		$httpsPost= new httpsPost($url, $dataToSend);	
  		$response = $httpsPost->getHttpsResponse();
  		
  		echo "\n\nRESPONSE = $response";

		if(!$response)
		{

			$response="<?xml version=\"1.0\"?><response><receipt>".
					"<ReceiptId>Global Error Receipt</ReceiptId>".
					"<ResponseCode>null</ResponseCode>".
					"<AuthCode>null</AuthCode><TransTime>null</TransTime>".
					"<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>".
					"<Message>null</Message><TransAmount>null</TransAmount>".
					"<CardType>null</CardType>".
					"<TransID>null</TransID><TimedOut>null</TimedOut>".
					"</receipt></response>";
		}

		//print "Got a xml response of: \n$response\n";
		$this->riskResponse=new riskResponse($response);

	}



	function getRiskResponse()
	{
		return $this->riskResponse;

	}

	function toXML( )
	{

		$req=$this->riskRequest ;
		$reqXMLString=$req->toXML();

		$xmlString .="<?xml version=\"1.0\"?>".
				"<request>".
				"<store_id>$this->store_id</store_id>".
				"<api_token>$this->api_token</api_token>".
				"<risk>".
				$reqXMLString.
				"</risk>".
				"</request>";

		return ($xmlString);

	}

}//end class riskHttpsPost



############# riskResponse #####################################################


class riskResponse{

	var $responseData;

	var $p; //parser

	var $currentTag;
	var $isResults;
	var $isRule;
	var $ruleName;
	var $results = array();
	var $rules = array();

	function riskResponse($xmlString)
	{

		$this->p = xml_parser_create();
		xml_parser_set_option($this->p,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($this->p,XML_OPTION_TARGET_ENCODING,"UTF-8");
		xml_set_object($this->p,$this);
		xml_set_element_handler($this->p,"startHandler","endHandler");
		xml_set_character_data_handler($this->p,"characterHandler");
		xml_parse($this->p,$xmlString);
		xml_parser_free($this->p);

	}//end of constructor


	function getRiskResponse()
	{
		return($this->responseData);
	}

	//-----------------  Receipt Variables  ---------------------------------------------------------//

	function getReceiptId()
	{
		print("\nreceiptId get value: ".$this->responseData['ReceiptId']);
		return ($this->responseData['ReceiptId']);
	}

	function getResponseCode()
	{
		return ($this->responseData['ResponseCode']);
	}

	function getMessage()
	{
		return ($this->responseData['Message']);
	}

	function getResults()
	{
		return ($this->results);
	}

	function getRules()
	{
		return ($this->rules);
	}

	//-----------------  Parser Handlers  ---------------------------------------------------------//

	function characterHandler($parser,$data)
	{
		@$this->responseData[$this->currentTag] .=$data;

		if($this->isResults)
		{
			//print("\n".$this->currentTag."=".$data);
			$this->results[$this->currentTag] = $data;
			 
		}

		if($this->isRule)
		{

			if ($this->currentTag == "RuleName")
			{
				$this->ruleName=$data;
			}
			$this->rules[$this->ruleName][$this->currentTag] = $data;

		}
	}//end characterHandler


	function startHandler($parser,$name,$attrs)
	{
		$this->currentTag=$name;

		if($this->currentTag == "Result")
		{
			$this->isResults=1;
		}

		if($this->currentTag == "Rule")
		{
			$this->isRule=1;
		}
	} //end startHandler

	function endHandler($parser,$name)
	{
		$this->currentTag=$name;

		if($name == "Result")
		{
			$this->isResults=0;
		}

		if($this->currentTag == "Rule")
		{
			$this->isRule=0;
		}

		$this->currentTag="/dev/null";
	} //end endHandler



}//end class riskResponse


################## riskRequest ###########################################################

class riskRequest{

	var $txnTypes =array(
			session_query => array('order_id','session_id','service_type','event_type'),
			attribute_query => array('order_id','policy_id','service_type'),
			assert => array('orig_order_id','activities_description','impact_description','confidence_description')
	);

	var $txnArray;
	var $procCountryCode = "";
	var $testMode = "";

	function riskRequest($txn)
	{
		if(is_array($txn))
		{
			$this->txnArray = $txn;
		}
		else
		{
			$temp[0]=$txn;
			$this->txnArray=$temp;
		}
	}
	
	function setProcCountryCode($countryCode)
	{
		$this->procCountryCode = ((strcmp(strtolower($countryCode), "us") >= 0) ? "_US" : "");
	}
	
	function setTestMode($state)
	{
		if($state === true)
		{
			$this->testMode = "_TEST";
		}
		else
		{
			$this->testMode = "";
		}
	}
	
	function getURL()
	{
		$g=new mpgGlobals();
		$gArray=$g->getGlobals();
	
		//$txnType = $this->getTransactionType();
	
		$hostId = "MONERIS".$this->procCountryCode.$this->testMode."_HOST";
		$fileId = "MONERIS".$this->procCountryCode."_FILE";
	
		$url =  $gArray['MONERIS_PROTOCOL']."://".
				$gArray[$hostId].":".
				$gArray['MONERIS_PORT'].
				$gArray[$fileId];
	
		echo "PostURL: " . $url;
	
		return $url;
	}

	function toXML()
	{

		$tmpTxnArray=$this->txnArray;

		$txnArrayLen=count($tmpTxnArray); //total number of transactions
		for($x=0;$x < $txnArrayLen;$x++)
		{
			$txnObj=$tmpTxnArray[$x];
			$txn=$txnObj->getTransaction();

			$txnType=array_shift($txn);
			$tmpTxnTypes=$this->txnTypes;
			$txnTypeArray=$tmpTxnTypes[$txnType];
			$txnTypeArrayLen=count($txnTypeArray); //length of a specific txn type

			$txnXMLString="";
			for($i=0;$i < $txnTypeArrayLen ;$i++)
			{
				$txnXMLString  .="<$txnTypeArray[$i]>"   //begin tag
									.$txn[$txnTypeArray[$i]] // data
							   . "</$txnTypeArray[$i]>"; //end tag
			}

			$txnXMLString = "<$txnType>$txnXMLString";

			$sessionQuery  = $txnObj->getSessionAccountInfo();
			 
			if($sessionQuery != null)
			{
				$txnXMLString .= $sessionQuery->toXML();
			}

			$attributeQuery  = $txnObj->getAttributeAccountInfo();
	
			if($attributeQuery != null)
			{
				$txnXMLString .= $attributeQuery->toXML();
			}
	
			$txnXMLString .="</$txnType>";
	
			$xmlString .=$txnXMLString;
	
			return $xmlString;
		}

		return $xmlString;

	}//end toXML
}//end class

##################### mpgSessionAccountInfo #######################################################

class mpgSessionAccountInfo
{

	var $params;
	var $sessionAccountInfoTemplate = array('policy','account_login','password_hash','account_number','account_name',
		'account_email','account_telephone','pan','account_address_street1','account_address_street2','account_address_city',
	'account_address_state','account_address_country','account_address_zip','shipping_address_street1','shipping_address_street2','shipping_address_city',
	'shipping_address_state','shipping_address_country','shipping_address_zip','local_attrib_1','local_attrib_2','local_attrib_3','local_attrib_4',
	'local_attrib_5','transaction_amount','transaction_currency');

	function mpgSessionAccountInfo($params)
	{
		$this->params = $params;
	}

	function toXML()
	{
		foreach($this->sessionAccountInfoTemplate as $tag)
		{
			$xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
		}
		return "<session_account_info>$xmlString</session_account_info>";
	}

}//end class mpgSessionAccountInfo

##################### mpgAttributeAccountInfo #######################################################

class mpgAttributeAccountInfo
{

	var $params;
	var $attributeAccountInfoTemplate = array('device_id','account_login','password_hash','account_number','account_name',
	'account_email','account_telephone','cc_number_hash','ip_address','ip_forwarded','account_address_street1','account_address_street2','account_address_city',
	'account_address_state','account_address_country','account_address_zip','shipping_address_street1','shipping_address_street2','shipping_address_city',
	'shipping_address_state','shipping_address_country','shipping_address_zip');

	function mpgAttributeAccountInfo($params)
	{
		$this->params = $params;
	}

	function toXML()
	{
		foreach($this->attributeAccountInfoTemplate as $tag)
		{
			$xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
		}

		return "<attribute_account_info>$xmlString</attribute_account_info>";
	}

}//end class


##################### riskTransaction #######################################################

class riskTransaction{

	var $txn;
	var $attributeAccountInfo = null;
	var $sessionAccountInfo = null;

	function riskTransaction($txn)
	{
		$this->txn=$txn;
	}

	function getTransaction()
	{
		return $this->txn;
	}

	function getAttributeAccountInfo()
	{
		return $this->attributeAccountInfo;
	}
	
	function setAttributeAccountInfo($attributeAccountInfo)
	{
		$this->attributeAccountInfo = $attributeAccountInfo;
	}

	function getSessionAccountInfo()
	{
		return $this->sessionAccountInfo;
	}
	
	function setSessionAccountInfo($sessionAccountInfo)
	{
		$this->sessionAccountInfo = $sessionAccountInfo;
	}
}//end class RiskTransaction

?>
