<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

class gevEffectivenessAnalysisGUI {
	const CMD_NEW = "newEntry";
	const CMD_CONFIRM = "confirmEntry";
	const CMD_SAVE = "saveEntry";

	const F_NAME = "name";
	const F_ORG_UNIT = "orgUnit";
	const F_TRAINTING = "training";
	const F_RESULT = "result";
	const F_USER_ID = "userId";
	const F_CRS_ID = "crsId";
	const F_RESULT_TEXT = "resultText";

	public function __construct() {
		global $ilCtrl, $tpl, $lng, $ilTabs;
		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;
		$this->gLng = $lng;
		$this->gTabs = $ilTabs;

		require_once("Services/GEV/Desktop/classes/EffectivenessAnalysis/class.gevEffectivenessAnalysis.php");
		$this->eff_analysis = gevEffectivenessAnalysis::getInstance();
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd(self::CMD_NEW);
		$this->setTabs();

		switch($cmd) {
			case self::CMD_NEW:
			case self::CMD_CONFIRM:
			case self::CMD_SAVE:
			case self::CMD_SEARCH_USER:
				$this->$cmd();
				break;
			default:
		}
	}

	protected function saveEntry() {
		$post = $_POST;
		$user_id = $post[self::F_USER_ID];
		$crs_id = $post[self::F_CRS_ID];
		$result = $post[self::F_RESULT];
		$info = $post[self::F_RESULT_TEXT];

		$this->eff_analysis->saveResult($crs_id, $user_id, $result, $info);

		$this->gCtrl->redirectByClass("gevMyEffectivenessAnalysisGUI");
	}

	protected function confirmEntry() {
		$form = $this->initForm();
		$post = $_POST;

		if(!$form->checkInput()) {
			$form->setValuesByPost();
			$this->newEntry($form);
			return;
		}

		if($this->eff_analysis->checkInfoIsRequired($post[self::F_RESULT], $post[self::F_RESULT_TEXT])) {
			$form->setValuesByPost();
			$ele = $form->getItemByPostVar(self::F_RESULT_TEXT);
			$ele->setAlert($this->gLng->txt("gev_eff_analysis_info_needed"));
			$this->newEntry($form);
			return;
		}

		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$confirmation_gui = new ilConfirmationGUI();
		$confirmation_gui->setFormAction($this->gCtrl->getFormAction($this));
		$confirmation_gui->setHeaderText($this->gLng->txt("gev_eff_analysis_confirm"));
		$confirmation_gui->setCancel($this->gLng->txt("gev_eff_analysis_cancel"), self::CMD_NEW);
		$confirmation_gui->setConfirm($this->gLng->txt("gev_eff_analysis_save"), self::CMD_SAVE);

		$confirmation_gui->addItem("", "", $this->gLng->txt("gev_eff_analysis_result").": ".$this->eff_analysis->getResultText($post[self::F_RESULT]));
		$confirmation_gui->addItem("", "", $this->gLng->txt("gev_eff_analysis_info").": ".$post[self::F_RESULT_TEXT]);

		$confirmation_gui->addHiddenItem(self::F_USER_ID, $post[self::F_USER_ID]);
		$confirmation_gui->addHiddenItem(self::F_CRS_ID, $post[self::F_CRS_ID]);
		$confirmation_gui->addHiddenItem(self::F_RESULT, $post[self::F_RESULT]);
		$confirmation_gui->addHiddenItem(self::F_RESULT_TEXT, $post[self::F_RESULT_TEXT]);

		$this->gTpl->setContent($confirmation_gui->getHTML());
	}

	protected function newEntry($form = null) {
		if($form === null) {
			$form = $this->initForm();
			$values = $this->getFormValues();
			$form->setValuesByArray($values);
		}

		$this->gTpl->setContent($form->getHtml());
	}

	protected function initForm() {
		$readonly = $this->getReadonly();

		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->gCtrl->getFormAction($this, self::CMD_CONFIRM));

