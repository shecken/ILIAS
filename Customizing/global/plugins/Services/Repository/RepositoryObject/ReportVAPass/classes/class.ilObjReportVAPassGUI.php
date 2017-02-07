<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
/**
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObjTalentAssessmentGUI extends ilObjectPluginGUI
{
	/**
	 * Initialisation
	 */
	protected function afterConstructor()
	{
		global $ilAccess, $ilCtrl;

		$this->gAccess = $ilAccess;
		$this->gCtrl = $ilCtrl;
	}

	/**
	 * Get type.
	 */
	final public function getType()
	{
		return "xvap";
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	public function performCommand($cmd)
	{
		switch ($cmd) {
			case "view":
				$this->view();
				break;
		}
	}

	/**
	 * After object has been created -> jump to this command
	 */
	public function getAfterCreationCmd()
	{
		return "view";
	}

	/**
	 * Get standard command
	 */
	public function getStandardCmd()
	{
		return "view";
	}

	public function initCreateForm($a_new_type)
	{
		$form = parent::initCreateForm($a_new_type);

		return $form;
	}

	protected function forwardSettings()
	{
		if (!$this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
			\ilUtil::sendFailure($this->plugin->txt('obj_permission_denied'), true);
			$this->gCtrl->redirectByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
		} else {
			$this->gTabs->setTabActive(self::TAB_SETTINGS);
			$actions = $this->object->getActions();
			$gui = new ilTalentAssessmentSettingsGUI($actions, $this->plugin->txtClosure(), $this->object->getId(), $this->object->getSettings()->getPotential());
			$this->gCtrl->forwardCommand($gui);
		}
	}

	protected function view()
	{
	}
}
