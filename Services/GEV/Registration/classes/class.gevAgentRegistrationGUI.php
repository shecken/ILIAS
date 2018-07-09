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

	protected function startAgentRegistration($a_form = null)
	{
		// get stellennummer and email
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");

		$title = new catTitleGUI("gev_registration", null, "GEV_img/ico-head-registration.png");

		$tpl = new ilTemplate("tpl.gev_agent_registration.html", false, false, "Services/GEV/Registration");

		if ($a_form !== null) {
			$form = $a_form;
			$form->setValuesByPost();
		} else {
			$form = $this->buildRegistrationForm();
		}
		$tpl->setVariable("FORM", $form->getHTML());

		return  $title->render()
			  . $tpl->get();
	}

	protected function registerAgent()
	{
		$res = $this->checkForm();

		if (!$res[1]) {
			return $this->startAgentRegistration($res[0]);
		}

		$form = $res[0];

		$user = new ilObjUser();
		$user->setLogin($form->getInput("username"));
		$user->setEmail($form->getInput("email"));
		$user->setPasswd($form->getInput("password"));
		$user->setLastname($form->getInput("lastname"));
		$user->setFirstname($form->getInput("firstname"));
		$user->setGender($form->getInput("gender"));
		$user->setUTitle($form->getInput("title"));
		$user->setPhoneOffice($form->getInput("b_phone"));

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

		$user_utils->setEmail($form->getInput("email"));
		$user_utils->setCompanyName($form->getInput("company_name"));

		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");

		$user_id = $user->getId();
		$jobnumber = $form->getInput("position");
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

		$ilAuth->username = $form->getInput("username");
		$ilAuth->password = $form->getInput("password");

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

		if ($_POST["chb1"] != 1) {
			$err = true;
			$chb = $form->getItemByPostVar("chb1");
			$chb->setAlert($this->lng->txt("evg_mandatory"));
		}

		$jobnumber = $form->getInput("position");

		if (!$this->checkValidJobnumber($jobnumber) || !$this->isAgent($jobnumber)) {
			$err = true;
			$form->getItemByPostVar("position")->setAlert($this->lng->txt("gev_evg_registration_not_found"));
		}

		return array($form, !$err);
	}

	protected function buildRegistrationForm()
	{
		require_once("Services/Calendar/classes/class.ilDate.php");
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilDateTimeInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioOption.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
		require_once("Services/Form/classes/class.ilPasswordInputGUI.php");
		require_once("Services/Form/classes/class.ilUserLoginInputGUI.php");
		require_once("Services/Form/classes/class.ilHiddenInputGUI.php");

		$form = new ilPropertyFormGUI();
		$form->addCommandButton("registerAgent", $this->lng->txt("register"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$position = new ilTextInputGUI($this->lng->txt("gev_position"), "position");
		$position->setSize(40);
		$position->setRequired(true);
		$form->addItem($position);

		$email = new ilEMailInputGUI($this->lng->txt("evg_email"), "email");
		$email->setSize(40);
		$email->setRequired(true);
		$form->addItem($email);

		$gender = new ilRadioGroupInputGUI($this->lng->txt("salutation"), "gender");
		$gender->addOption(new ilRadioOption($this->lng->txt("salutation_m"), "m"));
		$gender->addOption(new ilRadioOption($this->lng->txt("salutation_f"), "f"));
		$gender->setRequired(true);
		$form->addItem($gender);

		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$form->addItem($title);
		$lastname = new ilTextInputGUI($this->lng->txt("lastname"), "lastname");
		$lastname->setRequired(true);
		$form->addItem($lastname);

		$firstname = new ilTextInputGUI($this->lng->txt("firstname"), "firstname");
		$firstname->setRequired(true);
		$form->addItem($firstname);

		$company_name = new ilTextInputGUI($this->lng->txt("gev_company_name"), "company_name");
		$company_name->setRequired(true);
		$form->addItem($company_name);

		$username = new ilUserLoginInputGUI($this->lng->txt("gev_username_free"), "username");
		$username->setRequired(true);
		$form->addItem($username);

		$password1 = new ilPasswordInputGUI($this->lng->txt("password"), "password");
		$password1->setRequired(true);
		$form->addItem($password1);

		$b_phone = new ilTextInputGUI($this->lng->txt("gev_profile_phone"), "b_phone");
		$b_phone->setRequired(true);
		$form->addItem($b_phone);

		$chb1 = new ilCheckboxInputGUI("", "chb1");
		$chb1->setOptionTitle($this->lng->txt("uvg_toc"));
		$chb1->setRequired(true);
		$form->addItem($chb1);

		return $form;
	}
}
