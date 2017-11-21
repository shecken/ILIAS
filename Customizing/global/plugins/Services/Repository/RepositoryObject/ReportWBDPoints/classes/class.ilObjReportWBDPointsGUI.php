<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportWBDPointsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportWBDPointsGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportWBDPointsGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportWBDPointsGUI extends ilObjReportBaseGUI
{

	public function getType()
	{
		return 'xwbp';
	}

	protected function prepareTitle($a_title)
	{
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	protected function afterConstructor()
	{
		parent::afterConstructor();
		if ($this->object->plugin) {
			$this->tpl->addCSS($this->object->plugin->getStylesheetLocation('report.css'));
			$this->filter = $this->object->filter();
			$this->display = new \CaT\Filter\DisplayFilter(
				new \CaT\Filter\FilterGUIFactory,
				new \CaT\Filter\TypeFactory
			);
		}

		// $this->loadFilterSettings();
	}

	protected function render()
	{
		$this->gTpl->setTitle(null);
		$res = $this->title->render();
		$res .= $this->renderFilter();
		$res .= $this->renderTable();

		return $res;
	}

	protected function renderFilter()
	{
		$this->loadFilterSettings();
		$filter = $this->object->filter();
		$display = new \CaT\Filter\DisplayFilter(
			new \CaT\Filter\FilterGUIFactory,
			new \CaT\Filter\TypeFactory
		);
		global $ilCtrl;
		require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterFlatViewGUI.php");
		$filter_flat_view = new catFilterFlatViewGUI($this, $filter, $display, $ilCtrl->getCmd());

		return $filter_flat_view->render($this->filter_settings);
	}

	protected function loadFilterSettings()
	{
		$this->filter_settings = array();

		if (isset($_POST['filter'])) {
			$this->filter_settings = $_POST['filter'];
		}
		if (isset($_GET['filter'])) {
			$this->filter_settings = unserialize(base64_decode($_GET['filter']));
		}

		$this->object->addRelevantParameter('filter', base64_encode(serialize($this->filter_settings)));
		$this->object->filter_settings = $this->display->buildFilterValues($this->filter, $this->filter_settings);
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

	public static function transformResultRow($rec)
	{
		return self::transformResultRowCommon($rec);
	}

	public static function transformResultRowXLSX($rec)
	{
		return self::transformResultRowCommon($rec);
	}

	public static function transformResultRowCommon($rec) {
		if($rec['credit_points'] > 0) {
			$rec['credit_points'] = gevCourseUtils::convertCreditpointsToFormattedDuration((int)$rec['credit_points']);
		} else {
			$rec['credit_points'] = '';
		}
		return $rec;
	}
}
