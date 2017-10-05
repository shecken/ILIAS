<?php
/**
 * cat-tms-patch start
 */

/**
 * Displays the TMS training search
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilTrainingSearchGUI {
	const CMD_SHOW = "show";
	const CMD_SHOW_MODAL = "showModal";
	const CMD_FILTER = "filter";

	const F_TITLE = "f_title";
	const F_TYPE = "f_type";
	const F_TOPIC = "f_topic";
	const F_TARGET_GROUP = "f_target";
	const F_CITY = "f_city";
	const F_PROVIDER = "f_provider";
	const F_NOT_MIN_MEMBER = "f_not_min_member";
	const F_DURATION = "f_duration";

	/**
	 * @var ilTemplate
	 */
	protected $g_tpl;

	/**
	 * @var ilCtrl
	 */
	protected $g_ctrl;

	/**
	 * @var ilObjUser
	 */
	protected $g_user;

	/**
	 * @var ilPersonalDesktopGUI
	 */
	protected $parent;

	/**
	 * @var ilTrainingSearchDB
	 */
	protected $db;

	public function __construct(ilPersonalDesktopGUI $parent, ilTrainingSearchDB $db, Helper $helper, ilBookableFilter $filter) {
		global $DIC;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_user = $DIC->user();
		$this->g_lng = $DIC->language();
		$this->g_toolbar = $DIC->toolbar();
		$this->g_factory = $DIC->ui()->factory();
		$this->g_renderer = $DIC->ui()->renderer();

		$this->parent = $parent;
		$this->db = $db;
		$this->g_lng->loadLanguageModule('tsearch');

		$this->helper = $helper;
		$this->filter = $filter;
	}

	public function executeCommand() {
		$cmd = $this->g_ctrl->getCmd(self::CMD_SHOW);

		switch($cmd) {
			case self::CMD_SHOW:
				$this->show();
				break;
			case self::CMD_FILTER:
				$this->filter();
				break;
			default:
				throw new Exception("Unknown command: ".$cmd);
		}
	}

	/**
	 * Shows all bookable trainings
	 *
	 * @param string[] 	$filter
	 *
	 * @return void
	 */
	protected function show(array $filter = array()) {
		$crs_infos = $this->getBookableCourseInformations();
		$bookable_trainings = $this->createBookableCourseByFilter($crs_infos, array());
		$this->showTrainings($bookable_trainings);
	}

	/**
	 * Post processing for filter values
	 *
	 * @return void
	 */
	protected function filter() {
		$crs_infos = $this->getBookableCourseInformations();
		$filter = $this->parsePost();
		$bookable_trainings = $this->createBookableCourseByFilter($crs_infos, $filter);
		$this->showTrainings($bookable_trainings);
	}

	/**
	 * Show bookable trainings
	 *
	 * @param BookableCourse[] 	$bookable_trainings
	 *
	 * @return void
	 */
	protected function showTrainings(array $bookable_trainings) {
		require_once("Services/TMS/TrainingSearch/classes/class.ilTrainingSearchTableGUI.php");
		$table = new ilTrainingSearchTableGUI($this);
		$table->setData($bookable_trainings);

		$modal = $this->prepareModal();
		$this->g_tpl->setContent($modal."<br \><br \><br \>".$table->render());
		$this->g_tpl->show();
	}

	/**
	 * Parse port array for filter values
	 *
	 * @return string[]
	 */
	protected function parsePost() {
		$post = $_POST;

		$filter = array();
		$title = trim($post[self::F_TITLE]);
		if($title != "") {
			$filter[self::F_TITLE] = $title;
		}

		$type = $post[self::F_TYPE];
		if($type != -1) {
			$filter[self::F_TYPE] = $type;
		}

		$topic = $post[self::F_TOPIC];
		if($topic != -1) {
			$filter[self::F_TOPIC] = $topic;
		}

		$target_group = $post[self::F_TARGET_GROUP];
		if($target_group != -1) {
			$filter[self::F_TARGET_GROUP] = $target_group;
		}

		$city = $post[self::F_CITY];
		if($city != -1) {
			$filter[self::F_CITY] = $city;
		}

		$provider = $post[self::F_PROVIDER];
		if($provider != -1) {
			$filter[self::F_PROVIDER] = $provider;
		}

		$not_min_member = $post[self::F_NOT_MIN_MEMBER];
		if($not_min_member && $not_min_member == "1") {
			$filter[self::F_NOT_MIN_MEMBER] = $not_min_member;
		}

		$filter[self::F_DURATION] = $post[self::F_DURATION];

		return $filter;
	}

	/**
	 * Perform filter on all course informations
	 *
	 * @param array<int, ilObjCourse | ilObjBookingModalities[] | ilObjCourseClassification>
	 * @param array<int, string | int>
	 *
	 * @return BookableCourse[]
	 */
	protected function createBookableCourseByFilter(array $crs_infos, array $filter) {
		$ret = array();

		foreach ($crs_infos as $key => $value) {
			$crs = $value["crs"];

			$start_date = $crs->getCourseStart();
			$end_date = $crs->getCourseEnd();
			$title = $crs->getTitle();

			if($start_date === null) {
				unset($crs_infos[$key]);
				continue;
			}

			list($max_member, $booking_start_date, $booking_end_date, $waiting_list, $min_member, $bookings_available) = $this->helper->getBestBkmValues($value["xbkm"], $start_date);
			list($venue_id, $city, $address) = $this->helper->getVenueInfos($crs->getId());
			list($type_id,$type,$target_group_ids,$target_group,$goals,$topic_ids,$topics) = $this->helper->getCourseClassificationValues($value["xccl"]);
			list($provider_id) = $this->helper->getProviderInfos($crs->getId());

			if(!$this->filter->isInBookingPeriod($booking_start_date, $booking_end_date)) {
				echo "booking";
				unset($crs_infos[$key]);
				continue;
			}

			if(array_key_exists(self::F_DURATION, $filter)
				&& !$this->filter->courseInFilterPeriod($start_date, $filter[self::F_DURATION]["start"], $filter[self::F_DURATION]["end"])
			) {
				unset($crs_infos[$key]);
				continue;
			}

			if(array_key_exists(self::F_TARGET_GROUP, $filter)
				&& !$this->filter->courseHasTargetGroups($target_group_ids, $filter[self::F_TARGET_GROUP])
			) {
				unset($crs_infos[$key]);
				continue;
			}

			if(array_key_exists(self::F_TOPIC, $filter)
				&& !$this->filter->courseHasTopics($topic_ids, $filter[self::F_TOPIC])
			) {
				unset($crs_infos[$key]);
				continue;
			}

			if(array_key_exists(self::F_TYPE, $filter)
				&& !$this->filter->courseHasType($type_id, $filter[self::F_TYPE])
			) {
				unset($crs_infos[$key]);
				continue;
			}

			if(array_key_exists(self::F_TITLE, $filter)
				&& !$this->filter->crsTitleStartsWith($title, $filter[self::F_TITLE])
			) {
				unset($crs_infos[$key]);
				continue;
			}

			if(array_key_exists(self::F_NOT_MIN_MEMBER, $filter) 
				&& $this->filter->minMemberReached($crs->getRefId(), $min_member)
			) {
				unset($crs_infos[$key]);
				continue;
			}

			if(array_key_exists(self::F_CITY, $filter) 
				&& $venue_id != -1
				&& !$this->filter->courseHasVenue($venue_id, $filter[self::F_CITY])
			) {
				unset($crs_infos[$key]);
				continue;
			}

			if(array_key_exists(self::F_PROVIDER, $filter) 
				&& !$this->filter->courseHasProvider($provider_id, (int)$filter[self::F_PROVIDER])
			) {
				unset($crs_infos[$key]);
				continue;
			}

			$start_date_str = $this->helper->formatDate($start_date);
			$end_date_str = $this->helper->formatDate($end_date);

			$ret[] = $this->db->getBookableCourse($title,
				$type,
				$start_date_str,
				$bookings_available,
				$target_group,
				$goals,
				$topics,
				$end_date_str,
				$city,
				$address
			);
		}

		return $ret;
	}

	/**
	 * Get Bookable training infos
	 *
	 * @return array<int, ilObjCourse | ilObjBookingModalities[] | ilObjCourseClassification>
	 */
	protected function getBookableCourseInformations() {
		return $this->db->getBookableTrainingsFor($this->g_user->getId());
	}

	/**
	 * Prepare the filter modal
	 *
	 * @return void
	 */
	protected function prepareModal()
	{
		require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		require_once('./Services/Form/classes/class.ilTextInputGUI.php');
		require_once('./Services/Form/classes/class.ilDateDurationInputGUI.php');
		require_once("Services/Component/classes/class.ilPluginAdmin.php");

		// Build the form
		$form = new ilPropertyFormGUI();
		$form->setId(uniqid('form'));
		$form->setFormAction($this->g_ctrl->getFormAction($this, self::CMD_FILTER));

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

		if(ilPluginAdmin::isPluginActive('venues')) {
			$plugin = ilPluginAdmin::getPluginObjectById('venues');
			$actions = $plugin->getActions();

			$item = new ilSelectInputGUI($this->g_lng->txt('city'), self::F_CITY);
			$options = array(-1 => "Alle") + $actions->getVenueOptions();
			$item->setOptions($options);
			$form->addItem($item);
		}

		if(ilPluginAdmin::isPluginActive('trainingprovider')) {
			$plugin = ilPluginAdmin::getPluginObjectById('trainingprovider');
			$actions = $plugin->getActions();

			$item = new ilSelectInputGUI($this->g_lng->txt('provider'), self::F_PROVIDER);
			$options = array(-1 => "Alle") + $actions->getProviderOptions();
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
}

/**
 * cat-tms-patch end
 */
