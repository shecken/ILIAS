<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevAMDUtils.php';
require_once 'Services/GEV/Utils/classes/class.gevSettings.php';
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
require_once 'Services/GEV/Utils/classes/class.gevCourseUtils.php';

class ilObjReportCompanyGlobal extends ilObjReportBase
{

	protected $online;
	protected $relevant_parameters = array();
	protected static $participated = array('teilgenommen');
	protected static $columns_to_sum = array('crs_cnt_book' => 'crs_cnt_book', 'book_book' => 'book_book', 'crs_cnt_part' => 'crs_cnt_part', 'part_book' => 'part_book','wp_part' => 'wp_part');
	protected static $wbd_relevant = array('OKZ1','OKZ2','OKZ3');
	protected $types;
	protected $filter_orgus = array();
	protected $sql_filter_orgus = null;
	protected $template_ref_field_id;


	public function initType()
	{
		 $this->setType("xrcg");
		 $amd_utils = gevAMDUtils::getInstance();
		 $this->types = array_filter($amd_utils->getOptions(gevSettings::CRS_AMD_TYPE), function ($type) {
		 	if ($type != gevCourseUtils::CRS_TYPE_COACHING) {
		 		return $type;
		 	}
		 });
	}

	/**
	 * We can not use regular query logic here (since there is no outer-join in mysql and i would like to avoid a lot of subqueries)
	 * so lets take this opportunity to do some preparation work for the actual query construction in getTrainingTypeQuery at least.
	 *
	 * @inheritdoc
	 */
	protected function buildQuery($query)
	{
		return $this->prepareQueryComponents($query);
	}

	protected function getFilterSettings()
	{
		$filter = $this->filter();
		if ($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
		}
		return $settings;
	}

	protected function createLocalReportSettings()
	{
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rcg');
	}

	protected function prepareQueryComponents($query)
	{
		$this->filter_selections = $this->getFilterSettings();
		return $query;
	}

	/**
	 * @inheritdoc
	 */
	protected function buildFilter($filter)
	{
		return null;
	}

	public function filter()
	{
		require_once 'Services/GEV/Utils/classes/class.gevAMDUtils.php';
		require_once 'Services/GEV/Utils/classes/class.gevSettings.php';

		$db = $this->gIldb;
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$txt = function ($id) {
			return $this->plugin->txt($id);
		};

		return
		$f->sequence(
			$f->option(
				$txt("filter_no_wbd_imported"),
				""
			),
			$f->option(
				$txt("org_unit_recursive"),
				""
			)->clone_with_checked(true),
			$f->sequence(
				$f->dateperiod(
					$txt("period"),
					""
				),
				$f->multiselectsearch(
					$txt("org_unit_short"),
					"",
					$this->getAllOrguIds()
				),
				$f->multiselectsearch(
					$txt("crs_filter_topics"),
					"",
					gevAMDUtils::getInstance()->getOptions(gevSettings::CRS_AMD_TOPIC)
				),
				$f->multiselectsearch(
					$txt("edu_program"),
					"",
					$this->getEduPrograms()
				),
				$f->multiselectsearch(
					$txt("template_title"),
					"",
					$this->getTemplateTitles()
				),
				$f->multiselectsearch(
					$txt("course_type"),
					"",
					array("(hc.edu_program = ".$db->quote("dezentrales Training (AD)", "text")." AND hc.dct_type = ".$db->quote("fixed", "text").')'
								=> $txt("dec_fixed")
							,"(hc.edu_program = ".$db->quote("dezentrales Training (AD)", "text")." AND hc.dct_type = ".$db->quote("flexible", "text").')'
								=> $txt("dec_flexible")
							,"hc.edu_program != ".$db->quote("dezentrales Training (AD)", "text")
					=> $txt("non_dec"))
				),
				$f->multiselectsearch(
					$txt("wbd_relevant"),
					"",
					array(	$db->in('hucs.okz', self::$wbd_relevant, false, 'text')
								=> $txt('yes')
							,$db->in('hucs.okz', self::$wbd_relevant, true, 'text')
								=> $txt('no')
					)
				),
				$f->multiselectsearch(
					$txt("wb_time"),
					"",
					array('(hc.max_credit_points > 0 OR hc.crs_id < 0)'
								=> $txt('trainings_w_points')
							,'('.$db->in("hc.max_credit_points ", array('0','-empty-'), false, 'text')." AND hc.crs_id > 0 )"
					=> $txt('trainings_wo_points'))
				)
			)
		)->map(
			function ($no_wbd, $recursive, $start, $end, $org_unit_short, $crs_filter_topics, $edu_program, $template_title, $course_type, $wbd_relevant, $edupoints) {
						return array("no_wbd" => $no_wbd
									,"recursive" => $recursive
									,"org_unit" => $org_unit_short
									,"start" => $start
									,"end" => $end
									,"crs_topics_filter" => $crs_filter_topics
									,"edu_program" => $edu_program
									,"template_title" => $template_title
									,"course_type" => $course_type
									,"wbd_relevant" => $wbd_relevant
									,"edupoints" => $edupoints);
			},
			$tf->dict(array(		"no_wbd" => $tf->bool()
									,"recursive" => $tf->bool()
									,"org_unit" => $tf->lst($tf->int())
									,"start" => $tf->cls("DateTime")
									,"end" => $tf->cls("DateTime")
									,"crs_topics_filter" => $tf->lst($tf->string())
									,"edu_program" => $tf->lst($tf->string())
									,"template_title" => $tf->lst($tf->int())
									,"course_type" => $tf->lst($tf->string())
									,"wbd_relevant" => $tf->lst($tf->string())
									,"edupoints" => $tf->lst($tf->string())
						))
		);
	}

