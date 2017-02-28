<?php
require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
require_once 'Services/Form/classes/class.ilNonEditableValueGUI.php';
require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
require_once 'Modules/ManualAssessment/classes/LearningProgress/class.ilManualAssessmentLPInterface.php';
require_once 'Modules/ManualAssessment/classes/Notification/class.ilManualAssessmentPrimitiveInternalNotificator.php';
require_once 'Modules/ManualAssessment/classes/class.ilManualAssessmentLP.php';
require_once "Services/Calendar/classes/class.ilDateTime.php";
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
require_once 'Modules/ManualAssessment/classes/FileStorage/class.ilManualAssessmentFileStorage.php';
/**
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the ManualAssessment is used.
 * It caries a LPStatus, which is set manually.
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */
class ilManualAssessmentMemberGUI
{
	protected $notificator;

	public function __construct($members_gui, $a_parent_gui, $a_ref_id)
	{
		$this->notificator = new ilManualAssessmentPrimitiveInternalNotificator();
		global $ilCtrl, $tpl, $lng, $ilUser, $ilTabs;
		$this->ctrl = $ilCtrl;
		$this->members_gui = $members_gui;
		$this->parent_gui = $a_parent_gui;
		$this->object = $a_parent_gui->object;
		$this->ref_id = $a_ref_id;
		$this->tpl =  $tpl;
		$this->lng = $lng;
		$this->ctrl->saveParameter($this, 'usr_id');
		$this->examinee = new ilObjUser($_GET['usr_id']);
		$this->examiner = $ilUser;
		$this->setTabs($ilTabs);
		$this->member = $this->object->membersStorage()
						->loadMember($this->object, $this->examinee);

		$this->settings = $this->object->getSettings();
		$this->file_storage = $this->object->getFileStorage();
		$this->superior_examinate = $a_parent_gui->object->getSettings()->superiorExaminate();
		$this->superior_view = $a_parent_gui->object->getSettings()->superiorView();
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case 'edit':
			case 'save':
			case 'finalize':
			case 'finalizeConfirmation':
			case 'amend':
			case 'saveAmend':
			case 'cancelFinalize':
			case 'view':
			case 'cancel':
			case 'downloadAttachment':
				break;
			default:
				$this->parent_gui->handleAccessViolation();
				return;
		}
		$this->$cmd();
	}

	protected function view()
	{
		if (!$this->mayBeViewed()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}

		$form = $this->fillForm($this->initGradingForm(false), $this->member);
		$form = $this->fillForm($this->initGradingForm(false), $this->member);
		$form->addCommandButton('cancel', $this->lng->txt('mass_return'));
		$form = $this->possiblyAddDownloadAttachmentButtonTo($form);
		$this->renderForm($form);
	}

	protected function edit($form = null)
	{
		if (!$this->mayBeEdited()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}
		if ($form === null) {
			$form = $this->fillForm($this->initGradingForm(), $this->member);
		}
		$form->addCommandButton('save', $this->lng->txt('save'));
		$form->addCommandButton('finalizeConfirmation', $this->lng->txt('mass_finalize'));
		$form = $this->possiblyAddDownloadAttachmentButtonTo($form);
		$form->addCommandButton('cancel', $this->lng->txt('mass_return'));
		$this->renderForm($form);
	}

	protected function downloadAttachment()
	{
		if (!$this->mayBeEdited() && !$this->mayBeViewed() && !$this->mayBeAmended()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}
		$file_storage = $this->object->getFileStorage();
		$file_storage->setUserId($this->member->id());
		ilUtil::deliverFile($file_storage->getFilePath(), $this->member->fileName());
	}

	protected function save()
	{
		if (!$this->mayBeEdited()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}

		$form = $this->initGradingForm();
		$form->setValuesByArray(array('file' => $this->member->fileName()));
		if (!$form->checkInput()) {
			$form->setValuesByPost();
			$this->edit($form);
			return;
		}
		$this->saveMember($_POST);
		if ($this->object->isActiveLP()) {
			ilManualAssessmentLPInterface::updateLPStatusOfMember($this->member);
		}
		ilUtil::sendSuccess($this->lng->txt('mass_membership_saved'), true);
		$this->redirect('edit');
	}

	protected function finalizeConfirmation()
	{
		if (!$this->mayBeEdited()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}

		$form = $this->initGradingForm();
		$form->setValuesByArray(array('file' => $this->member->fileName()));

		if (!$form->checkInput()) {
			$form->setValuesByPost();
			$this->edit($form);
			return;
		}

		$this->saveMember($_POST);

		if (!$this->member->mayBeFinalized()) {
			ilUtil::sendFailure($this->lng->txt('mass_may_not_finalize'), true);
			$form->setValuesByPost();
			$this->edit($form);
			return;
		}


		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->addHiddenItem('usr_id', $_GET['usr_id']);
		$confirm->setHeaderText($this->lng->txt('mass_finalize_user_qst'));
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setConfirm($this->lng->txt('mass_finalize'), 'finalize');
		$confirm->setCancel($this->lng->txt('cancel'), 'cancelFinalize');

		$this->tpl->setContent($confirm->getHTML());
	}

	protected function finalize()
	{
		if (!$this->mayBeEdited()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}

		if (!$this->member->mayBeFinalized()) {
			ilUtil::sendFailure($this->lng->txt('mass_may_not_finalize'), true);
			$this->redirect('edit');
			return;
		}

		$this->member = $this->member->withFinalized();
		$this->object->membersStorage()->updateMember($this->member);
		if ($this->object->isActiveLP()) {
			ilManualAssessmentLPInterface::updateLPStatusOfMember($this->member);
		}

		ilUtil::sendSuccess($this->lng->txt('mass_membership_finalized'), true);
		$this->redirect('view');
	}

	protected function cancelFinalize()
	{
		$this->edit();
	}

	protected function amend($form = null)
	{
		if (!$this->mayBeAmended()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}

		if ($form === null) {
			$form = $this->fillForm($this->initGradingForm(), $this->member);
		}

		$form->addCommandButton('saveAmend', $this->lng->txt('save'));
		$form->addCommandButton('cancel', $this->lng->txt('mass_return'));
		$form = $this->possiblyAddDownloadAttachmentButtonTo($form);
		$this->renderForm($form);
	}

	protected function saveAmend()
	{
		if (!$this->mayBeAmended()) {
			$this->parent_gui->handleAccessViolation();
			return;
		}
		$form = $this->initGradingForm();
		$form->setValuesByArray(array('file' => $this->member->fileName()));
		if (!$form->checkInput()) {
			$form->setValuesByPost();
			$this->amend($form);
			return;
		}

		$this->saveMember($_POST, true);

		if ($this->object->isActiveLP()) {
			ilManualAssessmentLPInterface::updateLPStatusOfMember($this->member);
		}

		ilUtil::sendSuccess($this->lng->txt('mass_amend_saved'), true);
		$this->redirect("amend");
	}

	protected function cancel()
	{
		$this->ctrl->redirect($this->members_gui);
	}

	protected function redirect($cmd)
	{
		$this->ctrl->redirect($this, $cmd);
	}

	protected function renderForm(ilPropertyFormGUI $a_form)
	{
		$this->tpl->setContent($a_form->getHTML());
	}

	protected function updateDataInMemberByArray(ilManualAssessmentMember $member, $data, $new_file, $keep_examiner = false)
	{
		$member = $member->withRecord($data['record'])
					->withInternalNote($data['internal_note'])
					->withPlace($data['place'])
					->withEventTime($this->createDatetime($data['event_time']))
					->withLPStatus($data['learning_progress'])
					->withViewFile((bool)$data['user_view_file']);

		if (!$keep_examiner) {
			$member = $member->withExaminerId($this->examiner->getId());
		}

		if ($data['notify']  == 1) {
			$member = $member->withNotify(true);
		} else {
			$member = $member->withNotify(false);
		}

		if ($new_file) {
			$member = $member->withFileName($data['file']['name']);
		}
		return $member;
	}

	protected function setTabs(ilTabsGUI $tabs)
	{
		$tabs->clearTargets();
		$tabs->setBackTarget(
			$this->lng->txt('back'),
			$this->getBackLink()
		);
	}

	protected function getBackLink()
	{
		return $this->ctrl->getLinkTargetByClass(
			array(get_class($this->parent_gui)
					,get_class($this->members_gui)),
			'view'
		);
	}

	protected function initGradingForm($may_be_edited = true)
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('mass_edit_record'));
		$examinee_name = $this->examinee->getLastname().', '.$this->examinee->getFirstname();

		$usr_name = new ilNonEditableValueGUI($this->lng->txt('name'), 'name');
		$form->addItem($usr_name);

		$learning_progress = new ilSelectInputGUI($this->lng->txt('grading'), 'learning_progress');
		$learning_progress->setOptions(
			array(ilManualAssessmentMembers::LP_IN_PROGRESS => $this->lng->txt('mass_status_pending')
				, ilManualAssessmentMembers::LP_COMPLETED => $this->lng->txt('mass_status_completed')
			,
			ilManualAssessmentMembers::LP_FAILED => $this->lng->txt('mass_status_failed'))
		);
		$learning_progress->setDisabled(!$may_be_edited);
		$form->addItem($learning_progress);

		// record
		$ti = new ilTextAreaInputGUI($this->lng->txt('mass_record'), 'record');
		$ti->setInfo($this->lng->txt('mass_record_info'));
		$ti->setCols(40);
		$ti->setRows(5);
		$ti->setDisabled(!$may_be_edited);
		$ti->setRequired(true);
		$form->addItem($ti);
		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt('mass_internal_note'), 'internal_note');
		$ta->setInfo($this->lng->txt('mass_internal_note_info'));
		$ta->setCols(40);
		$ta->setRows(5);
		$ta->setDisabled(!$may_be_edited);
		$form->addItem($ta);

		$txt = new ilTextInputGUI($this->lng->txt('mass_place'), 'place');
		$txt->setRequired($this->settings->eventTimePlaceRequired());
		$txt->setDisabled(!$may_be_edited);
		$form->addItem($txt);

		$date = new ilDateTimeInputGUI($this->lng->txt('mass_event_time'), 'event_time');
		$date->setShowTime(true);
		$date->setRequired($this->settings->eventTimePlaceRequired());
		$date->setDisabled(!$may_be_edited);
		$form->addItem($date);


		require_once("Services/Form/classes/class.ilFileInputGUI.php");
		$file = new ilFileInputGUI($this->lng->txt('mass_upload_file'), 'file');
		$file->setRequired($this->object->getSettings()->fileRequired());
		$file->setDisabled(!$may_be_edited);
		$file->setAllowDeletion(true);
		$form->addItem($file);

		if (!$this->object->getSettings()->viewSelf()) {
			$cb = new ilCheckboxInputGUI($this->lng->txt('mass_user_view_file'), 'user_view_file');
			$cb->setInfo($this->lng->txt('mass_user_view_file_info'));
			$cb->setDisabled(!$may_be_edited);
			$form->addItem($cb);
			// notify examinee
			$notify = new ilCheckboxInputGUI($this->lng->txt('mass_notify'), 'notify');
			$notify->setInfo($this->lng->txt('mass_notify_explanation'));
			$notify->setDisabled(!$may_be_edited);
			$form->addItem($notify);
		}

		return $form;
	}

	protected function fillForm(ilPropertyFormGUI $a_form, ilManualAssessmentMember $member)
	{
		$dt = $member->eventTime()->get(IL_CAL_DATETIME);
		$dt = explode(" ", $dt);
		$event_time = ["date" => $dt[0], "time" => $dt[1]];

		$a_form->setValuesByArray(array(
			  'name' => $member->name()
			, 'record' => $member->record()
			, 'internal_note' => $member->internalNote()
			, 'place' => $member->place()
			, 'event_time' => $event_time
			, 'notify' => $member->notify()
			, 'learning_progress' => (int)$member->LPStatus()
			, 'file' => $member->fileName()
			, 'user_view_file' => $member->viewFile()
			));
		return $a_form;
	}

	protected function mayBeEdited()
	{
		if (!$this->isFinalized()
				&& ($this->userCanGrade() || $this->superiorCanGrade() || $this->mayGradeSelf())
		) {
			return true;
		}

		return false;
	}

	protected function mayBeViewed()
	{
		if (($this->isFinalized() &&
			($this->userCanGrade() || $this->superiorCanGrade() || $this->mayGradeSelf()))
			|| $this->userCanView()
			|| $this->superiorCanView()
			|| $this->mayViewSelf()
		) {
			return true;
		}

		return false;
	}

	protected function mayGradeSelf()
	{
		return $this->object->getSettings()->gradeSelf()
			&& $this->examinee->getId() === $this->examiner->getId()
			&& !$this->targetWasEditedByOtherUser($this->member);
	}

	protected function mayViewSelf()
	{
		return $this->object->getSettings()->viewSelf()
			&& $this->examinee->getId() === $this->examiner->getId();
	}

	protected function mayBeAmended()
	{
		if ($this->isFinalized()
				&& $this->userCanAmend()) {
			return true;
		}

		return false;
	}

	protected function userCanGrade()
	{
		return !$this->targetWasEditedByOtherUser($this->member) && $this->object->accessHandler()->checkAccessToObj($this->object, 'edit_learning_progress');
	}

	protected function superiorCanGrade()
	{
		return !$this->targetWasEditedByOtherUser($this->member) && $this->isSuperior($this->examiner->getId()) && $this->superior_examinate;
	}

	protected function userCanView()
	{
		return $this->object->accessHandler()->checkAccessToObj($this->object, 'read_learning_progress');
	}

	protected function superiorCanView()
	{
		return $this->isSuperior($this->examiner->getId()) && $this->superior_view;
	}

	protected function userCanAmend()
	{
		return $this->object->accessHandler()->checkAccessToObj($this->object, 'amend_grading');
	}

	protected function isFinalized()
	{
		return $this->member->finalized();
	}

	protected function isSuperior($examiner_id)
	{
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$examiner_id_utils = gevUserUtils::getInstance((int)$examiner_id);
		return $examiner_id_utils->isSuperiorOf($this->examinee->getId());
	}

	protected function saveMember($post, $keep_examiner = false)
	{
		$new_file = $this->uploadFile($post["file"], $post["file_delete"]);
		$this->member = $this->updateDataInMemberByArray($this->member, $post, $new_file, $keep_examiner);
		$this->object->membersStorage()->updateMember($this->member);
	}

	protected function uploadFile($file, $file_delete)
	{
		$new_file = false;
		$this->file_storage->setUserId($this->member->id());
		$this->file_storage->create();
		if (!$file["name"] == "" || $file_delete) {
			$this->file_storage->deleteCurrentFile();
			$this->file_storage->uploadFile($file);
			$new_file = true;
		}
		if (!$file["name"] == "") {
			$this->file_storage->uploadFile($file);
			$new_file = true;
		}
		return $new_file;
	}

	protected function targetWasEditedByOtherUser(ilManualAssessmentMember $member)
	{
		return (int)$member->examinerId() !== (int)$this->examiner->getId()
				&& 0 !== (int)$member->examinerId();
	}

	private function createDatetime(array $datetime)
	{
		return new ilDateTime($datetime["date"]." ".$datetime["time"], IL_CAL_DATETIME);
	}

	protected function possiblyAddDownloadAttachmentButtonTo($form)
	{
		if ($this->member->fileName() && $this->member->fileName() != "") {
			$form->addCommandButton('downloadAttachment', $this->lng->txt('mass_download_attached_file'));
		}
		return $form;
	}
}
