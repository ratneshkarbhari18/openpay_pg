<?php
//$ipn="SBDPG3373750760096";
if($firstname=="" && $g_type!='CommonWealth' ){
	echo "<h2>One or More field values are missing. Kindly check with the corresponding API Doc and update your Integration.</h2>";
	exit;
}
if($country=="" && $g_type!='CommonWealth'){
	echo "<h2>Country is missing. Kindly check with the corresponding API Doc and update your Integration.</h2>";
	exit;
}
$sqlTrnChk="SELECT client_transaction_id FROM t_master_sales where client_transaction_id='".$transaction_id."'";
//echo $sqlTrnChk;
$resultTrnCheck = mysqli_query($conn, $sqlTrnChk);
if (mysqli_num_rows($resultTrnCheck) > 0) {
	echo "<h2>Transaction Id Sent already exits in the System. Please send a new unique Transaction ID.</h2>";
	exit;
}
$sqlIpnChk="SELECT id FROM  t_admin where status='Y' and username='".$ipn."'";
$resultIpnCheck = mysqli_query($conn, $sqlIpnChk);
if (mysqli_num_rows($resultIpnCheck) < 1) {
	echo "<h2>Invalid Request. Account is either under Review or Blocked or IPN Doesnot Exit</h2>";
	exit;
}
function gatewayChk($start_date,$end_date,$g_type,$amount1,$volume,$conn) {
    //gatewayChk($start_date,$end_date,$g_type,$amount,$rowGetwayCheck["daily_volume"],$conn);
	$sqlLimitCheck = "SELECT sum(grossPrice) as SalesSum FROM  t_master_sales where status='Success' and g_type='".$g_type."' and rec_crt_date >= '".$start_date."' and rec_crt_date <= '".$end_date."'";
	//echo $sqlLimitCheck;
	$resultLimitCheck = mysqli_query($conn, $sqlLimitCheck);
	if($resultLimitCheck){
		$SalesSum=0;
		if (mysqli_num_rows($resultLimitCheck) > 0) {
			// output data of each row
			while($rowLimit = mysqli_fetch_assoc($resultLimitCheck)) {
				$SalesSum=$rowLimit["SalesSum"];
			}
		}
		if($SalesSum+$amount1 > $volume ){
			echo "<h2>".$g_type." Gateway Limit Crossed. Contact Service Provider.";
			exit;
		}
	}		
}
/******************************************Center GatewayChk*******************************************/
/*******Function**********/
function gatewayCenterChk($start_date,$end_date,$ipn,$g_type,$amount1,$volume,$conn) {
	$sqlLimitCheck = "SELECT sum(grossPrice) as SalesSum FROM  t_master_sales where ipn_no='".$ipn."' and status='Success' and g_type='".$g_type."' and rec_crt_date >= '".$start_date."' and rec_crt_date <= '".$end_date."'";
	//echo $sqlLimitCheck;
	//exit;
	$resultLimitCheck = mysqli_query($conn, $sqlLimitCheck);
	if($resultLimitCheck){
		$SalesSum=0;
		if (mysqli_num_rows($resultLimitCheck) > 0) {
			// output data of each row
			while($rowLimit = mysqli_fetch_assoc($resultLimitCheck)) {
				$SalesSum=$rowLimit["SalesSum"];
			}
		}
		if($SalesSum+$amount1 > $volume ){
			echo "<h2>".$g_type." Gateway Limit Crossed. Contact Service Provider.";
			exit;
		}
	}
}
/*********/
$GatewayCenterCheck="SELECT * FROM t_midCenter where ipn='".$ipn."' and gatewayID='".$g_type."'";
$resultGatewayCenterCheck = mysqli_query($conn, $GatewayCenterCheck);
if (mysqli_num_rows($resultGatewayCenterCheck) > 0) {
	while($rowGetwayCenterCheck = mysqli_fetch_assoc($resultGatewayCenterCheck)) {
		if($rowGetwayCenterCheck["deny"]=="Y"){
			$GatewayCheck1="SELECT customMessage, status FROM t_midmaster where gatewayID='".$g_type."'";
			$resultGatewayCheck1 = mysqli_query($conn, $GatewayCheck1);
			if (mysqli_num_rows($resultGatewayCheck1) > 0) {
				while($rowGetwayCheck1 = mysqli_fetch_assoc($resultGatewayCheck1)) {
					echo "<h2>".$rowGetwayCheck1["customMessage"]."</h2>";
					exit;
				}
			}
		}
		//if($rowGetwayCenterCheck["daily_volume"]>0){
			
			/*********************************/
			if( $rowGetwayCenterCheck["daily_volume"] > 0){
				$start_date=date('Y-m-d 00:00:00');
				$end_date=date('Y-m-d 23:59:59');
				//echo "ok";
				
				$FinalGatewayChkResult=gatewayCenterChk($start_date,$end_date,$ipn,$g_type,$amount1,$rowGetwayCenterCheck["daily_volume"],$conn);
				echo $FinalGatewayChkResult;
				//exit;
				
			}			
			if( $rowGetwayCenterCheck["weekly_volume"] > 0){
				$todayDay=date('l');
				if($todayDay=='Monday'){
					$start_date=date('Y-m-d 00:00:00');
				}	
				else{
					$start_date=date('Y-m-d 00:00:00',strtotime('-1 Monday'));
				}
				$end_date=date('Y-m-d 23:59:59');
				//echo "ok1";
				$FinalGatewayChkResult=gatewayCenterChk($start_date,$end_date,$ipn,$g_type,$amount1,$rowGetwayCenterCheck["weekly_volume"],$conn);
				echo $FinalGatewayChkResult;
								
			}			
			if( $rowGetwayCenterCheck["monthly_volume"] > 0){
				$start_date=date('Y-m-01 00:00:00');
				$end_date=date('Y-m-d 23:59:59');
				$FinalGatewayChkResult=gatewayCenterChk($start_date,$end_date,$ipn,$g_type,$amount1,$rowGetwayCenterCheck["monthly_volume"],$conn);
				echo $FinalGatewayChkResult;
				//echo "ok2";				
			}
			/*************************************/
		//}

	}	
}
/******************************************************************************************************/
$GatewayCheck="SELECT gatewayID, daily_volume, weekly_volume, monthly_volume, MaxSalesAmount, customMessage, status FROM t_midmaster where gatewayID='".$g_type."'";
$resultGatewayCheck = mysqli_query($conn, $GatewayCheck);
//exit;
//echo $GatewayCheck;
if (mysqli_num_rows($resultGatewayCheck) > 0) {
	while($rowGetwayCheck = mysqli_fetch_assoc($resultGatewayCheck)) {
		//print_r($rowGetwayCheck);
		
			if($rowGetwayCheck["status"]=="N"){
				//echo $g_type.' '.$ipn;
				if($g_type=="BBVA" && $ipn=="SBDPG3373750760096"){}
				else{
				echo "<h2>".$rowGetwayCheck["customMessage"]."</h2>";
				exit;
				}
			}
		
		if($rowGetwayCheck["MaxSalesAmount"] > 0){

			//echo $rowGetwayCheck["MaxSalesAmount"];
			//echo $amount;
			if( $amount1 > $rowGetwayCheck["MaxSalesAmount"]){

				echo "<h2>Maximum Transaction Amount Limit is Crossed</h2>";
				exit;				
			}
		}
		if($rowGetwayCheck["daily_volume"] > 0 || $rowGetwayCheck["weekly_volume"] > 0 || $rowGetwayCheck["monthly_volume"] > 0){
			//echo "Chk";
			if( $rowGetwayCheck["daily_volume"] > 0){
				$start_date=date('Y-m-d 00:00:00');
				$end_date=date('Y-m-d 23:59:59');
				//echo "ok";
				
				$FinalGatewayChkResult=gatewayChk($start_date,$end_date,$g_type,$amount1,$rowGetwayCheck["daily_volume"],$conn);
				echo $FinalGatewayChkResult;
				//exit;
				
			}			
			if( $rowGetwayCheck["weekly_volume"] > 0){
				$todayDay=date('l');
				if($todayDay=='Monday'){
					$start_date=date('Y-m-d 00:00:00');
				}	
				else{
					$start_date=date('Y-m-d 00:00:00',strtotime('-1 Monday'));
				}
				$end_date=date('Y-m-d 23:59:59');
				//echo "ok1";
				$FinalGatewayChkResult=gatewayChk($start_date,$end_date,$g_type,$amount1,$rowGetwayCheck["weekly_volume"],$conn);
				echo $FinalGatewayChkResult;
								
			}			
			if( $rowGetwayCheck["monthly_volume"] > 0){
				$start_date=date('Y-m-01 00:00:00');
				$end_date=date('Y-m-d 23:59:59');
				$FinalGatewayChkResult=gatewayChk($start_date,$end_date,$g_type,$amount1,$rowGetwayCheck["monthly_volume"],$conn);
				echo $FinalGatewayChkResult;
				//echo "ok2";				
			}
			
		}
	}	
}
//exit;
?>