<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* ilias.php. main script.
*
* If you want to use this script your base class must be declared
* within modules.xml.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/

include('./maintenance.inc.php');

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

//gev-patch 2559 do not show pre loader on download
ilUtil::setCookie("download_started", null, false, false, false);
//gev-patch end

global $ilCtrl, $ilBench;

$ilCtrl->setTargetScript("ilias.php");
$ilCtrl->callBaseClass();
$ilBench->save();
