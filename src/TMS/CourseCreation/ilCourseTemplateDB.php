<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Naive fetching of the course template infos using ILIAS.
 */
class ilCourseTemplateDB implements CourseTemplateDB {
	use CourseAccessExtension;

	const REPOSITORY_REF_ID = 1;

	/**
	 * @var	\ilTree
	 */
	protected $tree;

	public function __construct(\ilTree $tree) {
		$this->tree = $tree;
	}

	/**
	 * @inheritdocs
	 */
	public function getCreatableCourseTemplates($user_id) {
		assert('is_int($user_id)');

		$node = $this->tree->getNodeData(self::REPOSITORY_REF_ID);

		$copy_setting_nodes = $this->tree->getSubTree($node, false, ["xcps"]);
		$crs_template_info = [];

		foreach ($copy_setting_nodes as $cs_node) {
			$path = array_reverse($this->tree->getPathFull($cs_node));
			while($p = array_shift($path)) {
				if ($p["type"] == "crs") {
					if (!self::_mayUserCreateCourseFromTemplate($user_id, (int)$p["child"])) {
						break;
					}
					$cat = $this->getCategoryTitle($path);
					if (!isset($crs_template_info[$cat])) {
						$crs_template_info[$cat] = [];
					}
					$crs_template_info[$cat][] = new CourseTemplateInfo(
						$this->purgeTemplateInTitle($p["title"]),
						(int)$p["child"],
						$cat
					);
				}
			}
		}

		return $crs_template_info;
	}

	/**
	 * @param	array	$path	strange format from ilTree
	 * @return	string|null
	 */
	protected function getCategoryTitle(array $path) {
		// there need to be a least three elements in the path to
		// get the appropriate category:
		// "repo > cat 1 > cat 2 > crs" should yield "cat 1"
		if (count($path) < 3) {
			return null;
		}
		array_shift($path);
		$node = array_shift($path);
		return $node["title"];
	}

	/**
	 * @param	string	$title
	 * @return	string
	 */
	protected function purgeTemplateInTitle($title) {
		$matches = [];
		if (preg_match("/^[^:]*:(.*)$/", $title, $matches)) {
			return $matches[1];
		}
		return $title;
	}
}
