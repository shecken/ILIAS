<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Desktop for the Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @author   Martin Studer <ms@studer-raimann.ch>
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/MainMenu/classes/class.ilMainMenuGUI.php");
require_once("Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
require_once("Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitAccess.php");

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/WBD/classes/class.gevWBD.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportDiscovery/class.ilReportDiscovery.php';


class gevMainMenuGUI extends ilMainMenuGUI
{
	const IL_STANDARD_ADMIN = "gev_ilias_admin_menu";
	const GEV_REPORTING_MENU = "gev_reporting_menu";

	const HAS_REPORTING_MENU_RECALCULATION_IN_SECS = 60;

	/**
	 * @var  gevUserUtils
	 */
	protected $user_utils = null;
	/**
	 * @var ilCtrl
	 */
	protected $gCtrl;
		/**
	 * @var lng
	 */
	protected $gLng;
		/**
	 * @var ilAccess
	 */
	protected $gAccess;
	/**
	 * @var ilUser
	 */
	protected $gUser;

	/**
	 * @var ilReportDiscovery
	 */
	protected $report_discovery;

	public function __construct()
	{
		parent::__construct($a_target, $a_use_start_template);

		global $lng, $ilCtrl, $ilAccess, $ilUser, $tpl, $ilPluginAdmin;

		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		$this->gAccess = $ilAccess;
		$this->gUser = $ilUser;
		$this->g_tpl = $tpl;

		$this->report_discovery = new ilReportDiscovery($ilPluginAdmin, $this->gAccess);

		if ($this->gUser->getId() !== 0) {
			$this->user_utils = gevUserUtils::getInstance($this->gUser->getId());
			$this->wbd = gevWBD::getInstance($this->gUser->getId());
		}

		$this->gLng->loadLanguageModule("gev");
	}

	public function executeCommand()
	{
		$cmd = $this->gCtrl->getCmd();
		switch ($cmd) {
			case "getReportingMenuDropDown":
				assert($this->gCtrl->isAsynch());
				echo $this->getReportingMenuDropDown();
				die();
			case "getMenuDropDown":
				echo $this->getMenuDropDown();
				die();
			case "getSubEntries":
				echo $this->getSubMenuDropDown();
				die();
			default:
				throw new Exception("gevMainMenuGUI: Unknown Command '$cmd'.");
		}
	}

	protected function getAdminMenuDropDown()
	{
		require_once("Services/Administration/classes/class.ilAdministrationGUI.php");
		$gui = new ilAdministrationGUI();
		$elements = $gui->getAdminMenuEntries();
		$groups = $elements["groups"];
		$titems = $elements["titems"];

		$main_tpl = new ilTemplate("tpl.gev_main_menu_report_entries.html", true, true, "Services/GEV/Desktop");
		for ($i = 1; $i <= 3; $i++) {
			foreach ($groups[$i] as $group => $entries) {
				if (count($entries) > 0) {
					//Group title. Menu level 2
					$n_tpl = new ilTemplate("tpl.gev_main_menu_report_expand_entry.html", true, true, "Services/GEV/Desktop");
					$n_tpl->touchBlock("fix_submenu_ul");
					$id = $group;
					$class = "expand_entry";
					$link = "#";

					$n_tpl->setVariable("ENTRY_TITLE", $this->gLng->txt("adm_".$group));
					$n_tpl->setVariable("ENTRY_HREF", "#");
					$n_tpl->setVariable("ENTRY_TARGET", "_top");
					$n_tpl->setVariable("ENTRY_ID", $id);
					$n_tpl->setVariable("ENTRY_CLASS", $class);

					//Items of group. Menu level 3
					foreach ($entries as $e) {
						if ($e != "---") {
							$entry_tpl = new ilTemplate("tpl.gev_main_menu_report_entry.html", true, true, "Services/GEV/Desktop");
							$class = "single_entry";

							if ($_GET["admin_mode"] == "settings" && $titems[$e]["ref_id"] == ROOT_FOLDER_ID) {
								$title = $titems[$e]["title"];
								$link = "ilias.php?baseClass=ilAdministrationGUI&amp;ref_id=".$titems[$e]["ref_id"]."&amp;admin_mode=repository";
								$id = "mm_adm_rep";
							} else {
								$title = $titems[$e]["title"];
								$link = "ilias.php?baseClass=ilAdministrationGUI&amp;ref_id=".$titems[$e]["ref_id"]."&amp;cmd=jump";
								$id = "mm_adm_".$titems[$e]["type"];
							}

							$entry_tpl->setVariable("ENTRY_TITLE", $title);
							$entry_tpl->setVariable("ENTRY_HREF", $link);
							$entry_tpl->setVariable("ENTRY_TARGET", "_top");
							$entry_tpl->setVariable("ENTRY_ID", $id);
							$entry_tpl->setVariable("ENTRY_CLASS", $class);

							$n_tpl->setCurrentBlock("fix_submenu");
							$n_tpl->setVariable("ENTRY", $entry_tpl->get());
							$n_tpl->parseCurrentBlock();
						}
					}

					$n_tpl->touchBlock("fix_submenu_ul_end");
					$main_tpl->setCurrentBlock("entry");
					$main_tpl->setVariable("ENTRY", $n_tpl->get());
					$main_tpl->parseCurrentBlock();
				}
			}
		}

		return $main_tpl->get();
	}

	protected function getMenuDropDown()
	{
		$needed = $_POST["needed"];
		switch ($needed) {
			case self::IL_STANDARD_ADMIN:
				require_once("Services/Administration/classes/class.ilAdministrationGUI.php");
				return $this->getAdminMenuDropDown();
				break;
			case self::GEV_REPORTING_MENU:
				return $this->getReportingMenuDropDown();
		}
	}

	protected function getSubMenuDropDown()
	{
		$needed = $_POST["needed"];
		$last_call = ilSession::get('gev_last_report_menu_build_ts_'.$needed);
		$cached = ilSession::get('gev_last_report_menu_build_'.$needed);
		if (time() - (int)$last_call < 300 && $cached) {
			return $cached;
		}
		require_once("Services/Link/classes/class.ilLink.php");

		$tpl = new ilTemplate("tpl.gev_main_menu_report_entries.html", true, true, "Services/GEV/Desktop");
		$tpl->setVariable("MAIN_MENU_EXPAND_AJAX", "ilias.php?baseClass=gevMainMenuGUI&cmd=getSubEntries&cmdMode=asynch");


		$visible_repo_reports = $this->report_discovery->getVisibleReportItemsByType($needed, $this->gUser);
		$visible_repo_reports->sortByTitle();

		foreach ($visible_repo_reports as $visible_report) {
			$type = $visible_report->linkParameter()['type'];
			$ref_id = $visible_report->linkParameter()['ref_id'];
			$title = $visible_report->title();
			$link = ilLink::_getStaticLink($ref_id, $type);
			$class = "single_entry";

			$n_tpl = new ilTemplate("tpl.gev_main_menu_report_entry.html", true, true, "Services/GEV/Desktop");
			$n_tpl->setVariable("ENTRY_TITLE", $title);
			$n_tpl->setVariable("ENTRY_HREF", $link);
			$n_tpl->setVariable("ENTRY_TARGET", "_top");
			$n_tpl->setVariable("ENTRY_ID", $id);
			$n_tpl->setVariable("ENTRY_CLASS", $class);

			$tpl->setCurrentBlock("entry");
			$tpl->setVariable("ENTRY", $n_tpl->get());
			$tpl->parseCurrentBlock();
		}
		$out = $tpl->get();
		ilSession::set('gev_last_report_menu_build_'.$needed, $out);
		ilSession::set('gev_last_report_menu_build_ts_'.$needed, time());
		return $out;
	}

	public function renderMainMenuListEntries($a_tpl, $a_call_get = true)
	{
		$this->g_tpl->addCss("src/bootstrap/bootstrap.min.css");
		$this->g_tpl->addJavascript("src/bootstrap/bootstrap.min.js");
		$this->g_tpl->addJavascript("Services/CaTUIComponents/js/change_menu_content.js");

		// No Menu during registration or on makler page
		$basename = basename($_SERVER["PHP_SELF"]);
		if ($basename == "gev_registration.php"
			|| $basename == "gev_logindata.php"
			|| $basename == "makler.php" ) {
			return "";
		}

		if ($this->gUser->getId() == 0) {
			return "";
		}

		// auto user admin plugin
		$this->auto_user_admin_plugin = ilPlugin::getPluginObject(
			IL_COMP_SERVICE,
			"Cron",
			"crnhk",
			ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Cron", "crnhk", "autouseradmin")
		);

		// switch to patch template
		$a_tpl = new ilTemplate("tpl.gev_main_menu_entries.html", true, true, "Services/GEV/Desktop");

		//Set AJAX Link
		$a_tpl->setVariable("MAIN_MENU_AJAX", "ilias.php?baseClass=gevMainMenuGUI&cmd=getMenuDropDown&cmdMode=asynch");

		// known ref_ids
		$repository = 1;
		$user_mgmt = 7;
		$org_mgmt = 56;
		$mail_mgmt = 12;
		$competence_mgmt = 41;
		$general_settings = 9;

		//permissions
		$manage_courses = $this->gAccess->checkAccess("write", "", $repository);
		$search_courses = ($manage_courses && !$this->user_utils->hasRoleIn(array("Admin-TA")))
							|| ($this->user_utils && $this->user_utils->hasRoleIn(array("Admin-Ansicht", "Admin-Orga")));
		$manage_users = $this->gAccess->checkAccess("visible", "", $user_mgmt);
		$manage_org_units = $this->gAccess->checkAccess("visible", "", $org_mgmt);
		$manage_mails = $this->gAccess->checkAccess("visible", "", $mail_mgmt);
		$manage_competences = $this->gAccess->checkAccess("visible", "", $competence_mgmt);
		$has_super_admin_menu = $this->gAccess->checkAccess("write", "", $general_settings);

		$manage_auto_user_admin_plugin = false;
		if ($this->auto_user_admin_plugin && $this->auto_user_admin_plugin->active) {
			$manage_auto_user_admin_plugin = $has_super_admin_menu;
		}

		$has_managment_menu = ($manage_courses || $search_courses || $manage_users || $manage_org_units
							     || $manage_mails || $manage_competences || $manage_auto_user_admin_plugin)
							&& ($this->user_utils && !$this->user_utils->hasRoleIn(array("OD/BD", "UA", "FK", "DBV UVG", "84er", "OD", "FD", "BD")))
							;

		require_once("Services/TEP/classes/class.ilTEPPermissions.php");

		$employee_booking = ($this->user_utils && $this->user_utils->canViewEmployeeBookings());
		require_once("Services/GEV/Utils/classes/class.gevHAUtils.php");
		/**
		 * Disabled because of new role mapping in GOA 3.0
		 * BA 84 and HA 84 merged in role 84er
		 * Old BA 84 could not open ha units
		 */
		//$can_create_ha_unit = ($this->user_utils && ($this->user_utils->hasRoleIn(array("HA 84")) && !gevHAUtils::getInstance()->hasHAUnit($this->user_utils->getId())));
		$can_create_ha_unit = false;
		$local_user_admin = ($this->user_utils && $this->user_utils->isSuperior()); //Local User Administration Permission

		$has_others_menu = $employee_booking || $can_create_ha_unit;
		$could_do_wbd_registration = $this->wbd && $this->wbd->hasWBDRelevantRole() && !$this->wbd->getWBDBWVId() && ($this->wbd->getNextWBDAction() == gevWBD::USR_WBD_NEXT_ACTION_NOTHING);

		$manage_course_block_units = ($this->user_utils && !$this->user_utils->notEditBuildingBlocks());

		//Für den Anfang sollen das nur Administratoren sehen
		$is_training_manager = ($this->user_utils && $this->user_utils->isTrainingManagerOnAnyCourse());

		$view_all_assessments = ($this->user_utils && $this->user_utils->hasRoleIn(array("Administrator", "Admin-TA")));

		//get all OrgUnits of superior
		$arr_org_units_of_superior = $this->user_utils ? $this->user_utils->getOrgUnitsWhereUserIsDirectSuperior() : array();
		$arr_local_user_admin_links = array();
		if ($arr_org_units_of_superior) {
			foreach ($arr_org_units_of_superior as $arr_org_unit_of_superior) {
				if (ilObjOrgUnitAccess::_checkAccessAdministrateUsers($arr_org_unit_of_superior['ref_id'])) {
					$this->gCtrl->setParameterByClass("ilLocalUserGUI", "ref_id", $arr_org_unit_of_superior['ref_id']);
					$arr_local_user_admin_links[$arr_org_unit_of_superior['ref_id']]['title'] = ilObject::_lookupTitle($arr_org_unit_of_superior['obj_id']);
					$arr_local_user_admin_links[$arr_org_unit_of_superior['ref_id']]['url'] = $this->gCtrl->getLinkTargetByClass(array("ilAdministrationGUI","ilObjOrgUnitGUI","ilLocalUserGUI"), "index");
				}
			}
		}

		$main_menue_permissions = array("manage_courses"=>$manage_courses
									,"search_courses"=>$search_courses
									,"manage_users"=>$manage_users
									,"manage_org_units"=>$manage_org_units
									,"manage_mails"=>$manage_mails
									,"manage_auto_user_admin_plugin"=>$manage_auto_user_admin_plugin
									,"manage_course_block_units"=>$manage_course_block_units);

		$menu = array(
			//single entry?
			//render entry?
			//content
			//link title
			  "gev_search_menu" => array(true, true, "ilias.php?baseClass=gevDesktopGUI&cmd=toCourseSearch",$this->gLng->txt("gev_search_menu"), $this->gLng->txt("gev_search_menu"))
			, "gev_me_menu" => array(true, true, "ilias.php?baseClass=gevDesktopGUI&cmd=toMyCourses", $this->gLng->txt("gev_me_menu"))
			, "gev_others_menu" => array(false, $has_others_menu, array(
				  "gev_employee_booking" => array($employee_booking, "ilias.php?baseClass=gevDesktopGUI&cmd=toEmployeeBookings",$this->gLng->txt("gev_employee_booking"))
				, "gev_create_org_unit" => array($can_create_ha_unit, "ilias.php?baseClass=gevDesktopGUI&cmd=createHAUnit", $this->gLng->txt("gev_create_ha_org_unit"))
				, "gev_all_assessments" => array($view_all_assessments, "ilias.php?baseClass=gevDesktopGUI&cmd=toAllAssessments", $this->gLng->txt("gev_all_assessments"))
				), $this->gLng->txt("gev_others_menu"))
			, self::GEV_REPORTING_MENU => array(false, $this->hasReportingMenu(), null)

			, "gev_admin_menu" => array(false, $has_managment_menu, $this->_getAdminMainMenuEntries($main_menue_permissions), $this->gLng->txt("gev_admin_menu"))
			, self::IL_STANDARD_ADMIN => array(false, $has_super_admin_menu, null)
			);

		//Enhance Menu with Local Useradmin Roles
		if (count($arr_local_user_admin_links) > 0) {
			foreach ($arr_local_user_admin_links as $key => $arr_local_user_admin_link) {
				$menu["gev_others_menu"][2]["gev_my_local_user_admin_".$key] = array(
					$local_user_admin,
					$arr_local_user_admin_link['url'],
					sprintf($this->gLng->txt("gev_my_local_user_admin"), $arr_local_user_admin_link['title'])
					);
			}
		}

		$count = 1;
		foreach ($menu as $id => $entry) {
			if (! $entry[1]) {
				continue;
			}

			if ($entry[0]) {
				$this->_renderSingleEntry($a_tpl, $id, $entry, $count);
			} else {
				$this->_renderDropDownEntry($a_tpl, $id, $entry, $count);
			}
			$count++;
		}

		// Some ILIAS idiosyncracy copied from ilMainMenuGUI.
		if ($a_call_get) {
			return $a_tpl->get();
		}

		return "";
	}

	protected function _renderSingleEntry($a_tpl, $a_id, $a_entry, $count)
	{
		$a_tpl->setCurrentBlock("single_entry");

		$a_tpl->setVariable("ENTRY_ID", 'id="'.$a_id.'"');
		$a_tpl->setVariable("NUM", $count);
		$this->_setActiveClass($a_tpl, $a_id);
		$a_tpl->setVariable("ENTRY_TARGET", $a_entry[2]);
		$a_tpl->setVariable("ENTRY_TITLE", $a_entry[3]);

		$a_tpl->parseCurrentBlock();
	}

	protected function _renderDropDownEntry($a_tpl, $a_id, $a_entry, $count)
	{
		if ($a_id == self::IL_STANDARD_ADMIN) {
			$this->_renderAdminMenu($a_tpl, $count);
		} elseif ($a_id == self::GEV_REPORTING_MENU) {
			$this->_renderReportingMenu($a_tpl, $count);
		} else {
			$tpl = new ilTemplate("tpl.gev_main_menu_entry.html", true, true, "Services/GEV/Desktop");
			$tpl->setVariable("PARENT_ID", 'id="'.$a_id.'"');
			$tpl->setVariable("TITLE", $a_entry[3]);
			$tpl->setVariable("NUM", $count);
			$this->_setActiveClass($tpl, $a_id);

			foreach ($a_entry[2] as $id => $entry) {
				if ($entry[0]) {
					$tpl->setCurrentBlock("drop_entry");
					$tpl->setVariable("ENTRY_TITLE", $entry[2]);
					$tpl->setVariable("ENTRY_HREF", $entry[1]);
					$tpl->setVariable("ENTRY_TARGET", "_top");
					$tpl->parseCurrentBlock();
				}
			}

			$a_tpl->setCurrentBlock("multi_entry");
			$a_tpl->setVariable("CONTENT", $tpl->get());
			$a_tpl->parseCurrentBlock();
		}
	}

	protected function _renderAdminMenu($a_tpl, $count)
	{
		$tpl = new ilTemplate("tpl.gev_main_menu_entry.html", true, true, "Services/GEV/Desktop");
		$tpl->setVariable("PARENT_ID", 'id="expand_'.self::IL_STANDARD_ADMIN.'"');
		$tpl->setVariable("TITLE", $this->gLng->txt(self::IL_STANDARD_ADMIN));
		$tpl->setVariable("NUM", $count);
		$tpl->touchBlock("drop_placeholder");
		$this->_setActiveClass($tpl, self::IL_STANDARD_ADMIN);

		$a_tpl->setCurrentBlock("multi_entry");
		$a_tpl->setVariable("CONTENT", $tpl->get());
		$a_tpl->parseCurrentBlock();
	}

	protected function _renderReportingMenu($a_tpl, $count)
	{
		$tpl = new ilTemplate("tpl.gev_main_menu_entry.html", true, true, "Services/GEV/Desktop");
		$tpl->setVariable("PARENT_ID", 'id="expand_'.self::GEV_REPORTING_MENU.'"');
		$tpl->setVariable("TITLE", $this->gLng->txt(self::GEV_REPORTING_MENU));
		$tpl->setVariable("NUM", $count);
		$tpl->touchBlock("drop_placeholder");
		$this->_setActiveClass($tpl, self::GEV_REPORTING_MENU);

		$a_tpl->setCurrentBlock("multi_entry");
		$a_tpl->setVariable("CONTENT", $tpl->get());
		$a_tpl->parseCurrentBlock();
	}

	protected function _setActiveClass($a_tpl, $a_title)
	{
		if ($this->active == $a_title) {
			$a_tpl->setVariable("MM_CLASS", "MMActive");
		} else {
			$a_tpl->setVariable("MM_CLASS", "MMInactive");
		}
	}

	protected function _getAdminMainMenuEntries($main_menue_permissions)
	{
		$ret = array(
				  "gev_course_mgmt" => array($main_menue_permissions["manage_courses"], "goto.php?target=root_1",$this->gLng->txt("gev_course_mgmt"))
				, "gev_course_mgmt_search" => array($main_menue_permissions["search_courses"], "ilias.php?baseClass=gevDesktopGUI&cmd=toAdmCourseSearch",$this->gLng->txt("gev_course_search_adm"))
				, "gev_user_mgmt" => array($main_menue_permissions["manage_users"], "ilias.php?baseClass=ilAdministrationGUI&ref_id=7&cmd=jump",$this->gLng->txt("gev_user_mgmt"))
				, "gev_org_mgmt" => array($main_menue_permissions["manage_org_units"], "ilias.php?baseClass=ilAdministrationGUI&ref_id=56&cmd=jump",$this->gLng->txt("gev_org_mgmt"))
				, "gev_mail_mgmt" => array($main_menue_permissions["manage_mails"], "ilias.php?baseClass=ilAdministrationGUI&ref_id=12&cmd=jump",$this->gLng->txt("gev_mail_mgmt")));

		if ($main_menue_permissions["manage_auto_user_admin_plugin"]) {
			$plugin_config_gui = ilPlugin::getConfigureClassName($this->auto_user_admin_plugin->getPluginName());

			$this->gCtrl->setParameterByClass($plugin_config_gui, "ctype", $this->auto_user_admin_plugin->getComponentType());
			$this->gCtrl->setParameterByClass($plugin_config_gui, "cname", $this->auto_user_admin_plugin->getComponentName());
			$this->gCtrl->setParameterByClass($plugin_config_gui, "slot_id", $this->auto_user_admin_plugin->getSlotId());
			$this->gCtrl->setParameterByClass($plugin_config_gui, "pname", $this->auto_user_admin_plugin->getPluginName());
			$this->gCtrl->setParameterByClass($plugin_config_gui, "ref_id", 31);

			$link = $this->gCtrl->getLinkTargetByClass(array("ilAdministrationGUI", "ilobjcomponentsettingsgui", $plugin_config_gui), "configure");

			$ret["gev_aua_mgmt"] = array($main_menue_permissions["manage_auto_user_admin_plugin"], $link, $this->auto_user_admin_plugin->txt("manage_executions"));
		}

		$bb_pool = gevUserUtils::getBuildingBlockPoolsTitleUserHasPermissionsTo($this->gUser->getId(), array(gevSettings::USE_BUILDING_BLOCK, "visible"));
		foreach ($bb_pool as $key => $value) {
			$this->gCtrl->setParameterByClass("ilobjbuildingblockpoolgui", "ref_id", gevObjectUtils::getRefId($key));
			$link = $this->gCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI","ilobjbuildingblockpoolgui"), "showContent");
			$ret[$value] = array($main_menue_permissions["manage_course_block_units"], $link, $value);
			$this->gCtrl->clearParametersByClass("ilobjbuildingblockpoolgui");
		}

		return $ret;
	}

	protected function getReportingMenuDropDown()
	{
		$last_call = ilSession::get('gev_last_report_menu_build_ts');
		$cached = ilSession::get('gev_last_report_menu_build');
		if (time() - (int)$last_call < 300 && $cached) {
			return $cached;
		}
		require_once("Services/Link/classes/class.ilLink.php");
		$entries = [];

		$tpl = new ilTemplate("tpl.gev_main_menu_report_entries.html", true, true, "Services/GEV/Desktop");
		$tpl->setVariable("MAIN_MENU_EXPAND_AJAX", "ilias.php?baseClass=gevMainMenuGUI&cmd=getSubEntries&cmdMode=asynch");

		$visible_repo_reports = $this->report_discovery->getVisibleReportItemsForUser($this->gUser);
		$visible_repo_reports->sortByTitle();

		foreach ($visible_repo_reports as $visible_report) {
			$type = $visible_report->linkParameter()['type'];
			$ref_id = $visible_report->linkParameter()['ref_id'];
			$title = $visible_report->title();

			if ($visible_report instanceof \CaT\Plugins\ReportMaster\ReportDiscovery\Report) {
				$n_tpl = new ilTemplate("tpl.gev_main_menu_report_entry.html", true, true, "Services/GEV/Desktop");
				$id = $type;
				$class = "single_entry";
				$link = ilLink::_getStaticLink($ref_id, $type);
			} elseif ($visible_report instanceof \CaT\Plugins\ReportMaster\ReportDiscovery\Group) {
				$n_tpl = new ilTemplate("tpl.gev_main_menu_report_expand_entry.html", true, true, "Services/GEV/Desktop");
				$n_tpl->touchBlock("ajax_submenu");
				$id = "sub_".$type;
				$class = "expand_entry";
				$link = "#";
			}

			if ($n_tpl) {
				$n_tpl->setVariable("ENTRY_TITLE", $title);
				$n_tpl->setVariable("ENTRY_HREF", $link);
				$n_tpl->setVariable("ENTRY_TARGET", "_top");
				$n_tpl->setVariable("ENTRY_ID", $id);
				$n_tpl->setVariable("ENTRY_CLASS", $class);

				$tpl->setCurrentBlock("entry");
				$tpl->setVariable("ENTRY", $n_tpl->get());
				$tpl->parseCurrentBlock();
			}
		}

		$out = $tpl->get();
		ilSession::set('gev_last_report_menu_build', $out);
		ilSession::set('gev_last_report_menu_build_ts', time());
		return $out;
	}

	// Stores the info whether a user has a reporting menu in the session of the user to
	// only calculate it once. Will reuse that value on later calls.
	protected function hasReportingMenu()
	{
		$has_reporting_menu = ilSession::get("gev_has_reporting_menu");
		$last_permission_calculation = ilSession::get("gev_has_reporting_menu_calculation_ts");
		if ($has_reporting_menu === null
		||   $last_permission_calculation + self::HAS_REPORTING_MENU_RECALCULATION_IN_SECS < time()) {
			$visible_repo_reports = $this->report_discovery->getVisibleReportsObjectData($this->gUser);

			$has_reporting_menu = (count($visible_repo_reports) > 0);
			ilSession::set("gev_has_reporting_menu", $has_reporting_menu);
			ilSession::set("gev_has_reporting_menu_calculation_ts", time());
		}

		return $has_reporting_menu;
	}
}
