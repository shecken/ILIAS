<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

ini_set("memory_limit", "2048M");
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportBookingsByTpl extends ilObjReportBase
{
	protected $relevant_parameters = array();
	protected $sum_parts = array();
	protected $sum_table;

	public function __construct($ref_id = 0)
	{
		parent::__construct($ref_id);
		require_once $this->plugin->getDirectory().'/config/cfg.bk_by_tpl.php';
	}

	protected function createLocalReportSettings()
	{
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rbbt');
	}

	public function initType()
	{
		 $this->setType("xrbt");
	}

	public function prepareReport()
	{
		$this->sum_table = $this->buildSumTable(catReportTable::create());
		parent::prepareReport();
	}

	protected function getRowTemplateTitle()
	{
		return "tpl.gev_bookings_by_tpl_row.html";
	}

	/**
	 *	@inheritdoc
	 */
	protected function buildOrder($order)
	{
		return $order
			->defaultOrder("template_title", "ASC");
	}

	/**
	 *	@inheritdoc
	 */
	protected function buildTable($table)
	{
		$table 		->column("template_title", $this->plugin->txt("title"), true)
					->column("edu_program", $this->plugin->txt("edu_program"), true);
		foreach ($this->sum_parts as $title => $query) {
			$table
				->column($title, $this->plugin->txt($title), true);
		}
		return parent::buildTable($table);
	}

	protected function buildSumTable(catReportTable $table)
	{
		foreach ($this->sum_parts as $title => $query) {
			$table
				->column($title, $this->plugin->txt($title), true);
		}
		$table	->template("tpl.gev_bookings_by_tpl_sums_row.html", $this->plugin->getDirectory());
		return $table;
	}

	public function deliverSumTable()
	{
		if ($this->sum_table !== null) {
			return $this->sum_table;
		}
		throw new Exception("ilObjReportBase::deliverSumTable: you need to define a sum table.");
	}

	/**
	 *	@inheritdoc
	 */
	protected function buildFilter($filter)
	{
			return $filter;
	}

	public function filter()
	{
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$this->tpl_filter = '';
		if ($this->settings['is_local']) {
			$this->tpl_filter = '	AND '.$this->gIldb->in('crs.template_obj_id', $this->getSubtreeCourseTemplates(), false, 'integer');
		}

		$txt = function ($id) {
			return $this->plugin->txt($id);
		};
		global $lng;
		return 	$f->sequence(
			$f->option(
				$lng->txt('gev_org_unit_recursive'),
				''
			)->clone_with_checked(true),
			$f->multiselectsearch(
				$lng->txt("gev_org_unit_short"),
				'',
				$this->getRelevantOrgus()
			),
			$f->sequence(
				$f->dateperiod(
					$txt("period"),
					''
				),
				$f->multiselectsearch(
					$txt('edu_program'),
					'',
					$this->getDistinctRowEntriesFormTableForFilter('edu_program', 'hist_course')
				),
				$f->multiselectsearch(
					$txt('course_type'),
					'',
					$this->getDistinctRowEntriesFormTableForFilter('type', 'hist_course')
				),
				$f->multiselectsearch(
					$txt('crs_title'),
					'',
					$this->getTemplates()
				),
				$f->multiselectsearch(
					$txt('participation_status'),
					'',
					array(	"teilgenommen"=>"teilgenommen"
									,"fehlt ohne Absage"=>"fehlt ohne Absage"
									,"fehlt entschuldigt"=>"fehlt entschuldigt"
									,"nicht gesetzt"=>"gebucht, noch nicht abgeschlossen")
				),
				$f->multiselectsearch(
					$txt('booking_status'),
					'',
					$this->getDistinctRowEntriesFormTableForFilter('booking_status', 'hist_usercoursestatus')
				),
				$f->multiselectsearch(
					$txt('venue'),
					'',
					$this->getDistinctRowEntriesFormTableForFilter('venue', 'hist_course')
				),
				$f->multiselectsearch(
					$txt('provider'),
					'',
					$this->getDistinctRowEntriesFormTableForFilter('provider', 'hist_course')
				)
			)
		)->map(
			function ($recursive, $org_unit, $start, $end, $edu_program, $type, $template_title, $p_status, $b_status, $venue, $provider) {
							return array(
								'recursive' => $recursive
								,'org_unit' => $org_unit
								,'start' => $start
								,'end' => $end
								,'edu_program' => $edu_program
								,'type' => $type
								,'template_title' => $template_title
								,'p_status' => $p_status
								,'b_status' => $b_status
								,'venue' => $venue
								,'provider' => $provider
								);
			},
			$tf->dict(array(
								'recursive' => $tf->bool()
								,'org_unit' => $tf->lst($tf->int())
								,'start' => $tf->cls("DateTime")
								,'end' => $tf->cls("DateTime")
								,'edu_program' => $tf->lst($tf->string())
								,'type' => $tf->lst($tf->string())
								,'template_title' => $tf->lst($tf->int())
								,'p_status' => $tf->lst($tf->string())
								,'b_status' => $tf->lst($tf->string())
								,'venue' => $tf->lst($tf->string())
								,'provider' => $tf->lst($tf->string())
								))
		);
	}

	private function getRelevantOrgus()
	{
		$return = array();
		$res = $this->gIldb->query(
			'SELECT object_data.obj_id, title FROM object_data'
			.'	JOIN object_reference'
			.'		ON object_data.obj_id = object_reference.obj_id'
			.'			AND deleted IS NULL'
			.' 	WHERE type = \'orgu\''
		);
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$return[$rec['obj_id']] = $rec['title'];
		}
		return $return;
	}

	private function getDistinctRowEntriesFormTableForFilter($column, $table)
	{
		$sql = 	'SELECT DISTINCT '.$column.' FROM '.$table
				.'	WHERE hist_historic = 0'
				.'		AND '.$column.' != '.$this->gIldb->quote('-empty-', 'text')
				.'		AND '.$column.' IS NOT NULL';
		$return = array();
		$res = $this->gIldb->query($sql);
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$return[$rec[$column]] = $rec[$column];
		}
		return $return;
	}

	/**
	 *	@inheritdoc
	 */
	protected function buildQuery($query)
	{
		$this->filter_selections = $this->getFilterSettings();
		$query 		->select("crs.template_title")
					->select("crs.edu_program");
		foreach ($this->sum_parts as $title => $query_parts) {
			$query	->select_raw($query_parts["regular"]);
		}
		$query		->from("hist_course crs")
					->join("hist_usercoursestatus usrcrs")
						->on("crs.crs_id = usrcrs.crs_id");
		if ($this->orguFilterSelected()) {
			$orgu_filter_query =
				"JOIN (SELECT usr_id  \n"
					."	FROM hist_userorgu \n"
					." 	WHERE ".$this->orguFilterCondition()." \n"
					."	AND hist_historic = 0 AND `action` >= 0 GROUP BY usr_id) as orgu ON usrcrs.usr_id = orgu.usr_id \n";
			$query	->raw_join($orgu_filter_query);
		}
		$query 		->group_by("crs.template_obj_id")
					->compile();
		return $query;
	}

	public function deliverSumQuery()
	{
		$sum_sql = "SELECT ";
		$prefix = "";
		foreach ($this->sum_parts as $title => $query_parts) {
			$sum_sql .= $prefix.$query_parts["sum"];
			$prefix = $prefix === "" ? "," : $prefix;
		}
		$sum_sql .=
			" FROM ( \n"
			."	SELECT usrcrs.usr_id, crs.crs_id, usrcrs.booking_status, \n"
			."		usrcrs.participation_status, crs.type \n"
			."		FROM  `hist_course` crs \n"
			."			JOIN `hist_usercoursestatus` usrcrs  ON usrcrs.crs_id = crs.crs_id \n";
		if ($this->orguFilterSelected()) {
			$sum_sql .=
			"			JOIN hist_userorgu orgu ON orgu.usr_id = usrcrs.usr_id \n";
		}
		$sum_sql .=
			"		".$this->queryWhere();
		if ($this->orguFilterSelected()) {
			$sum_sql .=
			" 		AND ".$this->orguFilterCondition()
			."		AND orgu.action >=0 AND orgu.hist_historic = 0";
		}
		$sum_sql .=
			"		GROUP BY usrcrs.usr_id, crs.crs_id"
			.") as temp";
		return $sum_sql;
	}

	protected function queryWhere()
	{

		return '	WHERE '.PHP_EOL
				.'	crs.hist_historic = 0'.PHP_EOL
				.'	AND usrcrs.hist_historic = 0'.PHP_EOL
				.'	AND crs.template_obj_id > 0'.PHP_EOL
				.'	AND crs.template_obj_id IS NOT NULL'.PHP_EOL
				.'	AND usrcrs.booking_status != \'-empty-\''.PHP_EOL
				.$this->dateperiodFilterCondition()
				.$this->eduProgrammFilterCondition()
				.$this->typeFilterCondition()
				.$this->templateTitleFilterCondition()
				.$this->participationStatusFilterCondition()
				.$this->bookingStatusFilterCondition()
				.$this->venueFilterCondition()
				.$this->providerFilterCondition();
	}

	protected function getFilterSettings()
	{
		$filter = $this->filter();
		if ($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
		}
		return $settings;
	}

	private function orguFilterSelected()
	{
		return count($this->filter_selections['org_unit']) > 0;
	}

	private function orguFilterCondition()
	{
		$orgu_selection = $this->filter_selections['org_unit'];
		if ($orgu_selection) {
			if ($this->filter_selections['recursive']) {
				$orgu_selection = $this->addRecursiveOrgusToSelection($orgu_selection);
			}
			return $this->gIldb->in('orgu.orgu_id', $orgu_selection, false, 'integer');
		}
		return '';
	}

	private function addRecursiveOrgusToSelection(array $selection)
	{
		require_once 'Services/GEV/Utils/classes/class.gevOrgUnitUtils.php';
		$aux = $selection;
		foreach ($selection as $orgu_id) {
			$ref_id = gevObjectUtils::getRefId($orgu_id);
			$aux[] = $orgu_id;
			foreach (gevOrgUnitUtils::getAllChildren(array($ref_id)) as $child) {
				$aux[] = $child["obj_id"];
			}
		}
		return $aux;
	}

	private function dateperiodFilterCondition()
	{
		if ($this->filter_selections['start'] !== null) {
			$start = $this->filter_selections['start']->format('Y-m-d');
		} else {
			$start = date('Y').'-01-01';
		}
		if ($this->filter_selections['end'] !== null) {
			$end = $this->filter_selections['end']->format('Y-m-d');
		} else {
			$end = date('Y').'-12-31';
		}
		return
			'	AND ( ('
			.'		(`usrcrs`.`end_date` >= '.$this->gIldb->quote($start, 'date').' OR `usrcrs`.`end_date` = \'0000-00-00\' OR `usrcrs`.`end_date` = \'-empty-\' )'
			.' 			AND `usrcrs`.`begin_date` <= '.$this->gIldb->quote($end, 'date').' )'
			.' 		OR usrcrs.hist_historic IS NULL )';
	}

	private function eduProgrammFilterCondition()
	{
		$selection = $this->filter_selections['edu_program'];
		if (count($selection)>0) {
			return '	AND '.$this->gIldb->in('crs.edu_program', $selection, false, 'text');
		}
		return '';
	}

	private function typeFilterCondition()
	{
		$selection = $this->filter_selections['type'];
		if (count($selection)>0) {
			return '	AND '.$this->gIldb->in('crs.type', $selection, false, 'text');
		}
		return '';
	}

	private function templateTitleFilterCondition()
	{
		$selection = $this->filter_selections['template_title'];
		if (count($selection)>0) {
			return '	AND '.$this->gIldb->in('crs.template_obj_id', $selection, false, 'text');
		}
		return '';
	}

	private function participationStatusFilterCondition()
	{
		$selection = $this->filter_selections['p_status'];
		if (count($selection)>0) {
			return '	AND '.$this->gIldb->in('usrcrs.participation_status', $selection, false, 'text');
		}
		return '';
	}

	private function bookingStatusFilterCondition()
	{
		$selection = $this->filter_selections['b_status'];
		if (count($selection)>0) {
			return '	AND '.$this->gIldb->in('usrcrs.booking_status', $selection, false, 'text');
		}
		return '';
	}

	private function venueFilterCondition()
	{
		$selection = $this->filter_selections['venue'];
		if (count($selection)>0) {
			return '	AND '.$this->gIldb->in('crs.venue', $selection, false, 'text');
		}
		return '';
	}

	private function providerFilterCondition()
	{
		$selection = $this->filter_selections['provider'];
		if (count($selection)>0) {
			return '	AND '.$this->gIldb->in('crs.provider', $selection, false, 'text');
		}
		return '';
	}

	public function insertSumData($table, callable $callback)
	{
		$res = $this->gIldb->query($this->deliverSumQuery());
		$summed_data = $this->gIldb->fetchAssoc($res);

		if (count($summed_data) == 0) {
			$summed_data = array();
			foreach ($this->sum_parts as $name => $query) {
				$summed_data[$name] = 0;
			}
		}
		$table->setData(array(call_user_func($callback, $summed_data)));
		return $table;
	}

	public function getRelevantParameters()
	{
		return $this->relevant_parameters;
	}

	protected function getTemplates()
	{
		$query = 	'SELECT od.obj_id, od.title FROM adv_md_values_text amd_val '
					.'	JOIN object_data od ON od.obj_id = amd_val.obj_id'
					.'	WHERE amd_val.field_id = '.$this->gIldb->quote(
						gevSettings::getInstance()
													->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE),
						'integer'
					)
					.'		AND amd_val.value = '.$this->gIldb->quote('Ja', 'text');
		$return = array();
		$res = $this->gIldb->query($query);
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$return[$rec['obj_id']] = $rec['title'];
		}
		return $return;
	}
}
