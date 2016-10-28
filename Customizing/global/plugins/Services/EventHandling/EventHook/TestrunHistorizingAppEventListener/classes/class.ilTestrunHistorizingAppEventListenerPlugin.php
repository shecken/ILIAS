<?php

require_once 'Services/EventHandling/classes/class.ilEventHookPlugin.php';

class ilTestrunHistorizingAppEventListenerPlugin extends ilEventHookPlugin {

	protected $db;

	protected function init() {
		global $ilDB;
		$this->db = $ilDB;
		parent::init();
	}

	public function getDbInstance() {
		return $this->db;
	}

	public function getPluginName() {
		return "TestrunHistorizingAppEventListener";
	}


	public function handleEvent($a_component, $a_event, $a_parameter) {
		if($this->eventRelevant($a_component, $a_event, $a_parameter)) {
			$this->historize($this->dataProvider($a_component,$a_event,$a_parameter), $this->historizer());
		}
	}

	protected function historize(DataProvider $data_provider, ilHistorizingStorage $hist_storage) {
		$hist_storage->updateHistorizedData(
				$data_provider->caseId(),
				$data_provider->data(),
				$data_provider->creator(),
				time(),
				$data_provider->massAction());
	}

	protected function dataProvider($a_component,$a_event, array $a_parameter) {
		require_once $this->getDirectory().'/classes/class.TestrunHistorizingDataProvider.php';
		return new TestrunHistorizingDataProvider($a_component,$a_event,$a_parameter,$this);
	}

	protected function historizer() {
		require_once $this->getDirectory().'/classes/class.TestrunHistorizer.php';
		return new TestrunHistorizer();
	}

	public function eventRelevant($a_component, $a_event, $a_parameter) {
		return ($a_component === 'Modules/Test' && $a_event === 'testPassFinished') ||
		($a_component === 'Services/Object' && $a_event === 'update' && $a_parameter['obj_type'] === 'tst');
	}
}