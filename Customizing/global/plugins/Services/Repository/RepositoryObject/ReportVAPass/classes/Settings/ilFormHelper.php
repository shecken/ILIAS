<?php

namespace CaT\Plugins\ReportVAPass\Settings;
use \CaT\Plugins\ReportVAPass\ilActions;

trait ilFormHelper
{
	public function addSettingsFormItems($form)
	{
		$ti = new \ilTextInputGUI($this->txt("setting_sp_node_ref_id"), ilActions::F_SP_NODE_REF_ID);
		$ti->setRequired(true);
		$form->addItem($ti);

		return $form;
	}

	public function addSettingsEditFormItems($form)
	{
		$form = $this->addSettingsFormItems($form);

		$cb = new \ilCheckboxInputGUI($this->txt("setting_online"), ilActions::F_ONLINE);
		$form->addItem($cb);

		return $form;
	}
}
