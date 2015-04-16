<?php

	require_once("Services/Init/classes/class.ilInitialisation.php");
//	require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");

	require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
	
	class spxImportRoles {

		private static $spxdb;

	//connect to db

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

	//performs db queries

		private function queryspxdb($query) {
			return mysql_query($query, self::$spxdb);
		}

	//terminates db connection

		private function closespxdb() {
			mysql_close(self::$spxdb);
		}


		private function getRolesHandler() {
			global $ilDB;
			$sql = "SELECT * FROM SEEPEXroles WHERE scope = ".$ilDB->quote("global","text");
			return self::queryspxdb($sql);
		}
	
		private function createRoles($rolehandler) {
			
			require_once("./Services/AccessControl/classes/class.ilRbacAdmin.php");

			global $ilDB;
			$a_role = gevRoleUtils::getInstance();
			$present_global_roles = $a_role->getGlobalRoles();

			$global_roles_to_keep = array();

			echo ROLE_FOLDER_ID;

			$RBACadmin = new ilRbacAdmin();
			
			while($res = mysql_fetch_assoc($rolehandler)) {

				$roleId = $a_role->getRoleIdByName($res["roleName"]);
				if(!$roleId) {

					$a_role->createGlobalRole($res["roleName"],"");
					
					$roleId = $a_role->getRoleIdByName($res["roleName"]);

					echo "Creating role ".$res["roleName"]." roleid ".$roleId;

				} else {

					
					echo " Role ".$res["roleName"]." allready exists! roleid: ".$roleId;

					if($res["roleName"] != "Administrator" && $res["roleName"] != "Anonymous") {
						$RBACadmin->deassignUsers($roleId);
					} else {
						echo "Keeping users in role ".$res["roleName"]." role id ".$roleId."!!	";
					}


				}


				$sql="UPDATE SEEPEXroles SET roleid = ".$ilDB->quote($roleId,"text")
					." WHERE roleName = ".$ilDB->quote($res["roleName"],"text");

				self::queryspxdb($sql);

				$global_roles_to_keep[$roleId] = $res["roleName"];

			}

			foreach($present_global_roles as $p_role_id => $p_role_name) {
				if( !isset($global_roles_to_keep[$p_role_id])) {
					$RBACadmin->deleteRole($p_role_id,ROLE_FOLDER_ID);
					echo "Deleting role ".$p_role_name." role id ".$p_role_id."!!	";
				}
			}

		}

		public static function ImportRoles() {

			self::connectspxdb();			
			self::createRoles(self::getRolesHandler());
			self::closespxdb();
		}
	}
?>