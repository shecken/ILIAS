<?php
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
			self::connectspxdb();
			self::getUsrHandler();

			while($res=mysql_fetch_assoc(self::$usrHandler)) {


				$usrexists=ilObjUser::_lookUpId($res["login"]);

				if ($usrexists && $res["transfer"]=="nein")
				{
					$usr=new ilObjUser($usrexists);
					$usr->delete();
				}

				else if ($usrexists && $res["transfer"]=="ja")
				{

					$usr=new ilObjUser($usrexists);




					if (!$usr->getGender()) {
						if ($res["gender"]=="m" || $res["gender"]=="f") {
							$usr->setGender($res["gender"]);
						} 
						else {
							$usr->setGender("m");
						}

					}
					if (!$usr->getEmail()) {
						if ($res["email"])
						{ 
							$usr->setEmail($res["email"]);
						}
						else {
							$usr->setEmail("Please@fillin.youremail");
						}
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

					/*$usr=new ilObjUser();
					$res["passwd_type"] = IL_PASSWD_PLAIN;
					$res["passwd"] = "XXX";
					$res["time_limit_unlimited"] = 1;
					$res["agree_date"] = ilUtil::now();

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
					*/
				}


			}
			self::closespxdb();
		}
	}
?>