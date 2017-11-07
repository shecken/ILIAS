<?php

/**
 * cat-tms-patch start
 */

class Helper {
	const F_TITLE = "f_title";
	const F_TYPE = "f_type";
	const F_TOPIC = "f_topic";
	const F_TARGET_GROUP = "f_target";
	const F_CITY = "f_city";
	const F_PROVIDER = "f_provider";
	const F_NOT_MIN_MEMBER = "f_not_min_member";
	const F_DURATION = "f_duration";
	const F_SORT_VALUE = "f_sort_value";

	const S_ALL = "s_all";
	const S_TITLE = "s_title";
	const S_PERIOD = "s_period";
	const S_TYPE = "s_type";
	const S_CITY = "s_city";

	/**
	 * @var ilObjUser
	 */
	protected $g_user;

	public function __construct() {
		global $DIC;
		$this->g_user = $DIC->user();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_lng = $DIC->language();
		$this->g_factory = $DIC->ui()->factory();
		$this->g_renderer = $DIC->ui()->renderer();
	}

	/**
	 * Get needed values from bkm. Just best for user
	 *
	 * @param ilObjBookingModalities[] 	$bms
	 * @param ilDateTime 	$crs_start_date
	 *
	 * @return array<integer, int | ilDateTime | string>
	 */
	public function getBestBkmValues(array $bkms, ilDateTime $crs_start_date) {
		$plugin = ilPluginAdmin::getPluginObjectById('xbkm');
		return $plugin->getBestBkmValues($bkms, $crs_start_date);
	}

	/**
	 * Get information about selected venue
	 *
	 * @param int 	$crs_id
	 *
	 * @return string[]
	 */
	public function getVenueInfos($crs_id) {
		$plugin = ilPluginAdmin::getPluginObjectById('venues');
		if(!$plugin) {
			return array(-1,"", "");
		}

		return $plugin->getVenueInfos($crs_id);
	}

	/**
	 * Get information about selected provider
	 *
	 * @param int 	$crs_id
	 *
	 * @return string[]
	 */
	public function getProviderInfos($crs_id) {
		$plugin = ilPluginAdmin::getPluginObjectById('trainingprovider');
		if(!$plugin) {
			return array(-1);
		}

		return $plugin->getProviderInfos($crs_id);
	}

	/**
	 * Get information from course classification object
	 *
	 * @param ilObjCourseClassification 	$ccl
	 *
	 * @return array<integer, string | int[] | string[] | null> 
	 */
	public function getCourseClassificationValues($ccl) {
		if($ccl === null) {
			return array(null,
				"",
				array(),
				array(),
				"",
				array(),
				array(),
			);
		}

		return $ccl->getCourseClassificationValues();
	}

