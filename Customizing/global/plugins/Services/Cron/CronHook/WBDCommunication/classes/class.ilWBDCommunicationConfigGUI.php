<?php
require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Form/classes/class.ilTextInputGUI.php");
require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
require_once("Services/Form/classes/class.ilTextAreaInputGUI.php");
require_once("Services/GEV/WBD/classes/class.gevWBD.php");
require_once("Customizing/global/plugins/Services/Cron/CronHook/WBDCommunication/classes/class.ilWBDCommunicationConfig.php");

/**
 * WBDCommunication Configuration
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilWBDCommunicationConfigGUI extends ilPluginConfigGUI {

	const REGEX_IDS = "/^[\d]+([\|\d]*|\d)$/";

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
			case "cancel":
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

		if(in_array("cpStorno", $_POST["actions"]) && $_POST["storno_rows"] != "") {

			if(!$this->checkString($_POST["storno_rows"])) {
				$form->setValuesByPost();
				$this->gTpl->setContent($form->getHtml());
				ilUtil::sendFailure($this->getPluginObject()->txt("string_is_wrong"));
				return;
			} else {
				$_POST["storno_rows"] = explode("|", $_POST["storno_rows"]);
			}
		} else {
			$_POST["storno_rows"] = null;
		}

		if(in_array("cpRequest", $_POST["actions"]) && $_POST["request_ids"] != "") {
			if(!$this->checkString($_POST["request_ids"])) {
				$form->setValuesByPost();
				$this->gTpl->setContent($form->getHtml());
				ilUtil::sendFailure($this->getPluginObject()->txt("string_is_wrong"));
				return;
			} else {
				$_POST["request_ids"] = explode("|", $_POST["request_ids"]);
			}
		} else {
			$_POST["request_ids"] = null;
		}

		$this->config_data = new ilWBDCommunicationConfig();
		$this->config_data->setValueByArray($_POST);
		$this->config_data->save();

		if($show_advice) {
			ilUtil::sendInfo($this->getPluginObject()->txt("storno_in_use"));
		} else {
			ilUtil::sendInfo($this->getPluginObject()->txt("save_success"));
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

		$form->addCommandButton("save", $this->getPluginObject()->txt("save"));
		$form->addCommandButton("cancel", $this->getPluginObject()->txt("cancel"));

		$siggy = new ilTextInputGUI($this->getPluginObject()->txt("siggy"), "siggy");
		if($this->config_data->loaded()) {
			$siggy->setValue($this->config_data->siggy());
		}
		$siggy->setRequired(true);
		$form->addItem($siggy);

		$wbd = new ilTextInputGUI($this->getPluginObject()->txt("wbd"), "wbd");
		if($this->config_data->loaded()) {
			$wbd->setValue($this->config_data->wbd());
		}
		$wbd->setRequired(true);
		$form->addItem($wbd);

		$config_path = new ilTextInputGUI($this->getPluginObject()->txt("config_path"), "config_path");
		if($this->config_data->loaded()) {
			$config_path->setValue($this->config_data->configPath());
		}
		$config_path->setRequired(true);
		$form->addItem($config_path);

		$run_script = new ilTextInputGUI($this->getPluginObject()->txt("run_script"), "run_script");
		if($this->config_data->loaded()) {
			$run_script->setValue($this->config_data->runScript());
		}
		$run_script->setRequired(true);
		$form->addItem($run_script);

		$actions = new ilMultiSelectInputGUI($this->getPluginObject()->txt("actions"), "actions");
		if($this->config_data->loaded()) {
			$actions->setValue($this->config_data->actions());
		}
		$actions->setRequired(true);
		$actions->setOptions(gevWBD::$actions);
		$form->addItem($actions);

		if($this->config_data->loaded() && in_array("cpStorno", $this->config_data->actions())) {
			$storno_rows = new ilTextAreaInputGUI($this->getPluginObject()->txt("storno_rows"), "storno_rows");
			$storno_rows->setInfo($this->getPluginObject()->txt("storno_rows_desc"));
			$storno_rows->setRows(10);
			$storno_rows->setCols(50);
			$storno_rows->setValue(implode("|",$this->config_data->stornoRows()));
			$storno_rows->setRequired(true);
			$form->addItem($storno_rows);
		}

		if($this->config_data->loaded() && in_array("cpRequest", $this->config_data->actions())) {
			$request_ids = new ilTextAreaInputGUI($this->getPluginObject()->txt("request_ids"), "request_ids");
			$request_ids->setInfo($this->getPluginObject()->txt("request_ids_desc"));
			$request_ids->setRows(10);
			$request_ids->setCols(50);
			$request_ids->setValue(implode("|",$this->config_data->requestIds()));
			$form->addItem($request_ids);
		}

		return $form;
	}

	protected function cancel() {

	}

	protected function checkString($string) {
		return preg_match(self::REGEX_IDS, $string);
	}
}