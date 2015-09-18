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
			case "AD":
				$FIELD_R="ADID";
				$FIELD_L="ADName";
				$TABLE="AdsName";
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
			case "AD":
				$FIELD_L="ADID";
				$FIELD_R="ADName";
				$TABLE="AdsName";
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
		if ( $TYPE == "Status" ) {
			$UPDATE_TABLE="RealtimeViews";
			$UPDATE_FILED="Status";
			$UPDATE_ADPG_F="ProgramlID";
			$UPDATE_ADPG_T="programstarttime";
		}else if ( $TYPE == "Favorite" ) {
			$UPDATE_TABLE="RealtimeViews";
			$UPDATE_FILED="Favorite";
			$UPDATE_ADPG_F="ProgramlID";
			$UPDATE_ADPG_T="programstarttime";
		}else if ( $TYPE == "ADStatus" ) {
			$UPDATE_TABLE="RealtimeViewADs";
			$UPDATE_FILED="Status";
			$UPDATE_ADPG_F="ADID";
			$UPDATE_ADPG_T="ADTime";
		}else if ( $TYPE == "ADFavorite" ) {
			$UPDATE_TABLE="RealtimeViewADs";
			$UPDATE_FILED="Favorite";
			$UPDATE_ADPG_F="ADID";
			$UPDATE_ADPG_T="ADTime";
		}else {
			return -15;
		}
		
		if ( $CHANNEL != null and $PROGRAM != null and $TIME !=null ) {
		
			$query=$this->con->prepare("INSERT INTO `$UPDATE_TABLE`
				       (`UserID`, `$UPDATE_ADPG_F`, `$UPDATE_ADPG_T`, `ChannelID`, `$UPDATE_FILED`) 
				VALUES (:UID, :PROGRAM, :TIME, :CHANNEL, :STATUS)");
			$query->bindParam(':UID',$UID,PDO::PARAM_INT);
			$query->bindParam(':PROGRAM',$PROGRAM,PDO::PARAM_INT);
			$query->bindParam(':CHANNEL',$CHANNEL,PDO::PARAM_INT);
			$query->bindParam(':STATUS',$SFIELD,PDO::PARAM_INT);
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
						$update=$this->con->prepare("UPDATE `$UPDATE_TABLE` SET `$UPDATE_FILED` = :STATUS 
							WHERE `$UPDATE_TABLE`.`UserID` = :UID
							AND `$UPDATE_TABLE`.`$UPDATE_ADPG_F` = :PROGRAM
							AND `$UPDATE_TABLE`.`$UPDATE_ADPG_T` = :TIME; ");
						$update->bindParam(':UID',$UID,PDO::PARAM_INT);
						$update->bindParam(':PROGRAM',$PROGRAM,PDO::PARAM_INT);
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
			$target_table="RealtimeViews";
			$target_field="ProgramlID";
			$target_time="programstarttime";
		} else if ( $type == "Channel") {
			$target_table="RealtimeViews";
			$target_field="ChannelID";
			$target_time="programstarttime";
		} else if ( $type == "AD") {
			$target_table="RealtimeViewADs";
			$target_field="ADID";
			$target_time="ADTime";
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
			FROM `$target_table` 
			WHERE `$target_field` = :TARGET $target_status AND
				`$target_time` BETWEEN :START_TIME AND :ENDING_TIME ");
		
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

	function myPrograms($UID,$TYPE,$status,$favorite,$limit,$start) {
		if ( $TYPE=="Program" ) {
			$table='`RealtimeViews`';
			$field_TIME='`programstarttime`';
			if ( $status == "null" ) {
				$field='`ChannelID` , `ProgramlID` , `programstarttime` , `Status` , `Favorite`';
				$w_field='';
			}
			else if ( $favorite === true ) {
				$field='`ChannelID` , `ProgramlID` , `programstarttime`';
				$w_field='AND `Favorite` = :status';
			}
			else {
				$field='`ChannelID` , `ProgramlID` , `programstarttime`';
				$w_field='AND `Status` = :status';
			}
		} else if ( $TYPE=="AD" ) {
			$table='`RealtimeViewADs`';
			$field_TIME='`ADTime`';
			if ( $status == "null" ) {
				$field='`ChannelID` , `ADID` , `ADTime` , `Status` , `Favorite`';
				$w_field='';
			}
			else if ( $favorite === true ) {
				$field='`ChannelID` , `ADID` , `ADTime`';
				$w_field='AND `Favorite` = :status';
			}
			else {
				$field='`ChannelID` , `ADID` , `ADTime`';
				$w_field='AND `Status` = :status';
			}
		}
		
		if ( $limit > 0 ) 
			$limitCond="LIMIT ".(int)$start.", ".(int)$limit;
		else 
			$limitCond="";
		
		$query=$this->con->prepare("SELECT $field
						FROM $table
						WHERE `UserID` = :UID $w_field
						ORDER by $field_TIME DESC
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
				if ( $TYPE=="Program" ) {
					if ( $status != "null" ) {
						array_push($res, array(
							"Channel"=>$this->lookupNames("Channel",$row["ChannelID"]),
							"Time"=>$row["programstarttime"],
							"Program"=>$this->lookupNames("Program",$row["ProgramlID"])
						));
					} else {
						array_push($res, array(
							"Channel"=>$this->lookupNames("Channel",$row["ChannelID"]),
							"Time"=>$row["programstarttime"],
							"Program"=>$this->lookupNames("Program",$row["ProgramlID"]),
							"Status"=>$row["Status"],
							"Favorite"=>$row["Favorite"]
						));
					}
					//print_r($row);
				} else if ( $TYPE=="AD" ) {
					if ( $status != "null" ) {
						array_push($res, array(
							"Channel"=>$this->lookupNames("Channel",$row["ChannelID"]),
							"Time"=>$row["ADTime"],
							"AD"=>$this->lookupNames("AD",$row["ADID"])
						));
					} else {
						array_push($res, array(
							"Channel"=>$this->lookupNames("Channel",$row["ChannelID"]),
							"Time"=>$row["ADTime"],
							"AD"=>$this->lookupNames("AD",$row["ADID"]),
							"Status"=>$row["Status"],
							"Favorite"=>$row["Favorite"]
						));
					}
					//print_r($row);
				}
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
	
	function getRanking($type,$status,$range,$limit,$start) {
		if ( $type == "Channel" ) {
			$table="`RealtimeViews`";
			$field="`ChannelID` ";
			$field_TIME="`programstarttime`";
		}
		else if ( $type == "Program" ) {
			$table="`RealtimeViews`";
			$field="`ChannelID` , `ProgramlID` ";
			$field_TIME="`programstarttime`";
		}
		else if ( $type == "AD" ) {
			$table="`RealtimeViewADs`";
			$field="`ADID`";
			$field_TIME="`ADTime`";
		}
		
		if ( $range == "Year" ) {
			$E_TIME=$this->formatTime($this->oldTime(0,"NOW"),"month");
			$S_TIME=$this->formatTime($this->oldTime(1,"year"),"month");
			$cond="Where $field_TIME BETWEEN \"$S_TIME\" AND \"$E_TIME\" ";
		} else if ( $range == "Month" ) {
			$E_TIME=$this->formatTime($this->oldTime(0,"NOW"),"day");
			$S_TIME=$this->formatTime($this->oldTime(1,"month"),"day");
			$cond="Where $field_TIME BETWEEN \"$S_TIME\" AND \"$E_TIME\" ";
		} else if ( $range == "Week" ) {
			$S_TIME=$this->formatTime($this->oldTime(1,"week"),"hour");
			$E_TIME=$this->formatTime($this->oldTime(0,"NOW"),"hour");
			$cond="Where $field_TIME BETWEEN \"$S_TIME\" AND \"$E_TIME\" ";
		} else if ( $range == "Day" ) {
			$E_TIME=$this->formatTime($this->oldTime(0,"NOW"),"second");
			$S_TIME=$this->formatTime($this->oldTime(1,"day"),"second");
			$cond="Where $field_TIME BETWEEN \"$S_TIME\" AND \"$E_TIME\" ";
		} else {
			$cond="Where ";
		}
		
		if ( $status == 0 AND $cond == "Where " ) 
			$cond="";
		else if (  $status == 0 )
			$cond.="";
		else
			$cond.=" AND `Status` = :STATUS";
		
		if ( $limit > 0 ) 
			$limitCond="LIMIT ".(int)$start.", ".(int)$limit;
		else 
			$limitCond="";
		
		$query=$this->con->prepare("SELECT $field , count(UserID) as Counting
						FROM $table
						$cond
						GROUP BY $field
						ORDER BY `Counting` DESC
						$limitCond");
		
		if ( $status != 0 )
			$query->bindParam(':STATUS',$status,PDO::PARAM_INT);
		
		if ( $query->execute() ) {
			$result = $query->fetchAll();
			$count=0;
			$res=array();
			foreach( $result as $row ) {
				$count++;
				if ( $type == "Channel" ) {
					array_push($res, array(
						"Channel"=>$this->lookupNames("Channel",$row["ChannelID"]),
						"Counting"=>$row["Counting"]
					));
				} else if ( $type == "Program" ) {
					array_push($res, array(
						"Channel"=>$this->lookupNames("Channel",$row["ChannelID"]),
						//"Time"=>$row["programstarttime"],
						"Program"=>$this->lookupNames("Program",$row["ProgramlID"]),
						"Counting"=>$row["Counting"]
					));
				} else if ( $type == "AD" ) {
					array_push($res, array(
						"Channel"=>$this->lookupNames("AD",$row["ADID"]),
						"Counting"=>$row["Counting"]
					));
				}
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

