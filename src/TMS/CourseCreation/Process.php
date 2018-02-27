<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS\CourseCreation;

/**
 * Creates courses based on templates.
 */
class Process {
	const WAIT_FOR_DB_TO_INCORPORATE_CHANGES_IN_S = 2;

	/**
	 * @var	\ilTree
	 */
	protected $tree;

	/**
	 * @var	\ilDBInterface
	 */
	protected $db;

	public function __construct(\ilTree $tree, \ilDBInterface $db) {
		$this->tree = $tree;
		$this->db = $db;
	}

	/**
	 * Run the course creation process for a given course.
	 *
	 * @return Request
	 */
	public function run(Request $request) {
		// TODO: get this from somewhere
		$client_id = "tms52";

		// TODO: This should be comming from the request since
		// users will be able to place their course somewhere else
		// soon.
		$parent = $this->tree->getParentId($request->getCourseRefId());

		// TODO: Turn this into something testable
		$source = \ilObjectFactory::getInstanceByRefId($request->getCourseRefId());

		$res = $source->cloneAllObject(
			$request->getSessionId(),
			$client_id,
			"crs",
			$parent,
			$request->getCourseRefId(),
			$this->getCopyWizardOptions($request),
			false,
			1,
			true
			// TODO: maybe reintroduce user parameter again? what was it good for?
			// TODO: maybe reintroduce timeout param to this method again
		);

		$ref_id = $res["ref_id"];
		$request = $request->withTargetRefIdAndFinishedTS((int)$ref_id, new \DateTime());

		sleep(self::WAIT_FOR_DB_TO_INCORPORATE_CHANGES_IN_S);

		$this->configureCopiedObjects($request);

		return $request;
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
			$options[(int)$sub] = ["type" => $request->getCopyOptionFor((int)$sub)];
		}
		return $options;
	}

	/**
	 * Configure copied objects.
	 *
	 * @param	Request $request
	 * @return	null
	 */
	protected function configureCopiedObjects(Request $request) {
		$target_ref_id = $request->getTargetRefId();
		assert('!is_null($target_ref_id)');

		$sub_nodes = array_merge(
			[$target_ref_id],
			$this->tree->getSubTreeIds($target_ref_id)
		);
		$mappings = $this->getCopyMappings($sub_nodes);
		foreach ($sub_nodes as $sub) {
			$configs = $request->getConfigurationFor($mappings[$sub]);
			if ($configs === null) {
				continue;
			}
			$object = $this->getObjectByRefId((int)$sub);
			assert('method_exists($object, "afterCourseCreation")');
			foreach($configs as $config) {
				$object->afterCourseCreation($config);
			}
		}
	}

	/**
	 * Get copy mappings for ref_ids, where target => source.
	 *
	 * @param	int[]	$ref_ids
	 * @return	array<int,int>
	 */
	protected function getCopyMappings(array $ref_ids) {
		$res = $this->db->query(
			"SELECT tgt.ref_id tgt_ref, src.ref_id src_ref ".
			"FROM object_reference tgt ".
			"JOIN copy_mappings mp ON tgt.obj_id = mp.obj_id ".
			"JOIN object_reference src ON mp.source_id = src.obj_id ".
			"WHERE ".$this->db->in("tgt.ref_id", $ref_ids, false, "integer")
		);
		$mappings = [];
		while ($row = $this->db->fetchAssoc($res)) {
			$mappings[(int)$row["tgt_ref"]] = (int)$row["src_ref"];
		}
		return $mappings;
	}

	/**
	 * Get an object for the given ref.
	 *
	 * @param	int		$ref_id
	 * @return	\ilObject
	 */
	protected function getObjectByRefId($ref_id) {
		assert('is_int($ref_id)');
		$object = \ilObjectFactory::getInstanceByRefId($ref_id);
		assert('$object instanceof \ilObject');
		return $object;
	}
}

