<?php

use \CaT\Ente\ILIAS\UnboundProvider as Base;
use \CaT\Ente\ILIAS\Entity;

class UnboundCourseProvider extends Base {
	/**
	 * @inheritdocs
	 */
	public function componentTypes() {
		return [];
	}

	/**
	 * Build the component(s) of the given type for the given object.
	 *
	 * @param   string    $component_type
	 * @param   Entity    $provider
	 * @return  Component[]
	 */
	public function buildComponentsOf($component_type, Entity $entity) {
		throw new \InvalidArgumentException("Unexpected component type '$component_type'");
	}
}
