<?php

class gevEffectivenessAnalysis {
	const F_TITLE = "title";
	const F_ORG_UNIT = "org_unit";
	const F_LOGIN = "login";
	const F_FINISHED = "finished";
	const F_PERIOD = "period";
	const F_PREFIX = "filter_";
	const F_RESULT = "result";
	const F_STATUS = "status";
	const F_SUPERIOR = "superior";
	const RESULT_PREFIX = "result";
	const STATE_FNISHED = "gev_eff_analysis_rep_finished";
	const STATE_OPEN = "gev_eff_analysis_rep_open";
	const STATE_FILTER_FINISHED = "rep_finished";
	const STATE_FILTER_OPEN = "rep_open";
	const STATE_FILTER_ALL = "rep_all";

	static $instance = null;
	static $reason_for_eff_analysis = array("D - Maßnahmen aus Defizit", "R - Rechtliche Anforderung, Pflichtschulung");

	public function __construct() {
		global $ilCtrl, $lng, $ilDB;

		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		$this->gDB = $ilDB;

		require_once("Services/GEV/Desktop/classes/EffectivenessAnalysis/class.gevEffectivenessAnalysisDB.php");
		$this->eff_analysis_db = new gevEffectivenessAnalysisDB($ilDB);
	}

	public static function getInstance() {
		if(self::$instance === null) {
			self::$instance = new gevEffectivenessAnalysis();
		}

		return self::$instance;
	}

	/**
	 * Get effectivness analysis for user
	 *
	 * @param int 		$user_id
	 * @param mixed[] 	$filter
	 * @param int 		$offset
	 * @param ini 		$limit
	 * @param string 	$order
	 * @param string 	$order_direction
	 *
	 * @return array<mixed[]>
	 */
	public function getEffectivenessAnalysis($user_id, array $filter, $offset, $limit, $order, $order_direction) {
		$my_employees = $this->getMyEmployees($user_id, $filter[self::F_ORG_UNIT], $filter[self::F_LOGIN]);

		if(empty($my_employees)) {
			return array();
		}

		return $this->eff_analysis_db->getEffectivenessAnalysisData($my_employees, self::$reason_for_eff_analysis, $filter, $offset, $limit, $order, $order_direction);
	}

	public function getCountEffectivenessAnalysis($user_id, array $filter) {
		$my_employees = $this->getMyEmployees($user_id, $filter[self::F_ORG_UNIT], $filter[self::F_LOGIN]);

		if(empty($my_employees) || count($my_employees) == 1 && !$my_employees[0]) {
			return 0;
		}

		return $this->eff_analysis_db->getCountEffectivenessAnalysisData($my_employees, self::$reason_for_eff_analysis, $filter);
	}

	public function saveResult($crs_id, $user_id, $result, $result_info) {
		$this->eff_analysis_db->saveResult($crs_id, $user_id, $result, $result_info);
	}

	public function getEffectivenessAnalysisReportData($user_id, array $filter, $order, $order_direction) {
		if($superior_id = $this->getIdOfSearchedSupervisor($filter)) {
			$my_employees = $this->getMyEmployees($superior_id, $filter[self::F_ORG_UNIT], $filter[self::F_LOGIN]);
		} else {
			$my_employees = $this->getMyEmployees($user_id, $filter[self::F_ORG_UNIT], $filter[self::F_LOGIN]);
		}

		if(empty($my_employees)) {
			return array();
		}

		return $this->eff_analysis_db->getEffectivenessAnalysisReportData($my_employees, self::$reason_for_eff_analysis, $filter, $order, $order_direction);
	}

	protected function getMyEmployees($user_id, $filter_orgunit, $filter_user) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstance($user_id);

		$login_id = -1;
		if(isset($filter_user) && $filter_user != "") {
			$login_id = ilObjUser::_lookupId($filter_user);
		}

		$my_employees = array();
		if($user_utils->isAdmin()) {
			$my_employees = $this->getAllPeopleIn(ilObjOrgUnit::getRootOrgRefId(), $login_id);
		} else {
			$orgus = $this->getOrgunitsOf($user_id, $filter_orgunit);
			if(empty($orgus) || (count($orgus) == 1 && !$orgus[0])) {
				return array();
			}

			$my_employees = $this->getEmployees($orgus, $login_id);

			if(empty($my_employees) || (count($my_employees) == 1 && !$my_employees[0])) {
				return array();
			}
		}

