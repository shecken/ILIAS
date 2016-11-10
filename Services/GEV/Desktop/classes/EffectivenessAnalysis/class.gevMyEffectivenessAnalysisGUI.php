<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @ilCtrl_Calls gevMyEffectivenessAnalysisGUI: gevEffectivenessAnalysisGUI
*/
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
		$next_class = $this->gCtrl->getNextClass();

		switch($next_class) {
			case "geveffectivenessanalysisgui":
				require_once("Services/GEV/Desktop/classes/EffectivenessAnalysis/class.gevEffectivenessAnalysisGUI.php");
				$gui = new gevEffectivenessAnalysisGUI();
				$this->gCtrl->forwardCommand($gui);
				break;
			default:
				switch($cmd) {
					case self::CMD_VIEW:
					case self::CMD_FILTER:
						$this->view();
						break;
				default:
			}
		}
	}

	protected function view() {
		$html = $this->getTitle()->render();

		$filter = $this->getFilter();
		$filter_values = $this->eff_analysis->buildFilterValuesFromFilter($filter);
		$html .= $filter->render();

		$this->gCtrl->setParameter($this, $filter->getGETName(), $filter->encodeSearchParamsForGET());
		$html .= $this->getTable($filter_values)->getHtml();
		$this->gCtrl->setParameter($this, $filter->getGETName(), null);

		$this->gTpl->setContent($html);
	}

	protected function getFilter() {
		$filter = $this->eff_analysis->getFilter($this->gUser->getId());
		$filter->action($this->gCtrl->getLinkTarget($this, self::CMD_FILTER))
				->compile();

		return $filter;
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