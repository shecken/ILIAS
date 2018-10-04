<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Command class for registration of an agent.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevAgentRegistrationGUI
{
	const F_CONNECTION = "f_connection";
	const F_GEV_MEDIATOR_NUMBER = "gev_mediator_number";
	const F_DIMAK_MEDIATOR_NUMBER = "dimak_mediator_number";
	const F_MEDIATOR_NUMBER = "mediator_number";
	const F_EMAIL = "email";
	const F_GENDER = "gender";
	const F_TITLE = "title";
	const F_LASTNAME = "lastname";
	const F_FIRSTNAME = "firstname";
	const F_COMPANY_NAME = "company_name";
	const F_USERNAME = "username";
	const F_PASSWORD = "password";
	const F_B_PHONE = "b_phone";
	const F_ACCEPT_TERMS = "accept_terms";

	const V_CON_GEV = "gev";
	const V_CON_DIMAK = "dimak";

	protected static $VALID_AGENT_STATUSES = [
		"608",
		"650",
		"651",
		"674",
		"679"
	];

	/**
	 * @var gevJobnumberDB
	 */
	protected $gev_jobnumber_DB;

	public function __construct()
	{
		global $lng, $ilCtrl, $tpl, $ilLog, $ilDB;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;
		$this->db = &$ilDB;
		$this->import = null;
		$this->stellennummer_data = null;

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand()
	{
		global $ilAuth;

		// The user should not be logged in...
		if ($ilAuth->checkAuth()) {
			ilUtil::redirect("login.php");
		}

		$cmd = $this->ctrl->getCmd();
		if ($cmd == "startRegistration") {
			$cmd = "startAgentRegistration";
		}

		switch ($cmd) {
			case "startAgentRegistration":
			case "registerAgent":
				$cont = $this->$cmd();
				break;
			default:
				ilUtil::redirect("login.php");
		}

		$this->tpl->setContent($cont);
		$this->tpl->show();
	}

	/**
	 * Check for valid jobnumber.
	 *
	 * @param 	string 	$jobnumber
	 * @return 	bool
	 */
	protected function checkValidJobnumber($jobnumber)
	{
		assert('is_string($jobnumber)');

		return $this->getDB()->checkForJobnumber($jobnumber);
	}

	/**
	 * Check whether 'vermittlerstatus' is a agent status.
	 *
	 * @param 	string 	$jobnumber
	 * @return 	bool	true = is a agent; false = isn't a agent
	 */
	protected function isAgent($jobnumber)
	{
		assert('is_string($jobnumber)');

		$status = $this->getDB()->getAgentStatus($jobnumber);

		if ($status != -1 && in_array($status, self::$VALID_AGENT_STATUSES)) {
			return true;
		}

		return false;
	}

	/**
	 * Get entries for jobnumber.
	 *
	 * @param 	string 	$jobnumber
	 * @param 	array
	 */
	protected function getEntriesForJobnumber($jobnumber)
	{
		assert('is_string($jobnumber)');

		return $this->getDB()->getEntryByJobnumber($jobnumber);
	}

	/**
	 * Get the gev adp db.
	 *
	 * @return 	gevADPDB
	 */
	protected function getDB()
	{
		if ($this->gev_jobnumber_DB === null) {
			require_once("./Services/GEV/Import/classes/class.gevJobnumberDB.php");
			$this->gev_jobnumber_DB = new gevJobnumberDB($this->db);
		}

		return $this->gev_jobnumber_DB;
	}

	protected function startAgentRegistration($form = null)
	{
		// get stellennummer and email
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		$title = new catTitleGUI("gev_registration", null, "GEV_img/ico-head-registration.png");
		$tpl = new ilTemplate("tpl.gev_agent_registration.html", false, false, "Services/GEV/Registration");

		if (is_null($form)) {
			$form = $this->buildRegistrationForm();
		}

		$tpl->setVariable("FORM", $form->getHTML());
		return  $title->render()
			  . $tpl->get();
	}

	protected function registerAgent()
	{
		list($form, $error) = $this->checkForm();

		if ($error) {
			$form->setValuesByPost();
			return $this->startAgentRegistration($form);
		}

		$user = new ilObjUser();
		$user->setLogin($form->getInput(self::F_USERNAME));
		$user->setEmail($form->getInput(self::F_EMAIL));
		$user->setPasswd($form->getInput(self::F_PASSWORD));
		$user->setLastname($form->getInput(self::F_LASTNAME));
		$user->setFirstname($form->getInput(self::F_FIRSTNAME));
		$user->setGender($form->getInput(self::F_GENDER));
		$user->setUTitle($form->getInput(self::F_TITLE));
		$user->setPhoneOffice($form->getInput(self::F_B_PHONE));

		// is not active, owner is root
		$user->setActive(0, 6);
		$user->setTimeLimitUnlimited(true);
		// user already agreed at registration
		$now = new ilDateTime(time(), IL_CAL_UNIX);
		$user->setAgreeDate($now->get(IL_CAL_DATETIME));
		$user->setIsSelfRegistered(true);

		$user->create();
		$user->saveAsNew();

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstanceByObj($user);

		$user_utils->setEmail($form->getInput(self::F_EMAIL));
		$user_utils->setCompanyName($form->getInput(self::F_COMPANY_NAME));

		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");

		$user_id = $user->getId();
		$jobnumber = $form->getInput(self::F_MEDIATOR_NUMBER);
		$data = $this->getEntriesForJobnumber($jobnumber);
		$vermittlerstatus = $data['agent_status'];

		$user_utils->setADPNumberGEV($data["jobnumber"]);
		$user_utils->setJobNumber($data["jobnumber"]);
		$user_utils->setAgentKey($data["vms"]);

		$role_title = gevSettings::$VMS_ROLE_MAPPING[$vermittlerstatus][0];
		$role_utils = gevRoleUtils::getInstance();
		$role_utils->assignUserToGlobalRole($user_id, $role_title);

		$settings = gevSettings::getInstance();
		$uvg_obj_id = $settings->getDBVPOUBaseUnitId();
		$org_utils = gevOrgUnitUtils::getInstance($uvg_obj_id);
		$org_utils->assignUser($user_id, "Mitarbeiter");

		$user->setActive(true, 6);
		$user->update();

		global $ilAuth;

		$ilAuth->username = $form->getInput(self::F_USERNAME);
		$ilAuth->password = $form->getInput(self::F_PASSWORD);

		$ilAuth->login();

		$user_utils->preventUserDataValidation();

		// If user got here via the agent offer, we need to redirect him to
		// the booking stuff...
		require_once("Services/Authentication/classes/class.ilSession.php");
		$after_registration = ilSession::get("gev_after_registration");
		if ($after_registration) {
			ilUtil::redirect($after_registration);
		} else {
			ilUtil::redirect("login.php");
		}
	}

	protected function checkForm()
	{
		$form = $this->buildRegistrationForm();
		$err = false;

		if (!$form->checkInput()) {
			$err = true;
		}

		if ($_POST[self::F_ACCEPT_TERMS] != 1) {
			$err = true;
			$chb = $form->getItemByPostVar(self::F_ACCEPT_TERMS);
			$chb->setAlert($this->lng->txt("evg_mandatory"));
		}

		$connection = $form->getInput(self::F_CONNECTION);
		if($connection == self::V_CON_GEV) {
			$jobnumber = $form->getInput(self::F_GEV_MEDIATOR_NUMBER);
			if (!$this->checkJobnumber($jobnumber)) {
				$err = true;
				$form->getItemByPostVar(self::F_GEV_MEDIATOR_NUMBER)->setAlert($this->lng->txt("gev_evg_registration_not_found"));
			}
		}

		if($connection == self::V_CON_DIMAK) {
			$jobnumber = $form->getInput(self::F_DIMAK_MEDIATOR_NUMBER);
			if (!$this->checkJobnumber($jobnumber)) {
				$err = true;
				$form->getItemByPostVar(self::F_DIMAK_MEDIATOR_NUMBER)->setAlert($this->lng->txt("gev_evg_registration_not_found"));
			}
		}

		return array($form, $err);
	}

	protected function checkJobNumber($jobnumber)
	{
		return $this->checkValidJobnumber($jobnumber) && $this->isAgent($jobnumber);
	}

	protected function buildRegistrationForm()
	{
		require_once("Services/Calendar/classes/class.ilDate.php");
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");

		$form = new ilPropertyFormGUI();
		$form->addCommandButton("registerAgent", $this->lng->txt("register"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$connection = new ilRadioGroupInputGUI($this->lng->txt("gev_connection"), self::F_CONNECTION);
		$option = new ilRadioOption($this->lng->txt("gev_mediator_number"), self::V_CON_GEV);
		$number = new ilTextInputGUI("", self::F_GEV_MEDIATOR_NUMBER);
		$number->setSize(40);
		$number->setRequired(true);
		$number->setInfo($this->lng->txt("gev_mediator_number_info"));
		$option->addSubItem($number);
		$connection->addOption($option);

		$option = new ilRadioOption($this->lng->txt("dimak_mediator_number"), self::V_CON_DIMAK);
		$number = new ilTextInputGUI("", self::F_DIMAK_MEDIATOR_NUMBER);
		$number->setSize(40);
		$number->setRequired(true);
		$number->setInfo($this->lng->txt("dimak_mediator_number_info"));
		$option->addSubItem($number);
		$connection->addOption($option);
		$connection->setRequired(true);
		$form->addItem($connection);

		$email = new ilEMailInputGUI($this->lng->txt("evg_email"), self::F_EMAIL);
		$email->setSize(40);
		$email->setRequired(true);
		$form->addItem($email);

		$gender = new ilRadioGroupInputGUI($this->lng->txt("salutation"), self::F_GENDER);
		$gender->addOption(new ilRadioOption($this->lng->txt("salutation_m"), "m"));
		$gender->addOption(new ilRadioOption($this->lng->txt("salutation_f"), "f"));
		$gender->setRequired(true);
		$form->addItem($gender);

		$title = new ilTextInputGUI($this->lng->txt("title"), self::F_TITLE);
		$form->addItem($title);
		$lastname = new ilTextInputGUI($this->lng->txt("lastname"), self::F_LASTNAME);
		$lastname->setRequired(true);
		$form->addItem($lastname);

		$firstname = new ilTextInputGUI($this->lng->txt("firstname"), self::F_FIRSTNAME);
		$firstname->setRequired(true);
		$form->addItem($firstname);

		$company_name = new ilTextInputGUI($this->lng->txt("gev_company_name"), self::F_COMPANY_NAME);
		$company_name->setRequired(true);
		$form->addItem($company_name);

		$username = new ilUserLoginInputGUI($this->lng->txt("gev_username_free"), self::F_USERNAME);
		$username->setRequired(true);
		$form->addItem($username);

		$password1 = new ilPasswordInputGUI($this->lng->txt("password"), self::F_PASSWORD);
		$password1->setRequired(true);
		$form->addItem($password1);

		$b_phone = new ilTextInputGUI($this->lng->txt("gev_profile_phone"), self::F_B_PHONE);
		$b_phone->setRequired(true);
		$form->addItem($b_phone);

		$chb1 = new ilCheckboxInputGUI("", self::F_ACCEPT_TERMS);
		$chb1->setOptionTitle($this->lng->txt("uvg_toc"));
		$chb1->setRequired(true);
		$form->addItem($chb1);

		return $form;
	}
}
