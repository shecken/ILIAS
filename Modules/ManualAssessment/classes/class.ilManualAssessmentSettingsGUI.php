<?php
/**
 * @ilCtrl_Calls ilManualAssessmentSettingsGUI: ilConditionHandlerInterface
*/
class ilManualAssessmentSettingsGUI
{

	const PROP_CONTENT = "content";
	const PROP_RECORD_TEMPLATE = "record_template";
	const PROP_TITLE = "title";
	const PROP_DESCRIPTION = "description";
	const PROP_EVENT_TIME_PLACE_REQUIRED = "event_time_place_required";

	const PROP_INFO_CONTACT = "contact";
	const PROP_INFO_RESPONSIBILITY = "responsibility";
	const PROP_INFO_PHONE = "phone";
	const PROP_INFO_MAILS = "mails";
	const PROP_INFO_CONSULTATION = "consultatilon";
	const PROP_FILE_REQUIRED = "file_required";
	const PROP_SUPERIOR_EXAMINATE = "superior_examinate";
	const PROP_SUPERIOR_VIEW = "superior_view";
	const PROP_GRADE_SELF = "grade_self";

	const TAB_EDIT = 'settings';
	const TAB_EDIT_INFO = 'infoSettings';
	const TAB_PRECONDITIONS = 'preconditions';

	public function __construct($a_parent_gui, $a_ref_id)
	{
		global $ilCtrl, $tpl, $lng;
		$this->ctrl = $ilCtrl;
		$this->parent_gui = $a_parent_gui;
		$this->object = $a_parent_gui->object;
		$this->ref_id = $a_ref_id;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->tabs_gui = $a_parent_gui->tabsGUI();
		$this->getSubTabs($this->tabs_gui);
	}

