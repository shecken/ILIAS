<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/ReportOverviewVA/classes/class.ilObjReportOverviewVATableGUI.php";
require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportOverviewVAGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportOverviewVAGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportOverviewVAGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportOverviewVAGUI extends ilObjReportBaseGUI
{
	protected static $success_img;
	protected static $in_progress_img;
	protected static $failed_img;
	protected static $not_yet_started_img;

	public function getType()
	{
		return 'xova';
	}

	protected function prepareTitle($a_title)
	{
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		$a_title->legend(catLegendGUI::create()
					->item(self::$success_img, "gev_passed")
					->item(self::$in_progress_img, "gev_in_progress")
					->item(self::$failed_img, "gev_failed")
					->item(self::$not_yet_started_img, "gev_not_yet_started"));
		;
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

		$this->loadFilterSettings();
	}

	protected function render()
	{

		$this->gTpl->setTitle(null);
		$res = $this->title->render();
		$res .= $this->renderFilter();
		$res .= $this->renderTable();

		return $res;
	}

	/**
	 * @inheritdoc
	 */
	public function renderReport()
	{
		self::$success_img  = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-green.png").'" />';
		self::$in_progress_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-orange.png").'" />';
		self::$failed_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-red.png").'" />';
		self::$not_yet_started_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-neutral.png").'" />';
		$this->object->prepareReport();
		$this->title = $this->prepareTitle(catTitleGUI::create());
		$this->spacer = $this->prepareSpacer(new catHSpacerGUI());
		$this->table = $this->prepareTable(new ilObjReportOverviewVATableGUI($this, "showContent"));
		$this->gTpl->setContent($this->render());
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

	/**
	 * @inheritdoc
	 */
	protected function renderExportButton()
	{
		return;
	}

	/**
	 * @inheritdoc
	 */
	public function renderQueryView()
	{
		return;
	}

	/**
	 * @inheritdoc
	 */
	protected function setSubTab($name, $link_target)
	{
		if ($name === "report_query_view") {
			return;
		}
		$this->gTabs->addSubTabTarget(
			$name,
			$this->gCtrl->getLinkTarget($this, $link_target),
			"write",
			get_class($this)
		);
	}
}
