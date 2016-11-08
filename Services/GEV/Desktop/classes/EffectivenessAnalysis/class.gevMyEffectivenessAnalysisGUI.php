<?php

class gevMyEffectivenessAnalysisGUI {
	const CMD_VIEW = "view";
	const CMD_FILTER = "filter";

	/**
	 * @var ilCrl
	 */
	protected $gCtrl;

	/**
	 * @var ilTemplate
	 */
	protected $gTpl;

	/**
	 * @var ilLanguage
	 */
	protected $gLng;

	public function __construct() {
		global $ilCtrl, $tpl, $lng, $ilUser;

		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;
		$this->gLng = $lng;
		$this->gUser = $ilUser;

		require_once("Services/GEV/Desktop/classes/EffectivenessAnalysis/class.gevEffectivenessAnalysis.php");
		$this->eff_analysis = gevEffectivenessAnalysis::getInstance();
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd(self::CMD_VIEW);

		switch($cmd) {
			case self::CMD_VIEW:
			case self::CMD_FILTER:
				$this->$cmd();
				break;
			default:
		}
	}

	protected function view(array $filter = array()) {
		$html = $this->getTitle()->render();
		$html .= $this->getFilter()->render();
		$html .= $this->getTable($filter)->getHtml();

		$this->gTpl->setContent($html);
	}

	protected function getFilter() {
		$filter = $this->eff_analysis->getFilter($this->gUser->getId());
		$filter->action($this->gCtrl->getLinkTarget($this, self::CMD_FILTER))
				->compile();

		return $filter;
	}

	protected function filter() {
		$filter_values = $this->eff_analysis->getFilterValues($_POST);

		$this->view($filter_values);
	}

	protected function getTitle() {
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		return new catTitleGUI("gev_my_effectiveness_analysis");
	}

	protected function getTable(array $filter) {
		require_once("Services/GEV/Desktop/classes/EffectivenessAnalysis/class.gevMyEffectivenessAnalysisTableGUI.php");
		return new gevMyEffectivenessAnalysisTableGUI($filter, $this);
	}
}