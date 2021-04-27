<?php
// ob_start();
class CreditSale{

public $liveurl = 'https://prod.mycardstorage.com/api/api.asmx';



function process_payment($firstname,$lastname,$email,$address,$zipcode,$credit_card_number,$expiry_month,$expiry_year,$cvv,$amount2,$var_TicketNumber)
{
   
	$process_url = $this->liveurl;

   

	$request_xml = $this->generate_prismpay_params_cc($firstname,$lastname,$email,$address,$zipcode,$credit_card_number,$expiry_month,$expiry_year,$cvv,$amount2,$var_TicketNumber);



    $response_xml = $this->submit_payment($process_url, $request_xml);


    $xml = new SimpleXMLElement($response_xml);

    $xml->registerXPathNamespace("soap", "http://www.w3.org/2003/05/soap-envelope");
    $body = $xml->xpath("//soap:Body");
   try
   {			
	   $response = $body[0]->CreditSale_SoapResponse->CreditSale_SoapResult;
	   $MCSTransactionID = $response->MCSTransactionID;
	   $ProcessorTransactionID = $response->ProcessorTransactionID;				
	   $responseCode = $response->Result->ResultCode;
	   $ResultDetail = $response->Result->ResultDetail;	
	   $status 			= 	$response->status;
	   $responseRes 		= 	$response;



	   if($responseCode==0)
	   {
		 
		  
         return $response;


	   }
	   else{
         return $response;

	   }

   }
   catch(ServiceError $e)
   {
      echo "Error Message from gw: ".$errorMsg ="Your transaction has been declined. Gateway Response: ".$e;
   }
}

   public function generate_prismpay_params_cc($firstname,$lastname,$email,$address,$zipcode,$credit_card_number,$expiry_month,$expiry_year,$cvv,$amount2,$var_TicketNumber)
   {
      $var_MCSAccountID = "809599";
      $var_UserName = 'OculusUser';
      $var_Password = 'dmWDclDY%u1G';
      $var_ServiceUserName = 'OculusSvcUser';
      $var_ServicePassword = 'x42VvaL#BW@C'; 
      $var_ApiKey = 'pk_9553af868bc24a76b2beb319b60be209';
   
      $var_FirstName			= $firstname;
      $var_LastName			= $lastname;
      $var_EmailAddress			= $email;
      $var_StreetAddress			= $address;
      $var_ZipCode			= $zipcode;

      $var_pp_credircard  = $credit_card_number;
      $var_pp_mm			= $expiry_month;
      $var_pp_yy			= $expiry_year;
      $var_pp_cvv			= $cvv;
      
      $var_pp_country_code = 484;
      $var_pp_currency_code = 986; //840;
      
      $var_Amount = $amount2;

      $dataSentToApi = array(
         "username" => $var_UserName,
         "password" => $var_Password,
         "service_username" => $var_ServiceUserName,
         "service_password" => $var_ServicePassword,
         "mcs_account_id" => $var_MCSAccountID,
         "var_apikey" => $var_ApiKey,
         "first_name" => $firstname,
         "last_name" => $lastname,
         "email" => $email,
         "street_address" => $var_StreetAddress,
         "zip_code" => $var_ZipCode,
         "cc_number" => $var_pp_credircard,
         "cc_expiry_month" => $var_pp_mm,
         "cc_expiry_year" => $var_pp_yy,
         "cc_cvv" => $var_pp_cvv,
         "country_code" => $var_pp_country_code,
         "currency_code" => $var_pp_currency_code,
         "amount" => $var_Amount,
         "ticket_number" => $var_TicketNumber
      );

      
      $xml_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:myc="https://MyCardStorage.com/">
      <soapenv:Header>
         <myc:AuthHeader>
            <myc:UserName>'.$var_UserName.'</myc:UserName>
            <myc:Password>'.$var_Password.'</myc:Password>
         </myc:AuthHeader>
      </soapenv:Header>
      <soapenv:Body>
         <myc:CreditSale_Soap>
            <myc:creditCardSale>
               <myc:ServiceSecurity>
                  <myc:ServiceUserName>'.$var_ServiceUserName.'</myc:ServiceUserName>
                  <myc:ServicePassword>'.$var_ServicePassword.'</myc:ServicePassword>
               <myc:MCSAccountID>'.$var_MCSAccountID.'</myc:MCSAccountID>
               <myc:ApiKey>'.$var_ApiKey.'</myc:ApiKey>
               </myc:ServiceSecurity>
               <myc:TokenData>
                  <myc:CardNumber>'.$var_pp_credircard.'</myc:CardNumber>
                  <myc:ExpirationMonth>'.$var_pp_mm.'</myc:ExpirationMonth>
                  <myc:ExpirationYear>'.$var_pp_yy.'</myc:ExpirationYear>
                  <myc:CVV>'.$var_pp_cvv.'</myc:CVV>
                  <myc:FirstName>'.$var_FirstName.'</myc:FirstName>
                  <myc:LastName>'.$var_LastName.'</myc:LastName>
                  <myc:StreetAddress>'.$var_StreetAddress.'</myc:StreetAddress>
                  <myc:ZipCode>'.$var_ZipCode.'</myc:ZipCode>
                  <myc:EmailAddress>'.$var_EmailAddress.'</myc:EmailAddress>
               </myc:TokenData>
               <myc:TransactionData>
                  <myc:Amount>'.$var_Amount.'</myc:Amount>
                  <myc:TicketNumber>'.$var_TicketNumber.'</myc:TicketNumber>
                  <myc:CurrencyCode>'.$var_pp_currency_code.'</myc:CurrencyCode>
               </myc:TransactionData>
            </myc:creditCardSale>
         </myc:CreditSale_Soap>
      </soapenv:Body>
      </soapenv:Envelope>'; 

      return $xml_string; 

   }

   public function submit_payment($url, $xml)
   {
      
      $curl = curl_init($url);
      curl_setopt ($curl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      $result = curl_exec($curl);
      if(curl_errno($curl)){
         throw new Exception(curl_error($curl));
      }
      curl_close($curl);
      return $result;
   }

}

