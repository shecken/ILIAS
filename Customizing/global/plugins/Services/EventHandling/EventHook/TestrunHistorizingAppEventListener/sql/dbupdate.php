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
			,'points_achived'		=> array('type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => 0)
			,'points_to_pass'		=> array('type' => 'integer', 'length' => 4, 'notnull' => false, 'default' => 0)
			,'testrun_finished_at'	=> array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0)
			,'passed'				=> array('type' => 'integer', 'length' => 1, 'notnull' => false, 'default' => 0)
			,'test_title'			=> array('type' => 'text', 'length' => 255, 'notnull' => true, 'default' => '') 
			));
}
?>