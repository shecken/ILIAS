<?php

	require_once("Services/Init/classes/class.ilInitialisation.php");
//	require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
	require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");

	require_once("Services/User/classes/class.ilObjUser.php")

	ilInitialisation::initILIAS();
	$usr = new ilObjUser();

	$host = $ilClientIniFile->readVariable('seepexdb', 'host');
	$user = $ilClientIniFile->readVariable('seepexdb', 'user');
	$pass = $ilClientIniFile->readVariable('seepexdb', 'pass');				
	$name = $ilClientIniFile->readVariable('seepexdb', 'name');
	$spxdb= mysql_connect($host, $user, $pass) 
		or die("Something is wrong: ".mysql_error());
	mysql_select_db($name, $spxdb);
	mysql_set_charset('utf8', $spxdb);

	

	$sql="SELECT * FROM iliasImport";

	$rec=mysql_query($sql);

	while($res=mysql_fetch_assoc($rec)) {

		$res["passwd_type"] = IL_PASSWD_PLAIN;
		$res["passwd"] = "CaT12345++";
		$res["time_limit_unlimited"]=1;

		$usr->create()
		$usr->createReference();
		$usr->assignData($res);
		$ctry=$res["roleCtry"];

		if( strtolower($ctry) == 'de' ) {
			$lng = 'de';
		} else if ( strtolower($ctry) == 'cn' )
			$lng = 'cn';
		} else {
			$lng = 'en';
		}

		$usr->setLanguage($lng);

		$usr->saveAsNew();
	}

?>
