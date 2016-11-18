<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/CaTUIComponents/classes/class.catAccordionTableGUI.php");

class gevMyEffectivenessAnalysisTableGUI extends catAccordionTableGUI {
	/**
	 * @var ilCtrl
	 */
	protected $gCtrl;

	/**
	 * @var ilLanguage
	 */
	protected $gLng;

	/**
	 * @var ilUser
	 */
	protected $gUser;

	public function __construct(array $filter, $a_parent_obj, $image_link, $a_parent_cmd = "", $a_template_context = "") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng, $ilUser;

		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		$this->gUser = $ilUser;

		$this->setEnableTitle(false);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);

		$this->determineOffsetAndOrder();
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));

		$this->setRowTemplate("tpl.spx_effectiveness_analysis_row.html", "Services/GEV/Desktop");

		$this->addColumn("", "expand", "0px", false, "catTableExpandButton");
		$this->addColumn($this->gLng->txt("gev_eff_analysis_lastname"), "lastname");
		$this->addColumn($this->gLng->txt("gev_eff_analysis_firstname"), "firstname");
		$this->addColumn($this->gLng->txt("gev_eff_analysis_email"), "email");
		$this->addColumn($this->gLng->txt("gev_eff_analysis_org_unit"), "orgunit");
		$this->addColumn($this->gLng->txt("gev_eff_analysis_title"), "title");
		$this->addColumn($this->gLng->txt("gev_eff_analysis_training_type"), "type");
		$this->addColumn($this->gLng->txt("gev_eff_analysis_date"), "begin_date");
		$this->addColumn($this->gLng->txt("gev_eff_analysis_lang"), "language");
		$this->addColumn($this->gLng->txt("gev_eff_analysis_finished_at"), "finish_date");
		$this->addColumn($this->gLng->txt("actions"), null);

		$this->eff_analysis_icon = $image_link;

		if(!$this->getOrderField()) {
			$order = "title";
			$this->setOrderField("title");
		} else {
			$order = $this->getOrderField();
		}

		require_once("Services/GEV/Desktop/classes/EffectivenessAnalysis/class.gevEffectivenessAnalysis.php");
		$this->eff_analysis = gevEffectivenessAnalysis::getInstance();
		$this->setMaxCount($this->eff_analysis->getCountEffectivenessAnalysis($this->gUser->getId(), $filter));

		$data = $this->eff_analysis->getEffectivenessAnalysis(
										$this->gUser->getId(),
										$filter, 
										$this->getOffset(),
										$this->getLimit(),
										$order,
										$this->getOrderDirection()
					   );

		$this->setData($data);

	}

	protected function fillRow($a_set) {
		$this->tpl->setVariable("ACCORDION_BUTTON_CLASS", $this->getAccordionButtonExpanderClass());
		$this->tpl->setVariable("ACCORDION_ROW", $this->getAccordionRowClass());
		$this->tpl->setVariable("COLSPAN", $this->getColspan());

		$this->tpl->setVariable("LASTNAME", $a_set["lastname"]);
		$this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
		$this->tpl->setVariable("EMAIL", $a_set["email"]);
		$this->tpl->setVariable("ORG_UNIT", $a_set["orgunit"]);
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("TRAINING_TYPE", $a_set["type"]);
		$start = date("d.m.Y", strtotime($a_set["begin_date"]));
		$end = date("d.m.Y", strtotime($a_set["end_date"]));

		if($start != $end) {
			$date = $start." - ".$end;
		} else {
			$date = $start;
		}

		$this->tpl->setVariable("DATE", $date);
		$this->tpl->setVariable("LANG", $a_set["language"]);
		$this->tpl->setVariable("DONE_AT", $a_set["finish_date"]);
		

		$this->tpl->setVariable("ID", $this->gLng->txt("gev_eff_analysis_id"));
		$this->tpl->setVariable("CITY_HEADER", $this->gLng->txt("gev_eff_analysis_city"));
		$this->tpl->setVariable("TARGET_GROUP_HEADER", $this->gLng->txt("gev_eff_analysis_target_group"));
		$this->tpl->setVariable("OBJECTIVES_HEADER", $this->gLng->txt("gev_eff_analysis_objectives"));
		$this->tpl->setVariable("CONTENT_HEADER", $this->gLng->txt("gev_eff_analysis_content"));
		$this->tpl->setVariable("RESULT_HEADER", $this->gLng->txt("gev_eff_analysis_result_long"));

		$this->tpl->setVariable("TARGET_GROUP", $a_set["training_number"]);
		$this->tpl->setVariable("CITY", $a_set["venue"]);
		$this->tpl->setVariable("TARGET_GROUP", $a_set["target_groups"]);
		$this->tpl->setVariable("OBJECTIVES", $a_set["objectives_benefits"]);
		$this->tpl->setVariable("CONTENT", $a_set["training_topics"]);

		if($a_set["result"] != "-") {
			$this->tpl->touchBlock("action_blanco");
			$this->tpl->setVariable("RESULT", $this->eff_analysis->getResultText($a_set["result"]));
		} else {
			$this->tpl->setCurrentBlock("action_icon");
			$this->tpl->setVariable("ACTIONS", $this->getAction($a_set["crs_id"], $a_set["user_id"]));
			$this->tpl->setVariable("IMAGE", $this->eff_analysis_icon);
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setVariable("RESULT", $a_set["result"]);
		}
	}

	protected function getAction($crs_id, $user_id) {
		$filter_param = $this->gCtrl->getParameterArrayByClass("gevEffectivenessAnalysisGUI")["filter_params"];

		$this->gCtrl->setParameterByClass("gevEffectivenessAnalysisGUI", "crs_id", $crs_id);
		$this->gCtrl->setParameterByClass("gevEffectivenessAnalysisGUI", "user_id", $user_id);
		$this->gCtrl->setParameterByClass("gevEffectivenessAnalysisGUI", "readonly", "read");
		$this->gCtrl->setParameterByClass("gevEffectivenessAnalysisGUI", "filter_params", null);
		$this->gCtrl->setParameterByClass("gevEffectivenessAnalysisGUI", "back", "view");
		$link = $this->gCtrl->getLinkTargetByClass(array("gevMyEffectivenessAnalysisGUI", "gevEffectivenessAnalysisGUI"));
		$this->gCtrl->setParameterByClass("gevEffectivenessAnalysisGUI", "crs_id", null);
		$this->gCtrl->setParameterByClass("gevEffectivenessAnalysisGUI", "user_id", null);
		$this->gCtrl->setParameterByClass("gevEffectivenessAnalysisGUI", "readonly", null);
		$this->gCtrl->setParameterByClass("gevEffectivenessAnalysisGUI", "back", null);
		$this->gCtrl->setParameterByClass("gevEffectivenessAnalysisGUI", "filter_params", $filter_param);

		return $link;
	}
}