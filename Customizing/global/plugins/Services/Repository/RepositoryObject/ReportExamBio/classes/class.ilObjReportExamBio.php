<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase2.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catSelectableReportTableGUI.php';
require_once 'Services/GEV/Utils/classes/class.gevCourseUtils.php';


class ilObjReportExamBio extends ilObjReportBase2 {

	private $forwarded = false;
	private $taget_user = null;
	private $target_course = null;

	const TRAINING = 'training';
	const TRAINING_USER = 'training_user';
	const USER = 'user';

	public function title() {
		switch($this->mode) {
			case self::USER:
				return $this->plugin->txt('my_exam_bio');
			case self::TRAINING:
				return sprintf($this->plugin->txt('participants_exam_bio'),$this->target_course->getTitle());
			case self::TRAINING_USER:
				return sprintf($this->plugin->txt('others_exam_bio'),$this->target_user->getLastname(),$this->target_user->getFirstname());
			default:
				return parent::title();
		}
	}

	public function description() {
		switch($this->mode) {
			case self::USER:
				return $this->plugin->txt('my_exam_bio_desc');
			case self::TRAINING:
				return sprintf($this->plugin->txt('participants_exam_bio_desc'));
			case self::TRAINING_USER;
				return sprintf($this->plugin->txt('others_exam_bio_desc'));
			default:
				return parent::description();
		}
	}

	private static function getNonvisualSettings($sf) {
		return $sf->reportSettings('rep_robj_rexbio')
				->addSetting($sf->settingBool('for_trainer',''));
	}

	public static function queryReports(array $obj_properties, $db) {
		$sf = new settingFactory($db);
		return $sf->reportSettingsDataHandler()->query($obj_properties,self::getNonvisualSettings($sf));
	}

	public static function readReportProperties($obj_id, $db) {
		$sf = new settingFactory($db);
		return $sf->reportSettingsDataHandler()->readObjEntry($obj_id,self::getNonvisualSettings($sf));
	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->sf->reportSettings('rep_robj_rexbio');
		$target_training_id = null;
		if($this->getRefId()) {
			$target_training_id = $this->getParentObjectOfTypeIds('crs')['obj_id'];
		}
		if($target_training_id) {
			$this->local_report_settings =
				$this->local_report_settings
					->addSetting($this->sf
						->settingBool('for_trainer',$this->plugin->txt('for_trainer')));
		}
	}

	public function forTrainerView() {
		return $this->settings['for_trainer'] && !$this->forwarded;
	}

	public function initType() {
		$this->setType("xexb");
	}

	public function prepareTable(catSelectableReportTableGUI $table) {

		if($this->forTrainerView()) {
			$table->defineFieldColumn($this->plugin->txt('lastname'),'lastname'
						,array('lastname' => $this->space->table('usr')->field('lastname')))
				->defineFieldColumn($this->plugin->txt('firstname'),'firstname'
						,array('firstname' => $this->space->table('usr')->field('firstname')))
				->defineFieldColumn($this->plugin->txt('orgunit'),'orgunit'
						,array('orgunit' => $this->space->table('orgu_all')->field('orgus')),true);
		}

		$max_points = $this->space->table('all_pass')->field('max_points');
		$acheived_points = $this->space->table('all_pass')->field('points_achieved');
		$test_title = $this->space->table('recent_pass_data')->field('test_title');
		$passed = $this->space->table('recent_pass_data')->field('test_passed');
		$testrun_finished_ts = $this->space->table('recent_pass_data')->field('testrun_finished_ts');
		$avg = $this->tf->avg('average', $this->tf->quot('quot',$acheived_points,$max_points));
		$max = $this->tf->max('best', $this->tf->quot('quot',$acheived_points,$max_points));

		$table->defineFieldColumn($this->plugin->txt('test_title'),'test_title',
					array('test_title' => $test_title))
			->defineFieldColumn($this->plugin->txt('test_date'),'test_date',
					array('test_date' => $testrun_finished_ts),true)
			->defineFieldColumn($this->plugin->txt('passed'),'passed',
					array('passed' => $passed))
			->defineFieldColumn($this->plugin->txt('max'),'max',array('max' => $max))
			->defineFieldColumn($this->plugin->txt('average'),'average',array('average' => $avg))
			->defineFieldColumn($this->plugin->txt('number_of_runs'),'runs',
					array('runs' => $this->tf->countAll('number_of_runs')),true);


		$this->space = $table->prepareTableAndSetRelevantFields($this->space);
		return $table;
	}

