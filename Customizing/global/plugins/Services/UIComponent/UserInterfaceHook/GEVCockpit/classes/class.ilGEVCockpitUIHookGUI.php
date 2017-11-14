<?php

/* Copyright (c) 2016, Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

require_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");
require_once("./Services/GEV/CourseSearch/classes/class.gevCourseSearch.php");
require_once("./Services/GEV/Utils/classes/class.gevMyTrainingsAdmin.php");
require_once("./Services/GEV/Utils/classes/class.gevSettings.php");
require_once("./Services/GEV/Utils/classes/class.gevObjectUtils.php");
include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/ReportExamBio/classes/class.ilObjReportExamBio.php';
include_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/ReportExamBio/classes/class.ilObjReportExamBioGUI.php';

use \CaT\Plugins\TalentAssessment;

/**
 * Creates a submenu for the Cockpit of the GEV.
 */
class ilGEVCockpitUIHookGUI extends ilUIHookPluginGUI
{
	const ADMIN_TRAININGS_CHECK_INTERVAL = "300";
	const ASSESSMENT_CHECK_INTERVAL = "300";

	protected $within_mediathek = null;

	public function __construct()
	{
		global $ilUser, $lng, $ilCtrl, $ilDB, $ilAccess, $rbacreview, $tree;
		$this->gLng = $lng;
		$this->gUser = $ilUser;
		$this->gCtrl = $ilCtrl;
		$this->gIldb = $ilDB;
		$this->gAccess = $ilAccess;
		$this->gRbacreview = $rbacreview;
		$this->gTree = $tree;
	}

	/**
	 * @inheritdoc
	 */
	public function getHTML($a_comp, $a_part, $a_par = array())
	{
		if ($a_part != "template_get"
			|| 	$a_par["tpl_id"] != "Services/MainMenu/tpl.main_menu.html"
			|| 	(!$this->isCockpit() && !$this->isSearch())
		   ) {
			return parent::getHTML($a_comp, $a_part, $a_par);
		}

		$this->active = $this->getActiveItem();
		$this->items = $this->getItems();

		$current_skin = ilStyleDefinition::getCurrentSkin();

		$this->addCss($current_skin);
		$html = $this->getSubMenuHTML($current_skin);

		return array
			( "mode" => ilUIHookPluginGUI::APPEND
			, "html" => $html
			);
	}

	protected function isCockpit()
	{
		$get = $_GET;
		$base_class = strtolower($get["baseClass"]);
		$cmd_class = strtolower($get["cmdClass"]);
		$cmd = strtolower($get["cmd"]);

		return $this->isBaseClassDesktopGUI($base_class, $cmd_class, $get, $cmd)
				&& $this->cmdClassNotIn($cmd_class)
				&& $this->cmdNotIn($cmd)
			;
	}

	protected function isBaseClassDesktopGUI($base_class, $cmd_class, $get, $cmd)
	{
		return ( $base_class == "gevdesktopgui"
				|| ($cmd_class == "ilobjreportedubiogui"
					&& $get["target_user_id"] == $this->gUser->getId())
				|| ($cmd_class == "ilobjreportexambiogui"
					&& $get["target_user_id"] == $this->gUser->getId())
				|| $base_class == "iltepgui"
				|| ($base_class == "ilrepositorygui" && $this->withinMediathek($get['ref_id']))
			)
		|| (
			$base_class == "ilobjplugindispatchgui" &&
				   (($cmd_class == "ilobjreportstudyprogrammegui" && $cmd == "showcontent")
				|| ($cmd_class == "ilindividualplangui" && ($cmd == "view" || $cmd == "showcontent")))
		);
	}

	protected function withinMediathek($ref_id)
	{
		if($this->within_mediathek === null) {
			$this->within_mediathek = $this->calculateWithinMediathek($ref_id);
		}
		return $this->within_mediathek;
	}

	protected function calculateWithinMediathek($ref_id)
	{
		if($ref_id === null) {
			return false;
		}
		$mediathek_ref_id = gevSettings::getInstance()->getCockpitMediathekRefId();
		return $this->gTree->isGrandChild($mediathek_ref_id,$ref_id) || $ref_id == $mediathek_ref_id;
	}

	protected function cmdClassNotIn($cmd_class)
	{
		$cmd_classes = array("gevcoursesearchgui"
							, "iladminsearchgui"
							, "gevemployeebookingsgui"
							, "gevdecentraltraininggui"
							, "gevdecentraltrainingcoursecreatingbuildingblock2gui"
						);

		return !in_array($cmd_class, $cmd_classes);
	}

