<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/GEV/Desktop/classes/EffectivenessAnalysis/class.gevEffectivenessAnalysis.php");
require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
require_once("Customizing/global/plugins/Services/Cron/CronHook/EffectivenessAnalysisReminder/classes/ilEffectivenessAnalysisReminderDB.php");

/**
 * Actions for effectiveness analysis plugin
 * Communication class to ILIAS
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilEffAnalysisActions {
	const FIRST = "first";
	const SECOND = "second";
	const FIRST_KEY = "eff_analysis_first_reminder";
	const SECOND_KEY = "eff_analysis_second_reminder";

	public function __construct(ilEffectivenessAnalysisReminderDB $db, ilLog $log, gevEffectivenessAnalysis $eff_analysis) {
		$this->db = $db;
		$this->log = $log;
		$this->eff_analysis = $eff_analysis;
	}

	/**
	 * Get distinct id's of all superiors
	 */
	public function getAllSuperiors() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		return gevOrgUnitUtils::getAllSuperios(ilObjOrgUnit::getRootOrgRefId());
	}

	/**
	 * Get user_ids for first mail
	 *
	 * @param int 	$superior_id
	 *
	 * @return int[]
	 */
	public function getUserIdsForFirstMail($superior_id) {
		return $this->eff_analysis->getUserIdsForFirstMail($superior_id);
	}

	/**
	 * Get user_ids for reminder
	 *
	 * @param int 	$superior_id
	 *
	 * @return int[]
	 */
	public function getUserIdsForReminder($superior_id) {
		return $this->eff_analysis->getUserIdsForReminder($superior_id);
	}

	/**
	 * Send first reminder
	 *
	 * @param int 		$crs_id
	 * @param int[]		$superiors
	 * @param int[]		$user_ids
	 */
	public function sendFirst($crs_id, array $superiors, array $user_ids) {
		$this->send($crs_id, $superiors, self::FIRST_KEY);
		$this->db->logMailSend($crs_id, $superiors[0], self::FIRST, $user_ids);
	}

	/**
	 * Send second reminder
	 *
	 * @param int 		$crs_id
	 * @param int[]		$superiors
	 * @param int[]		$user_ids
	 */
	public function sendReminder($crs_id, array $superiors, array $user_ids) {
		$this->send($crs_id, $superiors, self::SECOND_KEY);
		$this->db->logMailSend($crs_id, $superiors[0], self::SECOND, $user_ids);
	}

	/**
	 * Send the reminder mail
	 *
	 * @param int 		$crs_id
	 * @param int[]		$superiors
	 * @param string 	$type
	 */
	protected function send($crs_id, array $superiors, $key) {
		$auto_mails = new gevCrsAutoMails($crs_id);
		$mail = $auto_mails->getAutoMail($key);
		$mail->send($superiors);
	}
}