<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/ReportStudyProgrammeOverview/classes/class.ilObjReportStudyProgrammeOverviewTableGUI.php";
require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportStudyProgrammeOverviewGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportStudyProgrammeOverviewGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportStudyProgrammeOverviewGUI: ilCommonActionDispatcherGUI, ilIndividualPlanGUI
*/
class ilObjReportStudyProgrammeOverviewGUI extends ilObjReportBaseGUI
{
	protected static $success_img;
	protected static $in_progress_img;
	protected static $not_yet_started_img;

	/**
	 * @var	\ilCtrl
	 */
	protected $g_ctrl;

	/**
	 * @var	\ilTabs
	 */
	protected $g_tabs;

	/**
	 * @var	\ilIndividualPlanGUI
	 */
	protected $individual_plan_gui = null;

	/**
	 * Overwritten from ilObjectPluginGUI
	 */
	public function __construct($a_ref_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);

		global $ilCtrl, $ilTabs;
		$this->g_ctrl = $ilCtrl;
		$this->g_tabs = $ilTabs;
	}

	public function getType()
	{
		return 'xspo';
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	public function performCommand($cmd)
	{
		$next_class = $this->g_ctrl->getNextClass();

		switch ($next_class) {
			case 'ilindividualplangui':
				$this->view();
				break;
			default:
				parent::performCommand($cmd);
				break;
		}
	}

	/**
	 * Initialize the subgui initIndividualPlanGUI.
	 *
	 * @param	int $user_id
	 * @param	int $assignment_id
	 * @param	int $sp_ref_id
	 * @return 	null
	 */
	protected function initIndividualPlanGUI($user_id, $assignment_id, $sp_ref_id)
	{
		assert('is_int($user_id)');
		assert('is_int($assignment_id)');
		assert('is_int($sp_ref_id)');
		if ($this->individual_plan_gui !== null) {
			return;
		}
		$this->individual_plan_gui = new ilIndividualPlanGUI();
		$this->individual_plan_gui->setUserId($user_id);
		$this->individual_plan_gui->setAssignmentId($assignment_id);
		$this->individual_plan_gui->setSPRefId($sp_ref_id);
	}

	/**
	 * Overwritten from ilObject2GUI
	 *
	 * @return null
	 */
	protected function setLocator()
	{
		global $ilLocator;

		if ($this->g_ctrl->getNextClass() == "ilindividualplangui") {
			$this->initIndividualPlanGUI(
				(int)$_GET["user_id"],
				(int)$_GET["assignment_id"],
				(int)$_GET["spRefId"]
			);
			$this->individual_plan_gui->setLocatorItems($ilLocator);
			$this->gTpl->setLocator();
		} else {
			parent::setLocator();
		}
	}

	public function view()
	{
		require_once("Modules/StudyProgramme/classes/tables/class.ilIndividualPlanGUI.php");
		$this->gTpl->setTitle(null);
		$this->g_tabs->ActivateTab("content");
		$this->initIndividualPlanGUI(
			(int)$_GET["user_id"],
			(int)$_GET["assignment_id"],
			(int)$_GET["spRefId"]
		);
		$this->g_ctrl->forwardCommand($this->individual_plan_gui);
	}

	protected function prepareTitle($a_title)
	{
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		$a_title->legend(catLegendGUI::create()
					->item(self::$success_img, "rep_robj_xsp_passed")
					->item(self::$in_progress_img, "rep_robj_xsp_in_progress")
					->item(self::$not_yet_started_img, "rep_robj_xsp_not_yet_started"));
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
		self::$not_yet_started_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-neutral.png").'" />';
		$this->object->prepareReport();
		$this->title = $this->prepareTitle(catTitleGUI::create());
		$this->spacer = $this->prepareSpacer(new catHSpacerGUI());
		$this->table = $this->prepareTable(new ilObjReportStudyProgrammeOverviewTableGUI($this, "showContent"));
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

	public function initCreateForm($a_new_type)
	{
		$form = parent::initCreateForm($a_new_type);
		$this->addSettingsFormItems($form);

		return $form;
	}

	protected function addSettingsFormItems($form)
	{
		$ti = new \ilNumberInputGUI($this->lng->txt("rep_robj_xsp_setting_sp_node_ref_id"), "selected_study_prg");
		$ti->setRequired(true);
		$form->addItem($ti);

		return $form;
	}

	public function afterSave(ilObject $sp)
	{
		$settings = $sp->getSettingsData();
		$settings['selected_study_prg'] = $_POST['selected_study_prg'];
		$sp->setSettingsData($settings);
		$sp->update();
		ilUtil::sendSuccess($this->lng->txt("object_added"), true);
		parent::afterSave($sp);
	}

	protected function renderSettings()
	{
		if ($this->object->getStudyId() == null || ilObject::_lookupType($this->object->getStudyId(), true) != "prg") {
			if ($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
				ilUtil::sendInfo($this->plugin->txt('no_prg_warning'), true);
			}
		}
		$settings_form = $this->fillSettingsFormFromDatabase($this->settingsForm());
		$this->gTpl->setContent($settings_form->getHtml());
	}

	/**
	 * @inheritdoc
	 */
	protected function showContent()
	{
		if ($this->object->getStudyId() == null || ilObject::_lookupType($this->object->getStudyId(), true) != "prg") {
			if ($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
				ilUtil::sendInfo($this->plugin->txt('no_prg_warning'), true);
				$this->gCtrl->redirect($this, "settings");
			} else {
				ilUtil::sendInfo($this->plugin->txt('user_no_prg_warning'), true);
				ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmd=toMyCourses");
				$this->gCtrl->redirect($this->parent_obj, "view");
			}
		}

		if ($this->gAccess->checkAccess("read", "", $this->object->getRefId())) {
			$this->gTabs->activateTab("content");
			$this->object->prepareRelevantParameters();
			$this->setFilterAction($cmd);
			return $this->renderReport();
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function saveSettings()
	{
		$settings_form = $this->settingsForm();
		$settings_form->setValuesByPost();
		if ($settings_form->checkInput()) {
			$potential_study_id = $settings_form->getItemByPostVar('selected_study_prg')->getValue();
			if (ilObject::_lookupType($potential_study_id, true) !== "prg") {
				ilUtil::sendInfo($this->plugin->txt('no_prg_warning'), true);
				$settings_form->getItemByPostVar('is_online')->setValue(0);
			}

			$this->saveSettingsData($settings_form);
			$red = $this->gCtrl->getLinkTarget($this, "settings", "", false, false);
			ilUtil::redirect($red);
		}
		$this->gTpl->setContent($settings_form->getHtml());
	}

	protected function renderUngroupedTable($data)
	{
		if (!$this->object->deliverTable()->row_template_filename) {
			throw new Exception("No template defined for table ".get_class($this));
		}
		$this->table->setRowTemplate(
			$this->object->deliverTable()->row_template_filename,
			$this->object->deliverTable()->row_template_module
		);

		foreach ($this->object->deliverTable()->columns as $col) {
			$this->table->addColumn($col[2] ? $col[1] : $this->lng->txt($col[1]), $col[5] ? $col[0] : "", $col[3]);
		}

		if ($this->object->deliverOrder() !== null) {
			$this->table->setOrderField($this->object->deliverOrder()->getOrderField());
			$this->table->setOrderDirection($this->object->deliverOrder()->getOrderDirection());
		}

		$cnt = count($data);
		$this->table->setLimit($cnt);
		$this->table->setMaxCount($cnt);
		$external_sorting = true;

		if ($this->object->deliverOrder() === null ||
			in_array(
				$this->object->deliverOrder()->getOrderField(),
				$this->internal_sorting_fields ? $this->internal_sorting_fields : array()
			)
			) {
				$external_sorting = false;
		}

		$this->table->setExternalSorting($external_sorting);
		if ($this->internal_sorting_numeric) {
			foreach ($this->internal_sorting_numeric as $col) {
				$table->numericOrdering($col);
			}
		}

		$this->table->setData($data);
		$this->enableRelevantParametersCtrl();
		$return = $this->table->getHTML();
		$this->disableRelevantParametersCtrl();
		return $return;
	}
}
