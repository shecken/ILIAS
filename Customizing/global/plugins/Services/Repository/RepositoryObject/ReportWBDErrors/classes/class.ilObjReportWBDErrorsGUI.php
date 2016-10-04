<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportWBDErrorsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportWBDErrorsGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportWBDErrorsGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportWBDErrorsGUI extends ilObjReportBaseGUI {
	public function getType() {
		return 'xwbe';
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	public function performCustomCommand($cmd) {
		switch ($cmd) {
			case "saveFilter":
				return $this->saveFilter();
				break;
			case 'resolve':
				$err_id = $_GET['err_id'];
				require_once("Services/WBDData/classes/class.wbdErrorLog.php");
				$errlog = new wbdErrorLog();
				$errlog->resolveWBDErrorById($err_id);
				$this->object->setFilterAction("showContent");
				$this->object->prepareReport();
				$this->enableRelevantParametersCtrl();
				$this->gCtrl->redirect($this, "showContent");
				break;
			default:
				return parent::performCustomCommand($cmd);
		}
	}

	protected function render() {
		//$fs = $this->loadFilterSettings();
		//$this->gTpl->setTitle(null);

		//$res = ($this->title !== null ? $this->title->render() : "");

		//if ($fs === null) {
			$res .= $this->renderFilter();

		return $res;
	}

	protected function renderFilter() {
		$filter = $this->object->filter();
		$display = new \CaT\Filter\DisplayFilter
						( new \CaT\Filter\FilterGUIFactory
						, new \Cat\Filter\TypeFactory
						);

		require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterFlatViewGUI.php");
		$filter_flat_view = new catFilterFlatViewGUI($this, $filter, $display, "saveFilter");

		return $filter_flat_view->render();
	}

	protected function saveFilter() {
		var_dump($_POST);
		// $settings = $display->buildFilterValues($filter, $post["filter"]);
		// $this->saveFilterSettings($settings);
		// $this->gCtrl->redirect($this, "showContent");

		return $this->render();
	}

	protected function loadFilterSettings() {

	}

	protected function saveFilterSettings() {

	}

	protected function getPOST() {
		return $_POST;
	}
}