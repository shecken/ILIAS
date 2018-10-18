<?php

require_once __DIR__."/../PluginLanguage.php";
require_once "Services/GEV/Utils/classes/class.gevSettings.php";
require_once "Services/GEV/Utils/classes/class.gevCourseUtils.php";

class ilTransferUserGUI {
	use PluginLanguage;

	const F_LUSER = "f_lUser";
	const F_EUSER = "f_eUser";

	const CMD_SHOW_FORM = "showForm";
	const CMD_TRANSFER_USER = "transferUser";
	const CMD_AUTOCOMPLETE = "userfieldAutocomplete";

	public function __construct(
		ilCtrl $ctrl,
		ilTabsGUI $tabs,
		ilTemplate $tpl,
		Closure $txt,
		TransferActions $actions
	) {
		$this->ctrl = $ctrl;
		$this->tabs = $tabs;
		$this->tpl = $tpl;
		$this->txt = $txt;
		$this->actions = $actions;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass();

		switch($cmd) {
			case self::CMD_SHOW_FORM:
				$this->showForm();
				break;
			case self::CMD_AUTOCOMPLETE:
				$this->userfieldAutocomplete();
				break;
			case self::CMD_TRANSFER_USER:
				$this->transferUser();
				break;
			default:
				throw new Exception("Unknown command: ".$cmd);
		}
	}

	protected function showForm(ilPropertyFormGUI $form = null)
	{
		if(is_null($form)) {
			$form = $this->initForm();
		}

		$this->tpl->setContent($form->getHtml());
	}

	protected function initForm()
	{
		require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->txt("trans_user_transfer_user"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$ti = new ilTextInputGUI($this->txt("trans_user_user_to_delete"), self::F_LUSER);
		$ti->setInfo($this->txt("trans_user_user_to_delete_info"));
		$ti->setRequired(true);
		$autocomplete_link = $this->ctrl->getLinkTarget($this, self::CMD_AUTOCOMPLETE, "", true);
		$ti->setDataSource($autocomplete_link);
		$form->addItem($ti);

		$ti = new ilTextInputGUI($this->txt("trans_user_user_to_keep"), self::F_EUSER);
		$ti->setInfo($this->txt("trans_user_user_to_keep_info"));
		$ti->setRequired(true);
		$autocomplete_link = $this->ctrl->getLinkTarget($this, self::CMD_AUTOCOMPLETE, "", true);
		$ti->setDataSource($autocomplete_link);
		$form->addItem($ti);

		$form->addCommandButton(self::CMD_TRANSFER_USER, $this->txt("trans_user_transfer"));
		$form->addCommandButton(self::CMD_SHOW_FORM, $this->txt("cancel"));

		return $form;
	}

	protected function transferUser()
	{
		$form = $this->initForm();
		if(!$form->checkInput()) {
			$form->setValuesByPost();
			$this->showForm($form);
			return;
		}

		$post = $_POST;

		$login = $post[self::F_LUSER];
		$user_id = (int)ilObjUser::_lookupId($login);
		$lUser = new ilObjUser($user_id);

		$login = $post[self::F_EUSER];
		$user_id = (int)ilObjUser::_lookupId($login);
		$eUser = new ilObjUser($user_id);

		$utils_lUser = gevUserUtils::getInstanceByObjOrId($lUser);
		$utils_eUser = gevUserUtils::getInstanceByObjOrId($eUser);

		$today_ts = time();
		$today = date("Y-m-d", $today_ts);
		if($utils_lUser->courseToday($today_ts)) {
			ilUtil::sendFailure("User ".$lUser->getId()." has Course today. No Action!");
			$form->setValuesByPost();
			$this->showForm($form);
			return;
		}

		$lUser_crs_infos = $utils_lUser->coursesBefore($today);
		$eUser_crs_infos = $utils_eUser->coursesBefore($today);

		$blockers = $this->actions->getBlockers($lUser_crs_infos, $eUser_crs_infos);
		if(count($blockers) > 0)
		{
			$this->showBlockers($blockers);
			return;
		}

		$this->actions->changeCrsWhereLUserHasStatusAndEUserNot(
			$lUser_crs_infos,
			$eUser_crs_infos,
			$eUser->getId()
		);

		$this->actions->changeCrsWhereStatusIsHigher(
			$lUser_crs_infos,
			$eUser_crs_infos,
			$eUser->getId(),
			$lUser->getId()
		);

		$lUser_crs_infos = $utils_lUser->coursesAfter($today);
		$eUser_crs_infos = $utils_eUser->coursesAfter($today);
		$this->actions->cancelCoursesFor($lUser->getId(),$eUser->getId(), $lUser_crs_infos);
		$this->actions->bookCoursesFor($lUser_crs_infos, $eUser_crs_infos, $eUser->getId());

		$lUser_orgunits = $utils_lUser->getOrgUnitsWhereUserIsEmployee();
		if(count($lUser_orgunits) > 0) {
			$this->actions->changeOrgUnitsForLUser($lUser_orgunits, $lUser->getId());
		}

		$lUser->updateLogin($lUser->getLogin() . " INAKTIV");
		gevUserUtils::setUserActiveState($lUser->getId(), 0);

		$this->ctrl->redirect($this, self::CMD_SHOW_FORM);
	}

	protected function showBlockers(array $blockers)
	{
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->txt("trans_user_not_possible"));

		require_once "Services/Form/classes/class.ilNonEditableValueGUI.php";
		$ne = new ilNonEditableValueGUI($this->txt("trans_user_open_courses"));
		$text = "<ul><li>";
		$text .= join("</li><li>", $blockers);
		$text .= "</li></ul>";
		$ne->setValue($text);
		$form->addItem($ne);

		$form->addCommandButton(self::CMD_SHOW_FORM, $this->txt("back_to_form"));

		$this->tpl->setContent($form->getHtml());
	}


	protected function userfieldAutocomplete()
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