<?php
/**
*Users are assigned to their global functional roles.
*@author Denis Klöpfer
*/
	require_once("Services/User/classes/class.ilObjUser.php");
	require_once("Services/AccessControl/classes/class.ilRbacAdmin.php");

	class spxImportRolesFctn {
		private static $spxdb;
		private static $usrToFctnRoleHandler;
		
		private function connectspxdb () {
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

		private function queryspxdb ($query) {
			return mysql_query($query, self::$spxdb);
		}

		private function closespxdb () {
			mysql_close(self::$spxdb);
		}

		private function getusrToFctnRoleHandler () {
			$sql = "SELECT login, roleid FROM iliasImport,SEEPEXroles WHERE roleName = roleFctn";
			 self::$usrToFctnRoleHandler =self::queryspxdb($sql);
		}

		public static function ImportRolesFctn() {
			self::connectspxdb();
			self::getusrToFctnRoleHandler();

			$RBAC = new ilRbacAdmin(); 

			while($usr = mysql_fetch_assoc(self::$usrToFctnRoleHandler)) {

				$usr["usr_id"] = ilObjUser::_lookUpId($usr["login"]);
				if(!$rbacreview->isAssigned($usr["usr_id"],$usr["roleid"])) {		
					$RBAC->assignUser($usr["roleid"],$usr["usr_id"]);
				}
			}
			self::closespxdb();
		}
	}
?>