	protected function fetchData(callable $callback)
	{
		$data = $this->joinPartialDataSets(
			$this->fetchPartialDataSet($this->getPartialQuery(true)),
			$this->fetchPartialDataSet($this->getPartialQuery(false))
		);

		$sum_data = array();

		foreach ($data as &$row) {
			$row = call_user_func($callback, $row);
			foreach (self::$columns_to_sum as $column) {
				if (!isset($sum_data[$column])) {
					$sum_data[$column] = 0;
				}
				$sum_data[$column] += $row[$column];
			}
			$row['wp_part'] = gevCourseUtils::convertCreditpointsToFormattedDuration((int)$row['wp_part']);
		}

		$sum_data['type'] = $this->plugin->txt('sum');
		$sum_data['part_user'] = '--';
		$sum_data['book_user'] = '--';
		$sum_data['wp_part'] = gevCourseUtils::convertCreditpointsToFormattedDuration((int)$sum_data['wp_part']);
		$data['sum'] = $sum_data;

		return $data;
	}

	protected function getRowTemplateTitle()
	{
		return 'tpl.cat_global_company_report_data_row.html';
	}

	/**
	 * @inheritdoc
	 */
	protected function buildTable($table)
	{
		$table  ->column('type', $this->plugin->txt('type'), true)
				->column('crs_cnt_book', $this->plugin->txt('cnt_crs'), true)
				->column('book_book', $this->plugin->txt('bookings'), true)
				->column('book_user', $this->plugin->txt('members'), true)
				->column('crs_cnt_part', $this->plugin->txt('cnt_crs'), true)
				->column('part_book', $this->plugin->txt('participations'), true)
				->column('wp_part', $this->plugin->txt('wb_time'), true)
				->column('part_user', $this->plugin->txt('members'), true);
		return parent::buildTable($table);
	}

	/**
	 * @inheritdoc
	 */
	protected function buildOrder($order)
	{
		return $order;
	}

	public function buildQueryStatement()
	{
		return $this->getPartialQuery(true);
	}

