<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportWBDErrors extends ilObjReportBase {
	protected $relevant_parameters = array();
	protected $gCtrl;
	public $filter_settings;

	public function __construct($ref_id = 0) {
		parent::__construct($ref_id);
		global $ilCtrl,$lng;
		$this->gCtrl = $ilCtrl;
		$this->gLng = $lng;
		$this->filter_settings = null;
	}

	public function initType() {
		$this->setType("xwbe");
	}
	
	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_wbe');
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_wbd_errors_row.html";
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	protected function buildOrder($order) {
		// $order 	->mapping("course_id", "err.crs_id")
		// 		->mapping("resolve", "err.ts")
		// 		->defaultOrder("ts", "DESC");
		return $order;
	}

	protected function buildTable($table) {
		$table	->column("ts", $this->plugin->txt("ts"), true)
				->column("action", $this->plugin->txt("wbd_errors_action"), true)
				->column("internal", $this->plugin->txt( "wbd_errors_internal"), true)
				->column("user_id", $this->plugin->txt("usr_id"), true)
				->column("course_id", $this->plugin->txt("crs_id"), true)
				->column("firstname", $this->plugin->txt("firstname"), true)
				->column("lastname", $this->plugin->txt("lastname"), true)
				->column("title", $this->plugin->txt("title"), true)
				->column("begin_date", $this->plugin->txt("begin_date"), true)
				->column("end_date", $this->plugin->txt("end_date"), true)
				->column("reason",$this->plugin->txt( "wbd_errors_reason"), true)
				->column("reason_full", $this->plugin->txt("wbd_errors_reason_full"), true)
				->column("resolve", $this->plugin->txt("wbd_errors_resolve"), 1, 0, 1);
		return parent::buildTable($table);
	}

	protected function buildQuery($query) {
		return $query;
	}

	// TODO: Those are not really used, as we use the new filter logic
	// in this report. Remove em!
	protected function buildFilter($filter) {
		return null;
	}

	public function deliverFilter() {
		return null;
	}
	//
	// As is don't use a regular filter, i also don't need its params...
	protected function addFilterToRelevantParameters() {
	}

	public function filter() {
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$txt = function($id) { return $this->plugin->txt($id); };

		return $f->sequence(
					$f->sequence(
						$f->multiselect
							( $txt("reason")
							, ""
							, $this->getFilterValues('reason', 'wbd_errors')
						)->map(function($id_s) {return array_values($id_s);}
						,$tf->lst($tf->string()))
					,
					$f->multiselect
						( $txt("action")
						, ""
						, $this->getFilterValues('action', 'wbd_errors')
					)->map(function($id_s) {return $id_s;}
						,$tf->lst($tf->string()))
					,
					$f->multiselect
						( $txt("error_type")
						, ""
						, array(1=>"Intern", 0=>"WBD Fehler")
					)->map(function($id_s) {return $id_s;}
						,$tf->lst($tf->int()))
					)->map(function($reason,$action,$error_type) {
						return array( "reason" => $reason
							, "action" => $action
							, "error_type" => $error_type
							);}
						, $tf->dict(array("reason" => $tf->lst($tf->string())
							,"action" => $tf->lst($tf->string())
							,"error_type" => $tf->lst($tf->int()))))
				);
	}

	public function fetchData(callable $callback) {
		/**
		 *	The following is not nice. I'll have to think of a better way to postprocess data from database, than the static transformResultRow.
		 *	It probably would suffice simply to make is nonstatic...
		 */
		$db = $this->gIldb;
		$query = "SELECT DISTINCT err.id, err.usr_id, err.crs_id, err.internal, err.reason, err.reason_full, err.ts, err.action, usr.firstname, usr.lastname\n"
				.", crs.title, usrcrs.begin_date, usrcrs.end_date\n"
				." FROM wbd_errors err\n"
				." LEFT JOIN hist_user usr ON err.usr_id = usr.user_id\n"
				."     AND usr.hist_historic = 0\n"
				." LEFT JOIN hist_course crs ON err.crs_id = crs.crs_id\n"
				."    AND crs.hist_historic = 0\n"
				." LEFT JOIN hist_usercoursestatus usrcrs ON err.usr_id = usrcrs.usr_id\n"
				."    AND err.crs_id = usrcrs.crs_id\n"
				."    AND usrcrs.hist_historic = 0\n"
				." LEFT JOIN usr_data ud ON err.usr_id = ud.usr_id\n"
				." WHERE err.resolved = 0";

		$filter = $this->filter();

		if($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);

			if(!empty($settings[0]["reason"])) {
				$query .= "    AND ".$db->in("err.reason", $settings[0]["reason"], false, "text");
			}

			if(!empty($settings[0]["action"])) {
				$query .= "    AND ".$db->in("err.action", $settings[0]["action"], false, "text");
			}

			if(!empty($settings[0]["error_type"])) {
				$query .= "    AND ".$db->in("err.internal", $settings[0]["error_type"], false, "text");
			}
		}

		$res = $db->query($query);
		$data = array();
		while($rec = $db->fetchAssoc($res)) {
			$link_change_usr = $this->gCtrl->getLinkTargetByClass(
				array("iladministrationgui", "ilobjusergui"), "edit")
				.'&obj_id='.$rec['usr_id']
				.'&ref_id=7'; //ref 7: Manage user accounts here.
			$link_usr = '<a href="' .$link_change_usr.'">%s</a>';

			foreach (array('usr_id','firstname','lastname') as $key) {
				$rec[$key] = sprintf($link_usr, $rec[$key]);
			}

			$crs_ref_id = gevObjectUtils::getRefId($rec['crs_id']);
			if($crs_ref_id && $rec['crs_id'] > 0){
				$link_change_crs = $this->gCtrl->getLinkTargetByClass(
					array("ilrepositorygui", "ilobjcoursegui"), "editInfo")
					.'&ref_id='
					.$crs_ref_id;
				$link_change_crs = '<a href="' .$link_change_crs.'">%s</a>';
			} else {
				$link_change_crs = '%s';
			}
			$rec['crs_id'] = sprintf($link_change_crs, $rec['crs_id']);

			$rec['resolve'] = '<a href="' 
				.$this->gCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI","ilObjReportWBDErrorsGUI"), "resolve")
				.'&err_id='
				.$rec['id']
				.'">'
				.$this->plugin->txt("wbd_errors_resolve")
				.'</a>';

			if($this->gLng->exists($rec["reason_full"])) {
				$rec["reason_full"] = $this->gLng->txt($rec["reason_full"]);
			}

			if($rec["internal"] === "1") {
				$rec["internal"] = "Intern";
			} else if ($rec["internal"] === "0") {
				$rec["internal"] = "WBD Fehler";
			}

			$data[] = $rec;
		}

		return $data;
	}

	protected function getFilterValues($column, $table) {
		$ret = array();
		$query = "SELECT DISTINCT ".$column." FROM ".$table."";

		$res = $this->gIldb->query($query);
		while($row = $this->gIldb->fetchAssoc($res)) {
			$ret[$row[$column]] = $row[$column];
		}

		return $ret;
	}
}