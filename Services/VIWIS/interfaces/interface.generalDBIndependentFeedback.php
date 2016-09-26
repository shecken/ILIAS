<?php
/**
 * We may not rely on ILIAS-Feedbacks, since they seem instanctly
 * to store any information in Database. We will create our own simple
 * feedback-container.
 */

interface generalIndependentFeedback {
	/**
	 * For now we will only need one generic feedback,
	 * that should be shown independent on wether the question
	 * was answered correctly.
	 *
	 * @param	string	$feedback
	 */
	public function setGenericFeedback($feedback);

	/**
	 * Get generic feedback dependent on wether all answer-options
	 * were set correctly. In our case the return will be allways equal.
	 *
	 * @param	string|int	$qst_obj_id	May be anything in our usecase.
	 *									Should have no effect.
	 * @param	bool	$all_correct
	 * @return	string
	 */
	public function getGenericFeedbackExportPresentation( $qst_obj_id, $all_corect);

	/**
	 * For now we will not use answer dependend feedback. However,
	 * this function must be implemented.
	 *
	 * @param	string|int	$qst_obj_id	May be anything in our usecase.
	 *									Should have no effect.
	 * @param	bool	$all_correct
	 * @return	null
	 */
	public function getSpecificAnswerFeedbackExportPresentation( $qst_obj_id, $qans_ndex);
}