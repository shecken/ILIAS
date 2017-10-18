<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente\Component;

/**
 * Useful functions to process CourseInfo.
 */
trait CourseInfoHelper {
	/**
	 * Get components for the entity.
	 *
	 * @param	string		$component_type
	 * @return	Component[]
	 */
	abstract public function getComponentsOfType($component_type);

	/**
	 * Get information for a certain context ordered by priority.
	 *
	 * @param	mixed	$context	from CourseInfo
	 * @return	CourseInfo[]
	 */
	public function getCourseInfo($context) {
		$info = $this->getComponentsOfType(CourseInfo::class);

		$filter_by_context = function(CourseInfo $a) use ($context) {
			return $a->hasContext($context);
		};
		$info = array_filter($info, $filter_by_context);

		$sort_by_prio = function(CourseInfo $a, CourseInfo $b) {
			$a_prio = $a->getPriority();
			$b_prio = $b->getPriority();
			if ($a_prio < $b_prio) {
				return -1;
			}
			if ($a_prio > $b_prio) {
				return 1;
			}
			return 0;
		};
		usort($info, $sort_by_prio);

		return $info;
	}
}

