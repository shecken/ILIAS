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
		$this->template = $template;
	}

	/**
	 * Render mail preview
	 *
	 * @return string
	 */
	public function getHTML() {
		$tpl = new ilTemplate("tpl.mail_preview.html", true, true, "Services/Mail");
		$tpl->setVariable("PREVIEW_TITLE", "Vorschau für: ".$this->template->getTitle());
		$tpl->setVariable("SUBJECT_LABEL", "Betreff");
		$tpl->setVariable("SUBJECT", $this->template->getSubject());
		$tpl->setVariable("MESSAGE_LABLE", "Nachricht");
		$tpl->setVariable("MESSAGE", $this->placeholder($this->template->getMessage()));

		return $tpl->get();
	}

	protected function placeholder($message) {
		$context_class_preview = $this->getPreviewClassNameFromContext();
		require_once "Services/Mail/classes/Preview/class.".$context_class_preview.".php";
		$context_preview = new $context_class_preview();

		$processor = new ilMailTemplatePlaceholderResolver($context_preview, $message);
		$message = $processor->resolve($this->g_user, array(), false);

		return $message;
	}

	protected function getPreviewClassNameFromContext() {
		require_once 'Services/Mail/classes/class.ilMailTemplatePlaceholderResolver.php';
		$context = ilMailTemplateService::getTemplateContextById($this->template->getContext());
		$context_class = get_class($context);
		return $context_class.self::PREVIEW_CLASS_PREVIX;
	}
}