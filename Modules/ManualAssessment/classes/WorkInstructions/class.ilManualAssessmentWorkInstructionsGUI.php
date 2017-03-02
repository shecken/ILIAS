<?php

/**
 * GUI to edit the work intruction settings
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilManualAssessmentWorkInstructionsGUI
{
	const CMD_EDIT = "editWorkInstructions";
	const CMD_SAVE = "saveWorkInstructions";
	const CMD_DELIVER_FILE = "deliverFile";
	const CMD_DELETE_FILES = "deleteFiles";

	const F_TEXT_INPUT = "text_input";
	const F_UPLOAD_FILE = "file_upload";
	const F_UPLOADED_FILES = "uploaded_files";
	const F_TO_DELETE_FILES = "to_delete_files";

	const TPL_DL_LINK = '<a href="%s">%s</a>';

	/**
	 * @var ilCtrl
	 */
	protected $g_ctrl;

	/**
	 * @var ilTemplateGUI
	 */
	protected $g_tpl;

	/**
	 * @var ilLanguage
	 */
	protected $g_lng;

	/**
	 * @var ilManualAssessmentSettingsGUI
	 */
	protected $parent_gui;

	/**
	 * @var ilObjManualAssessment
	 */
	protected $object;

	/**
	 * @var ilManualAssessmentFileStorage
	 */
	protected $file_storage;

	public function __construct(ilManualAssessmentSettingsGUI $parent_gui)
	{
		global $ilCtrl, $tpl, $lng;

		$this->g_ctrl = $ilCtrl;
		$this->g_tpl = $tpl;
		$this->g_lng = $lng;

		$this->parent_gui = $parent_gui;
		$this->object = $parent_gui->object;
		$this->file_storage = $parent_gui->object->getFileStorage();
	}

	public function executeCommand()
	{
		$cmd = $this->g_ctrl->getCmd(self::CMD_EDIT);
		switch ($cmd) {
			case self::CMD_EDIT:
			case self::CMD_SAVE:
			case self::CMD_DELIVER_FILE:
			case self::CMD_DELETE_FILES:
				$this->$cmd();
				break;
			default:
				throw new Exception(__METHOD__.": Unknown command $cmd");
		}
	}

	/**
	 * Show edit form
	 *
	 * @return null
	 */
	protected function editWorkInstructions()
	{
		$form = $this->initForm();
		$this->setValues($form);
		$form->addCommandButton(self::CMD_SAVE, $this->g_lng->txt("save"));

		$this->g_tpl->setContent($form->getHtml());
	}

	/**
	 * Set values to form
	 *
	 * @return null
	 */
	protected function setValues(&$form)
	{
		$values = array();
		$values[self::F_TEXT_INPUT] = $this->object->getSettings()->workInstruction();

		$form->setValuesByArray($values);
	}

	/**
	 * Save work instructions
	 *
	 * @return null
	 */
	protected function saveWorkInstructions()
	{
		$post = $_POST;

		$this->saveText();
		$this->uploadFiles($_FILES[self::F_UPLOAD_FILE]);

		if (isset($post[self::F_UPLOADED_FILES])) {
			$this->confirmFileDelete($post[self::F_UPLOADED_FILES]);
			return;
		}

		$this->g_ctrl->redirect($this);
	}

	/**
	 * Saves the work intruction input text
	 *
	 * @return null
	 */
	protected function saveText()
	{
		$this->object->getSettings()->setWorkInstruction($_POST[self::F_TEXT_INPUT]);
		$this->object->update();
	}

	/**
	 * Upload new files
	 *
	 * @return null
	 */
	protected function uploadFiles($files)
	{
		$this->file_storage->create();
		foreach ($files["name"] as $key => $value) {
			$this->file_storage->uploadFile(array("name" => $value, "tmp_name" => $files["tmp_name"][$key]));
		}
	}

	/**
	 * Deliver clicked file
	 *
	 * @return null
	 */
	protected function deliverFile()
	{
		$file_name = $_GET["fileName"];
		ilUtil::deliverFile($this->file_storage->getAbsolutePath()."/".$file_name, $file_name);
	}

	/**
	 * Confirmation selected files should be deleted
	 *
	 * @return null
	 */
	protected function confirmFileDelete($file_names)
	{
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		foreach ($file_names as $key => $file_name) {
			$confirm->addItem(self::F_TO_DELETE_FILES.'[]', $file_name, $file_name);
		}

		$confirm->setHeaderText($this->g_lng->txt('mass_work_instruction_delete_confirm'));
		$confirm->setFormAction($this->g_ctrl->getFormAction($this));
		$confirm->setConfirm($this->g_lng->txt('mass_work_instruction_delete'), self::CMD_DELETE_FILES);
		$confirm->setCancel($this->g_lng->txt('cancel'), self::CMD_EDIT);

		$this->g_tpl->setContent($confirm->getHTML());
	}

	/**
	 * Delete selected files
	 *
	 * @return null
	 */
	protected function deleteFiles()
	{
		$file_names = $_POST[self::F_TO_DELETE_FILES];
		foreach ($file_names as $file_name) {
			$this->file_storage->deleteFileByName($file_name);
		}

		$this->g_ctrl->redirect($this);
	}

	/**
	 * Initialization of edit form
	 *
	 * @return ilPropertyFormGUI
	 */
	protected function initForm()
	{
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->g_lng->txt("mass_work_instructions"));
		$form->setFormAction($this->g_ctrl->getFormAction($this));

		$ta = new ilTextAreaInputGUI($this->g_lng->txt("mass_work_instruction_text"), self::F_TEXT_INPUT);
		$ta->setUseRte(true);
		$ta->setRows(10);
		$form->addItem($ta);

		$fi = new ilFileWizardInputGUI($this->g_lng->txt("mass_work_instruction_upload"), self::F_UPLOAD_FILE);
		$fi->setFilenames(array(0 => ''));
		$form->addItem($fi);

		$cbx = new ilCheckBoxGroupInputGUI($this->g_lng ->txt("mass_work_instruction_uploaded_files"), self::F_UPLOADED_FILES);
		$cbx->setInfo($this->g_lng ->txt("mass_work_instruction_uploaded_files_info"));
		foreach ($this->uploadedFilesInformations() as $value) {
			$option = new ilCheckboxOption(sprintf(self::TPL_DL_LINK, $value["link"], $value["file_name"]), $value["file_name"]);
			$cbx->addOption($option);
		}
		$form->addItem($cbx);

		return $form;
	}

	/**
	 * Get informations about uploaded files
	 *
	 * @return [array(string, string)]
	 */
	protected function uploadedFilesInformations()
	{
		$files = $this->object->getWorkIntructionFileNames();
		$file_infos = array();

		foreach ($files as $file_name) {
			$this->g_ctrl->setParameter($this, "fileName", $file_name);
			$link = $this->g_ctrl->getLinkTarget($this, "deliverFile");
			$this->g_ctrl->setParameter($this, "fileName", null);

			$file_infos[] = array("link"=>$link, "file_name"=>$file_name);
		}

		return $file_infos;
	}
}
