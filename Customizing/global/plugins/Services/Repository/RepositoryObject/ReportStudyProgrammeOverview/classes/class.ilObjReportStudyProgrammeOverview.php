<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once "Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php";
require_once "Services/GEV/Utils/classes/class.gevUserUtils.php";
require_once "Services/GEV/Utils/classes/class.gevOrgUnitUtils.php";
require_once "Services/Tracking/classes/class.ilLPStatus.php";

ini_set("memory_limit", "2048M");
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportStudyProgrammeOverview extends ilObjReportBase
{
	/**
	 * @var ilDb
	 */
	protected $g_db;

	/**
	 * @var \gevUserUtils
	 */
	protected $user_utils = null;

	/**
	 * @var \gevCourseUtils
	 */
	protected $course_utils = null;

	protected $edu_bio_employees = null;

	protected $relevant_parameters = array();

	public function initType()
	{
		$this->setType("xspo");
	}


	protected function createLocalReportSettings()
	{
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_xspo')
			->addSetting($this->s_f
								->settingInt('selected_study_prg', $this->plugin->txt('selected_study_prg')))
			->addSetting($this->s_f
								->settingBool('trainer_view', $this->plugin->txt('trainer_view')));
	}

	public function getStudyId()
	{
		if ((string)$this->getSettingsDataFor("selected_study_prg") !== "") {
			return (string)$this->getSettingsDataFor("selected_study_prg");
		}
		return null;
	}

	public function isTrainerView()
	{
		return $this->getSettingsDataFor("trainer_view");
	}

	protected function getRowTemplateTitle()
	{
		return "tpl.gev_overview_va_row.html";
	}

	public function getRelevantParameters()
	{
		return $this->relevant_parameters;
	}

	protected function buildQuery($query)
	{
		return null;
	}

	protected function buildTable($table)
	{
		$table	->column("firstname", $this->plugin->txt("firstname"), true, "4%")
				->column("lastname", $this->plugin->txt("lastname"), true, "4%")
				->column("orgunit", $this->plugin->txt("orgunit"), true, "4%")
				->column("entrydate", $this->plugin->txt("entrydate"), true, "4%")
				->column("status", $this->plugin->txt("status"), true, "4%");

		$osp = new ilObjStudyProgramme($this->getStudyId());

		foreach ($osp->getChildren() as $child) {
			if (!$child->isActive()) {
				continue;
			}
			$column_key = $child->getTitle();
			$column_key = strtolower($column_key);
			$column_key = str_replace(" ", "_", $column_key);
			$table->column($column_key, $child->getTitle(), true, "3%");
		}
		return parent::buildTable($table);
	}

	public function filter()
	{
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$txt = function ($id) {
			return $this->plugin->txt($id);
		};

		return
		$f->sequence(
			$f->sequence(
				$f->dateperiod(
					$txt("entrydate"),
					""
				)->map(
					function ($start, $end) use ($f) {
							return array (
										  "start" => $start
										 ,"end" => $end);
					},
					$tf->dict(
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
				$f->text(
					$txt("lastname"),
					""
				),
				$f->multiselectsearch(
					$txt("status"),
					"",
					array(ilLPStatus::LP_STATUS_COMPLETED_NUM => "teilgenommen",
							ilLPStatus::LP_STATUS_IN_PROGRESS_NUM => "in Bearbeitung",
							ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM => "noch nicht begonnen")
				)
			)
		)->map(
			function ($start, $end, $orgunit, $lastname, $status) {
						return array(
									"start" => $start
									,"end" => $end
									,"orgunits" => $orgunit
									,"lastname" => $lastname
									,"status" => $status);
			},
			$tf->dict(
				array(
							 "start" => $tf->cls("DateTime")
							 ,"end" => $tf->cls("DateTime")
							 ,"orgunits" => $tf->lst($tf->int())
							 ,"lastname" => $tf->string()
							 ,"status" => $tf->lst($tf->int()))
			)
		);
	}

	public function buildQueryStatement()
	{
		return null;
	}

	protected function mayViewEdubioOf($usr_id)
	{
		if ($this->edu_bio_employees === null) {
			$this->edu_bio_employees = $this->user_utils->getEmployeesWhereUserCanViewEduBios();
		}
		return in_array($usr_id, $this->edu_bio_employees);
	}

	protected function showUser($user_id)
	{
		if (!$this->isTrainerView()) {
			if ($this->user_utils->isAdmin()) {
				return true;
			}
			return $this->mayViewEdubioOf($user_id);
		} else {
			if ($this->course_utils === null) {
				$id = $this->getParentCourseId();
				if (!$id) {
					throw new \ilException("Trainer view activated, but no parent course found...");
				}
				$this->course_utils = gevCourseUtils::getInstance($id);
			}
			return $this->course_utils->isMember($user_id);
		}
	}

	protected function getParentCourseId()
	{
		$data = $this->gTree->getParentNodeData($this->getRefId());
		while ("crs" !== $data['type'] && (string)ROOT_FOLDER_ID !== (string)$data['ref_id']) {
			$data = $this->gTree->getParentNodeData($data['ref_id']);
		}
		return ( "crs" === $data['type'] )
			? $data['obj_id'] : null;
	}

	public function getParentCourseTitle()
	{
		$crs_id = $this->getParentCourseId();

		if (!$crs_id) {
			return null;
		}

		$crs = ilObjectFactory::getInstanceByObjId($crs_id);
		return $crs->getTitle();
	}

	protected function getData()
	{
		$osp = new ilObjStudyProgramme($this->getStudyId());
		$children = $osp->getChildren();
		$assignments = $osp->getAssignments();

		$user_ids = array_unique(array_map(function ($assignment) {
			return $assignment->getUserId();
		}, $assignments));

		$arr2 = array();
		foreach ($user_ids as $user_id) {
			if (!$this->showUser($user_id)) {
				continue;
			}

			$assigns_per_user = $osp->getAssignmentsOf($user_id);

			foreach ($assigns_per_user as $assign) {
				$utils = gevUserUtils::getInstance($user_id);
				$arr = array();
				$arr["user_id"] = $user_id;
				$arr["sp_ref_id"] = $this->getStudyId();
				$arr["assignment"] = $assign->getId();
				$arr["firstname"] = $utils->getFirstname();
				$arr["lastname"] = $utils->getLastname();
				$arr["orgunit"] = $utils->getOrgUnitNamesWhereUserIsEmployee();
				$entrydate = $utils->getEntryDate();
				if ($entrydate === null) {
					$arr["entry_date"] = "-";
				} else {
					$arr["entry_date"] = $entrydate->get(IL_CAL_FKT_DATE, "d.m.Y");
				}
				$arr["status"] = $osp->getProgressForAssignment($assign->getId())->getStatus();
				foreach ($children as $child) {
					if (!$child->isActive()) {
						continue;
					}
					$column_key = $child->getTitle();
					$column_key = strtolower($column_key);
					$column_key = str_replace(" ", "_", $column_key);
					$arr[$column_key] = $child->getProgressForAssignment($assign->getId())->getStatus();
					;
				}
				$arr2[] = $arr;
			}
		}
		return $arr2;
	}

	protected function executeFilterSettings(array $data)
	{
		$orgu_titles = array();

		$filter = $this->filter();
		if ($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);

			// filter logic for datetime
			$data = array_filter($data, function ($row) use ($settings) {
				$start = $settings['start']->getTimestamp();
				$end = $settings['end']->getTimestamp();
				$entry = strtotime($row['entry_date']);
				if ($row['entry_date'] == "-") {
					return $row;
				} elseif ($entry <= $end && $entry >= $start) {
					return $row;
				}
			});

			// filter logic for orgunits
			if (!empty($settings["orgunits"])) {
				foreach ($settings["orgunits"] as $value) {
					array_push($orgu_titles, gevOrgUnitUtils::getInstance($value)->getTitle());
				}
				$data = array_filter($data, function ($row) use ($orgu_titles) {
					if (array_intersect($orgu_titles, $row['orgunit'])) {
						return $row;
					}
				});
			}

			// filter logic for lastname
			if (!empty($settings['lastname'])) {
				$data = array_filter($data, function ($row) use ($settings) {
					if (stripos($row['lastname'], $settings['lastname']) === 0) {
						return $row;
					}
				});
			}

			// filter logic for status
			if (!empty($settings['status'])) {
				$data = array_filter($data, function ($row) use ($settings) {
					if (in_array($row['status'], $settings['status'])) {
						return row;
					}
				});
			}
		} else {
			$start = strtotime(date("Y-01-01"));
			$end = strtotime(date("Y-12-31"));
			$data = array_filter($data, function ($row) use ($start, $end) {
				$entry = strtotime($row['entry_date']);
				if ($row['entry_date'] == "-") {
					return $row;
				} elseif ($entry <= $end && $entry >= $start) {
					return $row;
				}
			});
		}
		return $data;
	}

	protected function fetchData(callable $callback)
	{

		$data = $this->executeFilterSettings($this->getData());

		foreach ($data as $key => $dat) {
			$dat['orgunit'] = implode(" ", $dat['orgunit']);
			$data[$key] = $dat;
		}
		return $data;
	}



	protected function buildFilter($filter)
	{
		return null;
	}

	protected function buildOrder($order)
	{
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

	/**
	 * @inheritdoc
	 */
	public function getReportMenuTitle() {
		if($crs_title = $this->getParentCourseTitle()) {
			return $this->getTitle()." (".$crs_title.")";
		}

		return $this->getTitle();
	}
}
