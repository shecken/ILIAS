<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevExpressLoginUtils.php");

/**
* Command class for registration of an express user.
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*/
class gevExpressRegistrationGUI
{
	const F_CONNECTION = "f_connection";
	const F_GEV_MEDIATOR_NUMBER = "gev_mediator_number";
	const F_DIMAK_MEDIATOR_NUMBER = "dimak_mediator_number";
	const F_MEDIATOR_NUMBER = "mediator_number";

	const V_CON_GEV = "gev";
	const V_CON_DIMAK = "dimak";

	protected static $VALID_AGENT_STATUSES = [
		"608",
		"650",
		"651",
		"674",
		"679"
	];

	public function __construct()
	{
		global $lng, $ilCtrl, $tpl, $ilLog, $ilAuth, $ilDB;

		$this->g_lng = $lng;
		$this->g_ctrl = $ilCtrl;
		$this->g_tpl = $tpl;
		$this->g_log = $ilLog;
		$this->g_auth = $ilAuth;
		$this->g_db = $ilDB;

		$this->crs_id = null;
		$this->user_id = null;
		$this->crs_utils = null;

		$this->g_tpl->getStandardTemplate();
	}

	public function executeCommand()
	{
		// The user should not be logged in...
		if ($this->g_auth->checkAuth()) {
			ilUtil::redirect("login.php");
		}

		if (!isset($_POST["type"])) {
			$cmd = "startExpRegistration";
		} else {
			$cmd = $_POST["type"];
		}

		switch ($cmd) {
			case "startExpRegistration":
			case "registerExpUser":
				$cont = $this->$cmd();
				break;
			case "redirectNewLogin":
				require_once("Services/Authentication/classes/class.ilSession.php");
				$crs_utils = gevCourseUtils::getInstance($_GET["crs_id"]);
				ilSession::set("gev_after_registration", $crs_utils->getPermanentBookingLink());
				ilUtil::redirect("gev_registration.php?goto=startAgentRegistration");
				break;
			case "redirectLogin":
				require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
				$crs_utils = gevCourseUtils::getInstance($_GET["crs_id"]);
				ilUtil::redirect($crs_utils->getPermanentBookingLink());
				break;
			default:
				ilUtil::redirect("login.php?target=login&client_id=Generali");
				break;
		}

		$this->g_tpl->setContent($cont);
		$this->g_tpl->show();
	}

	/**
	 * Register a new Express user
	 *
	 * @return null
	 */
	protected function registerExpUser()
	{
		$res = $this->checkForm();

		//If custom form check returns an error, move back to registration form
		if ($res[1]) {
			return $this->startExpRegistration($res[0]);
		}

		$form = $res[0];
		$this->crs_id = $_GET["crs_id"];

		//register new express user
		$expLoginUtils = gevExpressLoginUtils::getInstance();
		$this->user_id = $expLoginUtils->registerExpressUser($form);

		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$this->crs_utils = gevCourseUtils::getInstance($this->crs_id);
		$this->crs_utils->bookUser($this->user_id);

		$isSelfLearning = $this->crs_utils->getType() == "Selbstlernkurs";

		$status = $this->crs_utils->getBookingStatusOf($this->user_id);

		if ($status != ilCourseBooking::STATUS_BOOKED && $status != ilCourseBooking::STATUS_WAITING) {
			$this->failAtFinalize("Status was neither booked nor waiting.");
		}
		$this->finalizedBookingRedirect($status, $isSelfLearning);
	}

	/**
	 * Send failure message if user registration failed
	 *
	 * @param string 	$msg
	 *
	 * @return null
	 */
	protected function failAtFinalize($msg)
	{
		$this->g_log->write("gevBookingGUI::finalizeBooking: ".$msg);

		if ($this->crs_utils->getFreePlaces() == 0) {
			ilUtil::sendFailure($this->g_lng->txt("gev_finalize_booking_booked_out_error"), true);
		} else {
			ilUtil::sendFailure($this->g_lng->txt("gev_finalize_booking_error"), true);
		}

		$this->toMaklerOffer();
		exit();
	}

