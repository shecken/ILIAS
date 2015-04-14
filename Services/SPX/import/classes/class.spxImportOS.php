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

		//conects to db

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

		//performs db queries

		private function queryspxdb ($query) {
			$foo=mysql_query($query, self::$spxdb);

			return $foo;
		}

		//terminates db connection

		private function closespxdb() {
			mysql_close(self::$spxdb);
		}

		//includes child OU under parent OU and extends child by its ref-id

		private function IncludeOUInTree(&$child, $parent) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");

			if(!$objid = ilObject::_lookupObjIdByImportId($child["OUshort"])) {

				$orgu = new ilObjOrgUnit();
				$orgu->setTitle($child["OUilias"]);
				$orgu->create();
				$orgu->createReference();
				$orgu->setImportId($child["OUshort"]);
				$orgu->initDefaultRoles();
				$orgu->update();

				$refid = $orgu->getRefId();

				$orgu->putInTree($parent["refid"]);

				echo $child["OUilias"]." does not exist yet, creating...   ";
			} else {

				$orgu = new ilObjOrgUnit($objid, false);
				//$orgu->initDefaultRoles();
				//$orgu->update();
				$refid = gevObjectUtils::getRefId($objid);
				echo $child["OUshort"]." allready exists, do not create	  ";
			} 

			echo " refid:".$refid.", ";
			self::putRefidInDB($child["OUshort"],$refid);
			$child["refid"]=$refid;

		} 

		//searches db for children to some parent

		private function findChildren($parent) {
			global $ilDB;
			$sql="SELECT * FROM SEEPEXorg WHERE OUshortParent = ".$ilDB->quote($parent["OUshort"],"text"); 
			return self::queryspxdb($sql);
		}

		//writes the ref-id assigned to a created OU into db

		private function putRefidInDB($id,$refid) {
			global $ilDB;
			$sql= "UPDATE SEEPEXorg SET refid = ".$ilDB->quote($refid,"text")
				." WHERE OUshort = ".$ilDB->quote($id,"text");
			self::queryspxdb($sql);
		}




		private function buildOS($parent) {
			$rec = self::findChildren($parent);

			while($child=mysql_fetch_assoc($rec)) {

				self::IncludeOUInTree($child,$parent);

				self::buildOS($child);
			}	

		}

		//main importing procedure

		public static function runOSimport() {

			self::connectspxdb();
			self::$root=array("OUshort"=>"root","refid"=>ilObjOrgUnit::getRootOrgRefId());


			self::buildOS(self::$root);

			self::closespxdb();
			die();
		}

	
	}






?>