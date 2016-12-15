<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
require_once("Services/GEV/WBD/classes/class.gevWBD.php");
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportWBDErrorsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportWBDErrorsGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportWBDErrorsGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportWBDErrorsGUI extends ilObjReportBaseGUI {
	const FILTER_SESSION_VAR = "wbd_error_report";

	public function getType() {
		return 'xwbe';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	public function afterConstructor() {
		parent::afterConstructor();
		$this->filter_settings = false;
	}

	public function performCustomCommand($cmd) {
		switch ($cmd) {
			case "saveFilter":
				$this->saveFilter();
				break;
			case 'resolve':
			case 'feedback':
			case 'unableResolve':
				$this->$cmd();
				$this->gCtrl->redirect($this, "showContent");
				break;
			default:
				return parent::performCustomCommand($cmd);
		}
	}

	protected function resolve() {
		$error_ids = $this->decodeErrorIds($_GET['err_ids']);

		foreach($error_ids as $error_id) {
			gevWBD::resolveWBDErrorById($error_id);
			gevWBD::setWBDErrorStatusById($error_id, gevWBD::STATUS_RESOLVED);
		}
	}

	protected function feedback() {
		$error_ids = $this->decodeErrorIds($_GET['err_ids']);

		foreach($error_ids as $error_id) {
			gevWBD::setWBDErrorStatusById($error_id, gevWBD::STATUS_FEEDBACK);
		}
	}

	protected function unableResolve() {
		$error_ids = $this->decodeErrorIds($_GET['err_ids']);

		foreach($error_ids as $error_id) {
			gevWBD::setWBDErrorStatusById($error_id, gevWBD::STATUS_UNABLE_RESOLVE);
		}
	}

	protected function decodeErrorIds($error_ids) {
		$error_ids = base64_decode($error_ids);
		$error_ids = explode(",", $error_ids);

		return $error_ids;
	}

	protected function render() {
		$res = $this->renderFilter()."<br />";
		$res .= parent::renderTable();

		return $res;
	}

	protected function renderFilter() {
		$this->loadFilterSettings();
		$filter = $this->object->filter();
		$display = new \CaT\Filter\DisplayFilter
						( new \CaT\Filter\FilterGUIFactory
						, new \CaT\Filter\TypeFactory
						);

		if($this->filter_settings) {
			$this->object->filter_settings = $display->buildFilterValues($filter, $this->filter_settings);
		}

		require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterFlatViewGUI.php");
		$filter_flat_view = new catFilterFlatViewGUI($this, $filter, $display, "saveFilter");

		return $filter_flat_view->render($this->filter_settings);
	}

	protected function saveFilter() {
		$this->flushFilterSettings();
		if(isset($_POST["filter"])) {
			$filter = $this->object->filter();
			$display = new \CaT\Filter\DisplayFilter
						( new \CaT\Filter\FilterGUIFactory
						, new \CaT\Filter\TypeFactory
						);
			$this->saveFilterSettings($_POST["filter"]);
		}

		$this->gCtrl->redirect($this, "showContent");
	}

	protected function loadFilterSettings() {
		if ($this->filter_settings !== false) {
			return $this->filter_settings;
		}

		$tmp = ilSession::get(self::FILTER_SESSION_VAR);
		if ($tmp !== null) {
			$this->filter_settings =  unserialize($tmp);
		}
		else {
			$this->filter_settings = null;
		}

		return $this->filter_settings;
	}

	protected function saveFilterSettings($settings) {
		ilSession::set(self::FILTER_SESSION_VAR, serialize($settings));
		$this->filter_settings = $settings;
	}

	public function flushFilterSettings() {
		ilSession::clear(self::FILTER_SESSION_VAR);
		$this->filter_settings = null;
	}

	protected function getPOST() {
		return $_POST;
	}
}