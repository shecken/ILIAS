<?php


require_once 'Services/Tracking/classes/class.ilLPStatus.php';
require_once 'Modules/ManualAssessment/classes/LearningProgress/class.ilManualAssessmentLPInterface.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembers.php';

class ilLPStatusManualAssessment extends ilLPStatus {

	public function _getNotAttempted($a_obj_id) {
		return ilManualAssessmentLPInterface::getMembersHavingStatusIn($a_obj_id, 
			ilManualAssessmentMembers::LP_NOT_ATTEMPTED);
	}

	public function _getCountNotAttempted($a_obj_id) {
		return count(self::_getNotAttempted($a_obj_id));
	}

	public function _getCountInProgress($a_obj_id) {
		return count(self::_getInProgress($a_obj_id));
	}

	public function _getInProgress($a_obj_id) {
		return ilManualAssessmentLPInterface::getMembersHavingStatusIn($a_obj_id, 
			ilManualAssessmentMembers::LP_IN_PROGRESS);
	}

	public function _getCountCompleted($a_obj_id) {
		return count(self::_getCompleted($a_obj_id));
	}

	public function _getCompleted($a_obj_id) {
		return ilManualAssessmentLPInterface::getMembersHavingStatusIn($a_obj_id, 
			ilManualAssessmentMembers::LP_COMPLETED);
	}

	public function _getCountFailed() {
		return count(self::_getFailed($a_obj_id));
	}

	public function _getFailed($a_obj_id) {
		return ilManualAssessmentLPInterface::getMembersHavingStatusIn($a_obj_id, 
			ilManualAssessmentMembers::LP_FAILED);
	}

	function determineStatus($a_obj_id, $a_user_id, $a_obj = null) {
		switch ((string)ilManualAssessmentLPInterface::determineStatusOfMember($a_obj_id,$a_user_id)) {
			case (string)ilManualAssessmentMembers::LP_NOT_ATTEMPTED:
				return self::LP_STATUS_NOT_ATTEMPTED_NUM;
			case (string)ilManualAssessmentMembers::LP_IN_PROGRESS:
				return self::LP_STATUS_IN_PROGRESS_NUM;
			case (string)ilManualAssessmentMembers::LP_FAILED:
				return self::LP_STATUS_FAILED_NUM;
			case (string)ilManualAssessmentMembers::LP_COMPLETED:
				return self::LP_STATUS_COMPLETED_NUM;
			default:
				return self::LP_STATUS_NOT_ATTEMPTED_NUM;
		}
	}
}