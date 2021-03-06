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
	else if ( $type == "AD" )
		$Target="AD";
	
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
function getMyPrograms($con,$UID,$TYPE,$Status,$Fav,$Limit,$Start) {
	$resArray=$con->myPrograms($UID,$TYPE,$Status,$Fav,$Limit,$Start);
	
	if ( $resArray["Status"] < 0 ) {
		show_error($resArray["Status"],$resArray["Content"]);	
	} else if ( $resArray["Status"] == 0 ) {
		sentJSON(0,"無資料",null);
	} else {
		sentJSON($resArray["Status"],"成功取得清單",$resArray["Content"]);
	}
}

function getRanking($con,$type,$status,$range,$limit,$start) {
	$resArray=$con->getRanking($type,$status,$range,$limit,$start);
	
	if ( $resArray["Status"] < 0 ) {
		show_error($resArray["Status"],$resArray["Content"]);	
	} else if ( $resArray["Status"] == 0 ) {
		sentJSON(0,"無資料",null);
	} else {
		sentJSON($resArray["Status"],"成功取得清單",$resArray["Content"]);
	}
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
		case "/API/SENTFAVORITE":
		case "/API/SENTFAVORITE/":
			$ChannelID=$con->lookupIDs("Channel",$dataForm["Channel"]);
			$ProgramID=$con->lookupIDs("Program",$dataForm["Program"]);
			
			$res=$con->sent("Favorite",
				$dataForm["UID"],$ChannelID,$ProgramID,$dataForm["Time"],$dataForm["Status"]);
			sentResault($res);
			break;
		
		case "/API/SENTADSTATUS":
		case "/API/SENTADSTATUS/":
			$ChannelID=$con->lookupIDs("Channel",$dataForm["Channel"]);
			$ADID=$con->lookupIDs("AD",$dataForm["AD"]);
			
			$res=$con->sent("ADStatus",
				$dataForm["UID"],$ChannelID,$ADID,$dataForm["Time"],$dataForm["Status"]);
			sentResault($res);
			break;
		case "/API/SENTADFAVORITE":
		case "/API/SENTADFAVORITE/":
			$ChannelID=$con->lookupIDs("Channel",$dataForm["Channel"]);
			$ADID=$con->lookupIDs("AD",$dataForm["AD"]);
			
			$res=$con->sent("ADFavorite",
				$dataForm["UID"],$ChannelID,$ADID,$dataForm["Time"],$dataForm["Status"]);
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
		case "/API/GETADSTATUS":
		case "/API/GETADSTATUS/":
			getStatus("AD",$dataForm,$con);
			break;
		
		case "/API/GETMYPROGRAMS":
		case "/API/GETMYPROGRAMS/":
			getMyPrograms($con,$dataForm["UID"],"Program",$dataForm["Status"],null,
				$dataForm["Amount"],$dataForm["Skips"]);
			break;
		case "/API/GETMYFAVORITE":
		case "/API/GETMYFAVORITE/":
			getMyPrograms($con,$dataForm["UID"],"Program",1,true,
				$dataForm["Amount"],$dataForm["Skips"]);
			break;
		
		case "/API/GETMYAD":
		case "/API/GETMYAD/":
			getMyPrograms($con,$dataForm["UID"],"AD",$dataForm["Status"],null,
				$dataForm["Amount"],$dataForm["Skips"]);
			break;
		case "/API/GETMYADFAVORITE":
		case "/API/GETMYADFAVORITE/":
			getMyPrograms($con,$dataForm["UID"],"AD",1,true,
				$dataForm["Amount"],$dataForm["Skips"]);
			break;
		
		case "/API/GETCHANNELRANK":
		case "/API/GETCHANNELRANK/":
			getRanking($con,"Channel",$dataForm["Status"],$dataForm["Range"],
				$dataForm["Amount"],$dataForm["Skips"]);
			break;
		case "/API/GETPROGRAMRANK":
		case "/API/GETPROGRAMRANK/":
			getRanking($con,"Program",$dataForm["Status"],$dataForm["Range"],
				$dataForm["Amount"],$dataForm["Skips"]);
			break;
		case "/API/GETADRANK":
		case "/API/GETADRANK/":
			getRanking($con,"AD",$dataForm["Status"],$dataForm["Range"],
				$dataForm["Amount"],$dataForm["Skips"]);
			break;
		
		
		default:
			show_error(-7,"URI=".$_SERVER["REQUEST_URI"]."\nAPI=".$API);
			break;
		}
	}
	
}

$con=null;
?>
