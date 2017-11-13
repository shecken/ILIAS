<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';

ini_set("memory_limit", "2048M");
ini_set('max_execution_time', 0);
class ilObjReportEmplEduBios extends ilObjReportBase
{

	protected $relevant_parameters = array();
	const EARLIEST_CERT_START = "2013-09-01";

	public function initType()
	{
		 $this->setType("xeeb");
	}

	public function __construct($ref_id = 0)
	{
		parent::__construct($ref_id);
		global $lng;
		$this->gLng = $lng;
	}

	protected function createLocalReportSettings()
	{
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_reeb')
				->addSetting($this->s_f
								->settingBool('truncate_orgu_filter', $this->plugin->txt('truncate_orgu_filter')));
	}


	protected function pointsInCertYearSql($year)
	{
		$course_may_be_in_wbd = '('.$this->courseIsWbdBooked()
									.' OR '.$this->courseNotOlderThanOneYear().')';

		return   "SUM( IF (	usrcrs.begin_date >= usr.begin_of_certification + INTERVAL ".($year-1)." YEAR "
				."		AND usrcrs.begin_date < (usr.begin_of_certification + INTERVAL ".$year." YEAR)"
				."		AND ".$this->participationWBDRelevant()
				."		AND ".$course_may_be_in_wbd
				."		, FLOOR(usrcrs.credit_points / 3)"
				."		, 0"
				."		)"
				."	)";
	}

