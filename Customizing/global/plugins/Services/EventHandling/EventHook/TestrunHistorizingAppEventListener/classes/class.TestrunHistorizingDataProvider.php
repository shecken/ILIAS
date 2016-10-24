<?php
require_once 'Services/HistorizingStorage/interfaces/interface.DataProvider.php';
class TestrunHistorizingDataProvider implements DataProvider {

	const LAST_PASS = 'last_pass';
	const BEST_PASS = 'best_pass';

	protected $plugin;
	protected $component;
	protected $event;

	protected $case = null;#

	private $test = null;
	private $obj_id = null;

	public function __construct($a_component, $a_event, array $a_parameter, ilEventHookPlugin $plugin) {
		if(!$plugin->eventRelevant($a_component, $a_event, $a_parameter)) {
			throw new ilHistorizingException('invalid event passed to data collector');
		}
		$this->component = $a_component;
		$this->event = $a_event;
		$this->parameter = $a_parameter;
		$this->plugin = $plugin;
	}

	public function caseId() {
		if($this->case === null) {
			$this->case = $this->calculateCase();
		}
		return $this->case;
	}

	public function data() {
		if($this->data === null) {
			$this->data = $this->calculateData();
		}
		return $this->data;
	}

	public function creator() {
		global $ilUser;
		return $ilUser->getId();
	}

	public function massAction() {
		if($this->component === 'Services/Object' && $this->event === 'update') {
			return true;
		}
		return false;
	}

	private function calculateData() {

		if($this->component === 'Services/Object' && $this->event === 'update') {
			return array('test_title' => $this->getTestInstanceByObjId($this->parameter['obj_id'])->getTitle());
		} elseif($this->component === 'Modules/Test' && $this->event === 'testPassIncreased') {

			$test_session = $this->parameter['test_session'];
			$test = $this->getTestInstanceByObjId($this->getObjIdFromTestSession($test_session));

			$test_result = $test->getTestResult($test_session->getActiveId(),$test_session->getLastFinishedPass());

			return array(
				'test_title'			=> $test->getTitle()
				,'max_points'			=> $test_result['pass']['total_max_points']
				,'points_achieved'		=> $test_result['pass']['total_reached_points']
				,'percent_to_pass'		=> $this->getPercentToPass($test_session)
				,'testrun_finished_ts'	=> time()
				,'test_passed'			=> $test_result['test']['passed']
				,'pass_scoring'			=> $this->getPassScoring($test)
				);
		}
	}

	private function getObjIdFromTestSession(ilTestSession $test_session) {
		if($this->obj_id === null) {
			require_once 'Modules/Test/classes/class.ilObjTest.php';
			$this->obj_id = ilObjTest::_getObjectIDFromTestID($test_session->getTestId());
		}
		return $this->obj_id;
	}

	private function getTestInstanceByObjId($obj_id) {
		if($this->test === null) {
			require_once 'Modules/Test/classes/class.ilObjTest.php';
			$this->test = new ilObjTest($obj_id, false);
		}
		return $this->test;
	}

	private function getPassScoring(ilObjTest $test) {
		$t_pass_scoring = $test->getPassScoring();
		if($t_pass_scoring === SCORE_LAST_PASS) {
			return self::LAST_PASS;
		} elseif ($t_pass_scoring === SCORE_BEST_PASS) {
			return self::BEST_PASS;
		}
		throw new ilHistorizingException('unknown pass scoring');
	}

	private function getPercentToPass(ilTestSession $test_session) {
		$db = $this->plugin->getDbInstance();
		$sql = 'SELECT MIN(minimum_level)/100 as points_to_pass FROM tst_mark'
				.' 	WHERE test_fi = '.$db->quote($test_session->getTestId(),'integer')
				.'	AND passed = '.$db->quote(1,'integer')
				.'	GROUP BY test_fi';
		return $db->fetchAssoc($db->query($sql))['points_to_pass'];
	}

	private function calculateCase() {
		if($this->component === 'Services/Object' && $this->event === 'update') {
			return array('obj_id' => $this->parameter['obj_id']);
		} elseif($this->component === 'Modules/Test' && $this->event === 'testPassIncreased') {
			$test_session = $this->parameter['test_session'];
			return array(
				'usr_id' => $test_session->getUserId()
				,'obj_id' => $this->getObjIdFromTestSession($test_session)
				,'pass' => $test_session->getLastFinishedPass()
				);
		}
	}
}