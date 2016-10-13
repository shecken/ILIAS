<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catSelectableReportTableGUI.php';
use CaT\TableRelations as TableRelations;
use CaT\Filter as Filters;

class ilObjReportExamBio extends ilObjReportBase {

	public function __construct($a_ref_id = 0) {
		global $ilUser;
		$this->gUser = $ilUser;
		parent::__construct($a_ref_id);
		$this->gf = new TableRelations\GraphFactory();
		$this->pf = new Filters\PredicateFactory();
		$this->tf = new TableRelations\TableFactory($this->pf, $this->gf);

	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rcpn');
	}

	public function initType() {
		$this->setType("xexb");
	}
//defineFieldColumn($title, $column_id, array $fields = array(), $selectable = false, $sort = true , $no_excel =  false)
	public function prepareTable(catSelectableReportTableGUI $table) {
		if($this->isForTrainer()) {
			$table->defineFieldColumn($this->plugin->txt('lastname'),'lastname'
						,array('lastname' => $this->space->table('usr')->field('lastname')))
				->defineFieldColumn($this->plugin->txt('firstname'),'firstname'
						,array('firstname' => $this->space->table('usr')->field('firstname')))
				->defineFieldColumn($this->plugin->txt('orgunit'),'orgunit'
						,array('orgunit' => $this->space->table('orgu_all')->field('orgus')));
		}

		$max_points = $this->space->table('all_pass')->field('max_points');
		$acheived_points = $this->space->table('all_pass')->field('points_achieved');
		$test_title = $this->space->table('recent_pass_data')->field('test_title');
		$passed = $this->space->table('recent_pass_data')->field('test_passed');
		$testrun_finished_ts = $this->space->table('recent_pass_data')->field('testrun_finished_ts');
		$avg = $this->tf->avgSql('average', $this->tf->quotSql('quot',$acheived_points,$max_points));
		$max = $this->tf->maxSql('best', $this->tf->quotSql('quot',$acheived_points,$max_points));

		$table->defineFieldColumn($this->plugin->txt('test_title'),'test_title',
					array('test_title' => $test_title))
			->defineFieldColumn($this->plugin->txt('test_date'),'test_date',
					array('test_date' => $testrun_finished_ts))
			->defineFieldColumn($this->plugin->txt('passed'),'passed',
					array('passed' => $passed))
			->defineFieldColumn($this->plugin->txt('average'),'average',array('average' => $avg))
			->defineFieldColumn($this->plugin->txt('max'),'max',array('max' => $max))
			->defineFieldColumn($this->plugin->txt('number_of_runs'),'runs',
					array('runs' => $this->tf->countAllSql('number_of_runs')));


		$this->space = $table->prepareTableAndSetRelevantFields($this->space);
		return $table;
	}

	private function isForTrainer() {
		return true;
	}

	public function filter() {
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$for_trainer = $this->isForTrainer();
		$space = $this->space;
		$sequence_args = array(
			$f->dateperiod( $this->plugin->txt("error_dateperiod"), "")
				->map(	function($start,$end) use ($f,$space) {
							$pc = $f->dateperiod_timestamp_overlaps_predicate_fields
								( $space->table('recent_pass_data')->field('testrun_finished_ts')
								, $space->table('recent_pass_data')->field('testrun_finished_ts'));
							return $pc($start,$end);
						}
						,$tf->cls($tf->cls("CaT\Filter\Predicates\Predicate"))));

		if($for_trainer) {
			$sequence_args[] = $f->text($this->plugin->txt('lastname'))
								->map(	function($lastname) use ($f,$space,$pf) {
											if(!$lastname) {
												return $pf->_TRUE();
											}
											$pc = $f->text_like_field($space->table('usr')->field('lastname'));
											return $pc($lastname);
										}
										,$tf->cls($tf->cls("CaT\Filter\Predicates\Predicate")));
		}

		$sequence_args[] = $f->multiselect($this->plugin->txt('title'),'',$this->getDistinctTests())
							->map(	function($obj_ids) use ($f,$space,$pf) {
										if(count($obj_ids) === 0) {
											return $pf->_TRUE();
										}
										return $space->table('recent_pass_data')->field('obj_id')->IN($pf->list_int_by_array($usr_ids));
									}
									,$tf->cls($tf->cls("CaT\Filter\Predicates\Predicate")));

		$sequence_args[] = $f->multiselect($this->plugin->txt('title'),'',$this->getDistinctPass())
							->map(	function($pass) use ($f,$space,$pf) {
										if(count($pass) === 0) {
											return $pf->_TRUE();
										}
										return $space->table('recent_pass_data')->field('test_passed')->IN($pf->list_int_by_array($pass));
									}
									,$tf->cls($tf->cls("CaT\Filter\Predicates\Predicate")));

		return call_user_func(array($f,'sequence'),$sequence_args)->map(
				function (/*args*/) use ($for_trainer) {
					$args = func_get_args();
					reset($args);
					$return = array('last_pass_datetime_predicate' => current($args));
					next($args);
					if($for_trainer) {
						$return['lastname_predicate'] = current($args);
					}
					next($args);
					$return['test_title_predicate'] = current($args);
					next($args);
					$return['test_passed_predicate'] = current($args);
				}
				,$tf->lst($tf->cls("CaT\Filter\Predicates\Predicate"))
			);

	}