	protected function cmdNotIn($cmd)
	{
		$cmds = array("toallassessments");

		return !in_array($cmd, $cmds);
	}

	protected function isSearch()
	{
		return $_GET["baseClass"] == "gevDesktopGUI"
			&& $_GET["cmdClass"] == "gevcoursesearchgui"
			;
	}

	protected function getActiveItem()
	{
		if ($this->isCockpit()) {
			if ($_GET["cmdClass"] == "gevmycoursesgui") {
				return "bookings";
			}
			if ($_GET["cmdClass"] == "ilobjreportedubiogui") {
				return "edubio";
			}
			if ($_GET["cmdClass"] == "ilobjreportexambiogui") {
				return "exambio";
			}
			if ($_GET["cmdClass"] == "gevuserprofilegui") {
				return "profile";
			}
			if (strtolower($_GET["baseClass"]) == "iltepgui") {
				return "tep";
			}
			if ($_GET["cmdClass"] == "gevmytrainingsapgui") {
				return "trainer_ops";
			}
			if ($_GET["cmdClass"] == "gevmytrainingsadmingui") {
				return "training_admin";
			}
			if ($_GET["cmd"] == "toMyAssessments") {
				return "my_assessments";
			}
			if ($_GET["cmdClass"] == "ilobjreportstudyprogrammegui"
			||  $_GET["cmdClass"] == "ilindividualplangui") {
				$ref_id = $_GET["ref_id"];
				return "va_pass_".$ref_id;
			}
			if(strtolower($_GET['baseClass']) == "ilrepositorygui" && $this->withinMediathek($_GET['ref_id'])) {
				return 'mediathek';
			}
		}
		if ($this->isSearch()) {
			$this->target_user_id = $_POST["target_user_id"]
									? $_POST["target_user_id"]
									: ( $_GET["target_user_id"]
										? $_GET["target_user_id"]
										: $this->gUser->getId());
			$crs_search = gevCourseSearch::getInstance($this->target_user_id);
			$tab = $crs_search->getActiveTab();
			$active = "search_$tab";
			if (!in_array($active, array("search_onside", "search_webinar", "search_wbt"))) {
				return "search_all";
			}
			return $active;
		}

		return null;
	}

	protected function getItems()
	{
		if ($this->isCockpit()) {
			return $this->getCockpitItems();
		} elseif ($this->isSearch()) {
			return $this->getSearchItems();
		} else {
			throw new \LogicException("Should not get here...");
		}
	}