	public function filter() {
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$for_trainer_view = $this->forTrainerView();
		$space = $this->space;
		$sequence_args = array(
			$f->dateperiod( $this->plugin->txt("dateperiod"), "")
				->map(	function($start,$end) use ($f,$space) {
							$pc = $f->dateperiod_timestamp_overlaps_predicate_fields
								( $space->table('recent_pass_data')->field('testrun_finished_ts')
								, $space->table('recent_pass_data')->field('testrun_finished_ts'));
							return $pc($start,$end);
						}
						,$tf->cls("CaT\Filter\Predicates\Predicate")));

		if($for_trainer_view) {
			$sequence_args[] = $f->text($this->plugin->txt('lastname'))
								->map(	function($lastname) use ($f,$space,$pf) {
											if(!$lastname) {
												return $pf->_TRUE();
											}
											$pc = $f->text_like_field($space->table('usr')->field('lastname'));
											return $pc($lastname.'%');
										}
										,$tf->cls("CaT\Filter\Predicates\Predicate"));
		}

		$sequence_args[] = $f->multiselect($this->plugin->txt('test_title'),'',$this->getDistinctTests())
							->map(	function($obj_ids) use ($f,$space,$pf) {
										if(count($obj_ids) === 0) {
											return $pf->_TRUE();
										}
										return $space->table('recent_pass_data')->field('obj_id')->IN($pf->list_int_by_array($this->convertMixedArrayToIntArray($obj_ids)));
									}
									,$tf->cls("CaT\Filter\Predicates\Predicate"));

		$sequence_args[] = $f->multiselect($this->plugin->txt('pass_status'),'',$this->getDistinctPass())
							->map(	function($pass) use ($f,$space,$pf) {
										if(count($pass) === 0) {
											return $pf->_TRUE();
										}
										return $space->table('recent_pass_data')->field('test_passed')->IN($pf->list_int_by_array($this->convertMixedArrayToIntArray($pass)));
									}
									,$tf->cls("CaT\Filter\Predicates\Predicate"));

		return $f->sequence(call_user_func_array(array($f,'sequence'),$sequence_args)->map(
				function (/*args*/) use ($for_trainer_view) {
					$return = array();
					$args = func_get_args();
					if(count($args) > 0) {
						$return['last_pass_datetime_predicate'] = array_shift($args);
						if($for_trainer_view) {
							$return['lastname_predicate'] = array_shift($args);
						}
						$return['test_title_predicate'] = array_shift($args);
						$return['test_passed_predicate'] = array_shift($args);
					}
					return $return;
				}
				,$tf->lst($tf->cls("CaT\Filter\Predicates\Predicate"))
			));

	}

	public function initSpace() {
		$aux = $this->tf->histUsertestrun('recent_pass_aux');

		if(count($this->relevantUsers()) > 0 ) {
			$aux = $aux->addConstraint($aux->field('hist_historic')->EQ()->int(0)->_AND($aux->field('usr_id')->IN($this->pf->list_int_by_array($this->relevantUsers()))));
		} else {
			$aux = $aux->addConstraint($this->pf->_FALSE());
		}

		$recent_pass_case = $this->tf->derivedTable(
			$this->tf->TableSpace()
				->addTablePrimary($aux)
				->setRootTable($aux)
				->request($aux->field('usr_id'))
				->request($aux->field('obj_id'))
				->request($this->tf->max('recent_pass',$aux->field('pass')))
				->groupBy($aux->field('usr_id'))
				->groupBy($aux->field('obj_id')),'recent_pass_case');

		$all_pass = $this->tf->histUsertestrun('all_pass');
		$all_pass = $all_pass->addConstraint($all_pass->field('hist_historic')->EQ()->int(0));

		$recent_pass_data = $this->tf->histUsertestrun('recent_pass_data');
		$recent_pass_data = $recent_pass_data->addConstraint($recent_pass_data->field('hist_historic')->EQ()->int(0));

		$usr = $this->tf->histUser('usr');

		if(count($this->relevantUsers()) > 0 ) {
			$usr = $usr->addConstraint($usr->field('hist_historic')->EQ()->int(0)->_AND($usr->field('user_id')->IN($this->pf->list_int_by_array($this->relevantUsers()))));
		} else {
			$usr = $usr->addConstraint($this->pf->_FALSE());
		}

		$orgus = $this->tf->allOrgusOfUsers('orgu_all',$this->relevantUsers());

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
			->request($recent_pass_data->field('usr_id'))
			->request($recent_pass_data->field('obj_id'))
			->groupBy($recent_pass_case->field('usr_id'))
			->groupBy($recent_pass_case->field('obj_id'));
	}

