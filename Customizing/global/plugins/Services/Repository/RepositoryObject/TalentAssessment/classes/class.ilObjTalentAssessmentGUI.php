<?php
use CaT\Plugins\TalentAssessment;
use CaT\Plugins\TalentAssessment\Observator as Observator;

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__."/class.ilTalentAssessmentSettingsGUI.php");
require_once(__DIR__."/class.ilTalentAssessmentObservatorGUI.php");
require_once(__DIR__."/class.ilTalentAssessmentObservationsGUI.php");
/**
 * User Interface class for career goal repository object.
 *
 * @ilCtrl_isCalledBy ilObjTalentAssessmentGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjTalentAssessmentGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjTalentAssessmentGUI: ilTalentAssessmentSettingsGUI, ilTalentAssessmentObservatorGUI, ilRepositorySearchGUI
 * @ilCtrl_Calls ilObjTalentAssessmentGUI: ilTalentAssessmentObservationsGUI
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObjTalentAssessmentGUI extends ilObjectPluginGUI
{
	use TalentAssessment\Settings\ilFormHelper;

	const CMD_SHOWCONTENT = "showContent";
	const CMD_SUMMARY = "showSummary";
	const CMD_AUTOCOMPLETE = "userfieldAutocomplete";

	const TAB_SETTINGS = "tab_settings";
	const TAB_OBSERVATIONS = "tab_observations";
	const TAB_OBSERVATOR = "tab_observator";

	/**
	 * Initialisation
	 */
	protected function afterConstructor()
	{
		global $ilAccess, $ilTabs, $ilCtrl, $ilToolbar;

		$this->gAccess = $ilAccess;
		$this->gTabs = $ilTabs;
		$this->gCtrl = $ilCtrl;

		$this->tpl->addJavaScript('./Services/Form/js/date_duration.js');
	}

	/**
	 * Get type.
	 */
	final public function getType()
	{
		return "xtas";
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	public function performCommand($cmd)
	{
		$next_class = $this->gCtrl->getNextClass($this);

		switch ($next_class) {
			case "ilrepositorysearchgui":
				include_once "./Services/Search/classes/class.ilRepositorySearchGUI.php";
				$rep_search = new ilRepositorySearchGUI();
				$obj = new ilTalentAssessmentObservatorGUI($this, $this->object->getActions(), $this->plugin->txtClosure(), $this->object->getId());
				$rep_search->setCallback(
					$obj,
					"addObservator",
					$status
				);
				$rep_search->disableRoleSearch(true);

				$this->gCtrl->setReturn($this, "showObservator");
				$this->gCtrl->forwardCommand($rep_search);
				break;
			default:
				switch ($cmd) {
					case ilTalentAssessmentSettingsGUI::CMD_SHOW:
					case ilTalentAssessmentSettingsGUI::CMD_SAVE:
					case ilTalentAssessmentSettingsGUI::CMD_EDIT:
						$this->forwardSettings();
						break;
					case ilTalentAssessmentObservatorGUI::CMD_SHOW:
						$this->showObservator();
						break;
					case ilTalentAssessmentObservatorGUI::CMD_ADD:
					case ilTalentAssessmentObservatorGUI::CMD_DELETE:
					case ilTalentAssessmentObservatorGUI::CMD_DELETE_SELECTED:
					case ilTalentAssessmentObservatorGUI::CMD_CONFIRMED_DELETE:
						$this->forwardObservator();
						break;
					case self::CMD_SHOWCONTENT:
						$this->showContent();
						break;
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_LIST:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_OVERVIEW:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_CUMULATIVE:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_DIAGRAMM:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_REPORT:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATION_START:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATION_SAVE_VALUES:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATION_SAVE_REPORT:
					case ilTalentAssessmentObservationsGUI::CMD_OBSERVATION_PREVIEW_REPORT:
					case ilTalentAssessmentObservationsGUI::CMD_FINISH_TA:
						$this->forwardObservations();
						break;
					case self::CMD_AUTOCOMPLETE:
						$this->$cmd();
						break;
				}
		}
	}

	/**
	 * After object has been created -> jump to this command
	 */
	public function getAfterCreationCmd()
	{
		return "editProperties";
	}

	/**
	 * Get standard command
	 */
	public function getStandardCmd()
	{
		return "showContent";
	}

	public function initCreateForm($a_new_type)
	{
		$form = parent::initCreateForm($a_new_type);

		$db = $this->plugin->getSettingsDB();
		$career_goal_options = $db->getCareerGoalsOptions();
		$venue_options = $db->getVenueOptions();
		$autocomplete_link = $this->gCtrl->getLinkTarget($this, self::CMD_AUTOCOMPLETE, "", true);
		$org_unit_options = $db->getOrgUnitOptions();
		$this->addSettingsFormItems($form, $career_goal_options, $venue_options, $org_unit_options, $autocomplete_link);
		$form->getItemByPostVar(\CaT\Plugins\TalentAssessment\ilActions::F_REPORT_TITLE)->setValue($this->plugin->txt('report_title_default'));
		return $form;
	}

	public function afterSave(\ilObject $newObj)
	{
		$post = $_POST;
		$db = $this->plugin->getSettingsDB();
		$settings = $db->create((int)$newObj->getId(), \CaT\Plugins\TalentAssessment\Settings\TalentAssessment::IN_PROGRESS, 0, "text", "text", "text", "text", new \ilDateTime(date("Y-m-d H:i:s"), IL_CAL_DATETIME), new \ilDateTime(date("Y-m-d H:i:s"), IL_CAL_DATETIME), 0, 0, false, 0.0, 0.0, 0.0, "", "", "", "");
		$newObj->setSettings($settings);
		$actions = $newObj->getActions();
		$actions->update($post);

		$actions->createLocalRole($newObj);

		parent::afterSave($newObj);
	}

	/**
	 * Set tabs
	 */
	protected function setTabs()
	{
		$this->addInfoTab();

		$view_observations = $this->gAccess->checkAccess("view_observations", "", $this->object->getRefId());
		$edit_observations = $this->gAccess->checkAccess("edit_observation", "", $this->object->getRefId());
		$finish_ta = $this->gAccess->checkAccess("ta_manager", "", $this->object->getRefId());
		if ($view_observations || $edit_observations || $finish_ta) {
			if (!$this->object->getActions()->observationStarted($this->object->getId())) {
				$this->gTabs->addTab(self::TAB_OBSERVATIONS, $this->txt("observations"), $this->gCtrl->getLinkTarget($this, ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS));
			} else {
				$this->gTabs->addTab(self::TAB_OBSERVATIONS, $this->txt("observations"), $this->gCtrl->getLinkTarget($this, ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_LIST));
			}
		}

		if ($this->gAccess->checkAccess("edit_observator", "", $this->object->getRefId())) {
			$this->gTabs->addTab(self::TAB_OBSERVATOR, $this->txt("observator"), $this->gCtrl->getLinkTarget($this, ilTalentAssessmentObservatorGUI::CMD_SHOW));
		}

		if ($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
			$this->gTabs->addTab(self::TAB_SETTINGS, $this->txt("properties"), $this->gCtrl->getLinkTarget($this, ilTalentAssessmentSettingsGUI::CMD_EDIT));
		}

		$this->addPermissionTab();
	}

	protected function forwardSettings()
	{
		if (!$this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->gCtrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->gTabs->setTabActive(self::TAB_SETTINGS);
			$actions = $this->object->getActions();
			$gui = new ilTalentAssessmentSettingsGUI($actions, $this->plugin->txtClosure(), $this->object->getId(), $this->object->getSettings()->getPotential());
			$this->gCtrl->forwardCommand($gui);
		}
	}

	protected function showContent()
	{
		if ($this->gAccess->checkAccess("read", "", $this->object->getRefId())) {
			$this->gCtrl->redirectByClass("ilinfoscreengui", "showSummary");
		} elseif ($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
			$_GET["cmd"] = ilTalentAssessmentSettingsGUI::CMD_SHOW;
			$this->forwardSettings();
		} else {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->gCtrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		}
	}

	protected function forwardObservations()
	{
		$view_observations = $this->gAccess->checkAccess("view_observations", "", $this->object->getRefId());
		$edit_observations = $this->gAccess->checkAccess("edit_observation", "", $this->object->getRefId());
		$ta_manager = $this->gAccess->checkAccess("ta_manager", "", $this->object->getRefId());

		if (!($view_observations || $edit_observations || $ta_manager)) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->gCtrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->gTabs->setTabActive(self::TAB_OBSERVATIONS);
			$actions = $this->object->getActions();
			$gui = new ilTalentAssessmentObservationsGUI($this, $actions, $this->plugin->txtClosure(), $this->object->getSettings(), $this->object->getId());
			$this->gCtrl->forwardCommand($gui);
		}
	}

	protected function showObservator()
	{
		$this->renderUserSearch();
		$this->forwardObservator();
	}

	protected function renderUserSearch()
	{
		include_once "./Services/Search/classes/class.ilRepositorySearchGUI.php";
			ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$this->gToolbar,
				array(
					"auto_complete_name"	=> $this->txt("user"),
					"user_type"				=> $types,
					"submit_name"			=> $this->txt("add"),
					"add_search"			=> false
				)
			);
	}

	protected function forwardObservator()
	{
		if (!$this->gAccess->checkAccess("edit_observator", "", $this->object->getRefId())) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->gCtrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->gTabs->setTabActive(self::TAB_OBSERVATOR);
			$actions = $this->object->getActions();
			$gui = new ilTalentAssessmentObservatorGUI($this, $actions, $this->plugin->txtClosure(), $this->object->getId());
			$this->gCtrl->forwardCommand($gui);
		}
	}

	public function addInfoItems($info)
	{
		$settings = $this->object->getSettings();
		$actions = $this->object->getActions();
		$career_goal_obj = \ilObjectFactory::getInstanceByObjId($settings->getCareerGoalId());
		$observator = $actions->getAssignedUser($this->object->getId());
		$obsv_names = array_map(function ($obsv) {
			return $obsv["firstname"]." ".$obsv["lastname"];
		}, $observator);

		$info->addSection($this->txt('ta_info'));
		$info->addProperty($this->txt('title'), $this->object->getTitle());
		$info->addProperty($this->txt('description'), $this->object->getDescription());
		$info->addProperty($this->txt('state'), $this->txt($actions->potentialText()));


		$info->addProperty($this->txt('career_goal'), $career_goal_obj->getTitle());
		$info->addProperty($this->txt('venue'), $actions->getVenueName($settings->getVenue()));
		$info->addProperty($this->txt('observator'), implode(", ", $obsv_names));

		$start_date = $settings->getStartDate()->get(IL_CAL_DATE);
		$end_date = $settings->getEndDate()->get(IL_CAL_DATE);
		if ($start_date == $end_date) {
			$date = $start_date;
		} else {
			$date = $start_date." ".$this->txt("to")." ".$end_date;
		}

		$start_time = explode(" ", $settings->getStartDate());
		$end_time = explode(" ", $settings->getEndDate());

		$info->addProperty($this->txt('date'), $date);
		$info->addProperty($this->txt('start_time'), $start_time[1]);
		$info->addProperty($this->txt('end_time'), $end_time[1]);

		return $info;
	}

	public function userfieldAutocomplete()
	{
		include_once './Services/User/classes/class.ilUserAutoComplete.php';
		$auto = new ilUserAutoComplete();
		$auto->setSearchFields(array('login','firstname','lastname','email'));
		$auto->enableFieldSearchableCheck(false);
		if (($_REQUEST['fetchall'])) {
			$auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
		}
		echo $auto->getList($_REQUEST['term']);
		exit();
	}
}
