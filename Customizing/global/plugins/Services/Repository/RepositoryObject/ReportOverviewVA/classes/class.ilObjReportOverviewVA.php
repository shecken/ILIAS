<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once "Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php";

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportOverviewVA extends ilObjReportBase {
	protected $relevant_parameters = array();

	public function initType() {
		$this->setType("xova");
	}


	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_ova')
			->addSetting($this->s_f
								->settingString('selected_study_prg', $this->plugin->txt('selected_study_prg')));
	}

	protected function getStudyId() {
		if ((string)$this->getSettingsDataFor("selected_study_prg") !== "") {
			return (string)$this->getSettingsDataFor("selected_study_prg");
		}
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_overview_va_row.html";
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	protected function buildQuery($query) {
		return null;
	}

	protected function buildTable($table) {
		$table	->column("firstname", $this->plugin->txt("firstname"), true)
				->column("lastname", $this->plugin->txt("lastname"), true)
				->column("orgunit", $this->plugin->txt("orgunit"), true)
				->column("entrydate", $this->plugin->txt("entrydate"), true)
				->column("status", $this->plugin->txt("status"), true)
				->column("preparation", $this->plugin->txt("preparation"), true)
				->column("base_1", $this->plugin->txt("base_1"), true)
				->column("base_2", $this->plugin->txt("base_2"), true)
				->column("base_3", $this->plugin->txt("base_3"), true)
				->column("buildup_1", $this->plugin->txt("buildup_1"), true)
				->column("buildup_2", $this->plugin->txt("buildup_2"), true)
				->column("ihk_1", $this->plugin->txt("ihk_1"), true)
				->column("ihk_2", $this->plugin->txt("ihk_2"), true)
				->column("ihk_exam", $this->plugin->txt("ihk_exam"), true)
				->column("agency_1", $this->plugin->txt("agency_1"), true)
				->column("agency_2", $this->plugin->txt("agency_2"), true)
				->column("ca_certificate", $this->plugin->txt("va_certificate"), true);
		return parent::buildTable($table);
	}

	public function filter() {
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$txt = function($id) { return $this->plugin->txt($id); };

		return 
		$f->sequence
		(
			$f->sequence
			(
				$f->dateperiod
				(
					$txt("period")
					, ""
				)->map
					(
						function($start,$end) use ($f) 
						{
							$pc = $f->dateperiod_overlaps_predicate
							(
								"usrcrs.begin_date"
								,"usrcrs.begin_date"
							);
							return array ("date_period_predicate" => $pc($start,$end)
										 ,"start" => $start
										 ,"end" => $end);
						},
						$tf->dict
						(
							array
							(
								"date_period_predicate" => $tf->cls("CaT\\Filter\\Predicates\\Predicate")
								,"start" => $tf->cls("DateTime")
								,"end" => $tf->cls("DateTime")
							)
						)
					),
				$f->multiselectsearch(
					$txt("orgunit"),
					"",
					$this->getRelevantOrgus()
				),

				$f->text
				(
					$txt("lastname")
					,""
				),

				$f->multiselectsearch
				(
					$txt("status")
					, ""
					, ["teilgenommen", "in Bearbeitung", "nicht teilgenommen", "noch nicht begonnen"]
				)
			)
		)->map
				(
					function($date_period_predicate, $start, $end, $orgunit, $lastname, $status)
					{
						return array("period_pred" => $date_period_predicate
									,"start" => $start
									,"end" => $end
									,"orgunit" => $orgunit
									,"lastname" => $lastname
									,"status" => $status);
					},
					$tf->dict
					(
						array("period_pred" => $tf->cls("CaT\\Filter\\Predicates\\Predicate")
							 ,"start" => $tf->cls("DateTime")
							 ,"end" => $tf->cls("DateTime")
							 ,"orgunits" => $tf->lst($tf->string())
							 ,"lastname" => $tf->string()
							 ,"status" => $tf->lst($tf->string()))
					)
				);
	}

	public function buildQueryStatement() {
		$db = $this->gIldb;

$this->getStudyChildren();
		$query = "SELECT DISTINCT usr.firstname, usr.lastname\n"
				."FROM usr_data AS usr\n"
				."JOIN prg_usr_progress USING(usr_id)";
		// $query =   "SELECT DISTINCT `usr`.`firstname` ,`usr`.`lastname` ,`usr`.`birthday` ,`usr`.`bwv_id` ,`usr`.`wbd_type` ,`crs`.`title`\n"
		// 		  .", `crs`.`type` ,`usrcrs`.`begin_date` ,`usrcrs`.`end_date` ,`usrcrs`.`credit_points` ,`usrcrs`.`wbd_booking_id`\n"
		// 		  .", IF ( crs.custom_id <> '-empty-' , crs.custom_id , IF (usrcrs.gev_id IS NULL , '-' , usrcrs.gev_id ) ) as custom_id\n" 
		// 		  ." FROM `hist_usercoursestatus` usrcrs\n"
		// 		  ." JOIN `hist_user` usr\n"
		// 		  ."     ON usrcrs.usr_id = usr.user_id\n"
		// 		  ."         AND usr.hist_historic = 0\n"
		// 		  ." JOIN `hist_course` crs\n"
		// 		  ."     ON usrcrs.crs_id = crs.crs_id\n"
		// 		  ."         AND crs.hist_historic = 0\n"
		// 		  ." WHERE usrcrs.hist_historic = 0\n"
		// 		  ."     AND usrcrs.wbd_booking_id != '-empty-'\n"
		// 		  ."     AND usr.hist_historic = 0\n"
		// 		  ."     AND crs.hist_historic = 0\n"
		// 		  ."     AND usrcrs.hist_historic = 0\n";

		// $filter = $this->filter();
		// if($this->filter_settings) {
		// 	$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
		// 	$to_sql = new \CaT\Filter\SqlPredicateInterpreter($db);
		// 	$dt_query = $to_sql->interpret($settings["period_pred"]);
		// 	$query .= "     AND ".$dt_query;

		// 	if(!empty($settings['name'])) {
		// 		$query .= "    AND " .$db->like('usr.lastname', 'text', $settings['name']);
		// 	}

		// 	if(!empty($settings['wbd_types'])) {
		// 		$query .= "    AND " .$db->in('usr.wbd_type', $settings['wbd_types'], false, "text");
		// 	}
		// } else {
		// 	$query .= "     AND ((`usrcrs`.`begin_date` < '" .date("Y") ."-12-31' ) OR (`usrcrs`.`begin_date` = '".date("Y") ."-12-31' ) ) AND (('".date("Y") ."-01-01' < `usrcrs`.`begin_date` ) OR ('" .date("Y") ."-01-01' = `usrcrs`.`begin_date` ) )\n";
		// }
		// $query .= $this->queryOrder();

		return $query;
	}

	/**
	 * Description
	 * @param type $study_ref_id 
	 * @return array
	 */
	protected function getStudyChildren() {
		$prog_per_uid = array();
		$osp = new ilObjStudyProgramme($this->getStudyId());
		$assignments = $osp->getAssignments();
		foreach($assignments as $assignment) {
			$usr_id = $assignment->getUserId();
			$arr = array();
			foreach($osp->getChildren() as $child) {
				$arr[$child->getTitle()] = $child->getProgressesOf($usr_id);
			}
			$prog_per_uid[$usr_id] = $arr;
		}

		var_dump($prog_per_uid);exit;
		// alle benutzer holen assignments

	}

	protected function getAssignetUsers($study_ref_id) {
		$db = $this->gIldb;

		$query = "SELECT DISTINCT usr.firstname, usr.lastname\n"
				."FROM usr_data AS usr\n"
				."JOIN prg_usr_progress AS pup\n"
				."	USING(usr_id)\n"
				."JOIN object_reference AS ore\n"
				."ON ore.obj_id = pup.prg_id\n"
				."WHERE ore.ref_id = 93\n";

	}

	protected function fetchData(callable $callback) {
		$db = $this->gIldb;
		$query = $this->buildQueryStatement();
		$res = $this->gIldb->query($query);
		$data = array();
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$data[] = call_user_func($callback,$rec);
		}
		return $data;
	}

	protected function buildFilter($filter) {
		return null;
	}

	protected function buildOrder($order) {
		return $order;
	}

	private function getRelevantOrgus()
	{
		$ids = $this->getRelevantOrguIds();
		$options = array();
		foreach ($ids as $id) {
			$options[(int)$id] = ilObject::_lookupTitle($id);
		}

		return $options;
	}

	private function getRelevantOrguIds()
	{
		if (true) {//"1" === (string)$this->settings['all_orgus_filter']) {
			$ids = $this->getAllOrguIds();
		} else {
			$ids = array_unique(array_map(
				function ($ref_id) {
						return ilObject::_lookupObjectId($ref_id);
				},
				$this->user_utils->getOrgUnitsWhereUserCanViewEduBios()
			));
		}
		return $ids;
	}

	private function getAllOrguIds()
	{
		$query = 'SELECT DISTINCT obj_id FROM object_data JOIN object_reference USING(obj_id)'
				.'	WHERE type = \'orgu\' AND deleted IS NULL';
		$res = $this->gIldb->query($query);
		$return = array();
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = $rec["obj_id"];
		}
		return $return;
	}
}