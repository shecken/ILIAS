<?php
require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/GEV/WBD/classes/class.gevWBD.php");
require_once("Customizing/global/plugins/Services/Cron/CronHook/WBDCommunication/classes/class.ilWBDCommunicationConfig.php");

/**
 * WBDCommunication Configuration
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilWBDCommunicationConfigGUI extends ilPluginConfigGUI {

	/**
	 * @var ilWBDCommunicationConfig
	 */
	protected $config_data = null;


	public function __construct() {
		global $ilCtrl, $tpl;
		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;
	}

	/**
	 * @return array
	 */
	public function getFields() {
		
	}

	/**
	 * Handles all commmands, default is 'configure'
	 */
	function performCommand($cmd) {
		$this->load();
		switch ($cmd) {
			case "configure":
			case "save":
			case "update":
				$this->$cmd();
				break;
			default:
				$this->$cmd();
				break;
		}
	}

	protected function configure() {
		$this->gTpl->setContent($this->initConfigurationForm()->getHTML());
	}

	protected function save() {
		$form = $this->initConfigurationForm();

		if(in_array("cpStorno", $_POST["actions"]) && !isset($_POST["storno_rows"])) {
			$show_advice = true;
		} else if(!in_array("cpStorno", $_POST["actions"]) && isset($_POST["storno_rows"])) {
			$ele = $form->getItemByPostVar("storno_rows");
			$ele->setRequired(false);
			unset($_POST["storno_rows"]);
		}

		if(!in_array("cpRequest", $_POST["actions"]) && isset($_POST["request_ids"])) {
			unset($_POST["request_ids"]);
		}

		if(!$form->checkInput()) {
			$form->setValuesByPost();
			$this->gTpl->setContent($form->getHtml());
			return;
		}


		$_POST["storno_rows"] = (in_array("cpStorno", $_POST["actions"]) && $_POST["storno_rows"] != "") ? explode("|", $_POST["storno_rows"]) : null;
		$_POST["request_ids"] = (in_array("cpRequest", $_POST["actions"])) ? explode("|", $_POST["request_ids"]) : null;
		
		$this->config_data = new ilWBDCommunicationConfig();
		$this->config_data->setValueByArray($_POST);
		$this->config_data->save();

		if($show_advice) {
			ilUtil::sendInfo("Sie haben Storno gewählt, bla");
		} else {
			ilUtil::sendInfo("Zeugs gespeichert");
		}
		
		$this->configure();
	}

	protected function addPostValuesToForm($form, $post) {
		foreach ($post as $key => $value) {
			$hidden = new ilHiddenInputGUI($key);
			if(is_array($value)) {
				$value = serialize($value);
			}

			$hidden->setValue($value);
			$form->addItem($hidden);
		}
	}

	protected function load() {
		$this->config_data = new ilWBDCommunicationConfig();
		$this->config_data->load();
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	public function initConfigurationForm() {
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->gCtrl->getFormAction($this));
		$form->setShowTopButtons(false);

		if(!$this->config_data->loaded()) {
			$form->addCommandButton("save", "save");
		} else {
			$form->addCommandButton("save", "update");
		}

		$siggy = new ilTextInputGUI("siggy", "siggy");
		if($this->config_data->loaded()) {
			$siggy->setValue($this->config_data->siggy());
		}
		$siggy->setRequired(true);
		$form->addItem($siggy);

		$wbd = new ilTextInputGUI("wbd", "wbd");
		if($this->config_data->loaded()) {
			$wbd->setValue($this->config_data->wbd());
		}
		$wbd->setRequired(true);
		$form->addItem($wbd);

		$actions = new ilMultiSelectInputGUI("Action", "actions");
		if($this->config_data->loaded()) {
			$actions->setValue($this->config_data->actions());
		}
		$actions->setRequired(true);
		$actions->setOptions(gevWBD::$actions);
		$form->addItem($actions);

		if($this->config_data->loaded() && in_array("cpStorno", $this->config_data->actions())) {
			$storno_rows = new ilTextAreaInputGUI("storno rows", "storno_rows");
			$storno_rows->setInfo("Bitte trennen Sie die ID's mit einer Pipe (id|id|...|id).");
			$storno_rows->setRows(10);
			$storno_rows->setCols(50);
			$storno_rows->setValue(implode("|",$this->config_data->stornoRows()));
			$storno_rows->setRequired(true);
			$form->addItem($storno_rows);
		}

		if($this->config_data->loaded() && $this->config_data->requestIds()) {
			$request_ids = new ilTextAreaInputGUI("request ids", "request_ids");
			$request_ids->setInfo("Wenn Sie alle Benutzer abfagen wollen, dann lassen Sie dieses Feld bitte leer. Ansonsten trennen Sie die ID's mit einer Pipe (id|id|...|id).");
			$request_ids->setRows(10);
			$request_ids->setCols(50);
			$request_ids->setValue(implode("|",$this->config_data->requestIds()));
			$form->addItem($request_ids);
		}

		return $form;
	}
}