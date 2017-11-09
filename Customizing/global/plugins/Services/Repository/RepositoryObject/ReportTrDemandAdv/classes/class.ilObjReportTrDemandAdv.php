<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';

ini_set("memory_limit", "2048M");
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportTrDemandAdv extends ilObjReportBase
{
	protected $is_local;
	protected $relevant_parameters = array();

	public function initType()
	{
		 $this->setType("xtda");
	}

	protected function getRowTemplateTitle()
	{
		return "tpl.gev_training_utilisation_advanced_row.html";
	}

	protected function buildOrder($order)
	{
		return $order
					->defaultOrder("tpl_title", "ASC")
					->mapping("tpl_title", array("tpl_title","title","begin_date"))
					->mapping("booked_wl", array("waitinglist_active","booked_wl"));
	}

	protected function createLocalReportSettings()
	{
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rtda')
				->addSetting($this->s_f
								->settingBool('is_local', $this->plugin->txt('report_is_local')));
	}

	protected function getTopics()
	{
		require_once 'Services/GEV/Utils/classes/class.gevAMDUtils.php';
		require_once 'Services/GEV/Utils/classes/class.gevSettings.php';
		return	gevAMDUtils::getInstance()->getOptions(gevSettings::CRS_AMD_TOPIC);
	}

	protected function buildTable($table)
	{
		$table	->column('tpl_title', $this->plugin->txt('tpl_title'), true)
				->column('title', $this->plugin->txt('crs_title'), true)
				->column('type', $this->plugin->txt('crs_type'), true)
				->column('begin_date', $this->plugin->txt('crs_date'), true)
				->column('bookings', $this->plugin->txt('bookings'), true)
				->column('min_participants', $this->plugin->txt('min_participants'), true)
				->column('min_part_achived', $this->plugin->txt('min_part_achived'), true)
				->column('bookings_left', $this->plugin->txt('bookings_left'), true)
				->column('booked_wl', $this->plugin->txt('waitinglist'), true)
				->column('booking_dl', $this->plugin->txt('booking_dl'), true)
				->column('trainers', $this->plugin->txt('trainers'), true);
		return parent::buildTable($table);
	}

	protected function buildQuery($query)
	{
		$query
			->select('crs.crs_id')
			->select('crs.title')
			->select('crs.type')
			->select('crs.begin_date')
			->select_raw('tpl.title as tpl_title')
		//	->select_raw('(crs.max_participants - crs.bookings) as bookings_left')
	//		->select_raw('IF(crs.bookings >= crs.min_participants OR ( crs.bookings > 0 AND (crs.min_participants IS NULL'
	//					.'	OR crs.min_participants <= 0)),1,0) as min_part_achived')
			->select_raw('DATE_SUB(crs.begin_date,INTERVAL crs.dl_booking DAY) as booking_dl')
			->select('crs.end_date')
			->select_raw("IF(crs.waitinglist_active = 'Ja',1,0) as waitinglist_active")
			->select_raw("SUM(IF(usrcrs.booking_status = 'gebucht' AND usrcrs.function = 'Mitglied',1,0)) as bookings")
			->select_raw('crs.min_participants')
			->select_raw('crs.max_participants')
			->select_raw("SUM(IF(usrcrs.booking_status = 'auf Warteliste',1,0)) as booked_wl")
			->select_raw(" GROUP_CONCAT("
						." IF(usrcrs.function = 'Trainer',CONCAT(usr.firstname,' ',usr.lastname) ,NULL)"
						." SEPARATOR ', ') as trainers")
			->from('hist_course tpl')
			->join('hist_course crs')
				->on('crs.template_obj_id = tpl.crs_id')
			->left_join('hist_usercoursestatus usrcrs')
				->on(' usrcrs.crs_id = crs.crs_id AND usrcrs.hist_historic = 0 ')
			->left_join('hist_user usr')
				->on('usr.user_id = usrcrs.usr_id '
					.' AND usr.hist_historic = 0 ')
			->compile();
		return $query;
	}

	protected function buildFilter($filter)
	{
		return null;
	}

	public function filter()
	{
		$db = $this->gIldb;
		$pf = new \CaT\Filter\PredicateFactory();
		$tf = new \CaT\Filter\TypeFactory();
		$f = new \CaT\Filter\FilterFactory($pf, $tf);
		$txt = function ($id) {
			return $this->plugin->txt($id);
		};

		return
		$f->sequence(
			$f->sequence(

				/* BEGIN BLOCK - PERIOD */
				$f->dateperiod(
					$txt("period"),
					""
				)->map(
					function ($start, $end) use ($f) {
							$pc = $f->dateperiod_overlaps_predicate(
								"crs.begin_date",
								"crs.begin_date"
							);
							return array ("date_period_predicate" => $pc($start,$end)
										 ,"start" => $start
										 ,"end" => $end);
					},
					$tf->dict(
						array
							(
								"date_period_predicate" => $tf->cls("CaT\\Filter\\Predicates\\Predicate")
								,"start" => $tf->cls("DateTime")
								,"end" => $tf->cls("DateTime")
							)
					)
				),
				/* END BLOCK - PERIOD */


				/* BEGIN BLOCK - FILTER TOPICS */
				$f->multiselectsearch(
					$txt("filter_topics"),
					"",
					$this->getTopics()
				),
				/* END BLOCK - FILTER TOPICS */


				/* BEGIN BLOCK - TRAINING TYPE */
				$f->multiselectsearch(
					$txt("training_type"),
					"",
					array('Webinar' => 'Webinar','Präsenztraining' => 'Präsenztraining')
				),
				/* END BLOCK - TRAINING TYPE */


				/* BEGIN BLOCK - STATUS */
				$f->multiselectsearch(
					$txt("status"),
					"",
					array('min_participants > bookings' => $txt('cancel_danger'), 'min_participants <= bookings' => $txt('no_cancel_danger'))
				),
				/* END BLOCK - STATUS */

				/* BEGIN BLOCK - WAITING LIST */
				$f->multiselectsearch(
					$txt("waiting_list"),
					"",
					array("crs.waitinglist_active = 'Ja'" => $txt('waiting_list'), "crs.waitinglist_active = 'Nein'" => $txt('no_waiting_list'))
				),
				/* END BLOCK - WAITING LIST */

				/* BEGIN BLOCK - BOOKING OVER */
				$f->multiselectsearch(
					$txt("booking_over"),
					"",
					array($db->quote(date('Y-m-d'), 'text')." > booking_dl " => $txt('book_dl_over'),
							$db->quote(date('Y-m-d'), 'text')." <= booking_dl " => $txt('book_dl_not_over'))
				)
				/* END BLOCK - BOOKING OVER */
			)
		)->map(
			function ($date_period_predicate, $start, $end, $filter_topics, $training_type, $status, $waiting_list, $booking_over) {
						return array("period_pred" => $date_period_predicate
									,"start" => $start
									,"end" => $end
									,"filter_topics" => $filter_topics
									,"training_type" => $training_type
									,"status" => $status
									,"waiting_list" => $waiting_list
									,"booking_over" => $booking_over);
			},
			$tf->dict(
				array("period_pred" => $tf->cls("CaT\\Filter\\Predicates\\Predicate")
							 ,"start" => $tf->cls("DateTime")
							 ,"end" => $tf->cls("DateTime")
							 ,"filter_topics" => $tf->lst($tf->string())
							 ,"training_type" => $tf->lst($tf->string())
							 ,"status" => $tf->lst($tf->string())
							 ,"waiting_list" => $tf->lst($tf->string())
							 ,"booking_over" => $tf->lst($tf->string())
						)
			)
		);
	}

	public function buildQueryStatement()
	{
		$db = $this->gIldb;
		$query_object = $this->buildQuery(catReportQuery::create());
		$select = $query_object->sql();

		$where = " WHERE  (crs.begin_date >= " .$db->quote(date('Y-m-d'), "text") .")\n"
				."     AND (crs.is_cancelled != 'Ja' OR crs.is_cancelled IS NULL)\n"
				."     AND crs.hist_historic = 0\n"
				."     AND crs.is_template = 'Nein'\n"
				."     AND crs.begin_date != '0000-00-00'\n"
				."     AND crs.type IN ('Webinar','Präsenztraining')\n"
				."     AND tpl.hist_historic = 0\n"
				."     AND tpl.is_template = 'Ja'\n";

		$filter = $this->filter();
		if ($this->filter_settings) {
			$settings = call_user_func_array(array($filter, "content"), $this->filter_settings);
			$to_sql = new \CaT\Filter\SqlPredicateInterpreter($db);
			$dt_query = $to_sql->interpret($settings['period_pred']);
			$where .= "    AND " .$dt_query;
			$having = "";
			if (!empty($settings['filter_topics'])) {
				$select .= " JOIN (SELECT topic_set_id FROM hist_topicset2topic JOIN hist_topics\n"
						  ."         USING (topic_id)\n"
						  ."         WHERE ".$db->in('topic_title', $settings['filter_topics'], false, 'text') ."\n"
						  ."         GROUP BY topic_set_id) AS crs_topics\n"
						  ." ON crs.topic_set = crs_topics.topic_set_id\n";

				$where .= "     AND crs.topic_set != -1";
			}

			if (!empty($settings['training_type'])) {
				$where .= "    AND " .$db->in("crs.type", $settings['training_type'], false, "text");
			}

			if (!empty($settings['status'][0]) && !empty($settings['status'][1])) {
				$having .= "    AND (" .$settings['status'][0]
						.  "    OR " .$settings['status'][1] .")";
			} elseif (!empty($settings['status'][0])) {
				$having .= "    AND (" .$settings['status'][0] .")";
			} elseif (!empty($settings['status'][1])) {
				$having .= "    AND (" .$settings['status'][1] .")";
			}

			if (!empty($settings['waiting_list'])) {
				$where .= "    AND (" .implode(' OR ', $settings['waiting_list']).")";
			}

			if (!empty($settings['booking_over'])) {
				$having .= "    AND (" .$settings['booking_over'][0] .")";
			}
		} else {
			$where .= "     AND ((`crs`.`begin_date` < '" .date("Y") ."-12-31' ) OR (`crs`.`begin_date` = '".date("Y") ."-12-31' ) ) AND (('".date("Y") ."-01-01' < `crs`.`begin_date` ) OR ('" .date("Y") ."-01-01' = `crs`.`begin_date` ) )\n";
		}

		if ((string)$this->settings['is_local'] === "1") {
			$where .= "     AND " .$db->in("crs.template_obj_id", array_unique($this->getSubtreeCourseTemplates()), false, "integer");
		}
		$group = " GROUP BY crs.crs_id ";
		$order = $this->queryOrder();
		if ($having !== "") {
			$having = " HAVING TRUE\n"
					 .$having;
		}
		$query = $select . $where . $group .$having .$order;
		return $query;
	}

	protected function fetchData(callable $callback)
	{
		$db = $this->gIldb;

		$query = $this->buildQueryStatement();
		$res = $db->query($query);
		$data = [];

		while ($rec = $db->fetchAssoc($res)) {
			$data[] = call_user_func($callback, $rec);
		}
		return $data;
	}

	public function getRelevantParameters()
	{
		return $this->relevant_parameters;
	}

	protected function getSubtreeCourseTemplates()
	{
		$query = 	'SELECT obj_id FROM adv_md_values_text amd_val '
					.'	WHERE '.$this->gIldb->in(
						'obj_id',
						$this->getSubtreeTypeIdsBelowParentType('crs', 'cat'),
						false,
						'integer'
					)
					.'		AND field_id = '.$this->gIldb->quote(
						gevSettings::getInstance()
													->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE),
						'integer'
					)
					.'		AND value = '.$this->gIldb->quote('Ja', 'text');
		$return = array();
		$res = $this->gIldb->query($query);
		while ($rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = $rec['obj_id'];
		}
		return $return;
	}
}