	protected function getRoleIdsForRoleTitles(array $titles)
	{
		$query = 'SELECT obj_id FROM object_data '
				.'	WHERE '.$this->gIldb->in('title', $titles, false, 'text')
				.'		AND type = '.$this->gIldb->quote('role', 'text');
		$res = $this->gIldb->query($query);
		$return = array();
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = $rec['obj_id'];
		}
		return $return;
	}

	protected function getWbdRelevantRoleIds()
	{
		return $this->getRoleIdsForRoleTitles(gevWBD::$wbd_relevant_roles);
	}

	protected function getTpServiceRoleIds()
	{
		return $this->getRoleIdsForRoleTitles(gevWBD::$wbd_tp_service_roles);
	}

	private function ifUserCertPeriodValidThenElse($statement_if_true, $statement_if_false)
	{
		return 'IF ( usr.begin_of_certification >= \''.self::EARLIEST_CERT_START.'\''
							.'   ,'.$statement_if_true
							.'   ,'.$statement_if_false.')';
	}

	public function buildQueryStatement()
	{
		$points_total = 'SUM( IF(usrcrs.credit_points > 0, usrcrs.credit_points, 0) )';

		$query =
			'SELECT'
			.'	usr.user_id'
			.'	,usr.lastname'
			.'	,usr.firstname'
			.'	,usrd.login'
			.'	,usr.adp_number'
			.'	,usr.job_number'
			.'	,orgu_all.org_unit'
			.'	,orgu_all.org_unit_above1'
			.'	,orgu_all.org_unit_above2'
			.'	,roles.roles'
			.'	,usr.begin_of_certification'
			.'	,'.$points_total.' as points_total_goa'
			.'	,'.$this->ifUserCertPeriodValidThenElse('usr.begin_of_certification', $this->gIldb->quote('-', 'text')).' as cert_period'
			.'	,'.$this->ifUserCertPeriodValidThenElse($this->pointsInCertYearSql(1), $this->gIldb->quote('-', 'text')).' as points_year1'
			.'	,'.$this->ifUserCertPeriodValidThenElse($this->pointsInCertYearSql(2), $this->gIldb->quote('-', 'text')).' as points_year2'
			.'	,'.$this->ifUserCertPeriodValidThenElse($this->pointsInCertYearSql(3), $this->gIldb->quote('-', 'text')).' as points_year3'
			.'	,'.$this->ifUserCertPeriodValidThenElse($this->pointsInCertYearSql(4), $this->gIldb->quote('-', 'text')).' as points_year4'
			.'	,'.$this->ifUserCertPeriodValidThenElse($this->pointsInCertYearSql(5), $this->gIldb->quote('-', 'text')).' as points_year5'
			.'	,'.$this->pointsInCurrentPeriod().' as points_sum'
			.'	,'.$this->attention().'	AS attention'
			.'	FROM hist_user usr'
			.'	JOIN usr_data usrd'
			.'		ON usr.user_id = usrd.usr_id'
			.'	JOIN ('.$this->WBDAndTPSRolesCount().') AS roles'
			.'		ON roles.usr_id = usr.user_id'
			.'	JOIN ('.$this->allOrgusOfUser().') AS orgu_all'
			.'		ON orgu_all.usr_id = usr.user_id'
			.'	LEFT JOIN hist_usercoursestatus as usrcrs'
			.'		ON usr.user_id = usrcrs.usr_id'
			.'			AND usrcrs.hist_historic = 0 '
			.'			AND usrcrs.credit_points > 0'
			.'			AND usrcrs.participation_status = \'teilgenommen\''
			.'			AND usrcrs.booking_status = \'gebucht\''
			.'			'.$this->filterWBDImported()
			.$this->whereConditions($query);

		$query .= '	GROUP BY usr.user_id'
					.$this->havingConditions()
					.'	'.$this->queryOrder();
		return $query;
	}

	private function whereConditions()
	{
		$where =
			'	WHERE '.$this->gIldb->in('usr.user_id', $this->relevant_users, false, 'integer')
			.' 		AND usr.hist_historic = 0';
		$where = $this->possiblyAddLastnameCondition($where);
		$where = $this->possiblyAddWbdRelevantCondition($where);
		return $where;
	}

	private function havingConditions()
	{
		$having_critical = $this->havingCritical();
		$having_critical_fourth = $this->havingCriticalFourth();
		$havings = array();
		if ($having_critical !== '') {
			$havings[] = $having_critical;
		}
		if ($having_critical_fourth !== '') {
			$havings[] = $having_critical_fourth;
		}
		if (count($havings) > 0) {
			return '	HAVING '.implode(' AND ', $havings);
		}
		return '';
	}

	private function havingCritical()
	{
		$selection = $this->filter_selections['critical'];
		if ($selection) {
			return 'attention = \'X\'';
		}
		return '';
	}

	private function havingCriticalFourth()
	{
		$selection = $this->filter_selections['critical_4th'];
		if ($selection) {
			$cert_year_sql =
				' YEAR( CURDATE( ) ) - YEAR( begin_of_certification ) '
					.'- ( DATE_FORMAT( CURDATE( ) , \'%m%d\' ) < DATE_FORMAT( begin_of_certification, \'%m%d\' ) )';
			return 'begin_of_certification >= \''.self::EARLIEST_CERT_START.'\' AND '.
						$cert_year_sql.' = 4 AND attention = \'X\'';
		}
		return '';
	}

	private function possiblyAddLastnameCondition($where)
	{
		$input = $this->filter_selections['lastname'];
		if (is_string($input) && $input !== '') {
			return $where.'		AND usr.lastname LIKE '.$this->gIldb->quote($input.'%', 'text');
		}
		return $where;
	}

	private function possiblyAddWbdRelevantCondition($where)
	{
		$selection = $this->filter_selections['wbd_relevant'];
		if ($selection) {
			return $where.'		AND (roles.num_wbd_roles > 0 '
						.'			OR usr.okz != '.$this->gIldb->quote("-empty-", 'text').')';
		}
		return $where;
	}

	private function filterWBDImported()
	{
		$selection = $this->filter_selections['no_wbd_imported'];
		if ($selection) {
			return 'AND usrcrs.crs_id > 0';
		}
		return '';
	}

	private function allOrgusOfUser()
	{
		$selection = $this->filter_selections['orgu_selection'];
		if ($selection === null) {
			if ($this->get) {
			}
		}
		return
			'SELECT usr_id, GROUP_CONCAT(DISTINCT orgu_title SEPARATOR \', \') as org_unit'.PHP_EOL
			.'		, GROUP_CONCAT(DISTINCT org_unit_above1 SEPARATOR \';;\') as org_unit_above1'.PHP_EOL
			.'		, GROUP_CONCAT(DISTINCT org_unit_above1 SEPARATOR \';;\') as org_unit_above2'.PHP_EOL
			.'	FROM hist_userorgu'.PHP_EOL
			.'	WHERE '.$this->gIldb->in("usr_id", $this->relevant_users, false, "integer").PHP_EOL
			.'		AND action >= 0 AND hist_historic = 0'.PHP_EOL
			.'	GROUP BY usr_id'.PHP_EOL;
	}

	private function WBDAndTPSRolesCount()
	{
		$wbd_relevant_condition = $this->gIldb->in('rol_id', $this->getWbdRelevantRoleIds(), false, 'integer');
		$tp_service_condition = $this->gIldb->in('rol_id', $this->getTpServiceRoleIds(), false, 'integer');
		return
			'SELECT usr_id'.PHP_EOL
			.'	,SUM(IF('.$wbd_relevant_condition.',1,0)) AS num_wbd_roles'.PHP_EOL
			.'	,SUM(IF('.$tp_service_condition.',1,0)) AS num_tp_service_roles'.PHP_EOL
			.'	,GROUP_CONCAT(DISTINCT rol_title ORDER BY rol_title ASC SEPARATOR \', \') AS roles '.PHP_EOL
			.'	FROM hist_userrole '.PHP_EOL
			.'	WHERE action >= 0 AND hist_historic = 0 '.PHP_EOL
			.'		AND '.$this->gIldb->in("usr_id", $this->relevant_users, false, 'integer').PHP_EOL
			.'	GROUP BY usr_id '.PHP_EOL;
	}

	private function attention()
	{
		$no_tp_service_condition =
			"(roles.num_tp_service_roles = 0"
			."	AND usr.wbd_type != ".$this->gIldb->quote(gevWBD::WBD_TP_SERVICE, "text")
			.")";
		$tp_service_condition =
			"(roles.num_tp_service_roles > 0"
			."	OR usr.wbd_type = ".$this->gIldb->quote(gevWBD::WBD_TP_SERVICE, "text")
			.")";

		$cert_year_sql = " YEAR( CURDATE( ) ) - YEAR( begin_of_certification ) "
						."- ( DATE_FORMAT( CURDATE( ) , '%m%d' ) < DATE_FORMAT( begin_of_certification, '%m%d' ) )";
		return
			'CASE '.PHP_EOL
			.'	WHEN '.$no_tp_service_condition.' THEN \'\''.PHP_EOL
			.'		WHEN '.$tp_service_condition.PHP_EOL
			.'			 AND usr.begin_of_certification <= '.$this->gIldb->quote(self::EARLIEST_CERT_START, 'date').' THEN \'X\''.PHP_EOL
			.'		WHEN '.$cert_year_sql.' = 1 AND '.$this->pointsInCurrentPeriod().' < 40 THEN \'X\''.PHP_EOL
			.'		WHEN '.$cert_year_sql.' = 2 AND '.$this->pointsInCurrentPeriod().' < 80 THEN \'X\''.PHP_EOL
			.'		WHEN '.$cert_year_sql.' = 3 AND '.$this->pointsInCurrentPeriod().' < 120 THEN \'X\''.PHP_EOL
			.'		WHEN '.$cert_year_sql.' = 4 AND '.$this->pointsInCurrentPeriod().' < 160 THEN \'X\''.PHP_EOL
			.'		ELSE \'\''.PHP_EOL
			.'END'.PHP_EOL;
	}


	private function pointsInCurrentPeriod()
	{
		$course_takes_place_in_cert_period =
				'	usrcrs.begin_date >= usr.begin_of_certification'
				.'		AND usrcrs.begin_date < (usr.begin_of_certification + INTERVAL 5 YEAR)';


		$course_may_be_in_wbd = '('.$this->courseIsWbdBooked()
									.' OR '.$this->courseNotOlderThanOneYear().')';

		return 'SUM( IF ('.$course_takes_place_in_cert_period
				.'		AND '.$this->participationWBDRelevant()
				.'		AND '.$course_may_be_in_wbd
				."        , usrcrs.credit_points"
				."        , 0"
				."        )"
				."   )";
	}


	private function courseIsWbdBooked()
	{
		return '(usrcrs.wbd_booking_id != \'-empty-\' AND usrcrs.wbd_booking_id IS NOT NULL )';
	}

	private function courseNotOlderThanOneYear()
	{
		return 'usrcrs.end_date >= '.$this->oneYearBeforeNow();
	}

	private function participationWBDRelevant()
	{
		return $this->gIldb->in('usrcrs.okz', array('OKZ1','OKZ2','OKZ3'), false, 'text');
	}

	private function oneYearBeforeNow()
	{
		return $this->gIldb->quote((new DateTime())->sub(new DateInterval('P1Y'))->format('Y-m-d'), 'date');
	}

	protected function getFilterSettings()
	{
		$filter = $this->filter();
		if ($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
		}
		return $settings;
	}

	protected function buildQuery($query)
	{
		$this->filter_selections = $this->getFilterSettings();
		if ($this->filter_selections['orgu_selection'] !== null) {
			$this->relevant_users
				= $this->getRelevantUsersByOrguSelection(
					$this->filter_selections['orgu_selection'],
					$this->filter_selections['recursive']
				);
		} else {
			$this->relevant_users = $this->getRelevantUsersByOrguSelection(
				$this->defaultOrguChoice()
			);
		}
		return $query;
	}



	protected function buildFilter($filter)
	{
		return $filter;
	}

	public function filter()
	{
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);

		$txt = function ($id) {
			return $this->plugin->txt($id);
		};
		global $lng;
		$self = $this;

		return
			$f->sequence(
				$f->option(
					$txt('show_critical_persons'),
					''
				),
				$f->option(
					$txt('show_critical_persons_4th_year'),
					''
				),
				$f->option(
					$txt('wbd_relevant_only'),
					''
				),
				$f->option(
					$txt('filter_no_wbd_imported'),
					''
				),
				$f->option(
					$lng->txt('gev_org_unit_recursive'),
					''
				)->clone_with_checked(!(bool)$this->getSettingsDataFor("truncate_orgu_filter")),
				$f->multiselect(
					$lng->txt("gev_org_unit_short"),
					'',
					$this->getRelevantOrgus()
				)->default_choice($this->defaultOrguChoice()),
				$f->text(
					$txt('lastname_filter'),
					''
				)
			)->map(function ($critical, $critical_fourth, $wbd_relevant, $no_wbd_imp, $recursive, $orgu_selection, $lastname) use ($self) {
								return array(
									'critical' => $critical
									,'critical_4th' => $critical_fourth
									,'wbd_relevant' => $wbd_relevant
									,'no_wbd_imp' => $no_wbd_imp
									,'recursive' => $recursive
									,'orgu_selection' => $orgu_selection
									,'lastname' => $lastname);
			}, $tf->dict(
				array(
								'critical' => $tf->bool()
								,'critical_4th' => $tf->bool()
								,'wbd_relevant' => $tf->bool()
								,'no_wbd_imp' => $tf->bool()
								,'recursive' => $tf->bool()
								,'orgu_selection' => $tf->lst($tf->int())
								,'lastname' => $tf->string()
								)
			));
	}


	private function getRelevantOrgus()
	{
		$orgu_refs = $this->user_utils->getOrgUnitsWhereUserCanViewEduBios();
		require_once "Services/GEV/Utils/classes/class.gevObjectUtils.php";
		$return = array();
		foreach ($orgu_refs as $ref_id) {
			$obj_id = gevObjectUtils::getObjId($ref_id);
			$return[$obj_id] = ilObject::_lookupTitle($obj_id);
		}
		return $return;

		//only truncate orgu filter settings if set
	}

	private function defaultOrguChoice()
	{
		if ((bool)$this->getSettingsDataFor("truncate_orgu_filter")) {
			return array_map(function ($v) {
				return $v["obj_id"];
			}, $this->user_utils->getOrgUnitsWhereUserIsDirectSuperior());
		}
		return array();
	}

	private function getRelevantUsersByOrguSelection(array $orgu_ids = array(), $recursive = false)
	{

		if (count($orgu_ids) > 0) {
			if ($recursive) {
				$orgu_ids = $this->addRecursiveOrgusToSelection($orgu_ids);
			}
			$query = 'SELECT usr_id FROM hist_userorgu'
					.'	WHERE hist_historic = 0 AND action >=0'
					.'		AND '.$this->gIldb->in('orgu_id', $orgu_ids, false, 'integer')
					.'	GROUP BY usr_id';
			$res = $this->gIldb->query($query);
			$aux = array();
			while ($rec = $this->gIldb->fetchAssoc($res)) {
				$aux[] = $rec['usr_id'];
			}
			return array_intersect($this->visibleUsers(), $aux);
		}
		return $this->visibleUsers();
	}

	private function addRecursiveOrgusToSelection(array $orgu_ids)
	{
		require_once 'Services/GEV/Utils/classes/class.gevOrgUnitUtils.php';
		$aux = $orgu_ids;
		foreach ($orgu_ids as $orgu_id) {
			$ref_id = gevObjectUtils::getRefId($orgu_id);
			$aux[] = $orgu_id;
			foreach (gevOrgUnitUtils::getAllChildren(array($ref_id)) as $child) {
				$aux[] = $child["obj_id"];
			}
		}
		return array_unique($aux);
	}

	private function visibleUsers()
	{
		return $this->user_utils->getEmployeesWhereUserCanViewEduBios();
	}

	protected function buildTable($table)
	{
		$table
						->column("lastname", $this->plugin->txt("lastname"), true)
						->column("firstname", $this->plugin->txt("firstname"), true)
						->column("points_sum", $this->plugin->txt("overall_points_cert_period"), true)
						->column("points_total_goa", $this->plugin->txt("overall_points_goa"), true)
						->column("cert_period", $this->plugin->txt("cert_period"), true)
						->column("attention", $this->plugin->txt("critical"), true)
						->column("login", $this->plugin->txt("login"), true)
						->column("adp_number", $this->plugin->txt("adp_number"), true)
						->column("job_number", $this->plugin->txt("job_number"), true)
						->column("od_bd", $this->plugin->txt("od_bd"), true, "", false, false)
						->column("org_unit", $this->plugin->txt("orgu_short"), true)
						->column("roles", $this->plugin->txt("roles"), true)
						->column("points_year1", "1", true)
						->column("points_year2", "2", true)
						->column("points_year3", "3", true)
						->column("points_year4", "4", true)
						->column("points_year5", "5", true);
		return parent::buildTable($table);
	}

	public function buildOrder($order)
	{
		$order->mapping("date", "crs.begin_date")
				->mapping("od_bd", array("org_unit_above1", "org_unit_above2"))
				->defaultOrder("lastname", "ASC")
				;
		return $order;
	}

	protected function getRowTemplateTitle()
	{
		return "tpl.gev_employee_edu_bios_row.html";
	}


	public function getRelevantParameters()
	{
		return $this->relevant_parameters;
	}
}
