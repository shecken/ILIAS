<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");

/**
* input GUI for a fixed time span (start and end date)
*
* @ingroup ServicesForm
*/
// gev-patch start
class ilFixedLengthPeriodInputGUI extends ilDateDurationInputGUI
// gev-patch end
{

	protected $g_user;
	protected $g_lng;

	public function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);

		global $ilUser, $lng;

		$this->g_user = $ilUser;
		$this->g_lng = $lng;
	}

	public function setValueByArray($a_values)
	{
		if (isset($a_values[$this->getPostVar()]['start']['date'])) {
			$start_date_string = $a_values[$this->getPostVar()]['start']['date'];
		} else {
			$start_date_string = '1970-01-01';
		}

		if (isset($a_values[$this->getPostVar()]['end']['date'])) {
			$end_date_string = $a_values[$this->getPostVar()]['end']['date'];
		} else {
			$end_date_string = '1970-01-01';
		}

		if (isset($a_values[$this->getPostVar()]['start']['time'])
			&& $a_values[$this->getPostVar()]['start']['time'] !== '00:00:00') {
			$start_time_string = $a_values[$this->getPostVar()]['start']['time'];
		} else {
			$start_time_string = '00:00:01';
		}

		if (isset($a_values[$this->getPostVar()]['end']['time'])
			&& $a_values[$this->getPostVar()]['end']['time'] !== '00:00:00') {
			$end_time_string = $a_values[$this->getPostVar()]['end']['time'];
		} else {
			$end_time_string = '00:00:01';
		}

		$this->setStart(new ilDateTime($start_date_string.' '.$start_time_string, IL_CAL_DATETIME, $this->g_user->getTimeZone()));
		$this->setEnd(new ilDateTime($end_date_string.' '.$end_time_string, IL_CAL_DATETIME, $this->g_user->getTimeZone()));
	}

	/**
	* Insert property html
	*
	*/
	public function render()
	{
		// gev-patch start
		if ($this->getShowDate()) {
			$tpl = new ilTemplate("tpl.prop_datetime_duration.html", true, true, "Services/GEV/DecentralTrainings");
		} else {
			$tpl = new ilTemplate("tpl.prop_datetime_duration_time_only.html", true, true, "Services/Form");
		}
		// gev-patch end

		// Init start
		if (is_a($this->getStart(), 'ilDate')) {
			$start_info = $this->getStart()->get(IL_CAL_FKT_GETDATE, '', 'UTC');
		} elseif (is_a($this->getStart(), 'ilDateTime')) {
			$start_info = $this->getStart()->get(IL_CAL_FKT_GETDATE, '', $this->g_user->getTimeZone());
		} else {
			$this->setStart(new ilDateTime(time(), IL_CAL_UNIX));
			$start_info = $this->getStart()->get(IL_CAL_FKT_GETDATE, '', $this->g_user->getTimeZone());
		}
		// display invalid input again
		if (is_array($this->invalid_input['start'])) {
			$start_info['year'] = $this->invalid_input['start']['y'];
			$start_info['mon'] = $this->invalid_input['start']['m'];
			$start_info['mday'] = $this->invalid_input['start']['d'];
		}

		// Init end
		if (is_a($this->getEnd(), 'ilDate')) {
			$end_info = $this->getEnd()->get(IL_CAL_FKT_GETDATE, '', 'UTC');
		} elseif (is_a($this->getEnd(), 'ilDateTime')) {
			$end_info = $this->getEnd()->get(IL_CAL_FKT_GETDATE, '', $this->g_user->getTimeZone());
		} else {
			$this->setEnd(new ilDateTime(time(), IL_CAL_UNIX));
			$end_info = $this->getEnd()->get(IL_CAL_FKT_GETDATE, '', $this->g_user->getTimeZone());
		}
		// display invalid input again
		if (is_array($this->invalid_input['end'])) {
			$end_info['year'] = $this->invalid_input['end']['y'];
			$end_info['mon'] = $this->invalid_input['end']['m'];
			$end_info['mday'] = $this->invalid_input['end']['d'];
		}

		$this->g_lng->loadLanguageModule("jscalendar");
		require_once("./Services/Calendar/classes/class.ilCalendarUtil.php");
		ilCalendarUtil::initJSCalendar();

		if (strlen($this->getActivationPostVar())) {
			$tpl->setCurrentBlock('prop_date_activation');
			$tpl->setVariable('CHECK_ENABLED_DATE', $this->getActivationPostVar());
			$tpl->setVariable('TXT_DATE_ENABLED', $this->activation_title);
			$tpl->setVariable('CHECKED_ENABLED', $this->activation_checked ? 'checked="checked"' : '');
			$tpl->setVariable('CHECKED_DISABLED', $this->getDisabled() ? 'disabled="disabled" ' : '');
			$tpl->parseCurrentBlock();
		}

		if (strlen($this->getStartText())) {
			$tpl->setVariable('TXT_START', $this->getStartText());
		}
		if (strlen($this->getEndText())) {
			$tpl->setVariable('TXT_END', $this->getEndText());
		}

		// Toggle fullday
		if ($this->enabledToggleFullTime()) {
			$tpl->setCurrentBlock('toggle_fullday');
			$tpl->setVariable('FULLDAY_POSTVAR', $this->getPostVar());
			$tpl->setVariable('FULLDAY_TOGGLE_NAME', $this->getPostVar().'[fulltime]');
			$tpl->setVariable('FULLDAY_TOGGLE_CHECKED', $this->toggle_fulltime_checked ? 'checked="checked"' : '');
			$tpl->setVariable('FULLDAY_TOGGLE_DISABLED', $this->getDisabled() ? 'disabled="disabled"' : '');
			$tpl->setVariable('TXT_TOGGLE_FULLDAY', $this->toggle_fulltime_txt);
			$tpl->parseCurrentBlock();
		}

		// gev-patch start
		if ($this->getShowDate()) {// or 1)
		// gev-patch end
			$tpl->setVariable('POST_VAR', $this->getPostVar());

			$tpl->setVariable(
				"START_SELECT",
				$this->renderDateSelect(
					$this->getPostVar()."[start]",
					$start_info['year'],
					$start_info['mon'],
					$start_info['mday'],
					$this->getStartYear(),
					2050,
					$this->getDisabled(),
					'ilInitDurationDate("'.$this->postvar.'");'
				)
			);


			$tpl->setVariable(
				"END_SELECT",
				$this->renderDateSelect(
					$this->getPostVar()."[end]",
					$end_info['year'],
					$end_info['mon'],
					$end_info['mday'],
					$this->getStartYear(),
					2050,
					true
				)
			);
		}
		if ($this->getShowTime()) {
			$tpl->setCurrentBlock("show_start_time");

			$tpl->setVariable(
				"START_TIME_SELECT",
				$this->renderTimeSelect(
					$this->getPostVar().'[start]',
					$start_info['hours'],
					$start_info['minutes'],
					false,
					'ilInitDurationTime("'.$this->postvar.'");'
				)
			);

			$tpl->setVariable("TXT_START_TIME", $this->getShowSeconds()
				? "(".$this->g_lng->txt("hh_mm_ss").")"
				: "(".$this->g_lng->txt("hh_mm").")");
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("show_end_time");

			$tpl->setVariable(
				"END_TIME_SELECT",
				$this->renderTimeSelect(
					$this->getPostVar().'[end]',
					$end_info['hours'],
					$end_info['minutes'],
					true
				)
			);

			$tpl->setVariable("TXT_END_TIME", $this->getShowSeconds()
				? "(".$this->g_lng->txt("hh_mm_ss").")"
				: "(".$this->g_lng->txt("hh_mm").")");
			$tpl->parseCurrentBlock();
		}

		if ($this->getShowTime() && $this->getShowDate()) {
			$tpl->setVariable("DELIM", "<br />");
		}

		// patch generali start
		if ($this->show_weight) {
			$weight_select = ilUtil::formSelect(
				(int)$this->current_weight,
				$this->getPostVar()."_wapp",
				ilTEP::getWeightOptions(),
				false,
				true
			);
			$tpl->setVariable(
				"WEIGHT_SELECT",
				sprintf($this->g_lng->txt("tep_weighing_input"), $weight_select)
			);
		}
		// patch generali end

		// gev-patch start
		if ($this->getMulti() && !$this->getDisabled()) {
			$tpl->setVariable("MULTI_ICONS", $this->getMultiIconsHTML($this->multi_sortable));
		}

		// this is really hacky stuff. do not try this at home or at all.
		if ($this->getMulti()) {
			$index = 0;
			foreach ($this->more_values as $value) {
				$tpl->setCurrentBlock("more_values");
				$tpl->setVariable("GROUP_ID", str_replace("[", "", str_replace("]", "", $this->getPostVar())));
				$tpl->setVariable("MINDEX", $index);
				$tpl->setVariable("MVALUE", $value);
				$tpl->parseCurrentBlock();
				$index++;
			}
		}
		// gev-patch end

		return $tpl->get();
	}

	protected function renderDateSelect($postvar, $year = 1970, $month = 1, $day = 1, $start_year = 1970, $end_year = 2050, $disabled = false, $script = "", array $additonal = array())
	{
		assert('is_string($postvar)');
		assert('is_int($year)');
		assert('is_int($month)');
		assert('is_int($day)');
		assert('is_int($start_year)');
		assert('is_int($end_year)');
		$tpl = new ilTemplate("tpl.date_select.html", true, true, "Services/GEV/DecentralTrainings");

		$year_aux = $start_year;
		if ($disabled) {
			$tpl->setVariable('DISABLED', 'disabled');
		}
		$tpl->setVariable('POSTVAR', $postvar);
		while ($year_aux <= $end_year) {
			$tpl->setCurrentBlock('years');
			$tpl->setVariable('VALUE', $year_aux);
			$tpl->setVariable('TITLE', $year_aux);
			if ($year_aux === $year) {
				$tpl->setVariable('SELECTED', 'selected');
			}
			$year_aux++;
			$tpl->parseCurrentBlock();
		}


		for ($month_aux = 1; $month_aux <= 12; $month_aux++) {
			$tpl->setCurrentBlock('months');
			$tpl->setVariable('VALUE', $month_aux);
			$tpl->setVariable('TITLE', $this->g_lng->txt('month_'.str_pad($month_aux, 2, '0', STR_PAD_LEFT).'_long'));
			if ($month_aux === $month) {
				$tpl->setVariable('SELECTED', 'selected');
			}
			$tpl->parseCurrentBlock();
		}

		for ($day_aux = 1; $day_aux <= 31; $day_aux++) {
			$tpl->setCurrentBlock('days');
			$tpl->setVariable('VALUE', $day_aux);
			$tpl->setVariable('TITLE', $day_aux);
			if ($day_aux === $day) {
				$tpl->setVariable('SELECTED', 'selected');
			}
			$tpl->parseCurrentBlock();
		}

		foreach ($additonal as $attribute => $value) {
			$add .= $attribute.'="'.$value.'" ';
		}
		$tpl->setVariable('ADDITIONAL', $add);
		$tpl->setVariable('SCRIPT', $script);
		return $tpl->get();
	}

	protected function renderTimeSelect($postvar, $hour = 0, $minute = 0, $disabled = false, $script = "", array $additonal = array())
	{
		assert('is_string($postvar)');
		assert('is_int($hour)');
		assert('is_int($minute)');
		$tpl = new ilTemplate("tpl.time_select.html", true, true, "Services/GEV/DecentralTrainings");
		$tpl->setVariable('POSTVAR', $postvar);
		if ($disabled) {
			$tpl->setVariable('DISABLED', 'disabled');
		}
		if ($minute%5 != 0 || $minute > 55 || $minute < 0) {
			$minute = 0;
		}
		if ($hour > 23 || $hour < 0) {
			$minute = 0;
		}
		$tpl->setVariable('SELECTED_M_'.$minute, 'selected="selected"');
		$tpl->setVariable('SELECTED_H_'.$hour, 'selected="selected"');
		$add = '';
		foreach ($additonal as $attribute => $value) {
			$add .= $attribute.'="'.$value.'" ';
		}
		$tpl->setVariable('ADDITIONAL', $add);
		$tpl->setVariable('SCRIPT', $script);
		return $tpl->get();
	}
}