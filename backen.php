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
	
	sentJSON($errcode,$errmsg,$detail);
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
			sentJSON(-1,"失敗",$res);
			break;
	}
	
}

function getChannelStatus($data_field,$con) {

	$ChannelID=$con->lookupIDs("Channel",$data_field["Channel"]);
	
	$res=$con->statics("Channel",
		$ChannelID,$data_field["Status"],"Year",false);
	echo "res(Year)=".$res."\n";
	
	$return_data=array(
		ChannelName => $data_field["Channel"],
		RecentlyView => 10000,
		RecentlyLike =>  5000,
		RecentlyHate =>  3000,
		WeeklyView => 100000,
		WeeklyLike =>  60000,
		WeeklyHate =>  30000,
		MonthView => 350000,
		MonthLike =>  18000,
		MonthHate =>  91000,
		YearView => 350000,
		YearLike =>  18000,
		YearHate =>  91000
	);
	sentJSON(1,"資料取得成功",$return_data);
}

function getProgramStatus($data_field) {
	$return_data=array(
		ProgramName => "假的節目名稱",
		RecentlyView => 10000,
		RecentlyLike =>  5000,
		RecentlyHate =>  3000,
		WeeklyView => 100000,
		WeeklyLike =>  60000,
		WeeklyHate =>  30000,
		MonthView => 350000,
		MonthLike =>  18000,
		MonthHate =>  91000,
		YearView => 350000,
		YearLike =>  18000,
		YearHate =>  91000
	);
	sentJSON(1,"資料取得成功",$return_data);
}

function getChannelComment($data_field) {
	
	
	
}

function getProgramComment($data_field) {
	
	
	
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
		show_error(-50,"請輸入UID");
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
			case "/API/SENTCOMMENT":
			case "/API/SENTCOMMENT/":
				$ChannelID=$con->lookupIDs("Channel",$dataForm["Channel"]);
				$ProgramID=$con->lookupIDs("Program",$dataForm["Program"]);
				
				$res=$con->sent("Comment",
					$dataForm["UID"],$ChannelID,$ProgramID,$dataForm["Time"],$dataForm["Comment"]);
				sentResault($res);
				break;
			case "/API/GETCHANNELSTATUS":
			case "/API/GETCHANNELSTATUS/":
				getChannelStatus($dataForm,$con);
				break;
			case "/API/GETPROGRAMSTATUS":
			case "/API/GETPROGRAMSTATUS/":
				getProgramStatus($dataForm);
				break;
			case "/API/GETCHANNELCOMMENT":
			case "/API/GETCHANNELCOMMENT/":
				getChannelComment($dataForm);
				break;
			case "/API/GETPROGRAMCOMMENT":
			case "/API/GETPROGRAMCOMMENT/":
				getProgramComment($dataForm);
				break;
			default:
				show_error(-52,"URI=".$_SERVER["REQUEST_URI"]."\nAPI=".$API);
				break;
		}
	}
	
}

$con=null;
?>