		if(!$readonly) {
			$form->addCommandButton(self::CMD_CONFIRM, $this->gLng->txt("gev_eff_analysis_save"));
		}

		$form->setTitle($this->gLng->txt("gev_eff_analysis"));

		$ne = new ilNonEditableValueGUI($this->gLng->txt("gev_eff_analysis_user"), self::F_NAME);
		$form->addItem($ne);

		$ne = new ilNonEditableValueGUI($this->gLng->txt("gev_eff_analysis_department"), self::F_ORG_UNIT);
		$form->addItem($ne);

		$ne = new ilNonEditableValueGUI($this->gLng->txt("gev_eff_analysis_training"), self::F_TRAINTING);
		$form->addItem($ne);

		$hi = new ilHiddenInputGUI(self::F_USER_ID);
		$form->addItem($hi);

		$hi = new ilHiddenInputGUI(self::F_CRS_ID);
		$form->addItem($hi);

		$si = new ilSelectInputGUI($this->gLng->txt("gev_eff_analysis_result"), self::F_RESULT);
		$si->setOptions($this->getResultOptions());
		$si->setRequired(true);
		$si->setDisabled($readonly);
		$form->addItem($si);

		$ta = new ilTextAreaInputGUI($this->gLng->txt("gev_eff_analysis_insert_info"), self::F_RESULT_TEXT);
		$ta->setDisabled($readonly);
		$form->addItem($ta);

		return $form;
	}

	protected function getFormValues() {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

		$user_utils = gevUserUtils::getInstance($this->getUserId());
		$crs_utils = gevCourseUtils::getInstance($this->getCrsId());

		$values = array();

		$values[self::F_NAME] = $user_utils->getFullName();
		$values[self::F_ORG_UNIT] = implode(", ", $user_utils->getTitelOfAllOrgUnits());
		$values[self::F_TRAINTING] = $crs_utils->getTitle().", ".$crs_utils->getFormattedDate();
		$values[self::F_USER_ID] = $user_utils->getId();
		$values[self::F_CRS_ID] = $crs_utils->getId();

		$result = $this->getResultData($crs_utils->getId(), $user_utils->getId());

		$values[self::F_RESULT] = $result["result"];
		$values[self::F_RESULT_TEXT] = $result["info"];

		return $values;
	}

	protected function getResultOptions() {
		return $this->eff_analysis->getResultOptions();
	}

	protected function getUserId() {
		if(isset($_GET["user_id"])) {
			return (int)$_GET["user_id"];
		}

		if(isset($_POST[self::F_USER_ID])) {
			return (int)$_POST[self::F_USER_ID];
		}
	}

	protected function getCrsId() {
		if(isset($_GET["crs_id"])) {
			return (int)$_GET["crs_id"];
		}

		if(isset($_POST[self::F_CRS_ID])) {
			return (int)$_POST[self::F_CRS_ID];
		}
	}

	protected function getReadonly() {
		var_dump($_GET);
		if(isset($_GET["readonly"])) {
			$val = $_GET["readonly"];
			if($val == "readonly") {
				return true;
			}
		}

		return false;
	}

	protected function getResultData($crs_id, $user_id) {
		return $this->eff_analysis->getResultDataFor($crs_id, $user_id);
	}

	protected function setTabs() {
		$back_link = $this->getBackLink();

		if($back_link && $back_link != "") {
			$this->gTabs->setBackTarget($this->gLng->txt("back"), $back_link);
		}
	}

	protected function getBackLink() {
		if(isset($_GET["back"])) {
			$back = $_GET["back"];

			if($back == "report") {
				return "ilias.php?baseClass=gevDesktopGUI&cmd=toReportEffectivenessAnalysis";
			} else if($back == "view") {
				return "ilias.php?baseClass=gevDesktopGUI&cmd=toMyEffectivenessAnalysis";
			}
		}

		return "";
	}
}