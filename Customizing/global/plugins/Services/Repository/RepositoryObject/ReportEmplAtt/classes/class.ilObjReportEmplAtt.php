<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevCourseUtils.php';

ini_set("memory_limit", "2048M");
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportEmplAtt extends ilObjReportBase
{
	protected $relevant_parameters = array();

	public function __construct($ref_id = 0)
	{
		parent::__construct($ref_id);
		global $lng;
		$this->gLng = $lng;
	}

	public function initType()
	{
		 $this->setType("xrea");
	}

	protected function createLocalReportSettings()
	{
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rea');
	}

	protected function getFilterSettings()
	{
		$filter = $this->filter();
		if ($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
		}
		return $settings;
	}

	/**
	 * @inheritdoc
	 */
	protected function buildQuery($query)
	{
		$this->filter_selections = $this->getFilterSettings();
		$query
			->select("usr.lastname")
			->select("usr.firstname")
			->select("usr.email")
			->select_raw("usr.adp_number")
			->select_raw("usr.job_number")
			->select("orgu_all.org_unit_above1")
			->select("orgu_all.org_unit_above2")
			->select_raw("GROUP_CONCAT(DISTINCT orgu_all.orgu_title SEPARATOR ', ') AS org_unit")
			->select_raw("GROUP_CONCAT(DISTINCT role.rol_title ORDER BY role.rol_title SEPARATOR ', ') AS roles")
			->select("usr.position_key")
			->select("crs.custom_id")
			->select("crs.title")
			->select("crs.venue")
			->select("crs.type")
			->select("usrcrs.credit_points")
			->select("usrcrs.booking_status")
			->select("usrcrs.participation_status")
			->select("usrcrs.begin_date")
			->select("usrcrs.end_date")
			->select("crs.edu_program")
			->from("hist_user usr")
			->join("hist_usercoursestatus usrcrs")
				->on("usr.user_id = usrcrs.usr_id AND usrcrs.hist_historic = 0")
			->join("hist_course crs")
				->on("crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0")
			->left_join("hist_userorgu orgu_all")
				->on("orgu_all.usr_id = usr.user_id")
			->left_join("hist_userrole role")
				->on("role.usr_id = usr.user_id");
		if ($this->orgusFiltered()) {
			$query->join("hist_userorgu orgu_filter")
				->on("orgu_filter.usr_id = usr.user_id "
					." 	AND ".$this->gIldb->in('orgu_filter.orgu_id', $this->getSelectedAndRecursiveOrgus(), false, 'integer')
					."	AND orgu_filter.action >=0 "
					."	AND orgu_filter.hist_historic = 0 "
					."	AND orgu_filter.rol_title = ".$this->gIldb->quote("Mitarbeiter", "text"));
		}
		if ($this->crsTopicsFiltered()) {
			$query->raw_join(' JOIN (SELECT topic_set_id FROM hist_topicset2topic JOIN hist_topics '
							.'			USING (topic_id) '
							.'			WHERE '.$this->gIldb->in('topic_title', $this->filter_selections['crs_topics'], false, 'text')
							.'			GROUP BY topic_set_id) as topics'
							.'	ON crs.topic_set = topics.topic_set_id');
		}
		$query
			->group_by("usr.user_id")
			->group_by("usrcrs.crs_id")
			->compile()
			;
		return $query;
	}

	private function orgusFiltered()
	{
		return count($this->filter_selections['orgus']) > 0;
	}

	private function crsTopicsFiltered()
	{
		return count($this->filter_selections['crs_topics']) > 0;
	}

	private function getSelectedAndRecursiveOrgus()
	{
		if ($this->filter_selections['recursive']) {
			return array_unique($this->addRecursiveOrgusToSelection($this->filter_selections['orgus']));
		}
		return  $this->filter_selections['orgus'];
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

	/**
	 * @inheritdoc
	 */
	protected function buildOrder($order)
	{
		$order->mapping("date", "crs.begin_date")
				->mapping("od_bd", array("org_unit_above1", "org_unit_above2"))
				->defaultOrder("lastname", "ASC")
				;
		return $order;
	}

	/**
	 * @inheritdoc
	 */
	protected function buildTable($table)
	{
		$table
			->column("lastname", $this->plugin->txt("lastname"), true)
			->column("firstname", $this->plugin->txt("firstname"), true)
			->column("email", $this->plugin->txt("email"), true)
			->column("adp_number", $this->plugin->txt("adp_number"), true)
			->column("job_number", $this->plugin->txt("job_number"), true)
			->column("od_bd", $this->plugin->txt("od_bd"), true, "", false, false)
			->column("org_unit", $this->plugin->txt("org_unit_short"), true)
			->column("roles", $this->plugin->txt("roles"), true)
			->column("custom_id", $this->plugin->txt("training_id"), true)
			->column("title", $this->plugin->txt("title"), true)
			->column("venue", $this->plugin->txt("location"), true)
			->column("type", $this->plugin->txt("learning_type"), true)
			->column("date", $this->plugin->txt("date"), true)
			->column("credit_points", $this->plugin->txt("credit_points"), true)
			->column("booking_status", $this->plugin->txt("booking_status"), true)
			->column("participation_status", $this->plugin->txt("participation_status"), true)
			;
		return parent::buildTable($table);
	}

	protected function queryWhere()
	{
		return '	WHERE'.PHP_EOL
				.'		usr.hist_historic = 0'
				.'		AND '.$this->gIldb->in("usr.user_id", $this->user_utils->getEmployeesWhereUserCanViewEduBios(), false, "integer")
				.'		AND usrcrs.booking_status != \'-empty-\''
				.'		AND usrcrs.participation_status != \'-empty-\''
				.'		AND usrcrs.booking_status != \'kostenfrei storniert\''
				.'		AND orgu_all.action >= 0'
				.'		AND orgu_all.hist_historic = 0'
				.'		AND orgu_all.rol_title = \'Mitarbeiter\''
				.'		AND role.action = 1'
				.'		AND role.hist_historic = 0'
				.$this->participationStausFilter()
				.$this->wbdImportedFilter()
				.$this->templateTitleFilter()
				.$this->datePeriodFilter()
				;
	}

	private function participationStausFilter()
	{
		$selection = $this->filter_selections['p_status'];
		if (count($selection) > 0) {
			return '	AND '.$this->gIldb->in('usrcrs.participation_status', $selection, false, 'text');
		}
		return '';
	}

	private function templateTitleFilter()
	{
		$selection = $this->filter_selections['tpl_title'];
		if (count($selection) > 0) {
			return '	AND '.$this->gIldb->in('crs.template_title', $selection, false, 'text');
		}
		return '';
	}

	private function wbdImportedFilter()
	{
		if ($this->filter_selections['no_wbd']) {
			return '	AND crs.crs_id > 0';
		}
		return '';
	}

	private function datePeriodFilter()
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

	/**
	 * @inheritdoc
	 */
	protected function buildFilter($filter)
	{
		return null;
	}

	private function templateTitles()
	{
		$return = array();
		foreach (gevCourseUtils::getTemplateTitleFromHisto() as $title) {
			$return[$title] = $title;
		}
		return $return;
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
					$f->option(
						$txt('filter_no_wbd_imported'),
						''
					),
					$f->multiselectsearch(
						$txt("title"),
						'',
						$this->templateTitles()
					),
					$f->multiselectsearch(
						$txt("participation_status"),
						'',
						array(	"teilgenommen"=>"teilgenommen"
								,"fehlt ohne Absage"=>"fehlt ohne Absage"
								,"fehlt entschuldigt"=>"fehlt entschuldigt"
								,"nicht gesetzt"=>"gebucht, noch nicht abgeschlossen")
					),
					$f->multiselectsearch(
						$lng->txt("gev_filter_topics"),
						'',
						$this->getTopics()
					)
				)
			)->map(
				function ($recursive, $orgus, $start, $end, $no_wbd, $tpl_title, $p_status, $crs_topics) {
						return array(
								'recursive' => $recursive
								,'orgus' => $orgus
								,'start' => $start
								,'end' => $end
								,'no_wbd' => $no_wbd
								,'tpl_title' => $tpl_title
								,'p_status' => $p_status
								,'crs_topics' => $crs_topics
							);
				},
				$tf->dict(
					array(
								'recursive' => $tf->bool()
								,'orgus' => $tf->lst($tf->int())
								,'start' => $tf->cls('DateTime')
								,'end' => $tf->cls('DateTime')
								,'no_wbd' => $tf->bool()
								,'tpl_title' => $tf->lst($tf->string())
								,'p_status' => $tf->lst($tf->string())
								,'crs_topics' => $tf->lst($tf->string())
							)
				)
			);
	}

	private function getRelevantOrgus()
	{
		$orgu_ids = array_unique(array_map(
			function ($ref_id) {
				return ilObject::_lookupObjectId($ref_id);
			},
			$this->user_utils->getOrgUnitsWhereUserCanViewEduBios()
		));
		$return = array();
		foreach ($orgu_ids as $orgu_id) {
			$return[$orgu_id] = ilObject::_lookupTitle($orgu_id);
		}
		return $return;
	}

	private function getTopics()
	{
		require_once 'Services/GEV/Utils/classes/class.gevAMDUtils.php';
		require_once 'Services/GEV/Utils/classes/class.gevSettings.php';
		return	gevAMDUtils::getInstance()->getOptions(gevSettings::CRS_AMD_TOPIC);
	}

	protected function getRowTemplateTitle()
	{
		return "tpl.gev_attendance_by_employee_row.html";
	}

	public function getRelevantParameters()
	{
		return $this->relevant_parameters;
	}
}
