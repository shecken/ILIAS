<?php
use ILIAS\TMS\Mailing;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextCourse.php');

/**
 * Class ilTMSICalBuilder
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilTMSICalBuilder implements Mailing\ICalBuilder
{
	const TITLE = "Titel";
	const DESCRIPTION = "Beschreibung";
	const DATE = "Datum";
	const VENUE = "Veranstalter";
	const TIME = "Zeit";

	/**
	 * @inheritdoc
	 */
	public function getICalFilePath(array $info)
	{
		$this->buildIcal($info);
	}

	/**
	 * @inheritdoc
	 */
	public function getICalString(array $info)
	{
		return $this->buildIcal($info);
	}

	/**
	 * Build an iCal string.
	 *
	 * @param 	array 	$info
	 * @return 	string
	 */
	protected function buildICal(array $info)
	{
		$crs_name = "";
		$description = "";
		$duration = "";
		$time = "";
		$venue = "";

		foreach($info as $i) {
			switch ($i->getLabel()) {
				case self::TITLE:
					$title =  $i->getValue();
					break;
				case self::DESCRIPTION:
					$description = $i->getValue();
					break;
				case self::DATE:
					$date = $i->getValue();
					break;
				case self::TIME:
					$time = $i->getValue();
					break;
				case self::VENUE:
					$venue = $i->getValue();
					break;
				default:
					break;
			}
		}

		// $start_date_obj = $date['start'];
		// $end_date_obj = $date['end'];
		// if ($start_date_obj === null || $end_date_obj === null) {
		// 	throw new Exception("gevUserUtils::buildICAL:"
		// 						." start- or end-date of course are not set."
		// 						." You have to provide both in order to create an ical event.");
		// }
		// $start_date =
		// 	$start_date_obj->get(IL_CAL_DATE)." ".$this->getFormattedStartTime().":00";
		// $end_date =
		// 	$end_date_obj->get(IL_CAL_DATE)." ".$this->getFormattedEndTime().":00";
		$calendar = new \Eluceo\iCal\Component\Calendar('generali-onlineakademie.de');
		$tz_rule_daytime = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_DAYLIGHT);
		$tz_rule_daytime
			->setTzName('CEST')
			->setDtStart(new \DateTime('1981-03-29 02:00:00', $dtz))
			->setTzOffsetFrom('+0100')
			->setTzOffsetTo('+0200');
		$tz_rule_daytime_rec = new \Eluceo\iCal\Property\Event\RecurrenceRule();
		$tz_rule_daytime_rec
			->setFreq(\Eluceo\iCal\Property\Event\RecurrenceRule::FREQ_YEARLY)
			->setByMonth(3)
			->setByDay('-1SU');
		$tz_rule_daytime->setRecurrenceRule($tz_rule_daytime_rec);
		$tz_rule_standart = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_STANDARD);
		$tz_rule_standart
			->setTzName('CET')
			->setDtStart(new \DateTime('1996-10-27 03:00:00', $dtz))
			->setTzOffsetFrom('+0200')
			->setTzOffsetTo('+0100');
		$tz_rule_standart_rec = new \Eluceo\iCal\Property\Event\RecurrenceRule();
		$tz_rule_standart_rec
			->setFreq(\Eluceo\iCal\Property\Event\RecurrenceRule::FREQ_YEARLY)
			->setByMonth(10)
			->setByDay('-1SU');
		$tz_rule_standart->setRecurrenceRule($tz_rule_standart_rec);
		$tz = new \Eluceo\iCal\Component\Timezone('Europe/Berlin');
		$tz->addComponent($tz_rule_daytime);
		$tz->addComponent($tz_rule_standart);
		$calendar->setTimezone($tz);
		$event = new \Eluceo\iCal\Component\Event();
		$event
			->setDtStart(new \DateTime($date['start']))
			->setDtEnd(new \DateTime($date['end']))
			->setNoTime(false)
			->setLocation($venue, $venue)
			->setUseTimezone(true)
			->setSummary($this->getTitle())
			->setDescription($this->getSubtitle())
			->setOrganizer(new \Eluceo\iCal\Property\Event\Organizer($organizer));
		$calendar
			->setTimezone($tz)
			->addComponent($event);
		$wstream = fopen($a_filename, "w");
		fwrite($wstream, $calendar->render());
		fclose($wstream);
		if ($a_send) {
			exit();
		}
		return array($a_filename, "calender.ics");
	}
}