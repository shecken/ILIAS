<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Database abstraction for effectiveness analyis
 *
 * @author Stefan Hecken <stefan.hecken@cocepts-and-training.de>
 */
class gevEffectivenessAnalysisDB {

	const EMPTY_TEXT = "-empty-";
	const EMPTY_DATE = "0000-00-00";

	public function __construct($db) {
		$this->gDB = $db;
	}

	/**
	 * Create the effectiveness analysis result table
	 */
	public function createTable() {
		if( !$this->gDB->tableExists('eff_analysis') ) {
			$this->gDB->createTable('eff_analysis', array(
				'crs_id' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
				),
				'user_id' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
				),
				'result' => array(
					'type' => 'integer',
					'length' => 4,
					'notnull' => true
				),
				'info' => array(
					'type' => 'clob',
					'notnull' => true
				),
				'finish_date' => array(
					'type' => 'date',
					'notnull' => false
				)
			));

			$this->gDB->addPrimaryKey('eff_analysis', array('crs_id', 'user_id'));
		}
	}

	/**
	 * Get data for my effectiveness analysis view
	 *
	 * @param int[] 		$employees
	 * @param string[] 		$reason_for_eff_analysis
	 * @param mixed[]		$filter
	 * @param int 			$offset
	 * @param int 			$limit
	 * @param string 		$order
	 * @param string 		$order_direction
	 *
	 * @return array<mixed[]>
	 */
	public function getEffectivenessAnalysisData($employees, array $reason_for_eff_analysis, array $filter, $offset, $limit, $order, $order_direction) {
		$query = "SELECT husr.user_id, husr.lastname, husr.firstname, husr.email\n"
				.", GROUP_CONCAT(DISTINCT husrorgu.orgu_title SEPARATOR ', ') AS orgunit\n"
				.", hcrs.title, hcrs.type, hcrs.begin_date, hcrs.end_date, hcrs.language, hcrs.training_number\n"
				."    , hcrs.venue, hcrs.target_groups, hcrs.objectives_benefits, hcrs.training_topics, hcrs.crs_id\n"
				.", effa.finish_date, effa.result\n";
		$query .= $this->getSelectBase($employees, $reason_for_eff_analysis);
		$query .= $this->getWhereByFilter($filter);
		$query .= $this->getGroupBy();
		$query .= " ORDER BY ".$order." ".$order_direction;
		$query .= " LIMIT ".$offset.", ".$limit;

		$ret = array();
		$result = $this->gDB->query($query);
		while($row = $this->gDB->fetchAssoc($result)) {
			foreach($row as $key => $value) {
				if($value == self::EMPTY_DATE || $value == self::EMPTY_TEXT || $value === null) {
					$row[$key] = "-";
				}
			}

			$ret[] = $row;
		}

		return $ret;
	}

	/**
	 * Get number of possible effectiveness analysis without offset and limit
	 *
	 * @param int 		$user_id
	 * @param mixed[]	$filter
	 *
	 * @return int
	 */
	public function getCountEffectivenessAnalysisData($employees, array $reason_for_eff_analysis, array $filter) {
		$query = "SELECT count(hcrs.crs_id) AS cnt\n";
		$query .= $this->getSelectBase($employees, $reason_for_eff_analysis);
		$query .= $this->getWhereByFilter($filter);
		$query .= $this->getGroupBy();

		$res = $this->gDB->query($query);

		return $this->gDB->numRows($res);
	}

	/**
	 * Get data for the effectiveness analyis report
	 *
	 * @param int 		$user_id
	 * @param mixed[] 	$filter
	 * @param string 	$order
	 * @param string 	$order_direction
	 *
	 * @return mixed[]
	 */
	public function getEffectivenessAnalysisReportData($employees, array $reason_for_eff_analysis, array $filter, $order, $order_direction) {
		$query = "SELECT husr.user_id, GROUP_CONCAT(DISTINCT CONCAT_WS(', ', husr.lastname, husr.firstname)) AS member, husr.email\n"
				.", GROUP_CONCAT(DISTINCT husrorgu.orgu_title SEPARATOR ', ') AS orgunit\n"
				.", GROUP_CONCAT(DISTINCT IF(ISNULL(husr2.lastname),NULL,CONCAT_WS(', ', husr2.lastname, husr2.firstname)) SEPARATOR ', ') AS superior\n"
				.", hcrs.title, hcrs.type, hcrs.begin_date, hcrs.end_date, hcrs.language, hcrs.training_number\n"
				."    , hcrs.venue, hcrs.target_groups, hcrs.objectives_benefits, hcrs.training_topics, hcrs.crs_id\n"
				."    , hcrs.reason_for_training\n"
				.", CASE hcrs.type\n"
				."      WHEN 'Online Training' THEN DATE_FORMAT(FROM_UNIXTIME(psusr.changed_on + (90 * 24 * 60 * 60)), '%Y-%b-%e')\n"
				."      ELSE DATE_ADD(hcrs.end_date, INTERVAL 90 DAY)\n"
				."  END AS scheduled\n"
				.", effa.finish_date, effa.result\n";
		$query .= $this->getSelectBase($employees, $reason_for_eff_analysis);
		$query .= $this->getWhereByFilter($filter);
		$query .= $this->getGroupBy();
		$query .= " ORDER BY ".$order." ".$order_direction;

		$res = $this->gDB->query($query);

		while($row = $this->gDB->fetchAssoc($res)) {
			foreach($row as $key => $value) {
				if($value == self::EMPTY_DATE || $value == self::EMPTY_TEXT || $value === null) {
					$row[$key] = "-";
				}
			}

			$ret[] = $row;
		}

		return $ret;
	}

	public function getEffectivenessAnalysisOpen($employees, array $reason_for_eff_analysis, array $filter) {
		$query = "SELECT hcrs.crs_id\n"
				.", CASE hcrs.type\n"
				."      WHEN 'Online Training' THEN DATE_FORMAT(FROM_UNIXTIME(psusr.changed_on + (90 * 24 * 60 * 60)), '%Y-%b-%e')\n"
				."      ELSE DATE_ADD(hcrs.end_date, INTERVAL 90 DAY)\n"
				."  END AS scheduled\n";
		$query .= $this->getSelectBase($employees, $reason_for_eff_analysis);
		$query .= $this->getWhereByFilter($filter);
		$query .= $this->getGroupBy();
		$query .= " HAVING scheduled <= ".$this->gDB->quote($filter[gevEffectivenessAnalysis::F_SCHEDULED], "text")."\n";

		$res = $this->gDB->query($query);
		while($row = $this->gDB->fetchAssoc($res)) {
			$ret[] = $row["crs_id"];
		}

		return $ret;
	}

	/**
	 * Save result for effectiveness analysis for each user
	 *
	 * @param int 		$crs_id
	 * @param int 		$user_id
	 * @param int 		$result
	 * @param string 	$result_info
	 */
	public function saveResult($crs_id, $user_id, $result, $result_info) {
		$values = array("crs_id" => array("integer", $crs_id)
					  , "user_id" => array("integer", $user_id)
					  , "result" => array("integer", $result)
					  , "info" => array("text", $result_info)
					  , "finish_date" => array("text", date('Y-m-d'))
			);

		$this->gDB->insert('eff_analysis', $values);
	}

	/**
	 * Get the base of select statement
	 *
	 * @return string
	 */
	protected function getSelectBase($employees, array $reason_for_eff_analysis) {
		$today_ts = strtotime(date('Y-m-d'). '00:00:00');
		$today_date = date('Y-m-d', strtotime(date('Y-m-d'). '00:00:00'));

		return " FROM hist_course hcrs\n"
				." JOIN crs_book crsb\n"
				."    ON crsb.crs_id = hcrs.crs_id\n"
				."        AND ".$this->gDB->in("crsb.user_id", $employees, false, "integer")."\n"
				."        AND status != ".$this->gDB->quote(4, "integer")."\n"
				." JOIN hist_user husr\n"
				."    ON husr.user_id = crsb.user_id\n"
				."        AND husr.hist_historic = 0\n"
				." JOIN hist_userorgu husrorgu\n"
				."    ON husrorgu.usr_id = husr.user_id\n"
				."        AND husrorgu.hist_historic = 0\n"
				."        AND husrorgu.action = 1\n"
				." JOIN hist_usercoursestatus husrcrs\n"
				."    ON husrcrs.crs_id = hcrs.crs_id\n"
				."        AND husrcrs.usr_id = husr.user_id\n"
				."        AND husrcrs.participation_status = ".$this->gDB->quote('status_successful', 'text')."\n"
				."        AND husrcrs.hist_historic = 0\n"
				." LEFT JOIN hist_userorgu husrorgu2\n"
				."    ON husrorgu2.orgu_id = husrorgu.orgu_id\n"
				."        AND husrorgu2.hist_historic = 0\n"
				."        AND husrorgu2.action = 1\n"
				."        AND husrorgu2.rol_title = ".$this->gDB->quote("Vorgesetzter", "text")."\n"
				." LEFT JOIN hist_user husr2\n"
				."    ON husrorgu2.usr_id = husr2.user_id\n"
				." LEFT JOIN crs_pstatus_usr psusr\n"
				."    ON psusr.crs_id = hcrs.crs_id\n"
				."        AND psusr.user_id = husr.user_id\n"
				." LEFT JOIN eff_analysis effa\n"
				."    ON effa.crs_id = hcrs.crs_id\n"
				."        AND effa.user_id = husr.user_id\n"
				." WHERE hcrs.hist_historic = 0\n"
				."     AND ".$this->gDB->in("hcrs.reason_for_training", $reason_for_eff_analysis, false, "text")."\n"
				."     AND (\n"
				."           (hcrs.type = ".$this->gDB->quote('Online Training', 'text')."\n"
				."                AND psusr.status = ".$this->gDB->quote(3, "integer")."\n"
				."                AND (psusr.changed_on + (90 * 24 * 60 * 60)) <= ".$this->gDB->quote($today_ts, "integer").")\n"
				."         OR\n"
				."           (".$this->gDB->in("hcrs.type", array('Live Training', 'Webinar'), false, "text")."\n"
				."                AND DATE_ADD(hcrs.end_date, INTERVAL 90 DAY) <= ".$this->gDB->quote($today_date, "text").")\n"
				."         )\n";
	}

	/**
	 * Get the group by statement
	 *
	 * @return string
	 */
	protected function getGroupBy() {
		return " GROUP BY husr.user_id, husr.lastname, husr.firstname, husr.email, hcrs.title, hcrs.type, hcrs.begin_date\n"
				 .", hcrs.end_date, hcrs.language, hcrs.training_number, hcrs.venue, hcrs.target_groups, hcrs.objectives_benefits\n"
				 .", hcrs.training_topics, hcrs.crs_id, effa.finish_date, effa.result";
	}

	/**
	 * Get where by filter values
	 *
	 * @param mixed[]		$filter
	 *
	 * @return string
	 */
	protected function getWhereByFilter(array $filter) {
		require_once("Services/GEV/Desktop/classes/EffectivenessAnalysis/class.gevEffectivenessAnalysis.php");
		$where = "";

		if(!isset($filter[gevEffectivenessAnalysis::F_STATUS]) 
			&& isset($filter[gevEffectivenessAnalysis::F_FINISHED]) 
			&& $filter[gevEffectivenessAnalysis::F_FINISHED] == gevEffectivenessAnalysis::STATE_FILTER_OPEN) 
		{
			$where .= "     AND effa.finish_date IS NULL\n";
		}

		if(isset($filter[gevEffectivenessAnalysis::F_PERIOD])) {
			$start = $filter[gevEffectivenessAnalysis::F_PERIOD]["start"]->get(IL_CAL_DATE);
			$end = $filter[gevEffectivenessAnalysis::F_PERIOD]["end"]->get(IL_CAL_DATE);

			$where .= "     AND hcrs.begin_date >= ".$this->gDB->quote($start, "text"). " AND hcrs.begin_date <= ".$this->gDB->quote($end, "text")."\n";
		}

		if(isset($filter[gevEffectivenessAnalysis::F_TITLE]) && $filter[gevEffectivenessAnalysis::F_TITLE] != "") {
			$where .= "     AND hcrs.title = ".$this->gDB->quote($filter[gevEffectivenessAnalysis::F_TITLE], "text")."\n";
		}

		if(isset($filter[gevEffectivenessAnalysis::F_RESULT]) && $filter[gevEffectivenessAnalysis::F_RESULT] != "") {
			$where .= "     AND effa.result = ".$this->gDB->quote($filter[gevEffectivenessAnalysis::F_RESULT], "integer")."\n";
		}

		if(isset($filter[gevEffectivenessAnalysis::F_STATUS]) && !empty($filter[gevEffectivenessAnalysis::F_STATUS])) {
			$status = $filter[gevEffectivenessAnalysis::F_STATUS];
			
			switch($status) {
				case gevEffectivenessAnalysis::STATE_FILTER_FINISHED:
					$where .= "     AND effa.finish_date IS NOT NULL\n";
					break;
				case gevEffectivenessAnalysis::STATE_FILTER_OPEN:
					$where .= "     AND effa.finish_date IS NULL\n";
					break;
				case gevEffectivenessAnalysis::STATE_FILTER_ALL:
					break;
				default;
					throw new Exception("gevEffectivenessAnalysisDB::getWhereByFilter: Wrong value for Filter ".gevEffectivenessAnalysis::F_STATUS);
			}
		}

		return $where;
	}

	/**
	 * Get reuslt data for crs and user
	 *
	 * @param int 		$crs_id
	 * @param int 		$user_id
	 *
	 * @return string[]
	 */
	public function getResultData($crs_id, $user_id) {
		$query = "SELECT result, info\n"
				." FROM eff_analysis\n"
				." WHERE crs_id = ".$this->gDB->quote($crs_id, "integer")."\n"
				."     AND user_id = ".$this->gDB->quote($user_id, "integer");

		$res = $this->gDB->query($query);
		$row = $this->gDB->fetchAssoc($res);

		return $row;
	}
}