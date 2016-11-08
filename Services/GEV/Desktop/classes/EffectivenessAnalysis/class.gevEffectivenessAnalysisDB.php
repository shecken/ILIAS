<?php

class gevEffectivenessAnalysisDB {

	const EMPTY_TEXT = "-empty-";
	const EMPTY_DATE = "0000-00-00";

	public function __construct($db) {
		$this->gDB = $db;
	}

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
					'notnull' => true
				)
			));

			$this->gDB->addPrimaryKey('eff_analysis', array('crs_id', 'user_id'));
		}
	}

	public function getEffectivenessAnalysisData($employees, array $reason_for_eff_analysis, array $filter, $offset, $limit, $order, $order_direction) {
		$query = "SELECT husr.user_id, husr.lastname, husr.firstname, husr.email\n"
				.", GROUP_CONCAT(husrorgu.orgu_title SEPARATOR ', ') AS orgunit\n"
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

	public function getCountEffectivenessAnalysisData($employees, array $reason_for_eff_analysis, array $filter) {
		$query = "SELECT count(hcrs.crs_id) AS cnt\n";
		$query .= $this->getSelectBase($employees, $reason_for_eff_analysis);
		$query .= $this->getWhereByFilter($filter);
		$query .= $this->getGroupBy();

		$res = $this->gDB->query($query);

		return $this->gDB->numRows($res);
	}

	protected function getSelectBase($employees, array $reason_for_eff_analysis) {
		return " FROM hist_course hcrs\n"
				." JOIN crs_book crsb\n"
				."    ON crsb.crs_id = hcrs.crs_id\n"
				."        AND ".$this->gDB->in("crsb.user_id", $employees, false, "integer")."\n"
				." JOIN hist_user husr\n"
				."    ON husr.user_id = crsb.user_id\n"
				."        AND husr.hist_historic = 0\n"
				." JOIN hist_userorgu husrorgu\n"
				."    ON husrorgu.usr_id = husr.user_id\n"
				."        AND husrorgu.hist_historic = 0\n"
				."        AND husrorgu.action = 1\n"
				." LEFT JOIN eff_analysis effa\n"
				."    ON effa.crs_id = hcrs.crs_id\n"
				."        AND effa.user_id = husr.user_id\n"
				." WHERE hcrs.hist_historic = 0\n"
				."     AND ".$this->gDB->in("hcrs.reason_for_training", $reason_for_eff_analysis, false, "text")."\n";
	}

	protected function getGroupBy() {
		return " GROUP BY husr.user_id, husr.lastname, husr.firstname, husr.email, hcrs.title, hcrs.type, hcrs.begin_date\n"
				 .", hcrs.end_date, hcrs.language, hcrs.training_number, hcrs.venue, hcrs.target_groups, hcrs.objectives_benefits\n"
				 .", hcrs.training_topics, hcrs.crs_id, effa.finish_date, effa.result";
	}

	protected function getWhereByFilter(array $filter) {
		$where = "";
		if(!isset($filter["finished"])) {
			$where .= "     AND effa.finish_date IS NULL\n";
		}

		if(isset($filter["period"])) {
			$start = $filter["period"]["start"]->get(IL_CAL_DATE);
			$end = $filter["period"]["end"]->get(IL_CAL_DATE);

			$where .= "     AND hcrs.begin_date >= ".$this->gDB->quote($start, "text"). " AND hcrs.begin_date <= ".$this->gDB->quote($end, "text")."\n";
		}

		if(isset($filter["title"]) && $filter["title"] != "") {
			$where .= "     AND hcrs.title = ".$this->gDB->quote($filter["title"], "text")."\n";
		}

		return $where;
	}
}