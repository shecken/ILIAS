<?php

require_once __DIR__."/../PluginLanguage.php";

class ilDeleteUserFromCourseGUI {
	use PluginLanguage;

	const CMD_SHOW_FORM = "showForm";
	const CMD_DELETE_USER = "deleteUser";
	const USR_LOGIN = "usr_id";
	const CRS_ID = "crs_id";
	const CMD_AUTOCOMPLETE = "userfieldAutocomplete";
	const WBD_BOOKING_ID_EMPTY_VALUE = "-empty-";

	public function __construct(
		ilCtrl $ctrl,
		ilTemplate $tpl,
		Closure $txt,
		ilDeleteUserActions $actions
	) {
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->txt = $txt;
		$this->actions = $actions;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch($cmd) {
			case self::CMD_SHOW_FORM:
				$this->showForm();
				break;
			case self::CMD_DELETE_USER:
				$this->deleteUser();
				break;
			case self::CMD_AUTOCOMPLETE:
				$this->userfieldAutocomplete();
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

	protected function deleteUser()
	{
		$form = $this->initForm();

		if(!$form->checkInput()) {
			$form->setValuesByPost();
			$this->showForm($form);
			return;
		}

		$post = $_POST;
		$crs_ref_id = (int)$post[self::CRS_ID];
		$crs = ilObjectFactory::getInstanceByObjId($crs_ref_id);

		if(!$this->actions->isCourseFinalized($crs->getId())) {
			ilUtil::sendInfo($this->txt("delete_usr_course_not_finished"));
			return;
		}
		$login = $post[self::CRS_ID];
		$user_id = (int)ilObjUser::_lookupId($login);

		$wbd_booking_infos = $this->actions->getWBDBookingInfos($crs->getId(), $user_id);
		$bookings = ilCourseBookings::getInstance($crs);

		$this->actions->setCourseUnfinalzied($crs->getId());
		$bookings->cancelWithoutCosts($user_id);
		$this->actions->setCourseFinalized($crs->getId());

		if($wbd_booking_infos["wbd_booking_id"] != self::WBD_BOOKING_ID_EMPTY_VALUE) {
			$this->showWBDBookingIdInfo($wbd_booking_infos);
			return;
		}

		$this->ctrl->redirect($this, self::CMD_SHOW_FORM);
	}

	protected function showWBDBookingIdInfo(array $wbd_booking_infos)
	{
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->txt("delete_usr_wbd_booking_form_title"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$ne = new ilNoneEditableInputGUI($this->txt("delete_usr_wbd_booking_date"));
		$ne->setValue($wbd_booking_infos["last_wbd_Report"]);
		$form->addItem($ne);

		$ne = new ilNoneEditableInputGUI($this->txt("delete_usr_wbd_booking_id"));
		$ne->setValue($wbd_booking_infos["wbd_booking_id"]);
		$form->addItem($ne);

		$form->addCommandButton(self::CMD_SHOW_FORM, $this->txt("back_to_form"));

		$this->setContent($form->getHtml());
	}

	protected function initForm() {
		require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->txt("delete_usr_form_title"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$ti = new ilTextInputGUI($this->txt("delete_usr_usr_id"), self::USR_LOGIN);
		$ti->setRequired(true);
		$autocomplete_link = $this->ctrl->getLinkTarget($this, self::CMD_AUTOCOMPLETE, "", true);
		$ti->setDataSource($autocomplete_link);
		$form->addItem($ti);

		$ti = new ilNumberInputGUI($this->txt("delete_usr_crs_id"), self::CRS_ID);
		$form->addItem($ti);

		$form->addCommandButton(self::CMD_DELETE_USER, $this->txt("delete_usr_remove_from_course"));
		$form->addCommandButton(self::CMD_SHOW_FORM, $this->txt("cancel"));

		return $form;
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