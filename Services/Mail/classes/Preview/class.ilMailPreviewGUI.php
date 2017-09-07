<?php
/**
 * Copyright (c) 2017 ILIAS open source, Extended GPL, see docs/LICENSE
 * CaT Concepts and Training GmbH
 */

/**
 * Preview form for mail templates
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilMailPreviewGUI {
	const PREVIEW_CLASS_PREVIX = "Preview";
	/**
	 * @var ilMailTemplate
	 */
	protected $template_id;

	public function __construct(ilMailTemplate $template) {
		global $DIC;
		$this->g_user = $DIC->user();
		$this->g_lng = $DIC->language();
		$this->template = $template;
	}

	/**
	 * Render mail preview
	 *
	 * @return string
	 */
	public function getHTML() {
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setPreventDoubleSubmission(false);
		$form->setTableWidth('100%');
		$form->setTitle("Vorschau für: ".$this->template->getTitle());

		require_once("Services/Mail/classes/class.ilMail.php");
		$from = new ilCustomInputGUI($this->g_lng->txt('from'));
		$from->setHtml(ilUtil::img(ilUtil::getImagePath('HeaderIconAvatar.svg'), ilMail::_getIliasMailerName()) . '<br />' . ilMail::_getIliasMailerName());
		$form->addItem($from);

		$to = new ilCustomInputGUI($this->g_lng->txt('mail_to'));
		$to->setHtml(ilUtil::htmlencodePlainString($this->g_lng->txt('user'), false));
		$form->addItem($to);

		$subject = new ilCustomInputGUI($this->g_lng->txt('subject'));
		$subject->setHtml(ilUtil::htmlencodePlainString($this->template->getSubject(), true));
		$form->addItem($subject);

		$message = new ilCustomInputGUI($this->g_lng->txt('message'));
		$message->setHtml($this->populatePlaceholder($this->template->getMessage()));
		$form->addItem($message);

		return $form->getHtml();
	}

	/**
	 * Replace placeholders with default values
	 *
	 * @param string 	$message
	 *
	 * @return string
	 */
	protected function populatePlaceholder($message) {
		$context_class_preview = $this->getPreviewClassNameFromContext();
		require_once "Services/Mail/classes/Preview/class.".$context_class_preview.".php";
		$context_preview = new $context_class_preview();

		$processor = new ilMailTemplatePlaceholderResolver($context_preview, $message);
		$message = $processor->resolve($this->g_user, array(), false);

		return $message;
	}

	/**
	 * Get name of preview context by template context
	 *
	 * @return string
	 */
	protected function getPreviewClassNameFromContext() {
		require_once 'Services/Mail/classes/class.ilMailTemplatePlaceholderResolver.php';
		$context = ilMailTemplateService::getTemplateContextById($this->template->getContext());
		$context_class = get_class($context);
		return $context_class.self::PREVIEW_CLASS_PREVIX;
	}
}