<?php
class RefreshCertificateHelper
{
	public function getSuccessfulParticipantsFor($crs_id) {
		require_once "Services/GEV/Utils/classes/class.gevCourseUtils.php";
		$crs_utils = gevCourseUtils::getInstance($crs_id);
		return $crs_utils->getSuccessfullParticipants();
	}

	public function updateCertificates($crs, $user_ids) {
		require_once "Modules/Course/classes/class.ilCourseCertificateAdapter.php";
		require_once "Services/Certificate/classes/class.ilCertificate.php";

		$certificate_adapter = new ilCourseCertificateAdapter($crs);
		$certificate = new ilCertificate($certificate_adapter);

		if($certificate->isComplete()) {
			$crs_id = $crs->getId();
			foreach($user_ids as $usr_id) {
				$this->refreshCertificate($crs_id, $usr_id, $certificate);
			}
		}
	}

	private function refreshCertificate($crs_id, $usr_id, $certificate) {
		$cert = $this->createCertificate($crs_id, $usr_id, $certificate);
		$this->rehistorizeCertificate($crs_id, $usr_id, $cert);
	}

	private function createCertificate($crs_id, $usr_id, $certificate) {
		return  $certificate->outCertificate(array("user_id" => $usr_id), false);
	}

	private function rehistorizeCertificate($crs_id, $usr_id, $cert) {
		$case_id = array("crs_id" => $crs_id, "usr_id" => $usr_id);
		$data = array("certificate" => $cert);
		require_once "Services/UserCourseStatusHistorizing/classes/class.ilUserCourseStatusHistorizing.php";
		ilUserCourseStatusHistorizing::updateHistorizedData($case_id, $data);
	}
}