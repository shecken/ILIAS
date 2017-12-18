<?php

/**
 * Configuration of udf fields takes place here.
 */

use CaT\IliasUserOrguImport as DUOI;

class ilUserConfiguratorGUI
{

	const CMD_SHOW_UDF_CONFIG = 'show_udf';
	const CMD_SAVE_UDF_CONFIG = 'save_udf';

	/**
	 * @param	ilUserOrguImportPlugin	$plugin
	 * @param	ilUserOrguImportConfigGUI	$parent
	 */
	public function __construct($plugin, $parent)
	{
		$this->plugin = $plugin;
		$this->parent = $parent;

		global $DIC;
		$this->tpl = $DIC['tpl'];
		$this->ctrl = $DIC['ilCtrl'];

		$errors = new DUOI\ErrorReporting\ErrorCollection();

		$this->f = $this->plugin->getFactory($errors);
	}

	/**
	 * Execute current ctrl commad for this GUI
	 *
	 * @return void
	 */
	public function executeCommand()
	{
		$this->cmd = $this->ctrl->getCmd(self::CMD_SHOW_UDF_CONFIG);
		switch ($this->cmd) {
			case self::CMD_SHOW_UDF_CONFIG:
				$this->showUdfConfig();
				break;
			case self::CMD_SAVE_UDF_CONFIG:
				$this->saveUdfConfig();
				break;
			default:
				$this->showUdfConfig();
		}
		return true;
	}

	protected function showUdfConfig()
	{
		$form = $this->getUdfConfigForm($this->f->UserFactory()->UserConfig());
		$this->tpl->setContent($form->getHTML());
	}

	protected function saveUdfConfig()
	{
		$config = $this->f->UserFactory()->UserConfig();
		$form = $this->getUdfConfigForm($config);
		$form->setValuesByPost();
		if ($form->checkInput()) {
			foreach ($config->possibleKeywords() as $keyword) {
				$id = $form->getItemByPostVar($keyword)->getValue();
				if (trim((string)$id) !== '') {
					$config = $config->withUdfId($keyword, (int)$id);
				} else {
					$config = $config->withUdfId($keyword, null);
				}
			}
			ilUtil::sendSuccess($this->plugin->txt('udf_saved'));
			$this->showUdfConfig();
		} else {
			$this->tpl->setContent($form->getHTML());
		}
	}

	/**
	 * Form element to configure udf settings.
	 *
	 * @param	C	$config
	 * @return	\ilPropertyFormGUI
	 */
	protected function getUdfConfigForm($user_config)
	{
		$form = $form = new ilPropertyFormGUI();
		$form->setTitle($this->plugin->txt('udf_configuration'));

		$form->setFormAction($this->ctrl->getFormAction($this));

		foreach ($user_config->possibleKeywords() as $keyword) {
			$id = $user_config->getUdfId($keyword);
			$em = new ilNumberInputGUI($this->plugin->txt($keyword), $keyword);
			$em->setMinValue(1);
			$em->allowDecimals(false);
			$em->setRequired(true);
			if ($id !== null) {
				$em->setValue($id);
			}
			$form->addItem($em);
		}
		$form->addCommandButton(self::CMD_SAVE_UDF_CONFIG, $this->plugin->txt('save_udf_config'));
		return $form;
	}
}
