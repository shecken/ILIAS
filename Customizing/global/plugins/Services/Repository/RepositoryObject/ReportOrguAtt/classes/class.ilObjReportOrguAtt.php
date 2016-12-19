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

class ilObjReportOrguAtt extends ilObjReportBase
{
	protected $relevant_parameters = array();
	protected $sum_parts = array();

	public function __construct($ref_id = 0)
	{
		parent::__construct($ref_id);

		require_once $this->plugin->getDirectory().'/config/cfg.att_org_units.php';
	}

	public function initType()
	{
		 $this->setType("xroa");
	}


	protected function createLocalReportSettings()
	{
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_roa')
				->addSetting($this->s_f
							->settingBool('is_local', $this->plugin->txt('report_is_local')))
				->addSetting($this->s_f
							->settingBool('all_orgus_filter', $this->plugin->txt('report_all_orgus')));
	}


	public function prepareReport()
	{
		$this->sum_table = $this->buildSumTable(catReportTable::create());
		parent::prepareReport();
		return $query;
	}


	protected function getFilterSettings()
	{
		$filter = $this->filter();
		if ($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
		}
		return $settings;
	}

	protected function buildSumTable(catReportTable $table)
	{
		foreach ($this->sum_parts as $title => $query) {
			$table
				->column($title, $this->plugin->txt($title), true);
		}
		$table	->template("tpl.gev_attendance_by_orgunit_sums_row.html", $this->plugin->getDirectory());
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
	 * @inheritdoc
	 */
	protected function buildOrder($order)
	{
		return $order
			->defaultOrder("orgu_title", "ASC");
	}

	/**
	 * @inheritdoc
	 */
	protected function buildTable($table)
	{
		$table	->column("orgu_title", $this->plugin->txt('orgu_title'), true)
				->column("odbd", $this->plugin->txt('od_bd'), true, "", false, false);
		foreach ($this->sum_parts as $title => $query) {
			$table
				->column($title, $this->plugin->txt($title), true);
		}
		return parent::buildTable($table);
	}

	protected function getRowTemplateTitle()
	{
		return "tpl.gev_attendance_by_orgunit_row.html";
	}

	/**
	 * @inheritdoc
	 */
	protected function buildQuery($query)
	{

		$this->filter_selections = $this->getFilterSettings();
		return $query;
	}

	public function buildQueryStatement()
	{
		$query =	'SELECT orgu.orgu_title '.PHP_EOL
					.'	,orgu.org_unit_above1'.PHP_EOL
					.'	,orgu.org_unit_above2'.PHP_EOL;
		foreach ($this->sum_parts as $title => $query_term) {
			$query .= '	,'.$query_term["regular"].PHP_EOL;
		}
		$query .= 	'	FROM hist_userorgu orgu'.PHP_EOL
					.'	JOIN hist_user usr'.PHP_EOL
					.'		ON usr.user_id = orgu.usr_id'.PHP_EOL
					.'	LEFT JOIN hist_usercoursestatus usrcrs'.PHP_EOL
					.'		ON usrcrs.usr_id = orgu.usr_id AND usrcrs.hist_historic = 0'.PHP_EOL
					.'			AND usrcrs.booking_status != '.$this->gIldb->quote('-empty-', 'text').PHP_EOL
					.'			'.$this->datePeriodFilter()
					.'			'.$this->noWBDImportedFilter()
					.'	LEFT JOIN hist_course crs'.PHP_EOL
					.'		ON crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0'.PHP_EOL
					.'			AND '.$this->tpl_filter
					.$this->courseTopicsFilter()
					.$this->queryWhere().PHP_EOL
					.'	GROUP BY orgu.orgu_id'.PHP_EOL
					.$this->queryOrder();
		return $query;
	}

	private function courseTopicsFilter()
	{
		$selection = $this->filter_selections['crs_topics'];
		if (count($selection)>0) {
			return
				'	JOIN (SELECT topic_set_id'
				.'			FROM hist_topicset2topic '
				.'				JOIN hist_topics USING (topic_id)'
				.'			WHERE '.$this->gIldb->in('topic_title', $selection, false, 'text')
				.'			GROUP BY topic_set_id) as crs_topics ON crs.topic_set = crs_topics.topic_set_id';
		}
		return '';
	}

	private function datePeriodFilter($query)
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
		return '	AND (usrcrs.begin_date <= \''.$end.'\''.PHP_EOL
			.'			 AND (usrcrs.end_date >= \''.$start.'\''.PHP_EOL
			.'				OR `usrcrs`.`end_date` = \'0000-00-00\' OR `usrcrs`.`end_date` = \'-empty-\'))'.PHP_EOL;
	}

