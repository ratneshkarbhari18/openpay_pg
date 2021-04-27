<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("/home/zkfzs1g2xscu/public_html/utils/db_connect.php");
date_default_timezone_set ("Europe/Madrid");
$paymentDate=date('Y-m-d H:i:s');
$ip=$_SERVER['REMOTE_ADDR'];
$count_row = rand(1,9999);
$invoice = unique_pin();


require("CreditSale.php");
$creditSale = new CreditSale();




if (isset($_POST['transaction_id']) && !empty($_POST['transaction_id'])) {


    $firstname = mysqli_real_escape_string($conn,$_POST["first_name"]); 
    $lastname = mysqli_real_escape_string($conn,$_POST["last_name"]); 
    $fullName = mysqli_real_escape_string($conn,$firstname." ".$lastname);
    $custName         = strtoupper($fullName);
    $email=mysqli_real_escape_string($conn,$_POST["email"]);
    $credit_card_number=mysqli_real_escape_string($conn,$_POST["ccnumber"]);
    $expiry_month=mysqli_real_escape_string($conn,$_POST["ccexpmon"]);
    $expiry_year=mysqli_real_escape_string($conn,$_POST["ccexpyr"]);
    $cvv=mysqli_real_escape_string($conn,$_POST["cvv"]); 
    $amount=$_POST["amount"];
    $address1 = $address= mysqli_real_escape_string($conn,$_POST['billAddress']);
    $zip = mysqli_real_escape_string($conn,$_POST['billZip']);
    $redirect_url = mysqli_real_escape_string($conn,$_POST['redirect_url']);
    $callback_url= mysqli_real_escape_string($conn,$_POST['callback_url']);
    $success_url=$callback_url= $fail_url=$redirect_url;
    $g_type = "Global";
    $zipcode = $zip;
    
    
 
    // New Fields to Add
    $phone = mysqli_real_escape_string($conn,$_POST['phoneNum']);
    $country          = mysqli_real_escape_string($conn,$_POST['billCountry']);
    $state = mysqli_real_escape_string($conn,$_POST['billState']);
    $city = mysqli_real_escape_string($conn,$_POST['billCity']);
    $ipn = mysqli_real_escape_string($conn,$_POST['ipn']);
    $transaction_id = mysqli_real_escape_string($conn,$_POST['transaction_id']);
 
    
 
    $orderId = $orderid =  $var_TicketNumber = unique_pin();
    $paidCurrency = "USD";
 
    $orderdescription = "";
    $ccnumber = mysqli_real_escape_string($conn,$credit_card_number);
    $ccexp = mysqli_real_escape_string($conn,$expiry_month.'/'.$expiry_year);
    $ip= $ipaddress  =   $_SERVER['REMOTE_ADDR'];

    $amount1=$amount;
        
    $amount=546*$amount;
    $amount2=$amount/100;

        
    /****************************Checking**********************************/
    require_once("gatewayRules.php");
    /**********************************************************************/ 

    $sql = "INSERT INTO  t_master_sales ( ipn_no,	client_transaction_id,	order_id,	customer_name,	customer_email,	customer_address,customer_city,customer_state,customer_country,customer_zip,customer_phone,	item_name,item_number,item_price,item_price_currency,grossPrice,currency_type,card_no,cvv,card_expiry,ip,g_type,	status,rec_crt_date,rec_up_date,callback_url,success_url,fail_url)VALUES ('".addslashes($ipn)."','".addslashes($transaction_id)."','".addslashes($orderid)."','".addslashes($custName)."','".addslashes($email)."','".addslashes($address1)."','".addslashes($city)."','".addslashes($state)."','".addslashes($country)."','".addslashes($zip)."','".addslashes($phone)."','".$orderdescription."','".$orderdescription."','".$amount2."','BR','".$amount1."','".$paidCurrency."','".$ccnumber."','".$cvv."','".$ccexp."','".$ipaddress."','".$g_type."',		'".addslashes('Process')."','".addslashes(date('y-m-d H:i:s'))."','".addslashes(date('y-m-d H:i:s'))."','".addslashes($callback_url)."','".addslashes($success_url)."','".addslashes($fail_url)."')";

    // Inserting data sent to crm
    $inserted = mysqli_query($conn, $sql);


    if ($inserted) {


        $response = $creditSale->process_payment($firstname,$lastname,$email,$address,$zipcode,$ccnumber,$expiry_month,$expiry_year,$cvv,$amount2,$var_TicketNumber);


        $MCSTransactionID = mysqli_real_escape_string($conn,$response->MCSTransactionID);
        $ProcessorTransactionID = mysqli_real_escape_string($conn,$response->ProcessorTransactionID);				
        $responseCode = mysqli_real_escape_string($conn,$response->Result->ResultCode);
        $ResultDetail = mysqli_real_escape_string($conn,$response->Result->ResultDetail);	
        $status 			= 	mysqli_real_escape_string($conn,$response->status);
        $responseRes 		= 	mysqli_real_escape_string($conn,$response);

        if($responseCode=='0'){
            $status="Success";   
           }else{
            $status="Declined";
        }
    
        // Updating Status in CRM now
    
        if ($ResultDetail=="50: General Decline | 50 General_Decline") {
            $ResultDetailArray = explode("|",$ResultDetail);
            $ResultDetail = $ResultDetailArray[0];
        }

        $rCode = mysqli_real_escape_string($conn,$responseCode.' '.$ResultDetail);
    
        // Fetching details
        $sql = "SELECT id,client_transaction_id,grossPrice,callback_url,success_url FROM  t_master_sales where order_id='".$var_TicketNumber."' ORDER BY id DESC LIMIT 1";
        $result = mysqli_query($conn, $sql);
    
        if (mysqli_num_rows($result) > 0) {
            // output data of each row
            while($row = mysqli_fetch_assoc($result)) {
                $id=$row["id"];
                $client_transaction_id=$row["client_transaction_id"];
                $grossPrice=$row["grossPrice"];
                $postback_url=$row["callback_url"];
                $success_url=$row["success_url"];
            }
        }
    
        $updateTransactionSQL = "UPDATE t_master_sales SET gatewayTransactionId='".$transaction_id."',response = '".$response."', response_code = '".$rCode."', status='".$status."', rec_up_date='".date('Y-m-d H:i:s')."' WHERE order_id='".$var_TicketNumber."' ORDER BY id DESC LIMIT 1";
    
        $updated = mysqli_query($conn, $updateTransactionSQL) or die("database error: ". mysqli_error($conn));
    

        $arr=array('transaction_id'=>$transaction_id,'status'=>$status,'paid_amount'=>$amount2);

        $data =  http_build_query($arr);
        $curl = curl_init($callback_url);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl,CURLOPT_HEADER, 0 ); // Colate HTTP header
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// Show the outputz
        curl_setopt($curl,CURLOPT_POST,true); // Transmit datas by using POST
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//Transmit datas by using POST
        //curl_setopt($curl,CURLOPT_REFERER,$returnUrl);
        $xmlrs = curl_exec($curl); 
        curl_close ($curl);
    
        // response log query  
        $sqlResponse = "INSERT INTO response_log ( 
            transaction_id,	
            response,	
            rec_crt_date
            )VALUES (
                '".addslashes($transaction_id)."',
                '".addslashes($xmlrs)."',
                '".addslashes(date('y-m-d H:i:s'))."'
                )";
        mysqli_query($conn, $sqlResponse);

        
    

        if ($responseCode==0) {

          

            echo "success";
    
        } else {

            echo "declined";

        }
    

    }else {
        echo mysqli_error($conn);
    }
    
}else  {
    echo "send transaction id";
}