<?php


//	die("FOO");


	require_once("Services/Init/classes/class.ilInitialisation.php");
	//require_once("Services/SPX/import/classes/class.spxImportRolesOU.php");
	//require_once("Services/SPX/import/classes/class.spxImportRolesNat.php");
	//require_once("Services/SPX/import/classes/class.spxImportRolesFctn.php");
	//require_once("Services/SPX/import/classes/class.spxImportOS.php");
	//require_once("Services/SPX/import/classes/class.spxImportRoles.php");
	//require_once("Services/SPX/import/classes/class.spxUsrImport.php");
	//require_once("Services/SPX/import/classes/class.spxUpdateUsrData.php");
	//require_once("Services/SPX/import/classes/class.spxBuildHisto.php");
	//require_once("Services/SPX/import/classes/class.spxCreateBookingStatus.php");
	require_once("Services/SPX/import/classes/class.spxCheckBookingStatus.php");

	ilInitialisation::initILIAS();
	
	//spxUsrImport::UsrImport();

	/*spxImportOS::runOSimport();
	echo "DONE: runOSImport\n";
	
	spxImportRoles::ImportRoles();
	echo "DONE: ImportRoles\n";
	
	spxUpdateUsrData::updateUsrData();
	echo "DONE: updateUsrData\n";

	spxImportRolesFctn::ImportRolesFctn();
	echo "DONE: ImportRolesFctn\n";
	
	spxImportRolesOU::ImportRolesOU();
	echo "DONE: ImportRolesOU\n";
	
	spxImportRolesNAT::ImportRolesNat();
	echo "DONE: ImportRolesNat\n";*/
	
	/*spxBuildHisto::run();
	echo "DONE: spxBuildHisto\n";*/

	/*spxCreateBookingStatus::run();
	echo "DONE: spxCreateBookingStatus\n";*/
	
	spxCheckBookingStatus::run();
	echo "DONE: spxCheckBookingStatus\n";
	
die('<hr><hr>done.');

?>

