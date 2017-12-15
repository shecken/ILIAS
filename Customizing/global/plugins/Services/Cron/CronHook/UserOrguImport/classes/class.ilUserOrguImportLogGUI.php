<?php

use CaT\IliasUserOrguImport as DUOI;

class ilUserOrguImportLogGUI
{

	const CMD_SHOW_FILTER = 'show_filter';
	const CMD_SHOW_LOG_ENTRIES = 'show_log_entries';

	const POST_ORGU_ID = 'orgu_id';
	const POST_PNR = 'pnr';
	const POST_AOB = 'aob';

	protected $tpl;

	protected $plugin;
	protected $parent;

	public function __construct($plugin, $parent)
	{
		$this->plugin = $plugin;
		$this->parent = $parent;

		global $DIC;
		$this->tpl = $DIC['tpl'];
		$this->ctrl = $DIC['ilCtrl'];

		$errors = new DUOI\ErrorReporting\ErrorCollection();
		$f = $this->plugin->getFactory($errors);
		$this->actions = new DUOI\User\ilUserActions($f->UserFactory());
		$this->log = $f->Log();
	}

	public function executeCommand()
	{
		$this->cmd = $this->ctrl->getCmd(self::CMD_SHOW_FILTER);
		switch ($this->cmd) {
			case self::CMD_SHOW_FILTER:
				$this->showFilter();
				break;
			case self::CMD_SHOW_LOG_ENTRIES:
				$this->showLogEntries();
				break;
			default:
				$this->showFilter();
		}
		return true;
	}

	protected function showFilter()
	{
		$form = $this->filterForm();
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}

	protected function showLogEntries()
	{
		$form = $this->filterForm();
		$form->setValuesByPost();

		$orgu_id = trim((string)$form->getItemByPostVar(self::POST_ORGU_ID)->getValue());
		$pnr = trim((string)$form->getItemByPostVar(self::POST_PNR)->getValue());
		$aob = trim((string)$form->getItemByPostVar(self::POST_AOB)->getValue());

		if ($orgu_id === '' && $pnr === '' && $aob === '') {
			\ilUtil::sendFailure($this->plugin->txt('no_filter_value_warning'));
			$this->showFilter();
		} else {
			$properties = [];
			if ($orgu_id !== '') {
				$properties['orgu_id'] = $orgu_id;
			}
			if ($pnr !== '') {
				$properties['pnr'] = $pnr;
			}
			if ($aob !== '') {
				$properties['aob'] = $aob;
			}

			$table = new DUOI\LogEntriesTableGUI(
				$this->log->lookupEntries($properties),
				$this->plugin,
				$this,
				$this->cmd
			);
			$this->tpl->setContent($form->getHTML().$table->getHTML());
		}
	}

	protected function filterForm()
	{
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->plugin->txt('filter_log_entries'));

		$form->setFormAction($this->ctrl->getFormAction($this));

		$orgu_id = new ilTextInputGUI(
			$this->plugin->txt('filter_log_orgu_id'),
			self::POST_ORGU_ID
		);
		$form->addItem($orgu_id);

		$pnr = new ilTextInputGUI(
			$this->plugin->txt('filter_log_pnr'),
			self::POST_PNR
		);
		$form->addItem($pnr);

		$aob = new ilTextInputGUI(
			$this->plugin->txt('filter_log_aob'),
			self::POST_AOB
		);
		$form->addItem($aob);

		$form->addCommandButton(self::CMD_SHOW_LOG_ENTRIES, $this->plugin->txt('filter_log_show_entries'));
		return $form;
	}
}
