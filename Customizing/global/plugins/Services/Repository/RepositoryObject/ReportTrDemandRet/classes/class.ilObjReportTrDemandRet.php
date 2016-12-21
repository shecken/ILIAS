<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';

ini_set("memory_limit", "2048M");
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportTrDemandRet extends ilObjReportBase
{
	protected $relevant_parameters = array();
	protected $is_local;

	public function initType()
	{
		$this->setType("xtdr");
	}

	protected function getRowTemplateTitle()
	{
		return "tpl.gev_tr_demand_ret_row.html";
	}

	public function getRelevantParameters()
	{
		return $this->relevant_parameters;
	}

	protected function buildOrder($order)
	{
		return $order
					->defaultOrder("tpl_title", "ASC")
					->mapping("tpl_title", array("tpl_title","title","begin_date"));
	}

	protected function createLocalReportSettings()
	{
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rtdr')
				->addSetting($this->s_f
								->settingBool('is_local', $this->plugin->txt('report_is_local')));
	}

	protected function buildTable($table)
	{
		$table	->column('tpl_title', $this->plugin->txt('tpl_title'), true)
				->column('title', $this->plugin->txt('crs_title'), true)
				->column('type', $this->plugin->txt('crs_type'), true)
				->column('begin_date', $this->plugin->txt('crs_date'), true)
				->column('succ_participations', $this->plugin->txt('succ_participations'), true)
				->column('bookings', $this->plugin->txt('bookings'), true)
				->column('cancellations', $this->plugin->txt('cancellations'), true)
				->column('venue', $this->plugin->txt('venue'), true)
				->column('accomodation', $this->plugin->txt('accomodation'), true)
				->column('overnights', $this->plugin->txt('overnights'), true)
				->column('trainers', $this->plugin->txt('trainers'), true)
				->column('is_cancelled', $this->plugin->txt('is_cancelled'), true);
		return parent::buildTable($table);
	}

	protected function buildQuery($query)
	{
		$this->filter_selections = $this->getFilterSettings();
		return $query;
		/*$query
			->select('crs.crs_id')
			->select('crs.title')
			->select('crs.type')
			->select('crs.begin_date')
			->select('crs.end_date')
			->select('crs.venue')
			->select('crs.accomodation')
			->select('crs.is_cancelled')
			->select_raw('tpl.title as tpl_title')
			->select_raw("SUM(IF(usrcrs.booking_status = 'gebucht'"
						." ,1,0)) as bookings")
			->select_raw("SUM(IF("
				.$this->gIldb->in('usrcrs.booking_status',
					array('kostenfrei storniert','kostenpflichtig storniert'),false,'text')
						." ,1,0)) as cancellations")
			->select_raw("SUM(IF(usrcrs.participation_status = 'teilgenommen'"
						." AND usrcrs.booking_status = 'gebucht',1,0)) as succ_participations")
			->select_raw("GROUP_CONCAT("
						." IF(usrcrs.function = 'Trainer',CONCAT(usr.firstname,' ',usr.lastname) ,NULL)"
						." SEPARATOR ', ') as trainers")
			->select_raw("SUM(IF(usrcrs.overnights IS NOT NULL"
						." AND usrcrs.overnights > 0 "
						." ,usrcrs.overnights,0)) as overnights")
			->from('hist_course tpl')
			->join('hist_course crs')
				->on('crs.template_obj_id = tpl.crs_id')
			->left_join('hist_usercoursestatus usrcrs')
				->on(' usrcrs.crs_id = crs.crs_id AND usrcrs.hist_historic = 0 ')
			->left_join('hist_user usr')
				->on('usr.user_id = usrcrs.usr_id '
					.' AND usr.hist_historic = 0 ');
		$this->crs_topics_filter->addToQuery($query)
			->group_by('crs.crs_id')
			->compile();
		return $query;*/
	}

	protected function getFilterSettings()
	{
		$filter = $this->filter();
		if ($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
		}
		return $settings;
	}

	public function buildQueryStatement()
	{
		$cancell_condition = $this->gIldb->in(
			'usrcrs.booking_status',
			array('kostenfrei storniert','kostenpflichtig storniert'),
			false,
			'text'
		);

		$successfull = 'usrcrs.participation_status = \'teilgenommen\''
			.' AND usrcrs.booking_status = \'gebucht\'';

		$crs_has_overnights = 'usrcrs.overnights IS NOT NULL AND usrcrs.overnights > 0';

		$trainers = 'GROUP_CONCAT('
					.'	IF(usrcrs.function = \'Trainer\',CONCAT(usr.firstname,\' \',usr.lastname) ,NULL)'
					.'	SEPARATOR \', \')';
		$query =
			'SELECT'
			.'	crs.crs_id'
			.'	,crs.title'
			.'	,crs.type'
			.'	,crs.begin_date'
			.'	,crs.end_date'
			.'	,crs.venue'
			.'	,crs.accomodation'
			.'	,crs.is_cancelled'
			.'	,tpl.title AS tpl_title'
			.'	,'.$this->sumIf('usrcrs.booking_status = \'gebucht\'').' AS bookings'
			.'	,'.$this->sumIf($cancell_condition).' AS cancellations'
			.'	,'.$this->sumIf($successfull).'	AS succ_participations'
			.'	,'.$this->sumIf($crs_has_overnights, 'usrcrs.overnights').' AS overnights'
			.'	,'.$trainers.' AS trainers'
			.'	FROM hist_course tpl'
			.'	JOIN hist_course crs'
			.'		ON crs.template_obj_id = tpl.crs_id'
			.'	LEFT JOIN hist_usercoursestatus usrcrs'
			.'		ON usrcrs.usr_id = crs.crs_id AND usrcrs.hist_historic = 0'
			.'	LEFT JOIN hist_user usr'
			.'		ON usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0'
			.$this->possiblyJoinCourseTopicsFilter()
			.$this->conditionsWhere()
			.'	GROUP BY tpl.crs_id';
		return $query;
	}

	private function conditionsWhere()
	{
		$where =
			'	WHERE'
			.'		(crs.end_date < '.$this->gIldb->quote(date('Y-m-d'), 'text')
								.' OR crs.is_cancelled = \'Ja\' )'
			.'		AND crs.hist_historic = 0'
			.'		AND crs.is_template = \'Nein\''
			.'		AND tpl.hist_historic = 0'
			.'		AND tpl.is_template = \'Ja\''
			.'		AND crs.begin_date != \'0000-00-00\''
			.'		AND '.$this->gIldb->in('crs.type', array('Webinar','Präsenztraining','Virtuelles Training'), false, 'text')
			.'		'.$this->localCondition()
			.$this->addFilters();
		return $where;
	}

	private function addFilters()
	{
		return $this->datePeriodFilter()
				.$this->courseTypeFilter()
				.$this->cancelledFilter();
	}

	private function courseTypeFilter()
	{
		$selection = $this->filter_selections['crs_type'];
		if (count($selection) > 0) {
			return '		AND '.$this->gIldb->in('crs.type', $selection, false, 'text');
		}
		return '';
	}

	private function cancelledFilter()
	{
		$selection = $this->filter_selections['cancelled'];
		if (count($selection) > 0) {
			return '('.implode(' OR ', $selection).')';
		}
		return '';
	}

	private function datePeriodFilter()
	{
		if ($this->filter_selections['start'] !== null) {
			$start = $this->filter_selections['start'];
		} else {
			$start = date('Y').'-01-01';
		}
		if ($this->filter_selections['end'] !== null) {
			$end = $this->filter_selections['end'];
		} else {
			$end = date('Y').'-12-31';
		}
		$start = $this->gIldb->quote($start, 'date');
		$end = $this->gIldb->quote($end, 'date');
		return '	AND ( ( (`crs`.`begin_date` >= '.$start
				.' 			OR `crs`.`begin_date` = \'0000-00-00\' OR `crs`.`begin_date` = \'-empty-\' )'
				.'		AND `crs`.`begin_date` <= '.$end.' ) )';
	}

	private function possiblyJoinCourseTopicsFilter()
	{
		$selection = $this->filter_selections['categories'];
		if (count($selection) > 0) {
			return
				'	JOIN (SELECT topic_set_id FROM hist_topicset2topic JOIN hist_topics '
				.'			USING (topic_id) '
				.'			WHERE '.$this->gIldb->in('topic_title', $selection, false, 'text')
				.'			GROUP BY topic_set_id) as topics'
				.'		ON crs.topic_set = topics.topic_set_id';
		}
		return '';
	}

	private function localCondition()
	{
		if ((string)$this->settings['is_local'] === "1") {
			return '	AND '.$this->gIldb->in('tpl.crs_id', array_unique($this->getSubtreeCourseTemplates()), false, 'integer');
		}
		return '';
	}

	private function sumIf($condition, $function = '1')
	{
		return 'SUM(IF('.$condition.','.$function.',0))';
	}

	protected function buildFilter($filter)
	{
		/*$local_condition = (string)$this->settings['is_local'] === "1"
            ? $this->gIldb->in('tpl.crs_id',array_unique($this->getSubtreeCourseTemplates()),false,'integer')
			: 'TRUE';
		$this->crs_topics_filter = new courseTopicsFilter('crs_topics','crs.topic_set');
		$this->crs_topics_filter->addToFilter($filter);
		$filter
			->dateperiod( 	  "period"
							, $this->plugin->txt("period")
							, $this->plugin->txt("until")
							, "crs.begin_date"
							, "crs.begin_date"
							, date("Y")."-01-01"
							, date("Y")."-12-31"
							, false
							)
            ->multiselect_custom( 'cancelled'
								, $this->plugin->txt("filter_cancelled")
                                , array("crs.is_cancelled  = 'Ja'"
										=> $this->plugin->txt('crs_is_cancelled'),
                                        "crs.is_cancelled  != 'Ja' OR crs.is_cancelled IS NULL"
										=> $this->plugin->txt('crs_is_not_cancelled'))
								, array()
								, ''
								, 200
								, 160
								)
			->multiselect(	   "training_type"
							 , $this->plugin->txt("training_type")
							 , 'crs.type'
							 , array('Webinar','Präsenztraining','Virtuelles Training')
							 , array()
							 , ""
							 , 200
                             , 160
							)
			->static_condition("(crs.end_date < ".$this->gIldb->quote(date('Y-m-d'),'text')
								." OR crs.is_cancelled = 'Ja' )")
			->static_condition('crs.hist_historic = 0')
			->static_condition("crs.is_template = 'Nein'")
			->static_condition("crs.begin_date != '0000-00-00'")
			->static_condition($this->gIldb->in('crs.type',array('Webinar','Präsenztraining','Virtuelles Training'),false,'text'))
			->static_condition($local_condition)
			->static_condition('tpl.hist_historic = 0')
			->static_condition('tpl.is_template = '.$this->gIldb->quote('Ja','text'))
			->action($this->filter_action)
			->compile();*/
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

		return
			$f->sequence(
				$f->sequence(
					$f->dateperiod(
						$txt("period"),
						''
					),
					$f->multiselectsearch(
						$lng->txt('categories'),
						'',
						gevAMDUtils::getInstance()->getOptions(gevSettings::CRS_AMD_TOPIC)
					),
					$f->multiselectsearch(
						$txt('training_type'),
						'',
						array('Webinar' => 'Webinar',
							'Präsenztraining' => 'Präsenztraining',
							'Virtuelles Training' => 'Virtuelles Training')
					),
					$f->multiselectsearch(
						$txt('filter_cancelled'),
						'',
						array(	"crs.is_cancelled  = 'Ja'"
									=> $this->plugin->txt('crs_is_cancelled'),
								"crs.is_cancelled  != 'Ja' OR crs.is_cancelled IS NULL"
									=> $this->plugin->txt('crs_is_not_cancelled'))
					)
				)
			)->map(function ($start, $end, $topics, $training_type, $cancelled, $categories) {
						return array('start' => $start->format('Y-m-d'),
									'end' => $end->format('Y-m-d'),
									'categories' => $categories,
									'training_type' => $training_type,
									'cancelled' => $cancelled
									);
			}, $tf->dict(array(
									'start' => $tf->string(),
									'end' => $tf->string(),
									'categories' => $tf->lst($tf->string()),
									'training_type' => $tf->lst($tf->string()),
									'cancelled' => $tf->lst($tf->string())

							)));
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
}
