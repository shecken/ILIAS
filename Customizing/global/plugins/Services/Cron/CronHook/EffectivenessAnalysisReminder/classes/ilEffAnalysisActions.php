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
	 * Get user ids first mail should be send
	 *
	 * @param int 		$crs_id
	 * @param int 		$superior_id
	 * @param int[] 	$user_ids
	 *
	 * @return int[]
	 */
	public function getUserIdsForFirstMail($crs_id, $superior_id, $user_ids) {
		$all_sent_user_ids = $this->db->getUserIdsTypeIsSend($crs_id, $superior_id, self::FIRST);

		if(count($all_sent_user_ids) == 0) {
			return $user_ids;
		}

		$to_send = array();
		foreach($all_sent_user_ids as $date => $sent_user_ids) {
			$to_send = array_merge($to_send, array_diff($user_ids, $sent_user_ids, $to_send));
		}

		return array_unique($to_send);
	}

	/**
	 * Get user ids reminder should be send
	 *
	 * @param int 		$crs_id
	 * @param int 		$superior_id
	 *
	 * @return bool
	 */
	public function getUserIdsForReminder($crs_id, $superior_id, $user_ids) {
		$first_sent_user_ids = $this->db->getUserIdsTypeIsSend($crs_id, $superior_id, self::FIRST);
		$reminder_sent_user_ids = $this->db->getUserIdsTypeIsSend($crs_id, $superior_id, self::SECOND);

		if(count($reminder_sent_user_ids) > 0) {
			$first_sent_user_ids = $this->cleanFirstUserIds($first_sent_user_ids, $reminder_sent_user_ids);
		}

		$to_send = array();

		$next_send = $this->getNextSendDate(2);
		foreach($reminder_sent_user_ids as $date => $sent_user_ids) {
			if($date <= $next_send) {
				$to_send = array_merge($to_send, array_diff($user_ids, $to_send));
			}
		}

		$next_send = $this->getNextSendDate(15);
		foreach($first_sent_user_ids as $date => $sent_user_ids) {
			if($date <= $next_send) {
				$to_send = array_merge($to_send, array_diff($sent_user_ids, $to_send));
			}
		}

		return array_unique($to_send);
	}

	protected function getNextSendDate($days) {
		$time = strtotime(date("Y-m-d"));
		$time = $time - (2 * 24 * 60 * 60);
		return date("Y-m-d", $time);
	}

	protected function cleanFirstUserIds($first_sent_user_ids, $reminder_sent_user_ids) {
		foreach($first_sent_user_ids as $date_first => $user_ids_first) {
			$new = array();
			foreach($reminder_sent_user_ids as $user_ids_reminder) {
				$new = array_merge($new, array_diff($user_ids_first, $user_ids_reminder, $new));
			}
			$first_sent_user_ids[$date_first] = $new;
		}

		return $first_sent_user_ids;
	}

	protected function filterToSendUser($to_send, $sent_user_ids, $user_ids, $next_send) {
		foreach($sent_user_ids as $date => $sent_user_id) {
			if($date <= $next_send) {
				$to_send = array_merge($to_send, array_diff($user_ids, $sent_user_id, $to_send));
			}
		}

		return $to_send;
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