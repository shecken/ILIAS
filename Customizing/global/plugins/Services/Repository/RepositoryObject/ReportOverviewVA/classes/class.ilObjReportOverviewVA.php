<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once "Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php";
require_once "Services/GEV/Utils/classes/class.gevUserUtils.php";
require_once "Services/GEV/Utils/classes/class.gevOrgUnitUtils.php";
require_once "Services/Tracking/classes/class.ilLPStatus.php";

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
				->column("status", $this->plugin->txt("status"), true);

		$osp = new ilObjStudyProgramme($this->getStudyId());
		
		foreach($osp->getChildren() as $child) {
			$column_key = $child->getTitle();
			$column_key = strtolower($column_key);
			$column_key = str_replace(" ", "_", $column_key);
			$table->column($column_key, $child->getTitle(), true);
		}
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
							return array (
										  "start" => $start
										 ,"end" => $end);
						},
						$tf->dict
						(
							array
							(
								"start" => $tf->cls("DateTime")
								,"end" => $tf->cls("DateTime")
							)
						)
					),
				$f->multiselectsearch(
					$txt("orgunit"),
					"",
					$this->getOrgus()
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
					, array(ilLPStatus::LP_STATUS_COMPLETED_NUM => "teilgenommen",
							ilLPStatus::LP_STATUS_IN_PROGRESS_NUM => "in Bearbeitung",
							ilLPStatus::LP_STATUS_FAILED_NUM => "nicht teilgenommen",
							ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM => "noch nicht begonnen")
				)
			)
		)->map
				(
					function($start, $end, $orgunit, $lastname, $status)
					{
						return array(
									"start" => $start
									,"end" => $end
									,"orgunits" => $orgunit
									,"lastname" => $lastname
									,"status" => $status);
					},
					$tf->dict
					(
						array(
							 "start" => $tf->cls("DateTime")
							 ,"end" => $tf->cls("DateTime")
							 ,"orgunits" => $tf->lst($tf->int())
							 ,"lastname" => $tf->string()
							 ,"status" => $tf->lst($tf->int()))
					)
				);
	}

	public function buildQueryStatement() {

	}

	/**
	 * Description
	 * @param type $study_ref_id 
	 * @return array
	 */
	protected function getData() {
		$osp = new ilObjStudyProgramme($this->getStudyId());
		$children = $osp->getChildren();
		$assignments = $osp->getAssignments();

		$user_ids = array_unique(array_map(function ($assignment) { return $assignment->getUserId(); }, $assignments));

		$arr2 = array();
		foreach($user_ids as $user_id) {
			$utils = gevUserUtils::getInstance($user_id);
			$arr = array();
			$arr["user_id"] = $user_id;
			$arr["firstname"] = $utils->getFirstname();
			$arr["lastname"] = $utils->getLastname();
			$arr["orgunit"] = $utils->getOrgUnitNamesWhereUserIsEmployee();
			$entrydate = $utils->getEntryDate();
			if($entrydate === null) {
				$arr["entry_date"] = "-";
			} else {
				$arr["entry_date"] = $entrydate->get(IL_CAL_FKT_DATE, "d.m.Y");
			}
			$arr["status"] = $this->statusToImage(ilLPStatus::_lookupStatus($osp->getId(), $user_id));
			foreach($children as $child) {
				$column_key = $child->getTitle();
				$column_key = strtolower($column_key);
				$column_key = str_replace(" ", "_", $column_key);
				$arr[$column_key] = $this->statusToImage(ilLPStatus::_lookupStatus($child->getId(), $user_id));
			}
			$arr2[] = $arr;
		}
		return $arr2;
	}

	protected function executeFilterSettings(array $data) {
		$orgu_titles = array();

		$filter = $this->filter();
		if($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);

			// filter logic for datetime
			$tmp = array();
			$data = array_filter($data, function($row) use($settings, &$tmp) {
				$start = $settings['start']->getTimestamp();
				$end = $settings['end']->getTimestamp();
				$entry = strtotime($row['entry_date']);
				if($row['entry_date'] == "-") {
					return $row;
				}else if($entry <= $end && $entry >= $start) {
					return $row;
				}
			});

			// filter logic for orgunits
			if(!empty($settings["orgunits"])) {
				foreach ($settings["orgunits"] as $value) {
					array_push($orgu_titles, gevOrgUnitUtils::getInstance($value)->getTitle());
				}
				$data = array_filter($data, function($row) use($orgu_titles) {
					if(array_intersect($orgu_titles, $row['orgunit'])) {
						return $row;
					}
				} );
			}

			// filter logic for lastname
			if(!empty($settings['lastname'])) {
				$data = array_filter($data, function($row) use ($settings){
					if(stripos($row['lastname'], $settings['lastname']) === 0) {
						return $row;
					}
				});
			}

			// filter logic for status
			if(!empty($settings['status'])) {
				$data = array_filter($data, function($row) use ($settings) {
					if(in_array((int)$row['status'], $settings['status'])) {
						return row;
					}
				});
			}
		}
		return $data;
	}

	protected function fetchData(callable $callback) {

		$data = $this->executeFilterSettings($this->getData());

		foreach($data as $key => $dat) {
			$dat['orgunit'] = implode(" ", $dat['orgunit']);
			$data[$key] = $dat;
		}
		return $data;
	}

	protected function statusToImage($status) {
		switch ($status) {
			case ilLPStatus::LP_STATUS_COMPLETED_NUM:
				return '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-green.png").'" />';
			case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
				return '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-orange.png").'" />';
			case ilLPStatus::LP_STATUS_FAILED_NUM:
				return '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-red.png").'" />';
			case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
				return '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-neutral.png").'" />';
		}

	}

	protected function buildFilter($filter) {
		return null;
	}

	protected function buildOrder($order) {
		return null;
	}

	private function getOrgus()
	{
		$ids = $this->getAllOrguIds();
		$options = array();
		foreach ($ids as $id) {
			$options[(int)$id] = ilObject::_lookupTitle($id);
		}
		return $options;
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