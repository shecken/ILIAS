<?php
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

class gevExpressLoginUtils {
	const F_CONNECTION = "f_connection";
	const F_GEV_MEDIATOR_NUMBER = "gev_mediator_number";
	const F_DIMAK_MEDIATOR_NUMBER = "dimak_mediator_number";

	const V_CON_GEV = "gev";
	const V_CON_DIMAK = "dimak";

	static protected $instance = null;

	protected static $VALID_AGENT_STATUSES = [
		"608",
		"650",
		"651",
		"674",
		"679"
	];
	
	protected function __construct(){
		global $ilDB;
		$this->db = $ilDB;
	}

	static public function getInstance() {
		if (self::$instance === null) {
			self::$instance = new gevExpressLoginUtils();
		}
		
		return self::$instance;
	}

	//register a new express user
	//param $a_form Form for user Data
	public function registerExpressUser($a_form){
		$this->user = new ilObjUser();
		$this->user->setLogin("expr_".$a_form->getInput("firstname").$a_form->getInput("lastname"));
		$this->user->setGender($a_form->getInput("gender"));
		$this->user->setEmail($a_form->getInput("email"));
		$this->user->setLastname($a_form->getInput("lastname"));
		$this->user->setFirstname($a_form->getInput("firstname"));
		$this->user->setInstitution($a_form->getInput("institution"));
		$this->user->setPhoneOffice($a_form->getINput("phone"));

		// is not active, owner is root
		$this->user->setActive(0, 6);
		$this->user->setTimeLimitUnlimited(true);
		// user already agreed at registration
		$now = new ilDateTime(time(),IL_CAL_UNIX);
		$this->user->setAgreeDate($now->get(IL_CAL_DATETIME));
		$this->user->setIsSelfRegistered(true);
		
		$this->user->create();
		$this->user->saveAsNew();

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstanceByObj($this->user);

		$user_utils->setCompanyName($a_form->getInput("institution"));
		$connection = $a_form->getInput(self::F_CONNECTION);
		if($connection == self::V_CON_GEV) {
			$jobnumber = $a_form->getInput(self::F_GEV_MEDIATOR_NUMBER);
			$data = $this->getEntriesForJobnumber($jobnumber);

			$user_utils->setADPNumberGEV($data["jobnumber"]);
			$user_utils->setJobNumber($data["jobnumber"]);
			$user_utils->setAgentPositionVFS($data["vms_text"]);
		}

		if($connection == self::V_CON_DIMAK) {
			$jobnumber = $a_form->getInput(self::F_DIMAK_MEDIATOR_NUMBER);
			$user_utils->setADPNumberGEV($jobnumber);
			$user_utils->setJobNumber($jobnumber);
		}

		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$role_utils = gevRoleUtils::getInstance();
		$role_utils->assignUserToGlobalRole($this->user->getId(), "ExpressUser");

		$this->user->setActive(true, 6);
		$this->user->update();

		return $this->user->getID();
	}

	/**
	 * Check for valid jobnumber.
	 *
	 * @param 	string 	$jobnumber
	 * @return 	bool
	 */
	public function isValidStellennummer($jobnumber)
	{
		assert('is_string($jobnumber)');

		return $this->getImport()->checkForJobnumber($jobnumber);
	}

	/**
	 * Check whether 'vermittlerstatus' is a agent status.
	 *
	 * @param 	string 	$jobnumber
	 * @return 	bool	true = is a agent; false = isn't a agent
	 */
	public function isAgent($jobnumber)
	{
		assert('is_string($jobnumber)');

		$status = $this->getImport()->getAgentStatus($jobnumber);

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

		return $this->getImport()->getEntryByJobnumber($jobnumber);
	}

	public function setExpressUserExperienceDate($a_usr_id){
		if($this->isExpressUser($a_usr_id)){
			require_once("Services/GEV/Utils/classes/class.gevSettings.php");

			$gev_settings = gevSettings::getInstance();
			$exit_udf_field_id = $gev_settings->getUDFFieldId(gevSettings::USR_UDF_EXIT_DATE);
			$sql = "UPDATE udf_text "
				  ."   SET value = CURDATE()"
				  ." WHERE usr_id = ".$this->db->quote($a_usr_id, "integer")
				  ."   AND field_id = ".$this->db->quote($exit_udf_field_id, "integer");
			$res = $this->db->query($sql);
		}
	}

	public function isExpressUser($a_usr_id) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$usrUtil = gevUserUtils::getInstance($a_usr_id);
		$globalRoles = $usrUtil->getGlobalRoles();

		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$roleUtils = gevRoleUtils::getInstance();
		$roleTitles = $roleUtils->getGlobalRolesTitles($globalRoles);
		
		$isExpressUser = false;

		foreach ($roleTitles as $key => $title) {
			if($title == "ExpressUser"){
				$isExpressUser = true;
				break;
			}
		}

		return $isExpressUser;
	}

	/**
	 * Get the gev adp db.
	 *
	 * @return 	gevADPDB
	 */
	protected function getImport()
	{
		if ($this->gev_jobnumber_DB === null) {
			require_once("./Services/GEV/Import/classes/class.gevJobnumberDB.php");
			$this->gev_jobnumber_DB = new gevJobnumberDB($this->db);
		}

		return $this->gev_jobnumber_DB;
	}
}
