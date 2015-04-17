<?php


//	die("FOO");


	require_once("Services/Init/classes/class.ilInitialisation.php");
	require_once("Services/SPX/import/classes/class.spxImportRolesOU.php");
	require_once("Services/SPX/import/classes/class.spxImportRolesNat.php");
	require_once("Services/SPX/import/classes/class.spxImportRolesFctn.php");
	require_once("Services/SPX/import/classes/class.spxImportOS.php");
	require_once("Services/SPX/import/classes/class.spxImportRoles.php");
	//require_once("Services/SPX/import/classes/class.spxUsrImport.php");
	require_once("Services/SPX/import/classes/class.spxUpdateUsrData.php");
	require_once("Services/SPX/import/classes/class.spxBuildHisto.php");

	ilInitialisation::initILIAS();
	
	//spxUsrImport::UsrImport();

	//spxImportOS::runOSimport();
	
	spxImportRoles::ImportRoles();
	
	spxUpdateUsrData::updateUsrData();

	spxImportRolesFctn::ImportRolesFctn();
	
	spxImportRolesOU::ImportRolesOU();
	
	spxImportRolesNAT::ImportRolesNat();
	

	
	//spxBuildHisto::run();
	
die('<hr><hr>done.');

?>