	/**
	 * Show succes mail and redirect to makler main page
	 *
	 * @param int 		$a_status
	 * @param boolean 	$a_isSelfLearning
	 *
	 * @return null
	 */
	protected function finalizedBookingRedirect($a_status, $a_isSelfLearning)
	{
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		$booked = $a_status == ilCourseBooking::STATUS_BOOKED;
		$automails = new gevCrsAutoMails($this->crs_id);

		if (!$a_isSelfLearning) {
			if ($booked) {
				$automails->send("self_booking_to_booked", array($this->user_id));
				$automails->send("invitation", array($this->user_id));
			} else {
				$automails->send("self_booking_to_waiting", array($this->user_id));
			}
		}

		ilUtil::sendSuccess(sprintf($booked ? $this->g_lng->txt("gev_was_booked_self")
											  : $this->g_lng->txt("gev_was_booked_waiting_self"), $this->crs_utils->getTitle()), true);

		ilUtil::redirect("dialog_login.php");
	}

	protected function toMaklerOffer()
	{
		ilUtil::redirect("dialog_login.php");
	}

	/**
	 * Custom form check
	 *
	 * @return mixed[]
	 */
	protected function checkForm()
	{
		$form = $this->buildRegistrationForm();
		$err = false;

		if (!$form->checkInput()) {
			$err = true;
		}

		if ($_POST["tou"] != 1) {
			$err = true;
			$chb = $form->getItemByPostVar("tou");
			$chb->setAlert($this->g_lng->txt("evg_mandatory"));
		}

		$connection = $form->getInput(gevExpressLoginUtils::F_CONNECTION);
		if($connection == gevExpressLoginUtils::V_CON_GEV) {
			$jobnumber = $form->getInput(gevExpressLoginUtils::F_GEV_MEDIATOR_NUMBER);
			if (!$this->checkJobnumber($jobnumber)) {
				$err = true;
				$form->getItemByPostVar(gevExpressLoginUtils::F_GEV_MEDIATOR_NUMBER)->setAlert($this->g_lng->txt("gev_evg_registration_not_found"));
			}
		}

		if($connection == gevExpressLoginUtils::V_CON_DIMAK) {
			$mediator_number = $form->getInput(gevExpressLoginUtils::F_DIMAK_MEDIATOR_NUMBER);
			if (!$this->checkDimakMediatorNumber($mediator_number)) {
				$err = true;
				$form->getItemByPostVar(gevExpressLoginUtils::F_DIMAK_MEDIATOR_NUMBER)->setAlert($this->g_lng->txt("gev_evg_registration_not_found"));
			}
		}

		return array($form, $err);
	}

	protected function checkDimakMediatorNumber($mediator_number)
	{
		return $this->getActions()->checkAgendNumber($mediator_number);
	}

