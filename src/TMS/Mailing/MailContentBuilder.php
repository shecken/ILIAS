<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * This builds content for mails in TMS, as e.g. used for
 * automatic notifications in courses.
 *
 */
interface MailContentBuilder {

	/**
	 * Get the template's id of this Mail.
	 *
	 * @return int
	 */
	public function getTemplateId();

	/**
	 * Get an instance of Mail with this template assigned.
	 *
	 * @param int 	$id
	 * @return Mail
	 */
	public function withTemplateId($id);

	/**
	 * Get an instance of Mail with these contexts.
	 * A context will provide values for placeholders.
	 *
	 * @param MailContext[]
	 * @return Mail
	 */
	public function withContexts($contexts);

	/**
	 * Get the subject of Mail.
	 *
	 * @return string
	 */
	public function getSubject();

	/**
	 * Get an instance of Mail with this subject.
	 *
	 * @param string 	$subject
	 * @return Mail
	 */
	public function withSubject($subject);


	/**
	 * Gets the message of Mail with filled placeholders,
	 * i.e.: apply all from placeholder values to template's message'.
	 *
	 * @return string
	 */
	public function getMessage();

	//TODO: atachments
}