	protected function getPartialQuery($has_participated)
	{
		if ($has_participated) {
			$prefix = 'part';
			$crs_cnt = 'COUNT(DISTINCT hc.crs_id) AS crs_cnt_part';
		} else {
			$prefix = 'book';
			$crs_cnt = 'COUNT(DISTINCT hc.crs_id) AS crs_cnt_book';
		}

		$query = 'SELECT '.$crs_cnt.', count(hc.type) AS crs_cnt, hc.type , COUNT(hucs.usr_id) '.self::$columns_to_sum[$prefix.'_book'].', COUNT(DISTINCT hucs.usr_id) '.$prefix.'_user' ;
		if ($has_participated) {
			$query .= ', '.$this->countParticipated();
		}
		$query .= 	' 	FROM hist_course hc'
					.'	JOIN hist_usercoursestatus hucs ON'
					.'		'.$this->userCourseSelectorByStatus($has_participated);
		$query = $this->possiblyAddOrguFilterJoin($query); //ok
		$query = $this->possiblyAddCourseTopicsFilterJoin($query); //ok
		$query .= 	'	WHERE hc.hist_historic = 0'
					.'		AND (hc.is_cancelled IS NULL OR hc.is_cancelled = '.$this->gIldb->quote('Nein', 'text').')'
					.'		AND hucs.hist_historic = 0'
					.'		AND hucs.booking_status = '.$this->gIldb->quote('gebucht', 'text')
					.'		AND '.$this->gIldb->in('hc.type', $this->types, false, 'text');
		$query = $this->addDatePeriodFilter($query);
		$query = $this->possiblyAddNoWBDReimportFilter($query);
		$query = $this->possiblyAddCourseTypeFilter($query); //ok
		$query = $this->possiblyAddTemplateTitleFilter($query); //ok
		$query = $this->possiblyAddEduProgramFilter($query); //ok
		$query = $this->possiblyAddEduPointsFilter($query); //ok
		$query = $this->possiblyAddWBDRelevantFilter($query); //ok
		$query .= 	'	GROUP BY hc.type';
		return $query;
	}