	protected function getSubTabs(ilTabsGUI $tabs)
	{
		$tabs->addSubTab(
			self::TAB_EDIT,
			$this->lng->txt("edit"),
			$this->ctrl->getLinkTarget($this, 'edit')
		);
		$tabs->addSubTab(
			self::TAB_EDIT_INFO,
			$this->lng->txt("mass_edit_info"),
			$this->ctrl->getLinkTarget($this, 'editInfo')
		);
		$tabs->addSubTab(
			self::TAB_PRECONDITIONS,
			$this->lng->txt("mass_edit_conditions"),
			$this->ctrl->getLinkTargetByClass(array('ilManualAssessmentSettingsGUI','ilConditionHandlerInterface'), 'listConditions')
		);
	}

	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch ($next_class) {
			case "ilconditionhandlerinterface":
				include_once './Services/AccessControl/classes/class.ilConditionHandlerInterface.php';
				$this->tabs_gui->setSubTabActive(self::TAB_PRECONDITIONS);
				$new_gui =& new ilConditionHandlerInterface($this);
				$this->ctrl->forwardCommand($new_gui);
				break;
			default:
				switch ($cmd) {
					case 'edit':
					case 'update':
					case 'cancel':
					case 'editInfo':
					case 'updateInfo':
						if (!$this->object->accessHandler()->checkAccessToObj($this->object, 'write')) {
							$this->parent_gui->handleAccessViolation();
						}
						$this->$cmd();
					    break;
				}
		}
	}

	protected function cancel()
	{
		$this->ctrl->redirect($this->parent_gui);
	}

	protected function edit()
	{
		$this->tabs_gui->setSubTabActive(self::TAB_EDIT);
		$form = $this->fillForm($this->initSettingsForm(), $this->object, $this->object->getSettings());
		$this->renderForm($form);
	}

	protected function editInfo()
	{
		$this->tabs_gui->setSubTabActive(self::TAB_EDIT_INFO);
		$form = $this->fillInfoForm($this->initInfoSettingsForm(), $this->object->getInfoSettings());
		$this->renderForm($form);
	}

	protected function updateInfo()
	{
		$this->tabs_gui->setSubTabActive(self::TAB_EDIT_INFO);
		$form = $this->initInfoSettingsForm();
		$form->setValuesByArray($_POST);
		if ($form->checkInput()) {
			$this->object->getInfoSettings()
				->setContact($_POST[self::PROP_INFO_CONTACT])
				->setResponsibility($_POST[self::PROP_INFO_RESPONSIBILITY])
				->setPhone($_POST[self::PROP_INFO_PHONE])
				->setMails($_POST[self::PROP_INFO_MAILS])
				->setConsultationHours($_POST[self::PROP_INFO_CONSULTATION]);
			$this->object->updateInfo();
			ilUtil::sendSuccess($this->lng->txt('mass_settings_saved'));
		}
		$this->renderForm($form);
	}

	protected function renderForm(ilPropertyFormGUI $a_form)
	{
		$this->tpl->setContent($a_form->getHTML());
	}

	protected function update()
	{
		$this->tabs_gui->setSubTabActive(self::TAB_EDIT);
		$form = $this->initSettingsForm();
		$form->setValuesByArray($_POST);
		if ($form->checkInput()) {
			$this->object->setTitle($_POST[self::PROP_TITLE]);
			$this->object->setDescription($_POST[self::PROP_DESCRIPTION]);
			$this->object->getSettings()->setContent($_POST[self::PROP_CONTENT])
								->setRecordTemplate($_POST[self::PROP_RECORD_TEMPLATE])
								->setFileRequired((bool)$_POST[self::PROP_FILE_REQUIRED])
								->setRecordTemplate($_POST[self::PROP_RECORD_TEMPLATE])
								->setEventTimePlaceRequired($_POST[self::PROP_EVENT_TIME_PLACE_REQUIRED])
								->setSuperiorExaminate((bool)$_POST[self::PROP_SUPERIOR_EXAMINATE])
								->setSuperiorView((bool)$_POST[self::PROP_SUPERIOR_VIEW])
								->setGradeSelf((bool)$_POST[self::PROP_GRADE_SELF]);
			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt('mass_settings_saved'));
		}
		$this->renderForm($form);
	}


	protected function initSettingsForm()
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('mass_edit'));

		// title
		$ti = new ilTextInputGUI($this->lng->txt('title'), self::PROP_TITLE);
		$ti->setSize(40);
		$ti->setRequired(true);
		$form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt('description'), self::PROP_DESCRIPTION);
		$ta->setCols(40);
		$ta->setRows(2);
		$form->addItem($ta);


		$item = new ilTextAreaInputGUI($this->lng->txt('mass_content'), self::PROP_CONTENT);
		$item->setInfo($this->lng->txt('mass_content_explanation'));
		$form->addItem($item);

		$item = new ilTextAreaInputGUI($this->lng->txt('mass_record_template'), self::PROP_RECORD_TEMPLATE);
		$item->setInfo($this->lng->txt('mass_record_template_explanation'));
		$form->addItem($item);

		$option = new ilCheckboxInputGUI($this->lng->txt('mass_event_time_place_required'), self::PROP_EVENT_TIME_PLACE_REQUIRED);
		$option->setInfo($this->lng->txt('mass_event_time_place_required_info'));
		$form->addItem($option);

		$cb = new ilCheckboxInputGUI($this->lng->txt('mass_file_required'), self::PROP_FILE_REQUIRED);
		$cb->setInfo($this->lng->txt('mass_file_required_info'));
		$form->addItem($cb);

		$cb = new ilCheckboxInputGUI($this->lng->txt('mass_superior_can_examinate'), self::PROP_SUPERIOR_EXAMINATE);
		$cb->setInfo($this->lng->txt('mass_superior_can_examinate_info'));
		$form->addItem($cb);

		$cb = new ilCheckboxInputGUI($this->lng->txt('mass_superior_view_assessments'), self::PROP_SUPERIOR_VIEW);
		$cb->setInfo($this->lng->txt('mass_superior_view_assessments_info'));
		$form->addItem($cb);

		$cb = new ilCheckboxInputGUI($this->lng->txt('mass_grade_self'), self::PROP_GRADE_SELF);
		$cb->setInfo($this->lng->txt('mass_grade_self_info'));
		$form->addItem($cb);

		$form->addCommandButton('update', $this->lng->txt('save'));
		$form->addCommandButton('cancel', $this->lng->txt('cancel'));
		return $form;
	}

	protected function initInfoSettingsForm()
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('mass_edit_info'));

		$ti = new ilTextInputGUI($this->lng->txt('mass_contact'), self::PROP_INFO_CONTACT);
		$ti->setSize(40);
		$form->addItem($ti);

		$ti = new ilTextInputGUI($this->lng->txt('mass_responsibility'), self::PROP_INFO_RESPONSIBILITY);
		$ti->setSize(40);
		$form->addItem($ti);

		$ti = new ilTextInputGUI($this->lng->txt('mass_phone'), self::PROP_INFO_PHONE);
		$ti->setSize(40);
		$form->addItem($ti);

		$ti = new ilTextInputGUI($this->lng->txt('mass_mails'), self::PROP_INFO_MAILS);
		$ti->setInfo($this->lng->txt('mass_info_emails_expl'));
		$ti->setSize(100);
		$form->addItem($ti);

		$item = new ilTextAreaInputGUI($this->lng->txt('mass_consultation_hours'), self::PROP_INFO_CONSULTATION);
		$item->setCols(40);
		$form->addItem($item);

		$form->addCommandButton('updateInfo', $this->lng->txt('save'));
		$form->addCommandButton('cancel', $this->lng->txt('cancel'));
		return $form;
	}

	protected function fillInfoForm(ilPropertyFormGUI $a_form, ilManualAssessmentInfoSettings $settings)
	{
		$a_form->setValuesByArray(array(
			  self::PROP_INFO_CONTACT => $settings->contact()
			, self::PROP_INFO_RESPONSIBILITY => $settings->responsibility()
			, self::PROP_INFO_PHONE => $settings->phone()
			, self::PROP_INFO_MAILS => $settings->mails()
			, self::PROP_INFO_CONSULTATION => $settings->consultationHours()
			));
		return $a_form;
	}

	protected function fillForm(ilPropertyFormGUI $a_form, ilObjManualAssessment $mass, ilManualAssessmentSettings $settings)
	{
		$a_form->setValuesByArray(array(
			  self::PROP_TITLE => $mass->getTitle()
			, self::PROP_DESCRIPTION => $mass->getDescription()
			, self::PROP_CONTENT => $settings->content()
			, self::PROP_RECORD_TEMPLATE => $settings->recordTemplate()
			, self::PROP_FILE_REQUIRED => $settings->fileRequired()
			, self::PROP_EVENT_TIME_PLACE_REQUIRED => $settings->eventTimePlaceRequired()
			, self::PROP_SUPERIOR_EXAMINATE => $settings->superiorExaminate()
			, self::PROP_SUPERIOR_VIEW => $settings->superiorView()
			, self::PROP_GRADE_SELF => $settings->gradeSelf()
			));
		return $a_form;
	}
}
