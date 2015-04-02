‚<?php
	require_once("Services/User/classes/class.ilObjUser.php");

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

		private static $kill_in_udf = 	array(
								'%Eintrittsdatum%'
								,'%Vertriebsregion%'
								,'%Standart-Skin%'
								,'%Aktive Benutzer%'
								,'%Persönnliches%');
		
		private	function generateRandomString($length = 10) {
    		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
   			$charactersLength = strlen($characters);
    		$randomString = '';
    		for ($i = 0; $i < $length; $i++) {
   		    	$randomString .= $characters[rand(0, $charactersLength - 1)];
    		}
  			return $randomString;
		}


		private function connectspxdb() {
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

		private function queryspxdb($query) {
			return mysql_query($query, self::$spxdb);
		}

		private function closespxdb() {
			mysql_close(self::$spxdb);
		}

		private function getUsrHandler() {
			$sql="SELECT * FROM `iliasImport`";
			 self::$usrHandler = self::queryspxdb($sql);
		}

		public static function updateUsrData() {
			
			$file=fopen('users_mising_data.dat','w');
			fputcsv($file,array("usrlogin","gender","email"),";");

			self::connectspxdb();
			self::getUsrHandler();


			global $ilDB;
			global $ilClientIniFile;
			while($res=mysql_fetch_assoc(self::$usrHandler)) {


				$usrexists=ilObjUser::_lookUpId($res["login"]);
		
				$ugm=array($res["login"],1,1);
				$flag=0;


				if ($usrexists && $res["transfer"]=="nein") {
					$usr=new ilObjUser($usrexists);
					$usr->delete();
				}
				else if ($usrexists && $res["transfer"]=="ja") {

					$usr=new ilObjUser($usrexists);
					$a=$usr->getFirstname();

					$usrgender=$usr->getGender();

					if (!$usrgender&&!$res["gender"]) {
						$foo = $ilClientIniFile->readVariable('generic_usr_data','gender');
						$usr->setGender($foo);
						$ugm[1]=0;
						$flag++;
					}
					else if ($res["gender"]&&$res["gender"]!=$usrgender) {
						$usr->setGender($res["gender"]);
					}

					$usremail=$usr->getEmail();

					if (!$usremail&&!$res["email"]) {
						$foo=$ilClientIniFile->readVariable('generic_usr_data','email');
						$usr->setEmail($foo);
						$ugm[2]=0;
						$flag++;
					} else if ($res["email"]&&$res["email"]!=$usremail) {
						$usr->setEmail($res["email"]);
					}
						




					$usr->setInstitution($res["institution"]);
					$usr->setDepartment($res["department"]);

					$usr->update();

					if (strtolower($res["roleCtry"]) == "de") {
						$lng = "de";
					} 
					else if (strtolower($res["roleCtry"]) == "cn") {
						$lng = "zh";
					} 
					else {
						$lng = "en";
					}

					$usr->setLanguage($lng);
					$usr->writePrefs();
				}
				else if (!$usrexists &&  $res["transfer"]=="ja") {


					$usr=new ilObjUser();
					$res["passwd_type"] = IL_PASSWD_PLAIN;
					//$res["passwd"] = self::generateRandomString();
					$res["passwd"] = $ilClientIniFile->readVariable('generic_usr_data', 'passwd');
					$res["time_limit_unlimited"] = 1;
					$res["agree_date"] = ilUtil::now();


					if(!$res["gender"]) {
						$res["gender"]=$ilClientIniFile->readVariable('generic_usr_data', 'gender');
						$ugm[1]=0;
					}
					if(!$res["email"]) {
						$res["email"]=$ilClientIniFile->readVariable('generic_usr_data', 'email');
						$ugm[2]=0;
					}


					$usr->create();

					$usr->assignData($res);

					$usr->saveAsNew();

					if (strtolower($res["roleCtry"]) == "de") {
						$lng = "de";
					} 
					else if (strtolower($res["roleCtry"]) == "cn") {
						$lng = "zh";
					} 
					else {
						$lng = "en";
					}

					$usr->setLanguage($lng);
					$usr->writePrefs();

				}
				if ($flag) {
					fputcsv($file,$ugm,";");
				}

			}
			self::closespxdb();
			fclose($file);



			$sql="UPDATE usr_data SET ".implode(" = NULL , ",self::$kill_in_usr_data)." = NULL ";
			$ilDB->query($sql);

			foreach (self::$kill_in_udf as $tokill) {
				$sql="UPDATE udf_text, udf_definition"
				." SET value = NULL WHERE `udf_text`.`field_id` = `udf_definition`.`field_id`"
				." AND field_name LIKE ".$ilDB->quote($tokill);
				$ilDB->query($sql);
			}
		}
	}
?>