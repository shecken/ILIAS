<?php

class gevEffectivenessAnalysis {
	

	const F_TITLE = "title";
	const F_ORG_UNIT = "org_unit";
	const F_LOGIN = "login";
	const F_FINISHED = "finished";
	const F_PERIOD = "period";
	const F_PREFIX = "filter_";

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
		$orgus = $this->getOrgunitsOf($user_id, $filter[self::F_ORG_UNIT]);
		if(empty($orgus) || count($orgus) == 1 && !$orgus[0]) {
			return array();
		}

		$login_id = -1;
		if(isset($filter[self::F_LOGIN]) && $filter[self::F_LOGIN] != "") {
			$login_id = ilObjUser::_lookupId($filter[self::F_LOGIN]);
		}
		$my_employees = $this->getEmployees($orgus, $login_id);

		if(empty($my_employees) || count($my_employees) == 1 && !$my_employees[0]) {
			return array();
		}

		return $this->eff_analysis_db->getEffectivenessAnalysisData($my_employees, self::$reason_for_eff_analysis, $filter, $offset, $limit, $order, $order_direction);
	}

	public function getCountEffectivenessAnalysis($user_id, array $filter) {
		$orgus = $this->getOrgunitsOf($user_id, $filter[self::F_ORG_UNIT]);
		if(empty($orgus) || count($orgus) == 1 && !$orgus[0]) {
			return 0;
		}

		$login_id = -1;
		if(isset($filter[self::F_LOGIN]) && $filter[self::F_LOGIN] != "") {
			$login_id = ilObjUser::_lookupId($filter[self::F_LOGIN]);
		}
		$my_employees = $this->getEmployees($orgus, $login_id);

		if(empty($my_employees) || count($my_employees) == 1 && !$my_employees[0]) {
			return 0;
		}

		return $this->eff_analysis_db->getCountEffectivenessAnalysisData($my_employees, self::$reason_for_eff_analysis, $filter);
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

	protected function getOrgunitTitle() {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$orgus = gevOrgUnitUtils::getAllChildren(array(56));
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

	public function getFilter($user_id) {
		require_once("Services/GEV/Reports/classes/class.catFilter.php");
		return catFilter::create()
						->dateperiod( self::F_PERIOD
									, $this->gLng->txt("gev_period")
									, $this->gLng->txt("gev_until")
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
									 , $this->getOrgunitTitle($user_id)
									 , array()
									 )
						->textinput(self::F_TITLE
									, $this->gLng->txt("gev_eff_analysis_title").":"
									, "")
						->textinput(self::F_LOGIN
									, $this->gLng->txt("gev_eff_analysis_login").":"
									, "");
	}

	public function getFilterValues($post) {
		$filter_values = array();
		
		if(isset($post[self::F_PREFIX.self::F_FINISHED])) {
			$filter_values[self::F_FINISHED] = (bool)$post[self::F_PREFIX.self::F_FINISHED];
		}

		if(isset($post[self::F_PREFIX.self::F_PERIOD])) {
			$start_arr = $post[self::F_PREFIX.self::F_PERIOD]["start"]["date"];
			$end_arr = $post[self::F_PREFIX.self::F_PERIOD]["end"]["date"];

			$filter_values[self::F_PERIOD] = array("start"=>$this->createDate($start_arr, "00:00:00"), "end"=>$this->createDate($end_arr, "23:59:59"));
		}
		
		if(isset($post[self::F_PREFIX.self::F_TITLE])) {
			$filter_values[self::F_TITLE] = $post[self::F_PREFIX.self::F_TITLE];
		}

		if(isset($post[self::F_PREFIX.self::F_LOGIN])) {
			$filter_values[self::F_LOGIN] = $post[self::F_PREFIX.self::F_LOGIN];
		}

		if(isset($post[self::F_PREFIX.self::F_ORG_UNIT])) {
			$filter_values[self::F_ORG_UNIT] = $post[self::F_PREFIX.self::F_ORG_UNIT];
			unset($filter_values[self::F_ORG_UNIT]["send"]);
		}

		return $filter_values;
	}

	protected function createDate($values, $time) {
		$m = $this->padLeading($values["m"]);
		$d = $this->padLeading($values["d"]);

		return new ilDateTime($values["y"]."-".$m."-".$d." ".$time, IL_CAL_DATETIME);
	}

	protected function padLeading($value) {
		return str_pad($value, 2, "0", STR_PAD_LEFT);
	}
}