	protected function getCockpitItems()
	{
		if (isset($_GET['spRefId']) && isset($_GET['user_id']) && $_GET['user_id'] != $this->gUser->getId()) {
			return array();
		}
		if ($this->gUser->getId() !== 0) {
			$user_utils = gevUserUtils::getInstanceByObj($this->gUser);
		} else {
			$user_utils = null;
		}

		$items = array();

		$items["bookings"]
			= array($this->gLng->txt("gev_bookings"), "ilias.php?baseClass=gevDesktopGUI&cmd=toMyCourses");

		if ($user_utils && ($edu_bio_link = $user_utils->getEduBioLink())) {
			$items["edubio"]
				= array($this->gLng->txt("gev_edu_bio"), $user_utils->getEduBioLink());
		}

		$sp_report_plugin = ilPlugin::getPluginObject(
			IL_COMP_SERVICE,
			"Repository",
			"robj",
			ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", "xsp")
		);

		if ($sp_report_plugin && $sp_report_plugin->isActive()) {
			$sp_report_object_ids = ilObject::_getObjectsByType("xsp");
			$gui = $sp_report_plugin->getGUIClass();

			foreach ($sp_report_object_ids as $obj_id => $obj_values) {
				$object = ilObjectFactory::getInstanceByObjId($obj_id);
				$ref_id = gevObjectUtils::getRefId($obj_id);

				if ($this->gAccess->checkAccessOfUser($this->gUser->getId(), "visible", "", $ref_id) && $object->showInCockpit()) {
					$this->gCtrl->setParameterByClass($gui, "ref_id", $ref_id);
					$link = $this->gCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI", $gui, 'ilindividualplangui'), "showContent");
					$this->gCtrl->setParameterByClass($gui, "ref_id", null);
					$items["va_pass_".$ref_id] = array($object->getTitle(), $link);
				}
			}
		}

		$ass_bio_plugin = ilPlugin::getPluginObject(
			IL_COMP_SERVICE,
			"Repository",
			"robj",
			ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", "xexb")
		);
		if ($ass_bio_plugin && $ass_bio_plugin->active) {
			if ($user_utils && ($ref_id = ilObjReportExamBioGUI::examBiographyReferenceForUsers($this->gIldb))) {
				if ($this->gAccess->checkAccessOfUser($this->gUser->getId(), 'read', '', $ref_id) || $user_utils->isAdmin()) {
					$items["exambio"]
						= array($this->gLng->txt("gev_exam_bio"), ilObjReportExamBioGUI::examBiographyLinkByRefId($ref_id, $this->gCtrl));
				}
			}
		}

		$na_quali_ref_id = gevSettings::getInstance()->getNAQualiCourseRefId();
		if ($na_quali_ref_id !== null && $this->gAccess->checkAccess("visible", "", $na_quali_ref_id)) {
			$link = $this->buildJillInstanceLink($na_quali_ref_id);
			$items['na_qualification'] = array($this->gLng->txt("na_link_label_cockpit"),$link);
		}

		$bol_ref_id = gevSettings::getInstance()->getBOLCourseRefId();
		if ($bol_ref_id !== null && $this->gAccess->checkAccess("visible", "", $bol_ref_id)) {
			$link = $this->buildJillInstanceLink($bol_ref_id);
			$items['bol_qualification'] = array($this->gLng->txt("bol_link_label_cockpit"),$link);
		}

		$items["profile"]
			= array($this->gLng->txt("gev_user_profile"), "ilias.php?baseClass=gevDesktopGUI&cmd=toMyProfile");


		require_once("Services/TEP/classes/class.ilTEPPermissions.php");
		if ($user_utils && ($user_utils->isAdmin() || ilTEPPermissions::getInstance($this->gUser->getId())->isTutor())) {
			$this->gLng->loadLanguageModule("tep");
			$items["tep"]
				= array($this->gLng->txt("tep_personal_calendar_title"), "ilias.php?baseClass=ilTEPGUI");

			$items["trainer_ops"]
				= array($this->gLng->txt("gev_mytrainingsap_title"), "ilias.php?baseClass=gevDesktopGUI&cmd=toMyTrainingsAp");
		}

		// TODO: The next time anybody comes here, this RBAC caching stuff in the session should be
		// abstracted.

		$has_admin_trainings = ilSession::get("gev_has_admin_trainings");
		$last_admin_trainings_calculation = ilSession::get("gev_has_admin_trainings_calculation_ts");

		if ($has_admin_trainings === null
				||   $last_admin_trainings_calculation + self::ADMIN_TRAININGS_CHECK_INTERVAL < time()) {
			$my_training_admin_utils = new gevMyTrainingsAdmin($this->gUser->getId());
			$has_admin_trainings = $my_training_admin_utils->hasAdminCourse(gevSettings::$CRS_MANAGER_ROLES);

			ilSession::set("gev_has_admin_trainings", $has_admin_trainings);
			ilSession::set("gev_has_admin_trainings_calculation_ts", time());
		}

		if ($has_admin_trainings) {
			$items["training_admin"]
				= array($this->gLng->txt("gev_my_trainings_admin"), "ilias.php?baseClass=gevDesktopGUI&cmd=toMyTrainingsAdmin");
		}

		$ta_plugin = ilPlugin::getPluginObject(
			IL_COMP_SERVICE,
			"Repository",
			"robj",
			ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", "xtas")
		);

		if ($ta_plugin->active) {
			$has_assessments = ilSession::get("gev_has_assessments");
			$last_assessments_calculation = ilSession::get("gev_has_assessments_calculation_ts");

			if ($has_assessments === null
					||   $last_assessments_calculation + self::ASSESSMENT_CHECK_INTERVAL < time()) {
				$assessments = $ta_plugin->getObservationsDB()->getAssessmentsData(array());

				foreach ($assessments as $key => $assessment) {
					$role_id = $this->gRbacreview->roleExists(TalentAssessment\ilActions::OBSERVATOR_ROLE_NAME."_".$assessment["obj_id"]);
					$usr_ids = $this->gRbacreview->assignedUsers($role_id);

					if (!in_array($this->gUser->getId(), $usr_ids)) {
						unset($assessments[$key]);
					}
				}

				$has_assessments = count($assessments) > 0;

				ilSession::set("gev_has_assessments", $has_assessments);
				ilSession::set("gev_has_assessments_calculation_ts", time());
			}

			if ($has_assessments) {
				$items["my_assessments"]
					= array($this->gLng->txt("gev_my_assessments"), "ilias.php?baseClass=gevDesktopGUI&cmd=toMyAssessments");
			}
		}
		$mediathek_ref_id = gevSettings::getInstance()->getCockpitMediathekRefId();
		$may_see_mediathek = $this->gAccess->checkAccess('read','',$mediathek_ref_id);

		if($may_see_mediathek) {
			$this->gCtrl->setParameterByClass('ilrepositorygui','ref_id',$mediathek_ref_id);
			$items['mediathek'] = array($this->gLng->txt('gev_mediathek'),$this->gCtrl->getLinkTargetByClass(array('ilrepositorygui','ilrepositorygui'),'render'));
			$this->gCtrl->setParameterByClass('ilrepositorygui','ref_id',null);
		}

		return $items;
	}

