<?php
require_once 'Services/VIWIS/exceptions/class.WBTLocatorException.php';
require_once 'Services/VIWIS/classes/class.WBTLocator.php';
/**
 * Redirect to the appropriate WBT.
 */
class WBTLocatorGUI {

	public function __construct() {
		global $ilDB, $ilCtrl, $lng;
		$this->ctrl = $ilCtrl;
		$this->locator = new WBTLocator($ilDB);
		$this->lng = $lng;
	}

	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		switch($cmd) {
			case 'redirect_viwis':
				$redirect_params = $this->locator->getRedirectParameterForQuestionRef($_GET['q_ref']);
				if($redirect_params !== null) {
					$redirect = "ilias.php?baseClass=ilSAHSPresentationGUI"
						."&ref_id=".$redirect_params['ref_id']
						.'&redirect_viwis='.$redirect_params['wbt_item'];
					ilUtil::redirect($redirect);
				} else {
					ilUtil::sendInfo(sprintf($this->lng->txt('no_wbt_found_for_ref'),$_GET['q_ref']));
				}
				break;
			default:
				throw new WBTLocatorException('unknown command: '.$cmd);
		}
	}
}