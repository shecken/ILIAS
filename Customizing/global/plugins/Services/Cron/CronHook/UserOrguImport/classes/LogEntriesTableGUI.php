<?php

namespace CaT\IliasUserOrguImport;

require_once 'Services/Table/classes/class.ilTable2GUI.php';

class LogEntriesTableGUI extends \ilTable2GUI
{
	public function __construct(
		array $entries,
		$a_plugin,
		$a_parent_obj,
		$a_parent_cmd
	) {

		global $DIC;

		parent::__construct($a_parent_obj, $a_parent_cmd);


		$this->plugin = $a_plugin;
		$this->parent = $a_parent_obj;
		$this->setFormAction($DIC['ilCtrl']->getFormAction($this->parent));
		$this->setEnableTitle(true);
		$this->setTitle($a_plugin->txt('log_table_title'));
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setExternalSorting(false);
		$this->setExternalSegmentation(false);
		$this->setDefaultOrderField("orgu_id");
		$this->setDefaultOrderDirection("asc");


		$this->setRowTemplate("tpl.log_row.html", $a_plugin->getDirectory());

		$this->setData($entries);

		$this->addColumn('', '');
		$this->addColumn($a_plugin->txt('log_table_orgu_id'), 'orgu_id');
		$this->addColumn($a_plugin->txt('log_table_pnr'), 'pnr');
		$this->addColumn($a_plugin->txt('log_table_orgu_title'), 'orgu_title');
		$this->addColumn($a_plugin->txt('log_table_timestamp'), Log\DatabaseLog::ROW_TIMESTAMP);
		$this->addColumn($a_plugin->txt('log_table_entry'), Log\DatabaseLog::ROW_ENTRY);
	}

	protected function fillRow($set)
	{
		$this->tpl->setVariable('ORGU_ID', $set['orgu_id']);
		$this->tpl->setVariable('PNR', $set['pnr']);
		$this->tpl->setVariable('ORGU_TITLE', $set['orgu_title']);
		$this->tpl->setVariable('TIMESTAMP', date('Y-m-d H:i:s', $set[Log\DatabaseLog::ROW_TIMESTAMP]));
		$this->tpl->setVariable('ENTRY', $set[Log\DatabaseLog::ROW_ENTRY]);
	}
}
