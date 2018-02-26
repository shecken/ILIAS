<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Creates courses based on templates.
 */
class Process {
	/**
	 * @var	\ilTree
	 */
	protected $tree;

	public function __construct(\ilTree $tree) {
		$this->tree = $tree;
	}

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

		// TODO: Turn this into something testable
		$source = \ilObjectFactory::getInstanceByRefId($request->getCourseRefId());

		$source->cloneAllObject(
			$request->getSessionId(),
			$client_id,
			"crs",
			$parent,
			$request->getCourseRefId(),
			$this->getCopyWizardOptions($request),
			false,
			1
			// TODO: maybe reintroduce user parameter again? what was it good for?
			// TODO: maybe reintroduce timeout param to this method again
		);
	}

	/**
	 * Get copy options for the ilCopyWizard from the request.
	 *
	 * @param Request	$request
	 * @return	array
	 */
	protected function getCopyWizardOptions(Request $request) {
		$sub_nodes = $this->tree->getSubTreeIds($request->getCourseRefId());
		$options = [];
		foreach ($sub_nodes as $sub) {
			$options[$sub] = $request->getCopyOptionFor($sub);
		}
		return $options;
	}
}