	protected function checkJobNumber($jobnumber)
	{
		return $this->checkValidJobnumber($jobnumber) && $this->isAgent($jobnumber);
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
	 * Get the gev adp db.
	 *
	 * @return 	gevADPDB
	 */
	protected function getDB()
	{
		if ($this->gev_jobnumber_DB === null) {
			require_once("./Services/GEV/Import/classes/class.gevJobnumberDB.php");
			$this->gev_jobnumber_DB = new gevJobnumberDB($this->g_db);
		}

		return $this->gev_jobnumber_DB;
	}

	protected function getActions()
	{
		if(is_null($this->data_actions)) {
			require_once("Customizing/global/plugins/Services/Cron/CronHook/DiMAkImport/classes/class.ilDiMAkImportPlugin.php");
			$plugin = new ilDiMAkImportPlugin();
			$this->data_actions = $plugin->getDataActions();
		}

		return $this->data_actions;
	}

	/**
	 * Show registration form
	 *
	 * @param ilPropertyFormGUI | null 	$a_form
	 *
	 * @return string
	 */
	protected function startExpRegistration($a_form = null)
	{
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");

		$tpl = new ilTemplate("tpl.gev_express_registration.html", false, false, "Services/GEV/Registration");
		$title = new catTitleGUI("gev_login_express", null, "GEV_img/ico-head-registration.png");

		if ($a_form !== null) {
			$form = $a_form;
			$form->setValuesByPost();
		} else {
			$form = $this->buildRegistrationForm();
		}
		$tpl->setVariable("FORM", $form->getHTML());

		return $title->render()
				.$tpl->get();
	}

	/**
	 * Create express user registation form
	 *
	 * @return ilPropertyFormGUI
	 */
	protected function buildRegistrationForm()
	{
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilNumberInputGUI.php");
		require_once("Services/Form/classes/class.ilHiddenInputGUI.php");
		require_once("Services/Form/classes/class.ilEMailInputGUI.php");
		require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioOption.php");

		$form = new ilPropertyFormGUI();
		$crs_id = $_GET["crs_id"];
		$this->g_ctrl->setParameter($this, "crs_id", $crs_id);
		$form->setFormAction($this->g_ctrl->getFormAction($this));
		$form->addCommandButton('next', $this->g_lng->txt('next'));
		$form->setId("expresslogin");

		$regType = new ilRadioGroupInputGUI(null, "type");
		$regType->setValue("redirectLogin");

		$optOld = new ilRadioOption($this->g_lng->txt('gev_login_express_existend_account'));
		$optOld->setValue("redirectLogin");
		$regType->addOption($optOld);

		$optNew = new ilRadioOption($this->g_lng->txt('gev_login_express_new_account'));
		$optNew->setValue("redirectNewLogin");
		$regType->addOption($optNew);

		$this->crs_utils = gevCourseUtils::getInstance($crs_id);

		if(!$this->crs_utils->isSelflearning()) {
			$optExp = new ilRadioOption($this->g_lng->txt('gev_login_express_no_login'));
			$optExp->setValue("registerExpUser");
			$regType->addOption($optExp);
				$gender = new ilRadioGroupInputGUI($this->g_lng->txt("salutation"), "gender");
				$gender->addOption(new ilRadioOption($this->g_lng->txt("salutation_m"), "m"));
				$gender->addOption(new ilRadioOption($this->g_lng->txt("salutation_f"), "f"));
				$gender->setRequired(true);
				$optExp->addSubItem($gender);

				$inputName = new ilTextInputGUI($this->g_lng->txt('firstname'), "firstname");
				$inputName->setRequired(true);
				$optExp->addSubItem($inputName);

				$inputSurName = new ilTextInputGUI($this->g_lng->txt('lastname'), "lastname");
				$inputSurName->setRequired(true);
				$optExp->addSubItem($inputSurName);

				$inputInstitution = new ilTextInputGUI($this->g_lng->txt('gev_login_express_companyname'), "institution");
				$optExp->addSubItem($inputInstitution);

				$connection = new ilRadioGroupInputGUI($this->g_lng->txt("gev_connection"), gevExpressLoginUtils::F_CONNECTION);
				$option = new ilRadioOption($this->g_lng->txt("gev_mediator_number"), gevExpressLoginUtils::V_CON_GEV);
				$number = new ilTextInputGUI($this->g_lng->txt("gev_mediator_number_info"), gevExpressLoginUtils::F_GEV_MEDIATOR_NUMBER);
				$number->setSize(40);
				$number->setRequired(true);
				$option->addSubItem($number);
				$connection->addOption($option);

				$option = new ilRadioOption($this->g_lng->txt("dimak_mediator_number"), gevExpressLoginUtils::V_CON_DIMAK);
				$number = new ilTextInputGUI($this->g_lng->txt("dimak_mediator_number_info"), gevExpressLoginUtils::F_DIMAK_MEDIATOR_NUMBER);
				$number->setSize(40);
				$number->setRequired(true);
				$option->addSubItem($number);
				$connection->addOption($option);
				$connection->setRequired(true);
				$optExp->addSubItem($connection);

				$inputEMail = new ilEMailInputGUI($this->g_lng->txt('email'), "email");
				$inputEMail->setRequired(true);
				$optExp->addSubItem($inputEMail);

				$inputPhone = new ilTextInputGUI($this->g_lng->txt('gev_login_express_phone_number'), "phone");
				$inputPhone->setRequired(true);
				$optExp->addSubItem($inputPhone);

				$checkToU = new ilCheckboxInputGUI('', "tou");
				$checkToU->setOptionTitle($this->g_lng->txt('gev_login_express_agreement'));
				$checkToU->setValue(1);
				$checkToU->setChecked(false);
				$optExp->addSubItem($checkToU);
			}

			$form->addItem($regType);

		return $form;
	}
}
