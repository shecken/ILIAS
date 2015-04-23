<?php
	require_once("Services/User/classes/class.ilObjUser.php");
	require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevSettings.php");
/**
*Using the provided data the language options of users are adjusted.
* Language is set to german for the members of DE role, to chinese for the members of CN role
* and to english for others.
*@author Denis Klöpfer
*/

	class spxUpdateUsrData {
		private static $spxdb;
		private static $usrHandler;
		
		private static $kill_in_usr_data =	array(
									"title"
									,"street"
									,"phone_office"
									,"hobby"
									,"phone_home"
									,"phone_mobile"
									,"fax"
									,"matriculation"
									,"client_ip"
									,"im_icq"
									,"im_yahoo"
									,"im_aim"
									,"im_skype"
									,"im_msn"
									,"delicious"
									,"birthday"
									,"im_jabber"
									,"im_voip"
									,"sel_country");
		
		private	static function generateRandomString($length = 10) {
    		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
   			$charactersLength = strlen($characters);
    		$randomString = '';
    		for ($i = 0; $i < $length; $i++) {
   		    	$randomString .= $characters[rand(0, $charactersLength - 1)];
    		}
  			return $randomString;
		}


		private static function connectspxdb() {
			global $ilClientIniFile;
			$host = $ilClientIniFile->readVariable('seepexdb', 'host');
			$user = $ilClientIniFile->readVariable('seepexdb', 'user');
			$pass = $ilClientIniFile->readVariable('seepexdb', 'pass');				
			$name = $ilClientIniFile->readVariable('seepexdb', 'name');
			self::$spxdb= mysql_connect($host, $user, $pass) 
				or die("Something is wrong: ".mysql_error());
			mysql_select_db($name, self::$spxdb);
			mysql_set_charset('utf8', self::$spxdb);
		}

		private static function queryspxdb($query) {
			return mysql_query($query, self::$spxdb);
		}

		private static function closespxdb() {
			mysql_close(self::$spxdb);
		}

		private static function getUsrHandler() {
			$sql = "SELECT * FROM iliasImport, SEEPEXorg WHERE OUshort=OU";
			self::$usrHandler = self::queryspxdb($sql);
		}

		private static function deleteUsers() {
			$sql = "SELECT * FROM iliasImport WHERE transfer ='nein'";
			$rec = self::queryspxdb($sql);
			while($res = mysql_fetch_assoc($rec)) {
				$usrId = ilObjUser::_lookUpId($res["login"]);
				if($usrId) {
					$usr = new ilObjUser($usrId);
					$usr->delete();
					echo "<br> user deleted ".$res["login"];
				}
			}
		}

		private static function setUsersInactive() {
			$sql = "SELECT * FROM iliasImport WHERE OU ='Exit'";
			$rec = self::queryspxdb($sql);
			while($res = mysql_fetch_assoc($rec)) {
				$usrId = ilObjUser::_lookUpId($res["login"]);
				if($usrId) {
					$usr = new ilObjUser($usrId);
					$usr->setApproveDate(null);	
					$usr->update();
					echo "<br> user set inactive ".$res["login"];
				}
			}
		}

		public static function updateUsrData() {
			
			//$users_missing_data = fopen('users_mising_data.dat','w');
			//$deleted_user = fopen('deleted_users.dat','w');
			//fputcsv($users_missing_data, array("usrlogin","gender","email"),";");

			self::connectspxdb();
			self::getUsrHandler();


			global $ilDB;
			global $ilClientIniFile;

			$UDFutils = gevUDFUtils::getInstance();

			while($res = mysql_fetch_assoc(self::$usrHandler)) {


				$usrexists = ilObjUser::_lookUpId($res["login"]);
		
				//$ugm = array($res["login"],1,1);



				if ($usrexists) {


					$usr = new ilObjUser($usrexists);
				
					$usremail = $usr->getEmail();

					if (!$usremail&&$res["email"]) {
						$usr->setEmail($res["email"]);
					}
					
					if(!$usr->getCountry()) {
						$usr->setCountry($res["country"]);
					}
					if(!$usr->getCity()) {
						$usr->setCity($res["city"]);
					}

					if($ctry = $usr->getSelectedCountry()) {
						$usr->setCountry($ctry);
					} elseif($res["country"]) {
						$usr->setCountry($res["country"]);
					}

					$usr->setInstitution($res["OUshort"]);
					$usr->setDepartment($res["OUilias"]);
					$usr->update();



					$UDFutils->setField($usrexists, USR_UDF_SALES_REGION, $res["salesregion"]);
					$UDFutils->setField($usrexists, USR_UDF_COMPANY_NAME, $res["comp"]);
					$UDFutils->setField($usrexists, USR_UDF_ENTRY_DATE, $res["empdate"]);
				} else if (!$usrexists&&$res["transfer"]=='ja') {
					echo '<h2>did not find a user: </h2>';  
					print_r($res);
					
				}

			}
			self::deleteUsers();
			self::setUsersInactive();
			self::closespxdb();

			$sql="UPDATE usr_data SET ".implode(" = NULL , ",self::$kill_in_usr_data)." = NULL ";
			$ilDB->query($sql);
		}
	}
?>
