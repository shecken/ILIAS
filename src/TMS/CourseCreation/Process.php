<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Creates courses based on templates.
 */
class Process {
	/**
	 * Run the course creation process for a given course.
	 *
	 * @return	void
	 */
	public function run(Request $request) {
		// TODO: replace these by proper dependency injection
		global $DIC;
		$db = $DIC->database();
		$tree = $DIC->repositoryTree();

		// TODO: get this from somewhere
		$client_id = "tms52";

		// TODO: This should be comming from the request since
		// users will be able to place their course somewhere else
		// soon.
		$parent = $tree->getParentId($request->getCourseRefId());

		$res = $db->query(
			"SELECT DISTINCT c.child ref_id, od.type "
			." FROM tree p"
			." RIGHT JOIN tree c"
			."	ON LOCATE(CONCAT(p.path,'.'),c.path) = 1"
			."	AND c.tree = p.tree"
			." LEFT JOIN object_reference oref ON oref.ref_id = c.child"
			." LEFT JOIN object_data od ON od.obj_id = oref.obj_id"
			." WHERE p.child = ".$db->quote($request->getCourseRefId(), "integer")
		);

		// These are options that tell the cloning method, that every child of the
		// template should be cloned as well.
		// TODO: replace these by options from copy settings
		$options = array();
		while ($rec = $db->fetchAssoc($res)) {
			$options[$rec["ref_id"]] = array("type" => 2);
		}

		// TODO: Turn this into something testable
		$source = \ilObjectFactory::getInstanceByRefId($request->getCourseRefId());

		$source->cloneAllObject(
			$request->getSessionId(),
			$client_id,
			"crs",
			$parent,
			$request->getCourseRefId(),
			 $options,
			false,
			1
			// TODO: maybe reintroduce user parameter again? what was it good for?
			// TODO: maybe reintroduce timeout param to this method again
		);
	}
}