	public function applyFilterToSpace($filter_values) {
		$settings = call_user_func_array(array($this->filter(), "content"), $filter_values)[0];

		$predicate = current($settings);
		while($sub_predicate = next($settings)) {
			$predicate = $predicate->_AND($sub_predicate);
		}
		$this->space->addFilter($predicate);
	}


	private function getDistinctTests() {
		$sql = 'SELECT obj_id, test_title FROM hist_usertestrun '
				.'	WHERE '.$this->gIldb->in('usr_id',$this->relevantUsers(),false,'integer')
				.'	AND hist_historic = 0'
				.'	GROUP BY obj_id';
		$res = $this->gIldb->query($sql);
		$return = array();
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$return[$rec['obj_id']] = $rec['test_title'];
		}
		return $return;
	}

	private function getDistinctPass() {
		return array(0 => $this->plugin->txt('test_not_passed')
					,1 => $this->plugin->txt('test_passed'));
	}

	private function convertMixedArrayToIntArray(array $ints) {
		$return = array();
		foreach ($ints as $int) {
			if($this->checkForInt($int)) {
				$return[] = intval($int);
			} else {
				throw new ilReportException('trying to intify non int');
			}
		}
		return $return;
	}

	private function checkForInt($int) {
		if(is_numeric($int) && (string)intval($int) === (string)$int) {
			return true;
		}
		return false;
	}

	private function relevantUsers() {
		return array_map(function($nummeric) {return (int)$nummeric;},$this->target_user_ids);
	}

	public function setTargets($viewer_id, $target_user_id = null) {
		if($this->settings['for_trainer']) {
			//report is accessed inside training
			$target_training_id = $this->getParentObjectOfTypeIds('crs')['obj_id'];
			if(!$target_training_id) {
				throw new ilException('may not view requested parameters');
			}
			if($target_user_id === null) {
				$this->mode = self::TRAINING;
				$this->target_course = gevCourseUtils::getInstanceByObj(new ilObjCourse($target_training_id,false));
				$this->target_user_ids = $this->target_course->getParticipants();
			} elseif($target_user_id !== null) {
				if(!in_array($target_user_id, gevCourseUtils::getInstanceByObj(new ilObjCourse($target_training_id,false))->getParticipants())) {
					throw new ilException('may not view requested parameters');
				}
				$this->mode = self::TRAINING_USER;
				$this->target_user = gevUserUtils::getInstance($target_user_id);
				$this->target_user_ids = array($target_user_id);
				$this->forwarded = true;
			}
		} else {
			$this->mode = self::USER;
			// own report, screw the target_user_id parameter
			$this->target_user_ids = array($viewer_id);
		}
	}

	public static function getAccessibleExambioInCrsForUserRefIds(gevCourseUtils $crs,$usr_id, $access, $db) {
		$ex_bios = $crs->objsInCourseOfType('xexb');
		$return = array();
		foreach ($ex_bios as $ex_bio) {
			if($access->checkAccessOfUser($usr_id ,'read', '', $ex_bio['ref_id']) && ilObjReportExamBio::readReportProperties($ex_bio['obj_id'],$db)['for_trainer']) {
				$return[] = $ex_bio['ref_id'];
			}
		}
		return $return;
	}
}