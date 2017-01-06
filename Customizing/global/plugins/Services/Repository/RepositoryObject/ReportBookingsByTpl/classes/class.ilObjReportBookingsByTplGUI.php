<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportBookingsByTplGUI : ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportBookingsByTplGUI : ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportBookingsByTplGUI : ilCommonActionDispatcherGUI
*/
class ilObjReportBookingsByTplGUI extends ilObjReportBaseGUI
{

	public function getType()
	{
		return 'xrbt';
	}

	protected function afterConstructor()
	{
		parent::afterConstructor();
		if ($this->object->plugin) {
			$this->tpl->addCSS($this->object->plugin->getStylesheetLocation('report.css'));
		}

		if ($this->object) {
			$this->filter = $this->object->filter();
			$this->display = new \CaT\Filter\DisplayFilter(
				new \CaT\Filter\FilterGUIFactory,
				new \CaT\Filter\TypeFactory
			);
		}
		$this->loadFilterSettings();
	}

	protected function loadFilterSettings()
	{
		if (isset($_POST['filter'])) {
			$this->filter_settings = $_POST['filter'];
		}
		if (isset($_GET['filter'])) {
			$this->filter_settings = unserialize(base64_decode($_GET['filter']));
		}
		if ($this->filter_settings) {
			$this->object->addRelevantParameter('filter', base64_encode(serialize($this->filter_settings)));
			$this->object->filter_settings = $this->display->buildFilterValues($this->filter, $this->filter_settings);
		}
	}

	protected function prepareTitle($a_title)
	{
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	protected function render()
	{
		$this->gTpl->setTitle(null);
		return 	$this->title->render()
				.$this->renderFilter()
				. $this->renderSumTable()
				. ($this->spacer !== null ? $this->spacer->render() : "")
				. $this->renderTable();
	}

	public function renderQueryView()
	{
		include_once "Services/Form/classes/class.ilNonEditableValueGUI.php";
		$this->object->prepareReport();
		$content = $this->renderFilter('query_view');

		$form = new ilNonEditableValueGUI($this->gLng->txt("report_query_text"));
		$form->setValue($this->object->deliverSumQuery());

		$form = new ilNonEditableValueGUI($this->gLng->txt("report_query_text"));
		$form->setValue($this->object->buildQueryStatement());

		$settings_form = new ilPropertyFormGUI();
		$settings_form->addItem($form);
		$content .= $settings_form->getHTML();
		$this->gTpl->setContent($content);
	}

	protected function renderFilter()
	{
		global $ilCtrl;
		require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterFlatViewGUI.php");
		$filter_flat_view = new catFilterFlatViewGUI($this, $this->filter, $this->display, $ilCtrl->getCmd());
		return $filter_flat_view->render($this->filter_settings, (string)$_POST['filtered'] === '1');
	}

	private function renderSumTable()
	{
		$table = new catTableGUI($this, "showContent");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$sum_table = $this->object->deliverSumTable();
		$table->setRowTemplate(
			$sum_table->row_template_filename,
			$sum_table->row_template_module
		);

		$table->addColumn("", "blank", "0px", false);
		$cnt = 1;
		$table->setLimit($cnt);
		$table->setMaxCount($cnt);
		foreach ($sum_table->columns as $col) {
			$table->addColumn($col[2] ? $col[1] : $this->object->plugin->txt($col[1]), $col[0], $col[3]);
		}
		$callback = get_class($this).'::transformResultRow';
		$table = $this->object->insertSumData($table, $callback);

		$this->enableRelevantParametersCtrl();
		$return = $table->getHtml();
		$this->disableRelevantParametersCtrl();
		return $return;
	}

	public static function transformResultRow($rec)
	{

		foreach ($rec as &$data) {
			if ((string)$data === "0") {
				$data = '-';
			}
		}
		return parent::transformResultRow($rec);
	}
}
