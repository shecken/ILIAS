<#1>
<?php
if(!$ilDB->tableExists('hist_usertestrun')) {
	$ilDB->createTable('hist_usertestrun',
		array(
			 'row_id'				=> array('type' => 'integer', 'length' => 4, 'notnull' => true)
			,'created_ts'			=> array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0)
			,'hist_historic'		=> array('type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0)
			,'hist_version'			=> array('type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => 0)
			,'creator_usr_id'		=> array('type' => 'integer', 'length' => 4, 'notnull' => true)
			,'usr_id'				=> array('type' => 'integer', 'length' => 4, 'notnull' => true)
			,'obj_id'				=> array('type' => 'integer', 'length' => 4, 'notnull' => true)
			,'pass'					=> array('type' => 'integer', 'length' => 4, 'notnull' => true)
			,'test_title'			=> array('type' => 'text', 'length' => 255, 'notnull' => true, 'default' => '') 
			,'max_points'			=> array('type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => 0)
			,'points_achieved'		=> array('type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => 0)
			,'percent_to_pass'		=> array('type' => 'float', 'notnull' => true, 'default' => 0)
			,'testrun_finished_at'	=> array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0)
			,'passed'				=> array('type' => 'integer', 'length' => 1, 'notnull' => true)
			,'pass_scoring'			=> array('type' => 'text', 'length' => 30, 'notnull' => true)
			));
	$ilDB->createSequence('hist_usertestrun');
}
?>