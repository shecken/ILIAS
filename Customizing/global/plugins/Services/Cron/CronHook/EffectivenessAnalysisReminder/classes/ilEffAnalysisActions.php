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
	 * Get open effective analysis
	 *
	 * @param int 	$user_id
	 *
	 * @return mixed[]
	 */
	public function getOpenEffectivenessAnalysis($user_id) {
		return $this->eff_analysis->getOpenEffectivenessAnalysis($user_id);
	}

	/**
	 * Should send first reminder
	 *
	 * @param int 		$crs_id
	 *
	 * @return bool
	 */
	public function shouldSendFirstReminder($crs_id) {
		return $this->db->shouldSendFirstReminder($crs_id, self::FIRST);
	}

	/**
	 * Should send secon reminder
	 *
	 * @param int 		$crs_id
	 *
	 * @return bool
	 */
	public function shouldSendSecondReminder($crs_id) {
		return $this->db->shouldSendSecondReminder($crs_id, self::FIRST, self::SECOND);
	}

	/**
	 * Send first reminder
	 *
	 * @param int 		$crs_id
	 * @param int[]		$superiors
	 */
	public function sendFirst($crs_id, array $superiors) {
		$this->send($crs_id, $superiors, self::FIRST_KEY);
		$this->db->reminderSend($crs_id, self::FIRST);
	}

	/**
	 * Send second reminder
	 *
	 * @param int 		$crs_id
	 * @param int[]		$superiors
	 */
	public function sendSecond($crs_id, array $superiors) {
		$this->send($crs_id, $superiors, self::SECOND_KEY);
		$this->db->reminderSend($crs_id, self::SECOND);
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