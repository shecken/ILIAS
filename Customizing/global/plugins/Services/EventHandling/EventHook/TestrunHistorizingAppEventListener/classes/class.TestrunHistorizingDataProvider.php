<?php
require_once 'Services/HistorizingStorage/interfaces/interface.DataProvider.php';
class TestrunHistorizingDataProvider implements DataProvider {

	protected $data = null;
	protected $case = null;
	protected $plugin;


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
			$this->case = $this->calculateCase($this->component, $this->event, $this->parameter);
		}
		return $this->case_id;
	}

	public function data() {
		if($this->data === null) {
			$this->data = $this->calculateData($this->component, $this->event, $this->parameter);
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

	private function calculateData($a_component, $a_event, array $a_parameter) {
		global $ilLog;
		if($this->component === 'Services/Object' && $this->event === 'update') {
			return array('test_title' => ilObject::_lookupTitle($a_parameter['obj_id']));
		} elseif($this->component === 'Modules/Test' && $this->event === 'testPassIncreased') {
			require_once 'Modules/Test/classes/class.ilObjTest.php';
			$test_session = $a_parameter['test_session'];
			$obj_id = ilObjTest::_getObjectIDFromTestID($test_session->getTestId());
			$test = new ilObjTest($obj_id, false);
			$test_results = $test->getTestResult($test_session->getActiveId())['pass'];

			$ilLog->dump($test_results);
			return array(
				'test_title'			=> $test->getTitle()
				,'max_points'			=> $test_results['total_max_points']
				,'points_achived'		=> $test_results['total_reached_points']
				,'points_to_pass'		=> $this->getPointsToPass()
				,'testrun_finished_at'	=> $test_session->getSubmittedTimestamp()
				,'testrun_passed'		=> $test_results['passed']
				,'pass_schema'			=> 'test'
				);
		}
	}

	private function getPointsToPass($test_session) {
		$db = $this->plugin->getDbInstance();
		$sql = 'SELECT MIN(minimum_level)/100 as pass_threshold FROM tst_mark'
				.' 	WHERE test_fi = '.$db->quote($test_session->getTestId(),'integer')
				.'	AND passed = '.$db->quote(1,'integer')
				.'	GROUP BY test_fi';
		return $db->fetchAssoc($db->query($sql))['pass_threshold'];
	}

	private function calculateCase($a_component, $a_event, array $a_parameter) {
		if($this->component === 'Services/Object' && $this->event === 'update') {
			return array('obj_id' => $a_parameter['obj_id']);
		} elseif($this->component === 'Modules/Test' && $this->event === 'testPassIncreased') {
			$test_session = $a_parameter['test_session'];
			$obj_id = ilObjTest::_getObjectIDFromTestID($test_session->getTestId());
			$usr_id = $test_session->getUserId();
			$pass = $test_session->getPass();
			return array('usr_id' => $usr_id, 'obj_id' => $obj_id, 'pass' => $pass);
		}
	}
}