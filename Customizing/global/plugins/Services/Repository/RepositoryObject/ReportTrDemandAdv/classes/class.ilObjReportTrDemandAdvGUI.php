<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportTrDemandAdvGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportTrDemandAdvGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjReportTrDemandAdvGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportTrDemandAdvGUI extends ilObjReportBaseGUI {
	public function getType() {
		return 'xtda';
	}

	protected function afterConstructor() {
		parent::afterConstructor();
		if($this->object->plugin) {
			$this->tpl->addCSS($this->object->plugin->getStylesheetLocation('report.css'));
		}

		if($this->object) {
			$this->filter = $this->object->filter();
			$this->display = new \CaT\Filter\DisplayFilter
						( new \CaT\Filter\FilterGUIFactory
						, new \CaT\Filter\TypeFactory
						);
		}

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
			$this->object->addRelevantParameter('filter', base64_encode(serialize($this->filter_settings)));
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
		$filter_flat_view = new catFilterFlatViewGUI($this, $this->filter, $this->display, $this->gCtrl->getCmd());
		return $filter_flat_view->render($this->filter_settings);
	}

	protected function renderTable() {
		$table = parent::renderTable();
		$this->gCtrl->setParameter($this, 'filter', null);
		return $sum_table.$table;
	}

	public function renderQueryView()
	{
		include_once "Services/Form/classes/class.ilNonEditableValueGUI.php";
		$this->object->prepareReport();
		$content = $this->renderFilter();
		$form = new ilNonEditableValueGUI($this->gLng->txt("report_query_text"));
		$form->setValue($this->object->buildQueryStatement());
		$settings_form = new ilPropertyFormGUI();
		$settings_form->addItem($form);
		$content .= $settings_form->getHTML();
		$this->gTpl->setContent($content);
	}

	protected function prepareTitle($a_title) {
		$a_title = parent::prepareTitle($a_title);
		$a_title->image("GEV_img/ico-head-edubio.png");
		return $a_title;
	}

	public static function transformResultRow($rec) {
		if($rec['title'] !== null) {
			if(ilObject::_exists($rec['crs_id']) &&'crs' === ilObject::_lookupType($rec['crs_id'])) {
				$ref_id = current(ilObject::_getAllReferences($rec['crs_id']));
				global $ilAccess;
				if($ilAccess->checkAccess("write", "editInfo", $ref_id, "crs", $rec['crs_id'])) {
					global $ilCtrl;
					$ilCtrl->setParameterByClass("ilObjCourseGUI","ref_id",$ref_id);
					$link = $ilCtrl->getLinkTargetByClass(array("ilRepositoryGUI","ilObjCourseGUI"),"editInfo");
					$ilCtrl->setParameterByClass("ilObjCourseGUI","ref_id",null);
					$rec["title"] = '<a href = "'.$link.'">'.$rec['title'].'</a>';
				} elseif ($ilAccess->checkAccess("write_reduced_settings", "showSettings", $ref_id, "crs", $rec['crs_id'])) {
					global $ilCtrl;
					$ilCtrl->setParameterByClass("gevDecentralTrainingGUI","ref_id",$ref_id);
					$link = $ilCtrl->getLinkTargetByClass(array("gevDesktopGUI","gevDecentralTrainingGUI"),"showSettings");
					$ilCtrl->setParameterByClass("gevDecentralTrainingGUI","ref_id",null);
					$rec["title"] = '<a href = "'.$link.'">'.$rec['title'].'</a>';
				}
			}
			$rec['min_part_achived'] = 
				(	(string)$rec['min_participants'] === "0"
					|| (string)$rec['min_participants'] === "-1"
					|| $rec['min_participants'] === null 
					|| $rec['bookings'] >= $rec['min_participants'])
						? 'Ja' : 'Nein';
			$rec['bookings_left'] =
				(	(string)$rec['max_participants'] === "0"
					|| (string)$rec['max_participants'] === "-1"
					|| $rec['max_participants'] === null )
						? 'keine Beschränkung' : max($rec['max_participants'] - $rec["bookings"],0);
			$rec['booked_wl'] =
					(string)$rec['waitinglist_active'] === "1"
						? $rec['booked_wl'] : 'inaktiv';

			$rec['begin_date'] = date_format(date_create($rec['begin_date']),'d.m.Y')
					.' - '.date_format(date_create($rec['end_date']),'d.m.Y');
			$rec['booking_dl'] = date_format(date_create($rec['booking_dl']),'d.m.Y');
		} else {
			$rec = array('tpl_title' => $rec['tpl_title']);
		}
		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLSX($rec) {
		if($rec['title'] !== null) {
			$rec['min_part_achived'] = 
				(	(string)$rec['min_part_achived'] === "1" 
					|| $rec['min_participants'] === null 
					|| (string)$rec['min_participants'] === '-1' )
						? 'Ja' : 'Nein';
			$rec['bookings_left'] =
				(	(string)$rec['max_participants'] === "0"
					||(string)$rec['max_participants'] === "-1"
					|| $rec['max_participants'] === null)
						? 'keine Beschränkung' : $rec['bookings_left'];
			$rec['booked_wl'] =
					(string)$rec['waitinglist_active'] === "1"
						? $rec['booked_wl'] : 'inaktiv';

			$rec['begin_date'] = date_format(date_create($rec['begin_date']),'d.m.Y')
					.' - '.date_format(date_create($rec['end_date']),'d.m.Y');
			$rec['booking_dl'] = date_format(date_create($rec['booking_dl']),'d.m.Y');
		} else {
			$rec = array(	'tpl_title' => $rec['tpl_title']);
		}
		return parent::transformResultRow($rec);
	}
}