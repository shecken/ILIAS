<?php

require_once __DIR__."/../PluginLanguage.php";

class ilRefreshAllGUI
{
	use PluginLanguage;

	const CMD_SHOW_FORM = "showForm";
	const CMD_REFRESH = "refresh";
	const CRS_ID = "crs_id";

	public function __construct(
		ilCtrl $ctrl,
		ilTemplate $tpl,
		Closure $txt,
		RefreshCertificateHelper $helper
	) {
		$this->ctrl = $ctrl;
		$this->tabs = $tabs;
		$this->tpl = $tpl;
		$this->txt = $txt;
		$this->helper = $helper;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch($cmd) {
			case self::CMD_SHOW_FORM:
				$this->showForm();
				break;
			case self::CMD_REFRESH:
				$this->refresh();
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
		$form->setTitle($this->txt("refresh_cert_all_title"));
		$form->setDescription($this->txt("refresh_cert_all_description"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$ni = new ilNumberInputGUI($this->txt("refresh_cert_all_crs_id"), self::CRS_ID);
		$ni->setMinValue(1);
		$ni->setRequired(true);
		$form->addItem($ni);

		$form->addCommandButton(self::CMD_REFRESH, $this->txt("refresh_cert_all_refresh"));
		$form->addCommandButton(self::CMD_SHOW_FORM, $this->txt("cancel"));

		return $form;
	}

	protected function refresh()
	{
		$form = $this->initForm();

		if(!$form->checkInput()) {
			$form->setValuesByPost();
			$this->showForm($form);
			return;
		}

		$post = $_POST;
		$crs_ref_id = $post[self::CRS_ID];
		$crs = ilObjectFactory::getInstanceByRefId($crs_ref_id);

		$user_ids = $this->helper->getSuccessfulParticipantsFor($crs->getId());
		if(count($user_ids) > 0) {
			$this->helper->updateCertificates($crs, $user_ids);
			ilUtil::sendSuccess($this->txt("refresh_cert_all_refresh_success"), true);
		} else {
			ilUtil::sendInfo($this->txt("refresh_cert_all_no_successful_participants"), true);
		}

		$this->ctrl->redirect($this, self::CMD_SHOW_FORM);
	}
}