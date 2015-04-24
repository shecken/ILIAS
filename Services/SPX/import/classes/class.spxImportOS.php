<?php

	require_once("Services/Init/classes/class.ilInitialisation.php");
	require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
	require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
	require_once("Services/Object/classes/class.ilObject.php");
/**
* Creates a tree of orgunits from seepexdb.SEEPEXorg. child-ids stored in OUshort, parent-ids in OUshortParent.
* @author: Denis Klöpfer
*/

	class spxImportOS {
		//$root contains the root-ref-id
		//$spxdb is the database-handler
		private static $root;
		private static $spxdb;
		static $PERMISSION_NAMES = array("view_learning_progress_rec");
		//conects to db

		private static function connectspxdb () {
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

		private static function queryspxdb ($query) {
			$foo=mysql_query($query, self::$spxdb);

			return $foo;
		}

		//terminates db connection

		private static function closespxdb() {
			mysql_close(self::$spxdb);
		}

		//includes child OU under parent OU and extends child by its ref-id

		private static function IncludeOUInTree(&$child, $parent) {
			require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");

			if(!$objid = ilObject::_lookupObjIdByImportId($child["OUshort"])) {

				echo '<br>' . $child["OUilias"]." does not exist yet, creating under".$parent["OUshort"];

				$orgu = new ilObjOrgUnit();
				$orgu->setTitle($child["OUilias"]);
				$orgu->create();
				$orgu->createReference();
				$orgu->setImportId($child["OUshort"]);
				$orgu->update();

				$refid = $orgu->getRefId();

				$orgu->putInTree($parent["refid"]);
				$orgu->initDefaultRoles();
	
				

			} else {

				$refid = gevObjectUtils::getRefId($objid);

				echo "<br>".$child["OUshort"]." allready exists, do not create	  ";
			} 


			echo " refid:".$refid.", ";
			self::putRefidInDB($child["OUshort"],$refid);
			$child["refid"]=$refid;

		} 

		//searches db for children to some parent

		private static function findChildren($parent) {
			global $ilDB;
			$sql="SELECT * FROM SEEPEXorg WHERE OUshortParent = ".$ilDB->quote($parent["OUshort"],"text"); 
			return self::queryspxdb($sql);
		}

		//writes the ref-id assigned to a created OU into db

		private static function putRefidInDB($id,$refid) {
			global $ilDB;
			$sql= "UPDATE SEEPEXorg SET refid = ".$ilDB->quote($refid,"text")
				." WHERE OUshort = ".$ilDB->quote($id,"text");
			self::queryspxdb($sql);
		}



		private static function buildOS($parent) {
			
			

			$rec = self::findChildren($parent);
			

			while($child=mysql_fetch_assoc($rec)) {

				self::IncludeOUInTree($child,$parent);

				self::buildOS($child);
			}	

		}

		private static function modifyOperations() {
			global $ilDB;
			require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

			$sql = "SELECT refid FROM SEEPEXorg WHERE OUshortParent =".$ilDB->quote("SEEPEX","text");
			$rec = self::queryspxdb($sql);
			while($res = mysql_fetch_assoc($rec)) {
				gevOrgUnitUtils::grantPermissionsRecursivelyFor($res["refid"], "superior", self::$PERMISSION_NAMES);
				echo "<br> adding permissions recursively at ".$res["refid"];
			}
		}
		//main importing procedure

		public static function runOSimport() {

			self::connectspxdb();
			self::$root=array("OUshort"=>"root","refid"=>ilObjOrgUnit::getRootOrgRefId());

			self::buildOS(self::$root);

			self::modifyOperations();

			self::closespxdb();
		}

	
	}



?>