	private function addDatePeriodFilter($query)
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
		$query .= 	' AND (hucs.end_date >= '.$this->gIldb->quote($start, "date").PHP_EOL.
					'		OR hucs.end_date = \'0000-00-00\''.PHP_EOL.
					'		OR hucs.end_date = \'-empty-\')'.PHP_EOL.
					' AND hucs.end_date <= '.$this->gIldb->quote($end, "date").PHP_EOL.
					' AND ( hc.type NOT IN (\'Selbstlernkurs\')'.PHP_EOL.
					'      OR ( (hucs.end_date = \'0000-00-00\' OR hucs.end_date = \'-empty-\')'.PHP_EOL.
					'           AND hucs.begin_date >= '.$this->gIldb->quote($start, "date").PHP_EOL.
					'           AND hucs.begin_date <= '.$this->gIldb->quote($end, "date").PHP_EOL.
					'         )'.PHP_EOL.
					'      OR ( hucs.end_date >= '.$this->gIldb->quote($start, "date").PHP_EOL.
					'           AND hucs.end_date <= '.$this->gIldb->quote($end, "date").PHP_EOL.
					'         )'.PHP_EOL.
					'    )'.PHP_EOL;
		return $query;
	}

	private function possiblyAddNoWBDReimportFilter($query)
	{
		$selection = $this->filter_selections['no_wbd'];
		if ($selection) {
			$query .= ' AND hucs.creator_user_id NOT IN(-666)';
		}
		return $query;
	}

	private function possiblyAddOrguFilterJoin($query)
	{
		$selection = $this->filter_selections['org_unit'];
		if (count($selection) > 0) {
			$recursive = $this->filter_selections['recursive'];
			if ($recursive) {
				$selection = $this->addRecursiveOrgusToSelection($selection);
			}
			$query .= 	' 	JOIN (SELECT usr_id FROM hist_userorgu '
						.'			WHERE action >= 0 AND hist_historic = 0 '
						.'				AND '.$this->gIldb->in('orgu_id', $selection, false, 'integer')
						.'			GROUP BY usr_id) AS orgus ON '
						.'		hucs.usr_id = orgus.usr_id ';
		}
		return $query;
	}

	private function addRecursiveOrgusToSelection(array $selection)
	{
		require_once 'Services/GEV/Utils/classes/class.gevOrgUnitUtils.php';
		$aux = array();
		foreach ($selection as $orgu_id) {
			$ref_id = gevObjectUtils::getRefId($orgu_id);
			$aux[] = $orgu_id;
			foreach (gevOrgUnitUtils::getAllChildren(array($ref_id)) as $child) {
				$aux[] = $child["obj_id"];
			}
		}
		return $aux;
	}

	private function possiblyAddCourseTopicsFilterJoin($query)
	{
		$selection = $this->filter_selections['crs_topics_filter'];
		if (count($selection) > 0) {
			$query .= 	'	JOIN (SELECT topic_set_id FROM hist_topicset2topic '
						.'			JOIN hist_topics '
						.'			USING (topic_id) '
						.'			WHERE '.$this->gIldb->in('topic_title', $selection, false, 'text')
						.'			GROUP BY topic_set_id) as crs_topics'
						.'		ON hc.topic_set = crs_topics.topic_set_id';
		}
		return $query;
	}

	private function possiblyAddTemplateTitleFilter($query)
	{
		$selection = $this->filter_selections['template_title'];
		if (count($selection) > 0) {
			$query .= 	' AND '.$this->gIldb->in('hc.template_obj_id', $selection, false, 'integer');
		}
		return $query;
	}

	private function possiblyAddEduProgramFilter($query)
	{
		$selection = $this->filter_selections['edu_program'];
		if (count($selection) > 0) {
			$query .= 	' AND '.$this->gIldb->in('hc.edu_program', $selection, false, 'text');
		}
		return $query;
	}

	private function possiblyAddCourseTypeFilter($query)
	{
		$selection = $this->filter_selections['course_type'];
		if (count($selection) > 0) {
			$query .= ' AND ('.implode(' OR ', $selection).')';
		}
		return $query;
	}

	private function possiblyAddEduPointsFilter($query)
	{
		$selection = $this->filter_selections['edupoints'];
		if (count($selection) > 0) {
			$query .= ' AND ('.implode(' OR ', $selection).')';
		}
		return $query;
	}

	private function possiblyAddWBDRelevantFilter($query)
	{
		$selection = $this->filter_selections['wbd_relevant'];
		if (count($selection) > 0) {
			$query .= ' AND ('.implode(' OR ', $selection).')';
		}
		return $query;
	}

	private function countParticipated()
	{
		return ' SUM( IF( hucs.credit_points IS NOT NULL AND hucs.credit_points > 0 AND '.$this->gIldb->in('hucs.okz', self::$wbd_relevant, false, 'text')
					.', hucs.credit_points, 0) ) '.self::$columns_to_sum['wp_part'];
	}

	protected function userCourseSelectorByStatus($has_participated)
	{
		if ($has_participated) {
			$return = 'hc.crs_id = hucs.crs_id'
				.'	AND hucs.participation_status = '.$this->gIldb->quote('teilgenommen', 'text');
		} else {
			$return = 'hc.crs_id = hucs.crs_id'
				.'	AND '.$this->gIldb->in('hucs.participation_status', array('nicht gesetzt','-empty-'), false, 'text');
		}
		return $return;
	}

	protected function fetchPartialDataSet($a_query)
	{
		$res = $this->gIldb->query($a_query);
		$return = array();
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$return[$rec["type"]] = $rec;
		}

		return $return;
	}

	protected function joinPartialDataSets(array $a_data, array $b_data)
	{
		$return = array();
		foreach ($this->types as $type) {
			if (!isset($a_data[$type])) {
				$a_data[$type] = array('type' => $type);
			}
			if (!isset($b_data[$type])) {
				$b_data[$type] = array('type' => $type);
			}
			$return[$type] = array_merge($a_data[$type], $b_data[$type]);
		}
		return $return;
	}

	public function getRelevantParameters()
	{
		return $this->relevant_parameters;
	}

	protected function getAllOrguIds()
	{
		$query = "SELECT obj_id, title FROM object_data JOIN object_reference USING(obj_id)"
				."	WHERE type = 'orgu' AND deleted IS NULL";
		$res = $this->gIldb->query($query);
		$return = array();
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$return[$rec["obj_id"]] = $rec["title"];
		}
		return $return;
	}

	private function getTemplateTitles()
	{
		$sql = 	'SELECT crs_id, title'
				.'	FROM hist_course '
				.' 	WHERE hist_historic = 0'
				.'		AND is_template = '.$this->gIldb->quote('Ja', 'text');
		$return = array();
		$res = $this->gIldb->query($sql);
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$return[$rec['crs_id']] = $rec['title'];
		}
		return $return;
	}

	private function getEduPrograms()
	{
		$sql = 	'SELECT DISTINCT edu_program FROM hist_course'
				.'	WHERE hist_historic = 0'
				.'		AND edu_program != '.$this->gIldb->quote('-empty-', 'text')
				.'		AND edu_program IS NOT NULL';
		$return = array();
		$res = $this->gIldb->query($sql);
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$return[$rec['edu_program']] = $rec['edu_program'];
		}
		return $return;
	}
}
