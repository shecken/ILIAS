<?php
require_once 'Services/VIWIS/interfaces/interface.GeneralDBIndependentFeedback.php';
class GenericFeedback implements GeneralDBIndependentFeedback {
	protected $generic_feedback = null;
	/**
	 * @inheritdoc
	 */
	public function setGenericFeedback($feedback) {
		$this->generic_feedback = $feedback;
	}

	/**
	 * @inheritdoc
	 */
	public function getGenericFeedbackExportPresentation( $qst_obj_id, $all_corect) {
		return $this->generic_feedback;
	}

	/**
	 * @inheritdoc
	 */
	public function getSpecificAnswerFeedbackExportPresentation( $qst_obj_id, $qans_ndex) {
		return null;
	}
}