	protected function buildJillInstanceLink($ref_id)
	{
		$backlink = basename($_SERVER["REQUEST_URI"]);

		$this->gCtrl->setParameterByClass("ilObjJillGUI", "ref_id", $ref_id);
		$this->gCtrl->setParameterByClass("ilObjJillGUI", "referrer", base64_encode($backlink));
		$link = $this->gCtrl->getLinkTargetByClass(
			array("ilObjPluginDispatchGUI", "ilObjJillGUI"),
			"xView"
		);
		$this->gCtrl->setParameterByClass("ilObjJillGUI", "ref_id", null);
		$this->gCtrl->setParameterByClass("ilObjJillGUI", "referrer", null);
		return $link;
	}

	protected function getSearchItems()
	{
		$items = array
			( "search"
				=> array("Suche")
			);
		$crs_search = gevCourseSearch::getInstance($this->target_user_id);
		$search_tabs = $crs_search->getPossibleTabs();
		$tab_counts = $crs_search->getCourseCounting();
		foreach ($search_tabs as $key => $data) {
			list($name, $link) = $data;
			$items["search_$key"] = array
				( $this->gLng->txt($name)." (".$tab_counts[$key].")"
				, $link
				);
		}
		return $items;
	}

	protected function getSubMenuHTML($current_skin)
	{
		assert('is_string($current_skin)');
		$tpl = $this->getTemplate($current_skin, true, true);
		$count = 1;
		foreach ($this->items as $id => $data) {
			list($label, $link) = $data;
			if ($this->active == $id) {
				$tpl->touchBlock("active");
			}
			$tpl->setCurrentBlock("item");
			$tpl->setVariable("ID", $id);
			$tpl->setVariable("LABEL", $label);
			$tpl->setVariable("LINK", $link);
			$tpl->parseCurrentBlock();
			$count++;
		}

		$tpl->addCss("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/GEVCockpit/templates/jquery.bxslider.css");
		$tpl->setCurrentBlock("js");

		if ($this->getCurrentSlide()) {
			$tpl->setVariable("ACTIVE_SLIDE", "<script>var active_slide = " . $this->getCurrentSlide() . ";</script>");
		}

		$tpl->parseCurrentBlock();
		$this->addJS();
		return $tpl->get();
	}

	protected function addJS()
	{
		require_once("./Services/jQuery/classes/class.iljQueryUtil.php");
		iljQueryUtil::initjQuery();
		global $tpl;
		$tpl->addJavaScript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/GEVCockpit/templates/jquery.bxslider.js");
	}

	protected function addCss($current_skin)
	{
		assert('is_string($current_skin)');
		global $tpl;
		$loc = $this->getStyleSheetLocation($current_skin);
		$tpl->addCss($loc);
	}

	protected function getTemplate($current_skin, $remove_unknown_vars, $remove_empty_blocks)
	{
		assert('is_string($current_skin)');
		$skin_folder = $this->getSkinFolder($current_skin);
		$tpl_file = "tpl.submenu.html";
		$tpl_path = $skin_folder."/Plugins/GEVCockpit/$tpl_file";
		if (is_file($tpl_path)) {
			return new ilTemplate($tpl_path, $remove_unknown_vars, $remove_empty_blocks);
		} else {
			return $this->plugin_object->getTemplate("tpl.submenu.html", $remove_unknown_vars, $remove_empty_blocks);
		}
	}

	protected function getStyleSheetLocation($current_skin)
	{
		assert('is_string($current_skin)');
		$skin_folder = $this->getSkinFolder($current_skin);
		$css_file = "submenu.css";
		$css_path = $skin_folder."/Plugins/GEVCockpit/$css_file";
		if (is_file($css_path)) {
			return $css_path;
		} else {
			return $this->plugin_object->getStyleSheetLocation("submenu.css");
		}
	}

	protected function getSkinFolder($current_skin)
	{
		assert('is_string($current_skin)');
		return "./Customizing/global/skin/$current_skin";
	}

	protected function getCurrentSlide()
	{
		$items = $this->getItems();

		$item_keys = array_keys($items);
		$searched_item_key = array_search($this->active, $item_keys);

		if ($searched_item_key) {
			return ($searched_item_key + 1);
		}

		return null;
	}
}
