<?php

/**
 * Configuration of orgu amd, record orgu-type and import root-ref-ids takes place here.
 */

use CaT\IliasUserOrguImport as DUOI;
use CaT\IliasUserOrguImport\Orgu\OrguConfig as C;

class ilOrguConfiguratorGUI
{
	const CMD_SHOW = 'show';
	const CMD_SAVE = 'save';

	protected $plugin;
	protected $parent;

	protected $ctrl;

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
		$this->cmd = $this->ctrl->getCmd(self::CMD_SHOW);
		switch ($this->cmd) {
			case self::CMD_SHOW:
				$this->show();
				break;
			case self::CMD_SAVE:
				$this->save();
				break;
			default:
				$this->show();
		}
		return true;
	}

	protected function show()
	{
		$config = $this->f->OrguFactory()->OrguConfig();
		$form = $this->getConfigurationForm($config);
		$this->tpl->setContent($form->getHTML());
	}

	protected function save()
	{
		$config = $this->f->OrguFactory()->OrguConfig();
		$form = $this->getConfigurationForm($config);
		$form->setValuesByPost();
		if ($form->checkInput()) {

			$root_ref_id = $form->getItemByPostVar(C::KEYWORD_ROOT_REF_ID)->getValue();
			if (trim((string)$root_ref_id) !== '') {
				$config->setRootRefId((int)$root_ref_id);
			}

			$exit_ref_id = $form->getItemByPostVar(C::KEYWORD_EXIT_REF_ID)->getValue();
			if (trim((string)$exit_ref_id) !== '') {
				$config->setExitRefId((int)$exit_ref_id);
			}

			ilUtil::sendSuccess($this->plugin->txt('config_saved'));
			$this->show();
		} else {
			$this->tpl->setContent($form->getHTML());
		}
	}

	/**
	 * Form element to configure orgu settings.
	 *
	 * @param	C	$config
	 * @return	\ilPropertyFormGUI
	 */
	protected function getConfigurationForm(C $config)
	{
		$form = $form = new ilPropertyFormGUI();
		$form->setTitle($this->plugin->txt('orgu_configuration'));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$em = $this->numberInput($this->plugin->txt('import_root'), C::KEYWORD_ROOT_REF_ID);
		$root_ref_id = $config->getRootRefId();
		if ($root_ref_id !== null) {
			$em->setValue($root_ref_id);
		}
		$form->addItem($em);

		$em = $this->numberInput($this->plugin->txt('import_exit'), C::KEYWORD_EXIT_REF_ID);
		$exit_ref_id = $config->getExitRefId();
		if ($exit_ref_id !== null) {
			$em->setValue($exit_ref_id);
		}
		$form->addItem($em);

		$form->addCommandButton(self::CMD_SAVE, $this->plugin->txt('save_config'));
		return $form;
	}

	/**
	 * Number input field
	 *
	 * @param	string	$title
	 * @param	string	$postvar
	 * @return \ilNumberInputGUI
	 */
	protected function numberInput($title, $postvar)
	{
			$em = new ilNumberInputGUI($title, $postvar);
			$em->setMinValue(1);
			$em->allowDecimals(false);
			$em->setRequired(true);
			return $em;
	}
}
