<?php 
require_once('database.php');

function sentJSON($status,$msg,$detail) {
	print json_encode(
		array("API_status" => $status,
			"API_msg" => $msg,
			"API_det" => $detail
		),JSON_UNESCAPED_UNICODE
	);
}

function show_error($errcode,$detail) {
	//各種錯誤
	
	sentJSON($errcode,"錯誤發生",$detail);
}

function sentResault($res) {
	switch ($res) {
		case 0:
			sentJSON(0,"傳送成功",null);
			break;
		case 1:
			sentJSON(1,"更新成功",null);
			break;
		default:
			sentJSON($res,"失敗","請查閱手冊");
			break;
	}
	
}

//not in use
function getStatus($type,$data_field,$con) {
	if ( $type == "Channel" ) 
		$Target="Channel";
	else if ( $type == "Program" ) 
		$Target="Program";
	
	$TargetID=$con->lookupIDs("$Target",$data_field[$Target]);
	
	$return_data=array(
		$Target."Name" => $data_field["$Target"],
		"RecentlyView" => $con->statics("$Target",
			$TargetID,"none","Day",false),
		"RecentlyLike" => $con->statics("$Target",
			$TargetID,1,"Day",false),
		"RecentlyHate" => $con->statics("$Target",
			$TargetID,-1,"Day",false),
		"WeeklyView" => $con->statics("$Target",
			$TargetID,"none","Week",false),
		"WeeklyLike" => $con->statics("$Target",
			$TargetID,1,"Week",false),
		"WeeklyHate" => $con->statics("$Target",
			$TargetID,-1,"Week",false),
		"MonthView" => $con->statics("$Target",
			$TargetID,"none","Month",false),
		"MonthLike" => $con->statics("$Target",
			$TargetID,1,"Month",false),
		"MonthHate" => $con->statics("$Target",
			$TargetID,-1,"Month",false),
		"YearView" => $con->statics("$Target",
			$TargetID,"none","Year",false),
		"YearLike" => $con->statics("$Target",
			$TargetID,1,"Year",false),
		"YearHate" => $con->statics("$Target",
			$TargetID,-1,"Year",false)
	);
	sentJSON(1,"資料取得成功",$return_data);
}

//連線至資料庫，並開始準備查詢
$con=new dbConnections();

if ( $con->status() ) {
	//先把URI全部轉大寫省麻煩
	$API=strtoupper($_SERVER["REQUEST_URI"]);
	//確認資料放在GET還是POST
	//另一種方法是$_SERVER["REQUEST_METHOD"]
	if (isset($_GET["UID"])) {
		$dataForm=$_GET;
		$API=substr($API,0,strrpos($_SERVER["REQUEST_URI"],"?"));
	}
	else if (isset($_POST["UID"])) {
		$dataForm=$_POST;
	}
	else { 
		show_error(-1,"請輸入UID");
	}
	
	if (isset($dataForm)) {
		switch( $API ) {
		case "/API/SENTSTATUS":
		case "/API/SENTSTATUS/":
			$ChannelID=$con->lookupIDs("Channel",$dataForm["Channel"]);
			$ProgramID=$con->lookupIDs("Program",$dataForm["Program"]);
			
			$res=$con->sent("Status",
				$dataForm["UID"],$ChannelID,$ProgramID,$dataForm["Time"],$dataForm["Status"]);
			sentResault($res);
			break;
		case "/API/GETCHANNELSTATUS":
		case "/API/GETCHANNELSTATUS/":
			getStatus("Channel",$dataForm,$con);
			break;
		case "/API/GETPROGRAMSTATUS":
		case "/API/GETPROGRAMSTATUS/":
			getStatus("Program",$dataForm,$con);
			break;
		case "/API/GETMYPROGRAMS":
		case "/API/GETMYPROGRAMS/":
			$con->myPrograms($dataForm["UID"],$dataForm["Status"],null,30,0);
			break;
		case "/API/GETMYFAVORITE":
		case "/API/GETMYFAVORITE/":
			$con->myPrograms($dataForm["UID"],1,true,30,0);
			break;
		
		default:
			show_error(-7,"URI=".$_SERVER["REQUEST_URI"]."\nAPI=".$API);
			break;
		}
	}
	
}

$con=null;
?>
