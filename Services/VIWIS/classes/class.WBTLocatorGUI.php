<?php
require_once 'Services/VIWIS/exceptions/class.WBTLocatorException.php';
require_once 'Services/VIWIS/classes/class.WBTLocator.php';
class WBTLocatorGUI {

	public function __construct() {
		global $ilDB, $ilCtrl;
		$this->ctrl = $ilCtrl;
		$this->locator = new WBTLocator($ilDB);
	}

	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		switch($cmd) {
			case 'redirect_viwis':
				$redirect_params = $this->locator->getRedirectParameterForQuestionRef($_GET['q_ref']);
				$redirect = "ilias.php?baseClass=ilSAHSPresentationGUI"
					."&ref_id=".$redirect_params['ref_id']
					.'&redirect_viwis='.$redirect_params['wbt_item'];
				ilUtil::redirect($redirect);
			default:
				throw new WBTLocatorException('unknown command: '.$cmd);
		}
	}
}