	private function noWBDImportedFilter()
	{
		$selection = $this->filter_selections['no_wbd'];
		if ($no_wbd_imported) {
			return ' AND usrcrs.crs_id > 0';
		}
		return '';
	}

	protected function deliverSumQuery()
	{
		$sum_terms = array();
		foreach ($this->sum_parts as $title => $query_term) {
			$sum_terms[] = $query_term["sum"];
		}
		$no_wbd_imported = $this->filter->get('no_wbd_imported');
		$sum_sql =
		"SELECT  "
		."	".implode(', ', $sum_terms)
		." 	FROM("
		."		SELECT DISTINCT orgu.usr_id, crs.crs_id, usrcrs.booking_status, "
		."			usrcrs.participation_status, crs.type "
		."			FROM hist_userorgu orgu "
		."			JOIN hist_user usr"
		."				ON orgu.usr_id = usr.user_id"
		."			LEFT JOIN `hist_usercoursestatus` usrcrs "
		."				ON usrcrs.usr_id = orgu.usr_id AND usrcrs.hist_historic = 0 "
		."					AND usrcrs.booking_status != ".$this->gIldb->quote('-empty-', 'text')
		."					".$this->datePeriodFilter()
		."					".$this->noWBDImportedFilter()
		."			LEFT JOIN `hist_course` crs "
		."				ON usrcrs.crs_id = crs.crs_id AND crs.hist_historic = 0 "
		."					AND ".$this->tpl_filter;
		$topics = $this->filter_selections['crs_topics'];
		if (count($topics) > 0) {
			$sum_sql .=
			"			JOIN hist_topicset2topic ts2t ON crs.topic_set = ts2t.topic_set_id"
			."			JOIN hist_topics ts ON ts2t.topic_id = ts.topic_id "
			."				AND ".$this->gIldb->in("ts.topic_title", $topics, false, 'text');
		}
		$sum_sql .=
		"			".$this->queryWhere()
		.") as temp";
		return $sum_sql;
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

	/**
	 * @inheritdoc
	 */
	protected function buildFilter($filter)
	{
		$this->orgu_filter = new recursiveOrguFilter('org_unit', 'orgu.orgu_id', true, true);
		if ("1" === (string)$this->settings['all_orgus_filter']) {
			$this->orgu_filter->setFilterOptionsAll();
		} else {
			$this->orgu_filter->setFilterOptionsByArray(
				array_unique(array_map(
					function ($ref_id) {
						return ilObject::_lookupObjectId($ref_id);
					},
					$this->user_utils->getOrgUnitsWhereUserCanViewEduBios()
				))
			);
		}
		$this->crs_topics_filter = new courseTopicsFilter('crs_topics', 'crs.topic_set');
		$this->orgu_filter->addToFilter($filter);
		$this->crs_topics_filter->addToFilter($filter);
		$filter	->dateperiod("period", $this->plugin->txt("period"), $this->plugin->txt("until"), "usrcrs.begin_date", "usrcrs.end_date", date("Y")."-01-01", date("Y")."-12-31", false, " OR TRUE")
				->multiselect("edu_program", $this->plugin->txt("edu_program"), "edu_program", gevCourseUtils::getEduProgramsFromHisto(), array(), "", 200, 160)
				->multiselect("type", $this->plugin->txt("course_type"), "type", gevCourseUtils::getLearningTypesFromHisto(), array(), "", 200, 160)
				->multiselect("template_title", $this->plugin->txt("crs_title"), "template_title", gevCourseUtils::getTemplateTitleFromHisto(), array(), "", 300, 160)
				->multiselect("participation_status", $this->plugin->txt("participation_status"), "participation_status", array(	"teilgenommen"=>"teilgenommen"
							 			,"fehlt ohne Absage"=>"fehlt ohne Absage"
							 			,"fehlt entschuldigt"=>"fehlt entschuldigt"
							 			,"nicht gesetzt"=>"gebucht, noch nicht abgeschlossen"), array(), "", 200, 160, "text", "asc", true)
				->multiselect("booking_status", $this->plugin->txt("booking_status"), "booking_status", catFilter::getDistinctValues('booking_status', 'hist_usercoursestatus'), array(), "", 200, 160)
				->multiselect("gender", $this->plugin->txt("gender"), "gender", array('f', 'm'), array(), "", 100, 160)
				->multiselect("venue", $this->plugin->txt("venue"), "venue", catFilter::getDistinctValues('venue', 'hist_course'), array(), "", 300, 160)
				->multiselect("provider", $this->plugin->txt("provider"), "provider", catFilter::getDistinctValues('provider', 'hist_course'), array(), "", 300, 160)
				->checkbox('no_wbd_imported', $this->plugin->txt("filter_no_wbd_imported"), " TRUE ", " TRUE ");
		if ("1" !== (string)$this->options['all_orgus_filter']) {
			$filter
			->static_condition($this->gIldb->in("orgu.usr_id", $this->user_utils->getEmployeesWhereUserCanViewEduBios(), false, "integer"));
		}
			$filter
				->static_condition('usr.hist_historic = 0')
				->static_condition("orgu.hist_historic = 0")
				->static_condition("orgu.action >= 0")
				->static_condition("orgu.rol_title = 'Mitarbeiter'")
				->action($this->filter_action)
				->compile();
		$date_filter = $filter->get("period");
		$this->date_start = $date_filter["start"]->get(IL_CAL_DATE);
		$this->date_end = $date_filter["end"]->get(IL_CAL_DATE);
		$this->tpl_filter
			= (int)$this->settings['is_local'] === 1
				? $this->gIldb->in('crs.template_obj_id', $this->getSubtreeCourseTemplates(), false, 'integer')
				: "TRUE" ;
		return $filter;
	}

	private function andFieldInSelection($field, array $selection)
	{
		return '		AND '.$this->gIldb->in($field, $selection, false, 'text').PHP_EOL;
	}

	private function addOrguFilterToQueryWhere($query_where)
	{
		$selection = $this->filter_selections['org_unit'];
		if (count($selection)>0) {
			if ($this->filter_selections['recursive']) {
				$selection = $this->addRecursiveOrgusToSelection($selection);
			}
			return $query_where.$this->andFieldInSelection('orgu.orgu_id', $selection);
		}
		return $query_where.$this->andFieldInSelection('orgu.orgu_id', $this->getRelevantOrguIds());
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
		return array_unique($aux);
	}

	private function addEduProgrammFilterToQueryWhere($query_where)
	{
		$selection = $this->filter_selections['edu_program'];
		if (count($selection)>0) {
			return $query_where.$this->andFieldInSelection('crs.edu_program', $selection);
		}
		return $query_where;
	}

	private function addTypeFilterToQueryWhere($query_where)
	{
		$selection = $this->filter_selections['type'];
		if (count($selection)>0) {
			return $query_where.$this->andFieldInSelection('crs.type', $selection);
		}
		return $query_where;
	}

	private function addTemplateTitleFilterToQueryWhere($query_where)
	{
		$selection = $this->filter_selections['template_title'];
		if (count($selection)>0) {
			return $query_where.$this->andFieldInSelection('crs.template_title', $selection);
		}
		return $query_where;
	}

	private function addParticipationStatusFilterToQueryWhere($query_where)
	{
		$selection = $this->filter_selections['p_status'];
		if (count($selection)>0) {
			return $query_where.$this->andFieldInSelection('usrcrs.participation_status', $selection);
		}
		return $query_where;
	}

	private function addBookingStatusFilterToQueryWhere($query_where)
	{
		$selection = $this->filter_selections['b_status'];
		if (count($selection)>0) {
			return $query_where.$this->andFieldInSelection('usrcrs.booking_status', $selection);
		}
		return $query_where;
	}

	private function addGenderFilterToQueryWhere($query_where)
	{
		$selection = $this->filter_selections['gender'];
		if (count($selection)>0) {
			return $query_where.$this->andFieldInSelection('usr.gender', $selection);
		}
		return $query_where;
	}

	private function addVenueFilterToQueryWhere($query_where)
	{
		$selection = $this->filter_selections['venue'];
		if (count($selection)>0) {
			return $query_where.$this->andFieldInSelection('crs.venue', $selection);
		}
		return $query_where;
	}

	private function addProviderFilterToQueryWhere($query_where)
	{
		$selection = $this->filter_selections['provider'];
		if (count($selection)>0) {
			return $query_where.$this->andFieldInSelection('crs.provider', $selection);
		}
		return $query_where;
	}

	protected function queryWhere()
	{
		$query_where =
			'	WHERE usr.hist_historic = 0'.PHP_EOL
			.'		AND orgu.hist_historic = 0'.PHP_EOL
			.'		AND orgu.action >= 0'.PHP_EOL
			.'		AND orgu.rol_title = \'Mitarbeiter\''.PHP_EOL;
		if ("1" !== (string)$this->options['all_orgus_filter']) {
			$query_where .=
				'		AND '.$this->gIldb->in("orgu.usr_id", $this->user_utils->getEmployeesWhereUserCanViewEduBios(), false, "integer").PHP_EOL;
		}
		$query_where = $this->addOrguFilterToQueryWhere($query_where);
		$query_where = $this->addEduProgrammFilterToQueryWhere($query_where);
		$query_where = $this->addTypeFilterToQueryWhere($query_where);
		$query_where = $this->addTemplateTitleFilterToqueryWhere($query_where);
		$query_where = $this->addParticipationStatusFilterToQueryWhere($query_where);
		$query_where = $this->addBookingStatusFilterToQueryWhere($query_where);
		$query_where = $this->addGenderFilterToQueryWhere($query_where);
		$query_where = $this->addVenueFilterToQueryWhere($query_where);
		$query_where = $this->addProviderFilterToQueryWhere($query_where);
		return $query_where;
	}

	public function filter()
	{
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);

		$txt = function ($id) {
			return $this->plugin->txt($id);
		};

		return 	$f->sequence(
			$f->option(
				$txt('filter_no_wbd_imported'),
				''
			),
			$f->option(
				$txt('org_unit_recursive'),
				''
			),
			$f->multiselectsearch(
				$txt("org_unit_short"),
				'',
				$this->getRelevantOrgus()
			),
			$f->sequence(
				$f->dateperiod(
					$txt("period"),
					''
				)
							->map(
								function ($start, $end) use ($f) {
									return array(
											"start" => $start
											,"end" => $end);
								},
								$tf->dict(
									array(
											"start" => $tf->cls("DateTime")
											,"end" => $tf->cls("DateTime"))
								)
							),
				$f->multiselectsearch(
					$txt("crs_filter_topics"),
					"",
					gevAMDUtils::getInstance()->getOptions(gevSettings::CRS_AMD_TOPIC)
				),
				$f->multiselectsearch(
					$txt('edu_program'),
					'',
					$this->getDistinctRowEntriesFormTableForFilter('edu_program', 'hist_course')
				),
				$f->multiselectsearch(
					$txt('type'),
					'',
					$this->getDistinctRowEntriesFormTableForFilter('type', 'hist_course')
				),
				$f->multiselectsearch(
					$txt('template_title'),
					'',
					$this->getDistinctRowEntriesFormTableForFilter('template_title', 'hist_course')
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
					$txt('gender'),
					'',
					array('f'=>'f','m' => 'm')
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
			)->map(
				function ($start, $end, $crs_topics, $edu_program, $type, $template_title, $p_status, $b_status, $gender, $venue, $provider) {
							return array(
								'start' => $start
								,'end' => $end
								,'crs_topics' => $crs_topics
								,'edu_program' => $edu_program
								,'type' => $type
								,'template_title' => $template_title
								,'p_status' => $p_status
								,'b_status' => $b_status
								,'gender' => $gender
								,'venue' => $venue
								,'provider' => $provider
								);
				},
				$tf->dict(
					array(
								'start' => $tf->cls("DateTime")
								,'end' => $tf->cls("DateTime")
								,'crs_topics' => $tf->lst($tf->string())
								,'edu_program' => $tf->lst($tf->string())
								,'type' => $tf->lst($tf->string())
								,'template_title' => $tf->lst($tf->int())
								,'p_status' => $tf->lst($tf->string())
								,'b_status' => $tf->lst($tf->string())
								,'gender' => $tf->lst($tf->string())
								,'venue' => $tf->lst($tf->string())
								,'provider' => $tf->lst($tf->string())
								)
				)
			)
		)->map(
			function ($no_wbd, $recursive, $org_unit, $start, $end, $crs_topics, $edu_program, $type, $template_title, $p_status, $b_status, $gender, $venue, $provider) {
							return array(
								'no_wbd' => $no_wbd
								,'recursive' => $recursive
								,'org_unit' => $org_unit
								,'start' => $start
								,'end' => $end
								,'crs_topics' => $crs_topics
								,'edu_program' => $edu_program
								,'type' => $type
								,'template_title' => $template_title
								,'p_status' => $p_status
								,'b_status' => $b_status
								,'gender' => $gender
								,'venue' => $venue
								,'provider' => $provider
								);
			},
			$tf->dict(
				array(
								'no_wbd' => $tf->bool()
								,'recursive' => $tf->bool()
								,'org_unit' => $tf->lst($tf->int())
								,'start' => $tf->cls("DateTime")
								,'end' => $tf->cls("DateTime")
								,'crs_topics' => $tf->lst($tf->string())
								,'edu_program' => $tf->lst($tf->string())
								,'type' => $tf->lst($tf->string())
								,'template_title' => $tf->lst($tf->int())
								,'p_status' => $tf->lst($tf->string())
								,'b_status' => $tf->lst($tf->string())
								,'gender' => $tf->lst($tf->string())
								,'venue' => $tf->lst($tf->string())
								,'provider' => $tf->lst($tf->string())
								)
			)
		);
	}

	protected function getSubtreeCourseTemplates()
	{
		$query = 	'SELECT obj_id FROM adv_md_values_text amd_val '
					.'	WHERE '.$this->gIldb->in(
						'obj_id',
						$this->getSubtreeTypeIdsBelowParentType('crs', 'cat'),
						false,
						'integer'
					)
					.'		AND field_id = '.$this->gIldb->quote(
						gevSettings::getInstance()
													->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE),
						'integer'
					)
					.'		AND value = '.$this->gIldb->quote('Ja', 'text');
		$return = array();
		$res = $this->gIldb->query($query);
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = $rec['obj_id'];
		}
		return $return;
	}

	public function getRelevantParameters()
	{
		return $this->relevant_parameters;
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

	private function getRelevantOrgus()
	{
		$ids = $this->getRelevantOrguIds();
		$options = array();
		foreach ($ids as $id) {
			$options[(int)$id] = ilObject::_lookupTitle($id);
		}
		return $options;
	}

	private function getRelevantOrguIds()
	{
		if ("1" === (string)$this->settings['all_orgus_filter']) {
			$ids = $this->getAllOrguIds();
		} else {
			$ids = array_unique(array_map(
				function ($ref_id) {
						return ilObject::_lookupObjectId($ref_id);
				},
				$this->user_utils->getOrgUnitsWhereUserCanViewEduBios()
			));
		}
		return $ids;
	}

	private function getAllOrguIds()
	{
		$query = 'SELECT DISTINCT obj_id FROM object_data JOIN object_reference USING(obj_id)'
				.'	WHERE type = \'orgu\' AND deleted IS NULL';
		$res = $this->gIldb->query($query);
		$return = array();
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = $rec["obj_id"];
		}
		return $return;
	}
}
