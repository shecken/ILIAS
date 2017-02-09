<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevCourseUtils.php';

ini_set("memory_limit", "2048M");
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportTrainerWorkload extends ilObjReportBase
{
	const MIN_ROW = "3991";
	const OP_TUTOR_IN_ORGU = 'tep_is_tutor';

	protected $table_sums;
	protected $relevant_parameters = array();
	protected $norms;


	public function __construct($ref_id = 0)
	{
		parent::__construct($ref_id);

		$this->ou_ids = null;

		require_once $this->plugin->getDirectory().'/config/cfg.trainer_workload.php';
	}

	public function initType()
	{
		 $this->setType("xrtw");
	}

	protected function getFilterSettings()
	{
		if ($this->filter_settings) {
			return call_user_func_array(array($this->filter(), "content"), $this->filter_settings);
		}
	}

	protected function createLocalReportSettings()
	{
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rtw')
				->addSetting($this->s_f
								->settingInt('annual_norm_training', $this->plugin->txt('annual_norm_training'))
								->setDefaultValue(1))
				->addSetting($this->s_f
								->settingInt('annual_norm_operation', $this->plugin->txt('annual_norm_operation'))
								->setDefaultValue(1))
				->addSetting($this->s_f
								->settingInt('annual_norm_office', $this->plugin->txt('annual_norm_office'))
								->setDefaultValue(1));
	}

	protected function hoursPerConditionRatioNorm($condition, $name, $function)
	{
		$sql = 	"SUM(IF(".$condition
				.",	".$function
				.",	0)"
				.")"
				." as ".$name;
		return $sql;
	}

	protected function buildQuery($query)
	{
		$this->set = $this->getFilterSettings();
		if ($this->set['start'] === null) {
			$this->set['start'] = new DateTime(date('Y').'-01-01');
		}
		if ($this->set['end'] === null) {
			$this->set['end'] = new DateTime(date('Y').'-12-31');
		}
		return null;
	}

	protected function buildFilter($filter)
	{

		return null;
	}



	public function filter()
	{
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$txt = function ($id) {
			return $this->plugin->txt($id);
		};

		return $f->sequence(
			$f->option(
				$txt("org_unit_recursive"),
				""
			)->clone_with_checked(true),
			$f->sequence(
				$f->dateperiod($txt("dateperiod"), ""),
				$f->multiselectsearch($txt("org_unit_short"), "", $this->getRelevantOrgus())
			)
		)->map(function ($recursive, $start, $end, $org_unit) {
						return array(
							'recursive' => $recursive
							, "org_unit" => $org_unit
							, "start" => $start
							, "end" => $end
							);
		}, $tf->dict(array(
							'recursive' => $tf->bool()
							,"org_unit" => $tf->lst($tf->int())
							,"start" => $tf->cls('DateTime')
							,"end" => $tf->cls('DateTime'))));
	}

	protected function getRowTemplateTitle()
	{
		return "tpl.gev_trainer_workload_row.html";
	}

	protected function buildTable($table)
	{
		$norms = $this->getNorms();
		$table->column("fullname", $this->plugin->txt("fullname"), true);
		foreach ($this->meta_cats as $meta_category => $categories) {
			foreach ($categories as $category) {
				$table->column($category, $this->plugin->txt($category), true);
			}
			if (count($categories)>1) {
				$table->column($meta_category."_sum", $this->plugin->txt($meta_category."_sum"), true);
			}
			if (isset($norms[$meta_category])) {
				$table->column($meta_category."_workload", $this->plugin->txt($meta_category."_workload"), true);
			}
		}
		$this->buildSumTable();
		return parent::buildTable($table);
	}

	protected function buildSumTable()
	{
		$norms = $this->getNorms();
		$this->table_sums = catReportTable::create();
		foreach ($this->meta_cats as $meta_category => $categories) {
			foreach ($categories as $category) {
				$this->table_sums->column($category, $this->plugin->txt($category), true);
			}
			if (count($categories)>1) {
				$this->table_sums->column($meta_category."_sum", $this->plugin->txt($meta_category."_sum"), true);
			}
			if (isset($norms[$meta_category])) {
				$this->table_sums->column($meta_category."_workload", $this->plugin->txt($meta_category."_workload"), true);
			}
		}
		$this->table_sums->template("tpl.gev_trainer_workload_sum_row.html", $this->plugin->getDirectory());
	}

	public function deliverSumTable()
	{
		return $this->table_sums;
	}

	protected function buildOrder($order)
	{
		return null;
	}

	protected function getNorms()
	{
		$norms = array();
		$norms['training']  = $this->settings['annual_norm_training'];
		$norms['operation']  = $this->settings['annual_norm_operation'];
		$norms['office']  = $this->settings['annual_norm_office'];
		return $norms;
	}

	protected function filterSettings()
	{
		if ($this->filter_settings) {
			return call_user_func_array(array($filter, "content"), $this->filter_settings);
		}
	}

	public function buildQueryStatement()
	{
		$filter = $this->filter();
		$db = $this->gIldb;
		$query = 'SELECT `hu`.`user_id` ,CONCAT(hu.lastname, \', \', hu.firstname) as fullname';

		foreach ($this->cats as $condition => $cat_settings) {
			$query .= ", " .$this->hoursPerConditionRatioNorm($cat_settings['condition'], $condition, $cat_settings['weight']);
		}
		$query .= 	'	FROM `hist_tep` ht'
					.'	JOIN `hist_user` hu ON ht.user_id = hu.user_id'
					.'		AND '.$db->in('hu.user_id', $this->getRelevantUsers(), false, 'integer')
					.'	JOIN `hist_tep_individ_days` htid ON individual_days = id'
					.'	LEFT JOIN `hist_course` hc'
					.'		ON context_id = crs_id'
					.'			AND ht.category = \'Training\''
					.'			AND hc.hist_historic = 0'
					.'			AND hc.type != '.$this->gIldb->quote(gevCourseUtils::CRS_TYPE_COACHING, "text")
					.'	WHERE'
					.'		ht.row_id > '.$this->gIldb->quote(self::MIN_ROW, 'integer')
					.'		AND hu.hist_historic = 0'
					.'		AND ht.hist_historic = 0'
					.'		AND (ht.category != \'Training\' OR (ht.context_id != 0 AND ht.context_id IS NOT NULL))'
					.'		AND ht.deleted = 0'
					.'		AND '.$this->getDatePeriodFilter();



		$query .= "	GROUP BY `hu`.`user_id`";
		$query .= $this->queryOrder();
		return $query;
	}

	private function getDatePeriodFilter()
	{
		$start = $this->gIldb->quote($this->set['start']->format('Y-d-m'), 'date');
		$end = $this->gIldb->quote($this->set['end']->format('Y-d-m'), 'date');
		return '( (`ht`.`begin_date` >= '.$start
				.' OR `ht`.`begin_date` = \'0000-00-00\''
				.' OR `ht`.`begin_date` = \'-empty-\' )'
				.'	AND `ht`.`begin_date` <= '.$end.' )';
	}

	protected function fetchData(callable $callback)
	{
		$db = $this->gIldb;
		$res = $db->query($this->buildQueryStatement());
		while ($rec = $db->fetchAssoc($res)) {
			$data[] = $rec;
		}

		$norms = $this->getNorms();
		$count_rows = 0;
		$this->sum_row = array();
		foreach ($this->meta_cats as $meta_category => $categories) {
			foreach ($categories as $category) {
				$this->sum_row[$category] = 0;
			}
			if (count($categories)>1) {
				$this->sum_row[$meta_category.'_sum'] = 0;
			}
			if (isset($norms[$meta_category])) {
				$this->sum_row[$meta_category.'_workload'] = 0;
			}
		}
		$period_days_factor = $this->getPeriodDays($this->set['start'], $this->set['end'])/365;
		foreach ($data as &$trainer_data) {
			$count_rows++;
			foreach ($this->meta_cats as $meta_category => $categories) {
				if (count($categories)>1) {
					$trainer_data[$meta_category.'_sum'] = 0;
					foreach ($categories as $category) {
						$this->sum_row[$category] += $trainer_data[$category];
						$trainer_data[$meta_category.'_sum'] += $trainer_data[$category];
					}
					$this->sum_row[$meta_category.'_sum'] += $trainer_data[$meta_category.'_sum'];
					if (isset($norms[$meta_category])) {
						$trainer_data[$meta_category.'_workload'] = 100*$trainer_data[$meta_category.'_sum']/($norms[$meta_category]*$period_days_factor);
						$this->sum_row[$meta_category.'_workload'] += $trainer_data[$meta_category.'_workload'];
					}
				} else {
					$this->sum_row[$meta_category] += $trainer_data[$meta_category];
					if (isset($this->norms[$meta_category])) {
						$meta_category_sum = count($categories)>1 ? $trainer_data[$meta_category.'_sum'] : $trainer_data[ $categories[0]];
						$trainer_data[$meta_category.'_workload'] = 100*$meta_category_sum/($norms[$meta_category]*$period_days_factor);
						$this->sum_row[$meta_category.'_workload'] += $trainer_data[$meta_category.'_workload'];
					}
				}
			}
			$trainer_data = call_user_func($callback, $trainer_data);
		}
		$count_rows = ($count_rows == 0) ? 1 : $count_rows;
		foreach ($norms as $meta_category => $norm) {
			$this->sum_row[$meta_category.'_workload'] = $this->sum_row[$meta_category.'_workload']/$count_rows;
		}
		$this->sum_row = call_user_func($callback, $this->sum_row);
		return $data;
	}

	protected function getPeriodDays(\DateTime $start, \DateTime $end)
	{
		return $period_days = ($end->getTimestamp() - $start->getTimestamp())/86400+1;
	}

	protected static function identity($rec)
	{
		return $rec;
	}

	public function fetchSumData()
	{
		return $this->sum_row;
	}

	protected function getRelevantOrgus()
	{

		$sql = 	"SELECT DISTINCT oda.title, oda.obj_id, rpa.ops_id, rop.ops_id AS chk "
				."	FROM rbac_pa rpa\n"
				."	JOIN rbac_operations rop\n"
				."		ON rop.operation = ".$this->gIldb->quote(self::OP_TUTOR_IN_ORGU, "text") ."\n"
				."			AND LOCATE( CONCAT( ':', rop.ops_id, ';' ) , rpa.ops_id ) >0\n"
				."	JOIN object_reference ore\n"
				."		ON ore.ref_id = rpa.ref_id\n"
				."	JOIN object_data oda\n"
				."		ON oda.obj_id = ore.obj_id\n";

		$res = $this->gIldb->query($sql);
		$relevant_orgus = array();

		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$perm_check = unserialize($rec['ops_id']);

			if (in_array($rec["chk"], $perm_check)) {
				$relevant_orgus[$rec['obj_id']] = $rec['title'];
			}
		}
		return array_unique($relevant_orgus);
	}

	protected function getRelevantUsers()
	{
		require_once './Services/AccessControl/classes/class.ilObjRole.php';
		$ignore_roles_ids = array();
		foreach ($this->ignore_roles as $role_title) {
			$ignore_roles_ids = array_merge($ignore_roles_ids, ilObjRole::_getIdsForTitle($role_title, 'role'));
		}
		$sql = 	"SELECT huo.usr_id, rpa.rol_id, rpa.ops_id, rop.ops_id AS chk\n"
				."	FROM rbac_pa rpa\n"
				."	JOIN rbac_operations rop\n"
				."		ON rop.operation = ".$this->gIldb->quote(self::OP_TUTOR_IN_ORGU, "text") ."\n"
				."			AND LOCATE( CONCAT( ':', rop.ops_id, ';' ) , rpa.ops_id ) >0\n"
				."	JOIN object_reference ore\n"
				."		ON ore.ref_id = rpa.ref_id\n"
				."	JOIN rbac_ua rua\n"
				."		ON rua.rol_id = rpa.rol_id\n"
				."	LEFT JOIN hist_userrole hur\n"
				."		ON hur.usr_id = rua.usr_id\n"
				."			AND ".$this->gIldb->in('hur.rol_id', $ignore_roles_ids, false, 'integer'). "\n"
				."			AND hur.hist_historic = 0\n"
				."			AND hur.action = 1\n"
				."	JOIN hist_userorgu huo\n"
				."		ON huo.`action` >= 0 AND huo.hist_historic = 0\n"
				."			AND huo.usr_id = rua.usr_id\n"
				."			AND ore.obj_id = huo.orgu_id\n"
				."	WHERE hur.hist_historic IS NULL\n"
				."		AND ".$this->gIldb->in("huo.usr_id", $this->user_utils->getEmployees(), false, "integer") ."\n"
				.$this->orguCondition();


		$res = $this->gIldb->query($sql);
		$relevant_users = array();

		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$perm_check = unserialize($rec['ops_id']);
			if (in_array($rec["chk"], $perm_check)) {
				$relevant_users[] = $rec['usr_id'];
			}
		}
		return array_unique($relevant_users);
	}


	public function getRelevantParameters()
	{
		return $this->relevant_parameters;
	}

	protected function createTemplateFile()
	{
		$norms = $this->getNorms();
		$str = fopen("Services/GEV/Reports/templates/default/"
			."tpl.gev_trainer_workload_row.html", "w");

		$tpl = '<tr class="{CSS_ROW}"><td></td>'."\n".'<td class = "bordered_right" >{VAL_FULLNAME}';
		foreach ($this->meta_cats as $meta_category => $categories) {
			foreach ($categories as $category) {
				$tpl .= "</td>\n".'<td align = "right">{VAL_'.strtoupper($category).'}';
			}
			if (count($categories)>1) {
				$class = "bold_content";
				if (!isset($norms[$meta_category])) {
					$class .= " bordered_right";
				}
				$tpl .= "</td>\n".'<td align = "right" class = "'.$class.'">{VAL_'.strtoupper($meta_category).'_SUM}';
			}
			if (isset($norms[$meta_category])) {
				$tpl.= "</td>\n".'<td align = "right" class = "bordered_right bold_content">{VAL_'.strtoupper($meta_category).'_WORKLOAD}';
			}
		}
		$tpl.= "</td>";
		$tpl .= "\n</tr>";
		fwrite($str, $tpl);
		fclose($str);

		$str = fopen("Services/GEV/Reports/templates/default/"
			."tpl.gev_trainer_workload_sum_row.html", "w");
		$tpl = '<tr class="{CSS_ROW}"><td>';
		foreach ($this->workload_meta as $meta_category => $categories) {
			foreach ($categories as $category) {
				$tpl .= "</td>\n".'<td align = "right">{VAL_'.strtoupper($category).'}';
			}
			if (count($categories)>1) {
				$class = "bold_content";
				if (!isset($norms[$meta_category])) {
					$class .= " bordered_right";
				}
				$tpl .= "</td>\n".'<td align = "right" class = "'.$class.'">{VAL_'.strtoupper($meta_category).'_SUM}';
			}
			if (isset($norms[$meta_category])) {
				$tpl.= "</td>\n".'<td align = "right" class = "bordered_right bold_content">{VAL_'.strtoupper($meta_category).'_WORKLOAD}';
			}
		}
		$tpl.= "</td>";
		$tpl .= "\n</tr>";
		fwrite($str, $tpl);
		fclose($str);
	}

	/**
	 * Retrive recursice  filter selection
	 *
	 * @return 	bool
	 */
	public function getRecursiveSelection()
	{
		return $this->set['recursive'];
	}

	/**
	 * Retrive bulk orgu filter selection
	 *
	 * @return 	int[]	$top_orgu_ids
	 */
	public function getSelection()
	{
		$top_orgu_ids = $this->set['org_unit'];
		return $top_orgu_ids;
	}

	/**
	 * get orgus and children depending on recursive setting
	 *
	 * @return	int[]	$orgu_ids
	 * @param	bool	$force_recursive
	 */
	public function getSelectionAndRecursive($force_recursive = false)
	{
		$orgu_ids = $this->getSelection();
		if (count($orgu_ids)>0 && ($this->getRecursiveSelection() || $force_recursive)) {
			return array_unique(array_merge($this->getChildrenOf($orgu_ids), $orgu_ids));
		}
		return $orgu_ids;
	}

	/**
	 * Get some children of given orgu ids.
	 *
	 * @return	int[]	$aux  all children of
	 * @param	int[]	$orgu_ids
	 */
	protected function getChildrenOf($orgu_ids)
	{
		require_once 'Services/GEV/Utils/classes/class.gevOrgUnitUtils.php';
		$aux = array();
		foreach ($orgu_ids as $orgu_id) {
			$ref_id = gevObjectUtils::getRefId($orgu_id);
			foreach (gevOrgUnitUtils::getAllChildren(array($ref_id)) as $child) {
				$aux[] = (int)$child["obj_id"];
			}
		}
		return $aux;
	}

	/**
	 * get query filter part for report query acc. to filter selection
	 *
	 * @return	string	$sql
	 */
	public function orguCondition()
	{
		if ($this->set['org_unit']) {
			$return = "";
			$orgus = $this->getRecursiveSelection() ? $this->getSelectionAndRecursive() : $this->getSelection();

			if (count($orgus) > 0) {
				$return .= "	AND " .$this->gIldb->in("huo.orgu_id", $orgus, false, 'integer');
			}
			return $return;
		} else {
			return "";
		}
	}
}