	/**
	 * Prepare the filter modal
	 *
	 * @param ilPropertyFormGUI $form
	 *
	 * @return string
	 */
	public function prepareModal(ilPropertyFormGUI $form)
	{
		require_once('./Services/Form/classes/class.ilTextInputGUI.php');
		require_once('./Services/Form/classes/class.ilDateDurationInputGUI.php');
		require_once("Services/Component/classes/class.ilPluginAdmin.php");

		$item = new ilTextInputGUI($this->g_lng->txt('title'), self::F_TITLE);
		$form->addItem($item);

		if(ilPluginAdmin::isPluginActive('xccl')) {
			$plugin = ilPluginAdmin::getPluginObjectById('xccl');
			$actions = $plugin->getActions();

			$item = new ilSelectInputGUI($this->g_lng->txt('type'), self::F_TYPE);
			$options = array(-1 => "Alle") + $actions->getTypeOptions();
			$item->setOptions($options);
			$form->addItem($item);

			$item = new ilSelectInputGUI($this->g_lng->txt('topic'), self::F_TOPIC);
			$options = array(-1 => "Alle") + $actions->getTopicOptions();
			$item->setOptions($options);
			$form->addItem($item);

			$item = new ilSelectInputGUI($this->g_lng->txt('target_group'), self::F_TARGET_GROUP);
			$options = array(-1 => "Alle") + $actions->getTargetGroupOptions();
			$item->setOptions($options);
			$form->addItem($item);
		}

		$item = new ilDateDurationInputGUI($this->g_lng->txt('duration'), self::F_DURATION);
		$item->setStart(new ilDateTime(date("Y-01-01 00:00:00"), IL_CAL_DATETIME));
		$item->setEnd(new ilDateTime(date("Y-12-31 23:59:59"), IL_CAL_DATETIME));
		$form->addItem($item);

		$item = new ilCheckboxInputGUI("", self::F_NOT_MIN_MEMBER);
		$item->setInfo($this->g_lng->txt('not_min_member'));
		$item->setValue(1);
		$form->addItem($item);

		$item = new ilHiddenInputGUI('cmd');
		$item->setValue('submit');
		$form->addItem($item);


		if (isset($_POST['cmd']) && $_POST['cmd'] == 'submit') {
			$form->setValuesByPost();
		}

		// Build a submit button (action button) for the modal footer
		$form_id = 'form_' . $form->getId();
		$submit = $this->g_factory->button()->primary($this->g_lng->txt('search'), "#")->withOnLoadCode(function($id) use ($form_id) {
			return "$('#{$id}').click(function() { $('#{$form_id}').submit(); return false; });";
		});
 

		$modal = $this->g_factory->modal()->roundtrip($this->g_lng->txt('filter'), $this->g_factory->legacy($form->getHTML()))
			->withActionButtons([$submit]);

		$button1 = $this->g_factory->button()->standard($this->g_lng->txt('search'), '#')
			->withOnClick($modal->getShowSignal());

		return $this->g_renderer->render([$button1, $modal]);
	}

	/**
	 * Parse port array for filter values
	 *
	 * @return string[]
	 */
	public function getFilterValuesFrom(array $values) {
		$filter = array();

		if(array_key_exists(self::F_TITLE, $values)
			&& trim($values[self::F_TITLE]) != ""
		) {
			$filter[self::F_TITLE] = trim($values[self::F_TITLE]);
		}

		if(array_key_exists(self::F_TYPE, $values)
			&& (int)$values[self::F_TYPE] != -1
		) {
			$filter[self::F_TYPE] = (int)$values[self::F_TYPE];
		}

		if(array_key_exists(self::F_TOPIC, $values)
			&& (int)$values[self::F_TOPIC] != -1
		) {
			$filter[self::F_TOPIC] = (int)$values[self::F_TOPIC];
		}

		if(array_key_exists(self::F_TARGET_GROUP, $values)
			&& (int)$values[self::F_TARGET_GROUP] != -1
		) {
			$filter[self::F_TARGET_GROUP] = (int)$values[self::F_TARGET_GROUP];
		}

		$not_min_member = $values[self::F_NOT_MIN_MEMBER];
		if($not_min_member && $not_min_member == "1") {
			$filter[self::F_NOT_MIN_MEMBER] = $not_min_member;
		}

		if(array_key_exists(self::F_DURATION, $values)) {
			$filter[self::F_DURATION] = $values[self::F_DURATION];
		}

		return $filter;
	}

	/**
	 * Form date for gui as user timezone string
	 *
	 * @param ilDateTime 	$dat
	 * @param bool 	$use_time
	 *
	 * @return string
	 */
	public function formatDate($dat, $use_time = false) {
		require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
		$out_format = ilCalendarUtil::getUserDateFormat($use_time, true);
		$ret = $dat->get(IL_CAL_FKT_DATE, $out_format, $this->g_user->getTimeZone());
		if(substr($ret, -5) === ':0000') {
			$ret = substr($ret, 0, -5);
		}

		return $ret;
	}

	/**
	 * Get the option for sorting of table
	 *
	 * @return string[]
	 */
	public function getSortOptions() {
		return array(Helper::S_ALL, Helper::S_TITLE, Helper::S_PERIOD, Helper::S_TYPE, Helper::S_CITY);
	}
}

/**
 * cat-tms-patch end
 */