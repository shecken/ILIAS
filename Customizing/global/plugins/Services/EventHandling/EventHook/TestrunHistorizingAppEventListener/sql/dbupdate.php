<#1>
<?php

/**
 * This service logs the testpassses. every testpass is a case on itself.
 * Note: a user doing a test may have several cases.
 */
if(!$ilDB->tableExists('hist_usertestrun')) {
	$ilDB->createTable('hist_usertestrun',
		array(
			 'row_id'				=> array('type' => 'integer', 'length' => 4, 'notnull' => true)
			,'created_ts'			=> array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0)
			,'hist_historic'		=> array('type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0)
			,'hist_version'			=> array('type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => 0)
			,'creator_usr_id'		=> array('type' => 'integer', 'length' => 4, 'notnull' => true)
			,'usr_id'				=> array('type' => 'integer', 'length' => 4, 'notnull' => true) // obj_id of the user, who did the testrun
			,'obj_id'				=> array('type' => 'integer', 'length' => 4, 'notnull' => true) // obj_id of the test
			,'pass'					=> array('type' => 'integer', 'length' => 4, 'notnull' => true) // the # of pass of the corresponding test
			,'test_title'			=> array('type' => 'text', 'length' => 255, 'notnull' => true, 'default' => '') // title of the test
			,'max_points'			=> array('type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => 0) // maximum points, that may be achieved in the test
			,'points_achieved'		=> array('type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => 0) // points achieved in recent testrun
			,'percent_to_pass'		=> array('type' => 'float', 'notnull' => true, 'default' => 0) // which fracture of max points should be achieven in order to pass the test
			,'testrun_finished_ts'	=> array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0) // timestamp of submition of therecent testrun
			,'test_passed'			=> array('type' => 'integer', 'length' => 1, 'notnull' => true) // is the test passed after this testrun? note, that it may change from 1 to 0 also
			,'pass_scoring'			=> array('type' => 'text', 'length' => 30, 'notnull' => true) // here it is stored, wether the last testrun pr the best testrun counts in order to pass the test
			));
	$ilDB->createSequence('hist_usertestrun');
	$ilDB->addPrimaryKey('hist_usertestrun', array('row_id'));

	$old_test_query_data =
		'SELECT'
		.'	seq.tstamp as created_ts'
		.'	,seq.tstamp as testrun_finished_ts'
		.'	,obj.title test_title'
		.'	,obj.obj_id'
		.'	,act.user_fi usr_id'
		.'	,act.user_fi creator_usr_id'
		.'	,pres.pass'
		.'	,pres.maxpoints max_points'
		.'	,pres.points points_achieved'
		.'	,IF(pts_min.percent_to_pass IS NOT NULL, pts_min.percent_to_pass, 0) percent_to_pass'
		.'	,IF(tst.pass_scoring = 0,'.$ilDB->quote('last_pass','text').','.$ilDB->quote('best_pass','text').') pass_scoring'
		.'	FROM tst_tests tst'
		.'	JOIN object_data obj'
		.'		ON tst.obj_fi = obj.obj_id'
		//test id =^ object id 
		//active id =^ user id x test id
		.'	JOIN tst_active act'
		.'		ON act.test_fi = tst.test_id'
		//infos about 
		.'	JOIN tst_sequence seq'
		.'		ON seq.active_fi = act.active_id'
		//testruns and resuts
		.'	JOIN tst_pass_result pres'
		.'		ON pres.active_fi = act.active_id AND pres.pass = seq.pass'
		//fraction of max points to pass
		.'	LEFT JOIN (SELECT test_fi,MIN(minimum_level)/100 as percent_to_pass FROM tst_mark'
		.'			WHERE passed = 1 GROUP BY test_fi) as pts_min '
		.'		ON tst.test_id = pts_min.test_fi'
		//chronological order
		.'	ORDER BY obj_id,pass'
		;

	$res = $ilDB->query($old_test_query_data);
	$test_passed_aux = 0;
	$usr_id_aux = 0;
	$obj_id_aux = 0;
	while($rec = $ilDB->fetchAssoc($res)) {

		if($rec['pass_scoring'] === 'last_pass') {
			$rec['test_passed'] = ($rec['points_achieved']/$rec['max_points'] >= $rec['percent_to_pass'] ) ? 1 : 0;
		} elseif($rec['pass_scoring'] === 'best_pass') {
			if($rec['usr_id'] != $usr_id_aux || $rec['obj_id'] != $obj_id_aux) {
				$test_passed_aux = ($rec['points_achieved']/$rec['max_points'] >= $rec['percent_to_pass'] ) ? 1 : 0;
			} elseif($test_passed_aux == 1) {

			} else {
				$test_passed_aux = ($rec['points_achieved']/$rec['max_points'] >= $rec['percent_to_pass'] ) ? 1 : 0;
			}
			$rec['test_passed'] = $test_passed_aux;

		}

		$usr_id_aux = $usr_id;
		$obj_id_aux = $obj_id;

		$insert =  array(
			'row_id' => array('integer',$ilDB->nextId('hist_usertestrun'))
			,'hist_version' => array('integer',1)
			,'hist_historic' => array('integer',0)
			,'created_ts' => array('integer',$rec['created_ts'])
			,'testrun_finished_ts' => array('integer',$rec['testrun_finished_ts'])
			,'test_title' => array('text',$rec['test_title'])
			,'creator_usr_id' => array('integer',$rec['creator_usr_id'])
			,'obj_id' => array('integer',$rec['obj_id'])
			,'usr_id' => array('integer',$rec['usr_id'])
			,'pass' => array('integer',$rec['pass'])
			,'max_points' => array('integer',$rec['max_points'])
			,'points_achieved' => array('integer',$rec['points_achieved'])
			,'percent_to_pass' => array('float',$rec['percent_to_pass'])
			,'test_passed' => array('integer',$rec['test_passed'])
			,'pass_scoring' => array('text',$rec['pass_scoring'])
			);
		$ilDB->insert('hist_usertestrun',$insert);
	}

	$ilDB->addIndex('hist_usertestrun',array('obj_id'),'tro');
	$ilDB->addIndex('hist_usertestrun',array('usr_id'),'tru');
	$ilDB->addIndex('hist_usertestrun',array('pass'),'trp');
}
?>