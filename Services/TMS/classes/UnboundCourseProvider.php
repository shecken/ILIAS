<?php

use \CaT\Ente\ILIAS\UnboundProvider as Base;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\CourseInfo;
use \ILIAS\TMS\CourseInfoImpl;

class UnboundCourseProvider extends Base {
	/**
	 * @inheritdocs
	 */
	public function componentTypes() {
		return [CourseInfo::class];
	}

	/**
	 * Build the component(s) of the given type for the given object.
	 *
	 * @param   string    $component_type
	 * @param   Entity    $provider
	 * @return  Component[]
	 */
	public function buildComponentsOf($component_type, Entity $entity) {
		global $DIC;
		$lng = $DIC["lng"];
		$object = $entity->object();
		if ($component_type === CourseInfo::class) {
			return
				[ new CourseInfoImpl
					( $entity
					, $lng->txt("title")
					, $object->getTitle()
					, ""
					, 100
					, [CourseInfo::CONTEXT_SEARCH_SHORT_INFO]
					)
				];
		}
		throw new \InvalidArgumentException("Unexpected component type '$component_type'");
	}
}