	public function initSpace() {
		$aux = $this->tf->histUsertestrun('recent_pass_aux');
		$aux = $aux->addConstraint($aux->field('hist_historic')->EQ()->int(0));

		$recent_pass_case = $this->tf->derivedTable(
			$this->tf->TableSpace()
				->addTablePrimary($aux)
				->setRootTable($aux)
				->request($aux->field('usr_id'))
				->request($aux->field('obj_id'))
				->request($this->tf->maxSql('recent_pass',$aux->field('pass')))
				->groupBy($aux->field('usr_id'))
				->groupBy($aux->field('obj_id')),'recent_pass_case');

		$all_pass = $this->tf->histUsertestrun('all_pass');
		$all_pass = $all_pass->addConstraint($all_pass->field('hist_historic')->EQ()->int(0));

		$recent_pass_data = $this->tf->histUsertestrun('recent_pass_data');
		$recent_pass_data = $recent_pass_data->addConstraint($recent_pass_data->field('hist_historic')->EQ()->int(0));

		$usr = $this->tf->histUser('usr');
		$usr = $usr->addConstraint($usr->field('hist_historic')->EQ()->int(0));
		global $ilUser;
		$orgus = $this->tf->allOrgusOfUsers('orgu_all',array((int)$ilUser->getId()));

		$this->space = $this->tf->TableSpace()
			->addTablePrimary($recent_pass_case)
			->addTableSecondary($recent_pass_data)
			->addTableSecondary($all_pass)
			->addTableSecondary($orgus)
			->addTableSecondary($usr)
			->setRootTable($recent_pass_case)
			->addDependency($this->tf->TableJoin($recent_pass_case,$all_pass,
				$recent_pass_case->field('usr_id')->EQ($all_pass->field('usr_id'))
					->_AND($recent_pass_case->field('obj_id')->EQ($all_pass->field('obj_id'))))
				)
			->addDependency($this->tf->TableJoin($recent_pass_case,$usr,
				$recent_pass_case->field('usr_id')->EQ($usr->field('user_id')))
				)
			->addDependency($this->tf->TableJoin($recent_pass_case,$orgus,
				$recent_pass_case->field('usr_id')->EQ($orgus->field('usr_id')))
				)
			->addDependency($this->tf->TableJoin($recent_pass_case,$recent_pass_data,
				$recent_pass_case->field('usr_id')->EQ($recent_pass_data->field('usr_id'))
					->_AND($recent_pass_case->field('obj_id')->EQ($recent_pass_data->field('obj_id')))
					->_AND($recent_pass_case->field('recent_pass')->EQ($recent_pass_data->field('pass'))))
				)
			->groupBy($recent_pass_case->field('usr_id'))
			->groupBy($recent_pass_case->field('obj_id'));
	}

	public function buildQuery( $query) {
		return;
	}

	public function buildFilter( $filter) {
		return;
	}

	public function prepareFilter(catFilter $filter) {
		$filter->action($this->filter_action)
			->compile();
		return $filter;
	}

	public function buildQueryStatement() {	

		return $this->getInterpreter()->getSql($this->space->query());
	}

	protected function getInterpreter() {
		if(!$this->interpreter) {
			$this->interpreter = new TableRelations\SqlQueryInterpreter( new Filters\SqlPredicateInterpreter($this->gIldb), $this->pf, $this->gIldb);
		}
		return $this->interpreter;
	}

	public function deliverData(callable $callable) {
		$res = $this->gIldb->query($this->getInterpreter()->getSql($this->space->query()));
		$return = array();
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = call_user_func($callable,$rec);
		}
		return $return;
	}

	protected function getRowTemplateTitle() {
		if($this->settings['admin_mode']) {
			return "tpl.report_coupons_admin_row.html";
		}
		return "tpl.report_coupons_row.html";
	}


	protected function buildOrder($order) {
		$order 	->defaultOrder("code", "ASC")
				;
		return $order;
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

}