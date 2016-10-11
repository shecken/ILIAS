<?php

require_once 'Services/HistorizingStorage/classes/class.ilHistorizingStorage.php';

class TestrunHistorizer extends ilHistorizingStorage {

	protected function getHistorizedTableName() {
		return 'hist_usertestrun';
	}

	protected function getVersionColumnName() {
		return 'hist_version';
	}


	protected function getHistoricStateColumnName() {
		return 'hist_historic';
	}


	protected function getCreatorColumnName() {
		return 'creator_usr_id';
	}

	protected function getCreatedColumnName() {
		return 'created_ts';
	}


	protected function getContentColumnsDefinition() {
		return  array(
			'test_title'			=> 'text'
			,'max_points'			=> 'integer'
			,'points_achieved'		=> 'integer'
			,'percent_to_pass'		=> 'float'
			,'testrun_finished_ts'	=> 'integer'
			,'test_passed'			=> 'integer'
			,'pass_scoring'			=> 'text'
			);
	}

	protected function getRecordIdColumn() {
		return 'row_id';
	}

	protected function getCaseIdColumns() {
		return array(
			'usr_id'	=> 'integer'
			,'obj_id' 	=> 'integer'
			,'pass' 	=> 'integer'
		);
	}
}