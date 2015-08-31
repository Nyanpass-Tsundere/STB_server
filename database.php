<?php 

//把連線弄成一個物件處理
class dbConnections{
	//直接用public(隨時可用) protected(物件及父/子物件) private(物件)宣變數
	//如果用var的話則視為public 
	protected $con;
	protected $connected=false;
	
	//建構元這樣寫
	function __construct( ){
		//匯入連線參數
		include "config.php";
		
		//連線
		try {
			//建立連線
			$this->con = new PDO(
				"$db_type".':host='."$db_host".';dbname='.
				"$db_name", "$db_username", "$db_password");
			//設定編碼
			$this->con->exec('SET NAMES '."$db_encode");
			//指定資料庫
			$this->con->exec('USE '."$db_name");
			
			$this->connected=true;
			return true;
		} catch (PDOException $e) {
			//完全連不上資料庫
			return -10;
		}
	}
	
	//解構元
	function __destruct( ) {
		//關閉資料庫連線
		$con=null;
	}
	
	function status( ) {
		return $this->connected;
	}
	
	function lookupIDs($TYPE,$NAME) {
		switch ($TYPE) {
			case "Channel":
				$FIELD_R="ChannelID";
				$FIELD_L="ChannelName";
				$TABLE="ChannelName";
				break;
			case "Program":
				$FIELD_R="ProgramID";
				$FIELD_L="ProgramName";
				$TABLE="ProgramName";
				break;
		}
		$query=$this->con->prepare("SELECT `$FIELD_R` 
				FROM `$TABLE`
				WHERE $TABLE.$FIELD_L = :NAME");
		$query->bindParam(':NAME',$NAME,PDO::PARAM_STR);
		$query->execute();
		$result = $query->fetchAll();
		if ( !array_key_exists("0",$result) ) {
			$add_query=$this->con->prepare("INSERT INTO `$TABLE`
								 (`$FIELD_L`) VALUES (:NAME)");
			$add_query->bindParam(':NAME',$NAME,PDO::PARAM_STR);
			$add_query->execute();
			$query->execute();
			$result = $query->fetchAll();
		}
		return $result["0"]["$FIELD_R"];
	}
	
	function lookupNames($TYPE,$NAME) {
		switch ($TYPE) {
			case "Channel":
				$FIELD_L="ChannelID";
				$FIELD_R="ChannelName";
				$TABLE="ChannelName";
				break;
			case "Program":
				$FIELD_L="ProgramID";
				$FIELD_R="ProgramName";
				$TABLE="ProgramName";
				break;
		}
		$query=$this->con->prepare("SELECT `$FIELD_R` 
				FROM `$TABLE`
				WHERE $TABLE.$FIELD_L = :NAME");
		$query->bindParam(':NAME',$NAME,PDO::PARAM_INT);
		$query->execute();
		$result = $query->fetchAll();
		if ( !array_key_exists("0",$result) ) {
			return 0;
		}
		return $result["0"]["$FIELD_R"];
	}
	
	function sent($TYPE,$UID,$CHANNEL,$PROGRAM,$TIME,$SFIELD) {
		if ( $TYPE == "Status") {
			$TABLE="RealtimeViews";
			$FIELD="Status";
			$FIELD_TYPE="INT";
		}
		/*
		else if ( $TYPE == "Comment" ) {
			$TABLE="RealtimeComment";
			$FIELD="Comment";
			$FIELD_TYPE="STR";
		}*/else {
			return -15;
		}
		
		if ( $CHANNEL != null and $PROGRAM != null and $TIME !=null ) {
		
			$query=$this->con->prepare("INSERT INTO `$TABLE`
				       (`UserID`, `ProgramlID`, `programstarttime`, `ChannelID`, `$FIELD`) 
				VALUES (:UID, :PROGRAM, :TIME, :CHANNEL, :SFILED)");
			$query->bindParam(':UID',$UID,PDO::PARAM_INT);
			$query->bindParam(':PROGRAM',$PROGRAM,PDO::PARAM_INT);
			$query->bindParam(':CHANNEL',$CHANNEL,PDO::PARAM_INT);
			$query->bindParam(':SFILED',$SFIELD,PDO::PARAM_INT);
			$query->bindParam(':TIME',$TIME,PDO::PARAM_LOB);
			
			if ( $query->execute() ) {
				return 0;
			}
			else {
				$error=$query->errorInfo();
				
				//處裡已經評價過的狀況
				if ( $error[0] == 23000 ) {
					switch ($SFIELD){
					case 0:
						return -21;
						break;
					case 100:
						$SFIELD=0;
					default:
						$update=$this->con->prepare("UPDATE `$TABLE` SET `$FIELD` = :STATUS 
							WHERE `RealtimeViews`.`UserID` = :UID
							AND `RealtimeViews`.`ProgramlID` = :PROGRAM
							AND `RealtimeViews`.`programstarttime` = :TIME; ");
						$update->bindParam(':UID',$UID,PDO::PARAM_INT);
						$update->bindParam(':PROGRAM',$PROGRAM,PDO::PARAM_INT);
						#$update->bindParam(':CHANNEL',$CHANNEL,PDO::PARAM_INT);
						$update->bindParam(':STATUS',$SFIELD,PDO::PARAM_INT);
						$update->bindParam(':TIME',$TIME,PDO::PARAM_LOB);
						
						if ( $update->execute() ) {
							return 1;
							break;
						}
						else {
							$error=$update->errorInfo();
							return $error;
						}
						
					}
				}
				else {
					return $error;
				}
				
			}
		
		} else {
			return -3;
		}
	}
	
	private function formatTime($unixTime,$skip) {
		if ( $skip == "second" ) return date("Y-m-d H:i:00", $unixTime);
		else if ( $skip == "minute" ) return date("Y-m-d H:00:00", $unixTime);
		else if ( $skip == "hour" ) return date("Y-m-d 00:00:00", $unixTime);
		else if ( $skip == "day" ) return date("Y-m-01 00:00:00", $unixTime);
		else if ( $skip == "month" ) return date("Y-01-01 00:00:00", $unixTime);
		else return date("Y-m-d H:i:s", $unixTime);
		
	}
	
	private function oldTime($num,$type) {
		if ( $type == "hour" ) {
			#return mktime()-60*60*$num;
			return mktime()-3600*$num;
		}
		else if ( $type == "day" ) {
			#return mktime()-60*60*24*$num;
			return mktime()-86400*$num;
		}
		else if ( $type == "week" ) {
			#return mktime()-60*60*24*7*$num;
			return mktime()-604800*$num;
		}
		else if ( $type == "month" ) {
			#return mktime()-60*60*24*30*$num;
			return mktime()-2592000*$num;
		}
		else if ( $type == "year" ) {
			#return mktime()-60*60*24*365*$num;
			return mktime()-31536000*$num;
		}
		else {
			return mktime();
		}
	}
	
	function statics($type,$target,$status,$seq,$noDup) {
		if ( $type == "Program" ) {
			$target_field="ProgramlID";
		} else if ( $type == "Channel") {
			$target_field="ChannelID";
		}
		
		if ( $status != "none" ) 
			$target_status="AND `Status` = :STATUS";
		else 
			$target_status="";
		
		$COND_DUP="";
		if ( $noDup === true ) $COND_DUP="DISTINCT";
		
		if ( $seq == "Day" ) {
			$E_TIME=$this->formatTime($this->oldTime(0,"NOW"),"second");
			$S_TIME=$this->formatTime($this->oldTime(1,"day"),"second");
		} else if ( $seq == "Week" ) {
			$E_TIME=$this->formatTime($this->oldTime(0,"NOW"),"hour");
			$S_TIME=$this->formatTime($this->oldTime(1,"week"),"hour");
		} else if ( $seq == "Month" ) {
			$E_TIME=$this->formatTime($this->oldTime(0,"NOW"),"day");
			$S_TIME=$this->formatTime($this->oldTime(1,"month"),"day");
		} else if ( $seq == "Year" ) {
			$E_TIME=$this->formatTime($this->oldTime(0,"NOW"),"month");
			$S_TIME=$this->formatTime($this->oldTime(1,"year"),"month");
		}
		#echo "S_TIME=$S_TIME\n";
		#echo "E_TIME=$E_TIME\n";
		
		$query=$this->con->prepare("SELECT COUNT( $COND_DUP `UserID` ) as COUNTING
			FROM `RealtimeViews` 
			WHERE `$target_field` = :TARGET $target_status AND
				`programstarttime` BETWEEN :START_TIME AND :ENDING_TIME ");
		
		$query->bindParam(':TARGET',$target,PDO::PARAM_INT);
		if ( $status != "none" ) 
			$query->bindParam(':STATUS',$status,PDO::PARAM_INT);	
		$query->bindParam(':START_TIME',$S_TIME,PDO::PARAM_INT);
		$query->bindParam(':ENDING_TIME',$E_TIME,PDO::PARAM_INT);
		$query->execute();
		
		$result = $query->fetchAll();	
		#$error=$query->errorInfo();
		#print_r($error);
		#print_r($result);
		#print("\n");
		return $result["0"]["COUNTING"];
	}
	
	function myPrograms($UID,$status,$favorite,$limit,$start) {
		if ( $status=="null" )
			$field='';
		else if ( $favorite === true ) 
			$field='AND `Favorite` = :status';
		else 
			$field='AND `Status` = :status';
		
		if ( $start > 0 ) 
			$limitCond="LIMIT ".(int)$start.", ".(int)$limit;
		else
			$limitCond="";
		
		$query=$this->con->prepare("SELECT `ChannelID` , `ProgramlID` , `programstarttime` 
						FROM `RealtimeViews`
						WHERE `UserID` = :UID $field
						ORDER by `programstarttime` DESC
						$limitCond");
						//LIMIT :limit , :start");
		$query->bindParam(':UID',$UID,PDO::PARAM_INT);
		if ( $status!="null" )
			$query->bindParam(':status',$status,PDO::PARAM_INT);
		//$query->bindParam(':start',$start,PDO::PARAM_INT);
		//$query->bindParam(':limit',$limit,PDO::PARAM_INT);
		
		if ( $query->execute() ) {
			$result = $query->fetchAll();
			$count=0;
			$res=array();
			foreach( $result as $row ) {
				$count++;
				array_push($res, array(
					"Channel"=>$this->lookupNames("Channel",$row["ChannelID"]),
					"Time"=>$row["programstarttime"],
					"Program"=>$this->lookupNames("Program",$row["ProgramlID"])
				));
				//print_r($row);
			}
			#echo "count=".$count;
			//print_r($result);
			//print_r($res);
			return array("Status"=>$count,"Content"=>$res);
		}
		else {
			$error=$query->errorInfo();
			return array("Status"=>-15,"Content"=>$error);
		}
	}
}

