<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportOrguAttGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportOrguAttGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, gevDBVReportGUI
* @ilCtrl_Calls ilObjReportOrguAttGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportOrguAttGUI extends ilObjReportBaseGUI
{

	public static $od_regexp = null;
	public static $bd_regexp = null;

	public function getType()
	{
		return 'xroa';
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

	protected function render()
	{
		$res = $this->renderFilter().'<br/>'
				.$this->renderSumTable().'<br/>'
				.$this->renderTable();
		return $res;
	}

	protected function renderFilter()
	{
		global $ilCtrl;
		require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterFlatViewGUI.php");
		$filter_flat_view = new catFilterFlatViewGUI($this, $this->filter, $this->display, $ilCtrl->getCmd());
		return $filter_flat_view->render($this->filter_settings);
	}

	protected function prepareTitle($a_title)
	{
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
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

	public function renderQueryView()
	{
		include_once "Services/Form/classes/class.ilNonEditableValueGUI.php";
		$this->object->prepareReport();
		$content = $this->renderFilter('query_view');
		$form = new ilNonEditableValueGUI($this->gLng->txt("report_query_text"));
		$form->setValue($this->object->buildQueryStatement());
		$settings_form = new ilPropertyFormGUI();
		$settings_form->addItem($form);
		$content .= $settings_form->getHTML();
		$this->gTpl->setContent($content);
	}

	protected function getRowTemplateTitle()
	{
		return "tpl.gev_attendance_by_orgunit_row.html";
	}

	public static function transformResultRow($rec)
	{

		foreach ($rec as &$data) {
			if ((string)$data === "0") {
				$data = '-';
			}
		}
		if (isset($rec['org_unit_above1'])) {
			if (!self::$od_regexp || !self::$bd_regexp) {
				require_once './Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/config/od_bd_strings.php';
			}
			$orgu_above =  $rec['org_unit_above1'];
			$orgu_above_above =  $rec['org_unit_above2'];
			if (preg_match(self::$od_regexp, $orgu_above)) {
				$od = $orgu_above;
			} elseif (preg_match(self::$od_regexp, $orgu_above_above)) {
				$od = $orgu_above_above;
			} else {
				$od = '-';
			}

			if (preg_match(self::$bd_regexp, $orgu_above)) {
				$bd = $orgu_above;
			} elseif (preg_match(self::$bd_regexp, $orgu_above_above)) {
				$bd = $orgu_above_above;
			} else {
				$bd = '-';
			}
			$rec['odbd'] = $od .'/' .$bd;
		}
		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLSX($rec)
	{
		foreach ($rec as &$data) {
			if ((string)$data === "0") {
				$data = '-';
			}
		}
		if (isset($rec['org_unit_above1'])) {
			if (!self::$od_regexp || !self::$bd_regexp) {
				require_once './Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/config/od_bd_strings.php';
			}
			$orgu_above =  $rec['org_unit_above1'];
			$orgu_above_above =  $rec['org_unit_above2'];
			if (preg_match(self::$od_regexp, $orgu_above)) {
				$od = $orgu_above;
			} elseif (preg_match(self::$od_regexp, $orgu_above_above)) {
				$od = $orgu_above_above;
			} else {
				$od = '-';
			}

			if (preg_match(self::$bd_regexp, $orgu_above)) {
				$bd = $orgu_above;
			} elseif (preg_match(self::$bd_regexp, $orgu_above_above)) {
				$bd = $orgu_above_above;
			} else {
				$bd = '-';
			}
			$rec['odbd'] = $od .'/' .$bd;
		}

		return parent::transformResultRowXLSX($rec);
	}
}
