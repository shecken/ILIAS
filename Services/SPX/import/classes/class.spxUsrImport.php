<?php

//	require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
//	require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");

	require_once("Services/User/classes/class.ilObjUser.php");
	require_once("Services/Utilities/classes/class.ilUtil.php");
	class spxUsrImport {

		private static $spxdb;
		private static $UsrDataHandler;

		private function connectspxdb() {
			global $ilClientIniFile;
			$host = $ilClientIniFile->readVariable('seepexdb', 'host');
			$user = $ilClientIniFile->readVariable('seepexdb', 'user');
			$pass = $ilClientIniFile->readVariable('seepexdb', 'pass');				
			$name = $ilClientIniFile->readVariable('seepexdb', 'name');
			self::$spxdb = mysql_connect($host, $user, $pass) 
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

		private function getUsrDataHandler() {
			$sql = "SELECT * FROM iliasImport";
			self::$UsrDataHandler = self::queryspxdb($sql);
		}

		static public function UsrImport() {
			self::connectspxdb();
			self::getUsrDataHandler();
			$usr = new ilObjUser();

			while ($res = mysql_fetch_assoc(self::$UsrDataHandler)) {
				
				$res["passwd_type"] = IL_PASSWD_PLAIN;
				$res["passwd"] = $ilClientIniFile->readVariable('generic_usr_data', 'passwd');
				$res["time_limit_unlimited"] = 1;
				$res["agree_date"] = ilUtil::now();

				$usr->create();
				//$usr->createReference();
				$usr->assignData($res);
				/*$ctry = $res["roleCtry"];

				if (strtolower($ctry) == 'de') {
					$lng = 'de';
				} 
				else if (strtolower($ctry) == 'cn') {
					$lng = 'zh';
				}
				else {
					$lng = 'en';
				}

				$usr->setLanguage($lng);*/

				$usr->saveAsNew();
				$usr->writePrefs();
				$usr->hasAcceptedUserAgreement();
				$usr->update();
			}
			self::closespxdb();
		}
	}	
?>
