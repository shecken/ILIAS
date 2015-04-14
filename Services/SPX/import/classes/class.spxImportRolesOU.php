<?php
/**
*Users are assigned to their corresponding org unit to the employee- or superior-role according to their status.
*@author Denis Klöpfer
*/


	require_once("Services/User/classes/class.ilObjUser.php");
	require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
	//require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");


	class spxImportRolesOU {
		private static $spxdb;
		private static $usrToOURoleHandler;

		
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

		private function getusrToOURoleHandler() {
			$sql="SELECT refid, login , OU, usrtype FROM `SEEPEXorg`, `iliasImport` WHERE OUshort=OU";
			 self::$usrToOURoleHandler =self::queryspxdb($sql);
		}




		public static function ImportRolesOU() {
			self::connectspxdb();
			self::getusrToOURoleHandler();

			while($usr=mysql_fetch_assoc(self::$usrToOURoleHandler)) {
				$usr2=ilObjUser::_lookUpId($usr["login"]);

				if(! $usr2) {
					continue;	
				}
				print '<br>assigning ' .$usr['login'];
				ob_flush();
        		flush();

				$usr2=array($usr2);

				$OU=new ilObjOrgUnit($usr["refid"]);

				if($usr["usrtype"]=="Manager") {

					$OU->assignUsersToSuperiorRole($usr2);

				} else {
					$OU->assignUsersToEmployeeRole($usr2);

				}
				$OU->update();
			}

			self::closespxdb();
		}
	}
?>