<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportTrainerWorkloadGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportTrainerWorkloadGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, gevDBVReportGUI
* @ilCtrl_Calls ilObjReportTrainerWorkloadGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportTrainerWorkloadGUI extends ilObjReportBaseGUI {
	const CMD_FILTER 	= "filter";

	public function performCustomCommand($cmd) {
		switch ($cmd) {
			case self::CMD_FILTER:
				$this->showContent();
				return true;
			default:
				return parent::performCustomCommand($cmd);
		}
	}

	public function getType() {
		return 'xrtw';
	}

	protected function afterConstructor() {
		parent::afterConstructor();
		if($this->object->plugin) {
			$this->tpl->addCSS($this->object->plugin->getStylesheetLocation('report.css'));
		}
		$this->filter = $this->object->filter();
		$this->display = new \CaT\Filter\DisplayFilter
						( new \CaT\Filter\FilterGUIFactory
						, new \CaT\Filter\TypeFactory
						);
		$this->loadFilterSettings();
	}

	protected function loadFilterSettings() {
		if(isset($_POST['filter'])) {
			$this->filter_settings = $_POST['filter'];
		}

		if(isset($_GET['filter'])) {
			$this->filter_settings = unserialize(base64_decode($_GET['filter']));
		}

		if($this->filter_settings) {
			$this->object->filter_settings = $this->display->buildFilterValues($this->filter, $this->filter_settings);
		}
	}

	protected function render() {
		$res = $this->renderFilter()."<br />";
		$res .= $this->renderTable();

		return $res;
	}

	protected function renderFilter() {
		require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterFlatViewGUI.php");
		$filter_flat_view = new catFilterFlatViewGUI($this, $this->filter, $this->display, self::CMD_FILTER);

		return $filter_flat_view->render($this->filter_settings);
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}


	protected function renderTable() {
		$this->gCtrl->setParameter($this, 'filter', base64_encode(serialize($this->filter_settings)));
		$table = parent::renderTable();
		$sum_table = $this->renderSumTable();
		$this->gCtrl->setParameter($this, 'filter', null);
		return $sum_table.$table;
	}

	private function renderSumTable(){
		
		$table = new catTableGUI($this, "showContent");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$table_sums = $this->object->deliverSumtable();

		$table->setRowTemplate(
			$table_sums->row_template_filename, 
			$table_sums->row_template_module
		);

		$table->addColumn("", "blank", "0px", false);
		foreach ($table_sums->columns as $col) {
			$table->addColumn( $col[2] ? $col[1] : $this->lng->txt($col[1])
							 , $col[0]
							 , $col[3]
							 );
		}

		$sum_row = $this->object->fetchSumData();	
		if(count($sum_row) == 0) {
			foreach(array_keys($table_sums->columns) as $field) {
				$sum_row[$field] = 0;
			}
		}

		$table->setData(array($sum_row));
		$this->enableRelevantParametersCtrl();
		$return = $table->getHtml();
		$this->disableRelevantParametersCtrl();
		return $return;
	}

	public static function transformResultRow($rec) {
		global $ilCtrl;
		foreach ($rec as $key => &$value) {
			if($key != 'fullname') {
				if(strpos($key,'_workload') === false) {
					$value = number_format($value,2,',','.');
				} else {
					$value = number_format($value,0,',','.');
				}
			}
		}
		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLSX($rec) {
		return self::transformResultRow($rec);
	}

	/**
	 * provide xlsx version of report for download.
	 */
	protected function exportExcel() {
		$this->object->prepareReport();

		$workbook = $this->getExcelWriter();

		$sheet_name = "report";
		$workbook
			->addSheet($sheet_name)
			->setRowFormatBold();

		$header = array();
		foreach ($this->object->deliverTable()->all_columns as $col) {
			if ($col[4]) {
				continue;
			}
			$in_header = $col[2] ? $col[1] : $this->lng->txt($col[1]);
			$header[] = str_replace('&shy;', '', strip_tags(htmlspecialchars_decode($in_header)));
		}
		$workbook
			->writeRow($header)
			->setRowFormatWrap();
		$callback = get_class($this).'::transformResultRowXLSX';
		foreach ($this->object->deliverData($callback) as $entry) {
			$row = array();
			foreach ($this->object->deliverTable()->all_columns as $col) {
				if ($col[4]) {
					continue;
				}
				$row[] = $entry[$col[0]];
			}
			$workbook->writeRow($row);
		}

		$workbook->offerDownload("report.xlsx");
	}
}