		return $my_employees;
	}

	protected function getOrgunitsOf($user_id, $filter_orgus) {
		$user_utils = gevUserUtils::getInstance($user_id);
		$orgus = $user_utils->getOrgUnitsWhereUserIsDirectSuperior();

		$orgus = array_map(function($orgu) use ($filter_orgus) {
			if(!empty($filter_orgus)) {
				require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
				$orgu_utils = gevOrgUnitUtils::getInstance($orgu["obj_id"]);
				if(in_array($orgu_utils->getTitle(), $filter_orgus)) {
					return $orgu["ref_id"];
				}
			} else {
				return $orgu["ref_id"];
			}
		}, $orgus);

		return $orgus;
	}

	/**
	 * Get all orgunit titles where user is direct superior
	 *
	 * @param int $user_id
	 *
	 * @return sting[]
	 */
	protected function getOrgunitTitlesOf($user_id) {
		$user_utils = gevUserUtils::getInstance($user_id);
		return $user_utils->getOrgUnitNamesWhereUserIsDirectSuperior();

	}

	public function getOrgunitTitle() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$orgus = gevOrgUnitUtils::getAllChildren(array(ilObjOrgUnit::getRootOrgRefId()));
		$orgus = array_map(function($orgu) {
			require_once("Services/Object/classes/class.ilObject.php");
			return ilObject::_lookupTitle($orgu["obj_id"]);
		}, $orgus);

		return $orgus;
	}

	protected function getEmployees($orgus, $login_id) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

		$empl = array_map(function($usr_id) use ($login_id) {
			if($login_id !== -1) {
				if($login_id == $usr_id) {
					return $usr_id;
				}
			} else {
				return $usr_id;
			}
		}, gevOrgUnitUtils::getEmployeesIn($orgus));

		return array_filter($empl, function($id) {
			if($id !== null) {
				return $id;
			}
		});
	}

	protected function getAllPeopleIn($org_unit_ref_id, $login_id) {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");

		$empl = array_map(function($usr_id) use ($login_id) {
			if($login_id !== -1) {
				if($login_id == $usr_id) {
					return $usr_id;
				}
			} else {
				return $usr_id;
			}
		}, gevOrgUnitUtils::getAllEmployees(array($org_unit_ref_id)));

		return array_filter($empl, function($id) {
			if($id !== null) {
				return $id;
			}
		});
	}

	public function getFilter($user_id) {
		require_once("Services/GEV/Reports/classes/class.catFilter.php");
		return catFilter::create()
						->dateperiod( self::F_PERIOD
									, $this->gLng->txt("gev_period")
									, $this->gLng->txt("gev_eff_analysis_show_until")
									, null
									, null
									, date("Y")."-01-01"
									, date("Y")."-12-31"
									, false
									, null
									)
						->checkbox(self::F_FINISHED
									, $this->gLng->txt("gev_eff_analysis_show_finished")
									, ""
									, ""
									, "")
						->multiselect(self::F_ORG_UNIT
									 , $this->gLng->txt("gev_eff_analysis_org_unit")
									 , null
									 , $this->getOrgunitTitle()
									 , array()
									 )
						->textinput(self::F_TITLE
									, $this->gLng->txt("gev_eff_analysis_title").":"
									, "")
						->textinput(self::F_LOGIN
									, $this->gLng->txt("gev_eff_analysis_login").":"
									, "");
	}

	public function getReportFilter() {
		require_once("Services/GEV/Reports/classes/class.catFilter.php");
		return catFilter::create()
						->dateperiod( self::F_PERIOD
									, $this->gLng->txt("gev_period")
									, $this->gLng->txt("gev_eff_analysis_show_until")
									, "usrcrs.begin_date"
									, "usrcrs.end_date"
									, date("Y")."-01-01"
									, date("Y")."-12-31"
									)
						->multiselect(self::F_STATUS
									 , $this->gLng->txt("gev_eff_analysis_status")
									 , null
									 , $this->getStatusOptions()
									 , array()
									 )
						->multiselect(self::F_ORG_UNIT
									 , $this->gLng->txt("gev_eff_analysis_org_unit")
									 , null
									 , $this->getOrgunitTitle()
									 , array()
									 )
						->multiselect(self::F_RESULT
									 , $this->gLng->txt("gev_eff_analysis_result")
									 , null
									 , $this->getResultOptions()
									 , array()
									 )
						->textinput(self::F_TITLE
									, $this->gLng->txt("gev_eff_analysis_title").":"
									, "")
						->textinput(self::F_LOGIN
									, $this->gLng->txt("gev_eff_analysis_member").":"
									, "")
						->textinput(self::F_SUPERIOR
									, $this->gLng->txt("gev_eff_analysis_superior").":"
									, "")
						;
	}

	public function buildFilterValuesFromFilter($filter) {
		$filter_values = array();

		if($filter->filterExists(self::F_FINISHED) && $filter->get(self::F_FINISHED)) {
			$filter_values[self::F_FINISHED] = self::STATE_FILTER_FINISHED;
		} else {
			$filter_values[self::F_FINISHED] = self::STATE_FILTER_OPEN;
		}

		if($filter->filterExists(self::F_PERIOD)) {
			$date = $filter->get(self::F_PERIOD);
			$filter_values[self::F_PERIOD] = array("start"=>$this->createDate($date["start"]->get(IL_CAL_DATE), "00:00:00")
											 , "end"=>$this->createDate($date["end"]->get(IL_CAL_DATE), "23:59:59"));
		}

		if($filter->filterExists(self::F_TITLE)) {
			$title = $filter->get(self::F_TITLE);
			if($title != "") {
				$filter_values[self::F_TITLE] = $title;
			}
		}

		if($filter->filterExists(self::F_LOGIN)) {
			$login = $filter->get(self::F_LOGIN);
			if($login != "") {
				$filter_values[self::F_LOGIN] = $login;
			}
		}

		if($filter->filterExists(self::F_ORG_UNIT)) {
			$orgunit = $filter->get(self::F_ORG_UNIT);
			if(!empty($orgunit)) {
				$filter_values[self::F_ORG_UNIT] = $orgunit;
			}
		}

		//additional filter values for report
		if($filter->filterExists(self::F_SUPERIOR)) {
			$superior = $filter->get(self::F_SUPERIOR);
			if($superior != "") {
				$filter_values[self::F_SUPERIOR] = $superior;
			}
		}

		$filter_values[self::F_STATUS] = self::STATE_FILTER_ALL;
		if($filter->filterExists(self::F_STATUS)) {
			$status = $filter->get(self::F_STATUS);
			if(!empty($status)) {
				if(count($status) == 1) {
					$val = $status[0];

					if($val == $this->gLng->txt(self::STATE_FNISHED)) {
						$filter_values[self::F_STATUS] = self::STATE_FILTER_FINISHED;
					} elseif($val == $this->gLng->txt(self::STATE_OPEN)) {
						$filter_values[self::F_STATUS] = self::STATE_FILTER_OPEN;
					}
				}
			}
		}

		if($filter->filterExists(self::F_RESULT)) {
			$result = $filter->get(self::F_RESULT);
			if(!empty($result)) {
				$result = array_map(function($res) {
					$ar = explode("-", $res);
					return trim($ar[0]);
				}, $result);
				$filter_values[self::F_RESULT] = $result;
			}
		}

		return $filter_values;
	}

	protected function createDate($date, $time) {
		return new ilDateTime($date." ".$time, IL_CAL_DATETIME);
	}

	protected function padLeading($value) {
		return str_pad($value, 2, "0", STR_PAD_LEFT);
	}

	public function getResultText($result_id) {
		return $this->gLng->txt(self::RESULT_PREFIX."_".$result_id);
	}

	public function checkInfoIsRequired($result, $result_text) {
		var_dump($result);
		var_dump(in_array($result, array(1,2,3)));
		if(in_array($result, array(1,2,3)) && $result_text == "") {
			return true;
		}

		return false;
	}

	public function getResultOptions() {
		return array(0 => $this->getResultText(0)
					,1 => $this->getResultText(1)
					,2 => $this->getResultText(2)
					,3 => $this->getResultText(3)
					,4 => $this->getResultText(4)
					,5 => $this->getResultText(5)
					,6 => $this->getResultText(6)
				);
	}

	protected function getStatusOptions() {
		return array($this->gLng->txt(self::STATE_FNISHED), $this->gLng->txt(self::STATE_OPEN));
	}

	public function getIdOfSearchedSupervisor($filter_values) {
		if(isset($filter_values[self::F_SUPERIOR])) {
			return ilObjUser::_lookupId($filter_values[self::F_SUPERIOR]);
		}
		
		return false;
	}

	public function isEmployeeOf($user_id, $superior_id) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstance($user_id);
		return $user_utils->isEmployeeOf($superior_id);
	}

	public function getSuperiorOf($user_id) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstance($user_id);
		$superiors = array_map(function($sup) {
			$sup_data = ilObjUser::_lookupName($sup);
			return $sup_data["lastname"].", ".$sup_data["firstname"];
		}, $user_utils->getDirectSuperiors());

		return implode("<br />", $superiors);
	}
}