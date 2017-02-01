<?php

require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
require_once 'Services/Form/classes/class.ilNonEditableValueGUI.php';
require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
require_once 'Modules/ManualAssessment/classes/LearningProgress/class.ilManualAssessmentLPInterface.php';
require_once 'Modules/ManualAssessment/classes/Notification/class.ilManualAssessmentPrimitiveInternalNotificator.php';
require_once 'Modules/ManualAssessment/classes/class.ilManualAssessmentLP.php';

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
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case 'view':
			case 'edit':
			case 'save':
			case 'finalize':
			case 'finalizeConfirmation':
			case 'amend':
			case 'saveAmend':
			case 'cancel':
				break;
			default:
				$this->parent_gui->handleAccessViolation();
		}
		$this->$cmd();
	}

	protected function view()
	{
		if (!$this->mayBeViewed()) {
			$this->parent_gui->handleAccessViolation();
		}

		$form = $this->fillForm($this->initGradingForm(false), $this->member);
		$form->addCommandButton('cancel', $this->lng->txt('mass_return'));
		$this->renderForm($form);
	}

	protected function edit($form = null)
	{
		if (!$this->mayBeEdited()) {
			$this->parent_gui->handleAccessViolation();
		}

		if ($form === null) {
			$form = $this->fillForm($this->initGradingForm(), $this->member);
		}

		$form->addCommandButton('save', $this->lng->txt('save'));
		$form->addCommandButton('finalizeConfirmation', $this->lng->txt('mass_finalize'));
		$form->addCommandButton('cancel', $this->lng->txt('mass_return'));

		$this->renderForm($form);
	}

	protected function save()
	{
		if (!$this->mayBeEdited()) {
			$this->parent_gui->handleAccessViolation();
		}

		$form = $this->initGradingForm();
		if (!$form->checkInput()) {
			$form->setValuesByPost();
			$this->edit($form);
			return;
		}

		$this->member = $this->updateDataInMemberByArray($this->member, $_POST);
		$this->object->membersStorage()->updateMember($this->member);

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
		}

		$form = $this->initGradingForm();
		if (!$form->checkInput()) {
			$form->setValuesByPost();
			$this->edit($form);
			return;
		}

		$this->member = $this->updateDataInMemberByArray($this->member, $_POST);
		$this->object->membersStorage()->updateMember($this->member);
		if ($this->member->mayBeFinalized()) {
			include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
			$confirm = new ilConfirmationGUI();
			$confirm->addHiddenItem('record', $_POST['record']);
			$confirm->addHiddenItem('internal_note', $_POST['internal_note']);
			$confirm->addHiddenItem('notify', $_POST['notify']);
			$confirm->addHiddenItem('learning_progress', $_POST['learning_progress']);
			$confirm->setHeaderText($this->lng->txt('mass_finalize_user_qst'));
			$confirm->setFormAction($this->ctrl->getFormAction($this));
			$confirm->setConfirm($this->lng->txt('mass_finalize'), 'finalize');
			$confirm->setCancel($this->lng->txt('cancel'), 'save');
			$this->tpl->setContent($confirm->getHTML());
		} else {
			ilUtil::sendFailure($this->lng->txt('mass_may_not_finalize'));
			$this->edit($form);
		}
	}

	protected function finalize()
	{
		if (!$this->mayBeEdited()) {
			$this->parent_gui->handleAccessViolation();
		}

		if (!$this->member->mayBeFinalized()) {
			ilUtil::sendFailure($this->lng->txt('mass_may_not_finalize'), true);
			$this->redirect('edit');
			return;
		}

		$this->member = $this->member->withFinalized()->maybeSendNotification($this->notificator);
		$this->object->membersStorage()->updateMember($this->member);
		if ($this->object->isActiveLP()) {
			ilManualAssessmentLPInterface::updateLPStatusOfMember($this->member);
		}

		ilUtil::sendSuccess($this->lng->txt('mass_membership_finalized'), true);
		$this->redirect('view');
	}

	protected function amend($form = null)
	{
		if (!$this->mayBeAmended()) {
			$this->parent_gui->handleAccessViolation();
		}

		if ($form === null) {
			$form = $this->fillForm($this->initGradingForm(), $this->member);
		}

		$form->addCommandButton('saveAmend', $this->lng->txt('save'));
		$form->addCommandButton('cancel', $this->lng->txt('mass_return'));
		$this->renderForm($form);
	}

	protected function saveAmend()
	{
		if (!$this->mayBeAmended()) {
			$this->parent_gui->handleAccessViolation();
		}

		$form = $this->initGradingForm();
		if (!$form->checkInput()) {
			$form->setValuesByPost();
			$this->amend($form);
			return;
		}

		$this->member = $this->updateDataInMemberByArray($this->member, $_POST);
		$this->object->membersStorage()->updateMember($this->member);

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

	protected function updateDataInMemberByArray(ilManualAssessmentMember $member, $data)
	{
		$member = $member->withRecord($data['record'])
					->withInternalNote($data['internal_note'])
					->withLPStatus($data['learning_progress'])
					->withExaminerId($this->examiner->getId())
					->withNotify(($data['notify']  == 1 ? true : false));
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

		$learning_progress = new ilSelectInputGUI($this->lng->txt('grading'), 'learning_progress');
		$learning_progress->setOptions(
			array(ilManualAssessmentMembers::LP_IN_PROGRESS => $this->lng->txt('mass_status_pending')
				, ilManualAssessmentMembers::LP_COMPLETED => $this->lng->txt('mass_status_completed')
			,
			ilManualAssessmentMembers::LP_FAILED => $this->lng->txt('mass_status_failed'))
		);
		$learning_progress->setDisabled(!$may_be_edited);
		$form->addItem($learning_progress);

		// notify examinee
		$notify = new ilCheckboxInputGUI($this->lng->txt('mass_notify'), 'notify');
		$notify->setInfo($this->lng->txt('mass_notify_explanation'));
		$notify->setDisabled(!$may_be_edited);
		$form->addItem($notify);

		return $form;
	}

	protected function fillForm(ilPropertyFormGUI $a_form, ilManualAssessmentMember $member)
	{
		$a_form->setValuesByArray(array(
			  'name' => $member->name()
			, 'record' => $member->record()
			, 'internal_note' => $member->internalNote()
			, 'notify' => $member->notify()
			, 'learning_progress' => (int)$member->LPStatus()
			));
		return $a_form;
	}

	protected function mayBeEdited()
	{
		if (!$this->isFinalized()
			&& !$this->targetWasEditedByOtherUser($this->member)
			&& $this->object->accessHandler()->checkAccessToObj($this->object, 'edit_learning_progress')) {
			return true;
		}

		return false;
	}

	protected function mayBeViewed()
	{
		if ($this->isFinalized()
			&& (
					(!$this->targetWasEditedByOtherUser($this->member)
						&& $this->object->accessHandler()->checkAccessToObj($this->object, 'edit_learning_progress')
					)
					|| $this->object->accessHandler()->checkAccessToObj($this->object, 'read_learning_progress')
				)
			) {
			return true;
		}

		return false;
	}

	protected function mayBeAmended()
	{
		if ($this->isFinalized()
			&& $this->object->accessHandler()->checkAccessToObj($this->object, 'amend_grading')) {
			return true;
		}

		return false;
	}

	protected function isFinalized()
	{
		return $this->member->finalized();
	}

	protected function targetWasEditedByOtherUser(ilManualAssessmentMember $member)
	{
		return (int)$member->examinerId() !== (int)$this->examiner->getId()
				&& 0 !== (int)$member->examinerId();
	}
}
