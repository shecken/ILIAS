<?php


/**
*Users are assigned to their global national roles.
*@author Denis Klöpfer
*/


	require_once("Services/User/classes/class.ilObjUser.php");
	require_once("Services/AccessControl/classes/class.ilRbacAdmin.php");

	class spxImportRolesNAT {
		private static $spxdb;
		private static $usrToNATRoleHandler;
		
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

		private function getusrToNATRoleHandler () {
			$sql="SELECT login, roleCtry, roleid FROM `iliasImport`, SEEPEXroles where roleCtry=roleName";
			 self::$usrToNATRoleHandler =self::queryspxdb($sql);
		}

		public static function ImportRolesNat() {
			self::connectspxdb();
			self::getusrToNATRoleHandler();

			$RBAC = new ilRbacAdmin(); 
			
			while($usr=mysql_fetch_assoc(self::$usrToNATRoleHandler)) {
				
				$usrid=ilObjUser::_lookUpId($usr["login"]);	

				$RBAC->assignUser($usr["roleid"],$usrid);
			}
			self::closespxdb();
		}
	}
?>